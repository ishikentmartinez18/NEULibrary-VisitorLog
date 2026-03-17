<?php
// ── api.php ── JSON REST-style endpoint ──────────────────────────────────────
session_start();
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/db.php';

$action = $_REQUEST['action'] ?? '';

switch ($action) {

    // ── VISITOR: check blocked + log visit ───────────────────────────────────
    case 'visitor_login':
        $rfid    = trim($_POST['rfid']    ?? '');
        $reason  = trim($_POST['reason']  ?? '');
        $program = trim($_POST['program'] ?? '');
        $name    = trim($_POST['name']    ?? '');

        if (!$rfid || !$reason || !$program || !$name) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit;
        }

        $db = getDB();

        $stmt = $db->prepare('SELECT id FROM blocked_visitors WHERE rfid = ?');
        $stmt->execute([$rfid]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'blocked' => true, 'message' => 'You are not allowed to use the library at this time.']);
            exit;
        }

        $stmt = $db->prepare('INSERT INTO visit_log (name, rfid, program, reason, timestamp) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([$name, $rfid, $program, $reason]);
        $id = $db->lastInsertId();

        $stmt = $db->prepare('SELECT * FROM visit_log WHERE id = ?');
        $stmt->execute([$id]);
        $entry = $stmt->fetch();

        echo json_encode(['success' => true, 'entry' => $entry]);
        break;

    // ── ADMIN: login ─────────────────────────────────────────────────────────
    case 'admin_login':
        $user = trim($_POST['username'] ?? '');
        $pass = trim($_POST['password'] ?? '');

        if (!$user || !$pass) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
            exit;
        }

        // Only requirement: email must end with @neu.admin.ph
        // Password can be anything — no database check needed
        if (!str_ends_with($user, '@neu.admin.ph')) {
            echo json_encode(['success' => false, 'message' => 'Email must end with @neu.admin.ph']);
            exit;
        }

        $_SESSION['admin_id']   = 1;
        $_SESSION['admin_user'] = $user;
        echo json_encode(['success' => true]);
        break;

    // ── ADMIN: logout ─────────────────────────────────────────────────────────
    case 'admin_logout':
        session_destroy();
        echo json_encode(['success' => true]);
        break;

    // ── ADMIN: get visit log (with filters) ───────────────────────────────────
    case 'get_log':
        requireAdmin();
        $db = getDB();

        $q      = '%' . trim($_GET['q']      ?? '') . '%';
        $reason = trim($_GET['reason'] ?? '');
        $from   = trim($_GET['from']   ?? '');
        $to     = trim($_GET['to']     ?? '');

        $sql    = 'SELECT * FROM visit_log WHERE (name LIKE ? OR rfid LIKE ? OR program LIKE ? OR reason LIKE ?)';
        $params = [$q, $q, $q, $q];

        if ($reason) { $sql .= ' AND reason = ?'; $params[] = $reason; }
        if ($from)   { $sql .= ' AND DATE(timestamp) >= ?'; $params[] = $from; }
        if ($to)     { $sql .= ' AND DATE(timestamp) <= ?'; $params[] = $to; }

        $sql .= ' ORDER BY timestamp DESC';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── ADMIN: stats ─────────────────────────────────────────────────────────
    case 'get_stats':
        requireAdmin();
        $db   = getDB();
        $data = [];

        $data['total'] = $db->query('SELECT COUNT(*) FROM visit_log')->fetchColumn();
        $data['today'] = $db->query("SELECT COUNT(*) FROM visit_log WHERE DATE(timestamp) = CURDATE()")->fetchColumn();
        $data['week']  = $db->query("SELECT COUNT(*) FROM visit_log WHERE timestamp >= NOW() - INTERVAL 7 DAY")->fetchColumn();
        $data['month'] = $db->query("SELECT COUNT(*) FROM visit_log WHERE YEAR(timestamp)=YEAR(NOW()) AND MONTH(timestamp)=MONTH(NOW())")->fetchColumn();

        echo json_encode(['success' => true, 'data' => $data]);
        break;

    // ── ADMIN: visitor summary ────────────────────────────────────────────────
    case 'get_visitors':
        requireAdmin();
        $db = getDB();
        $q  = '%' . trim($_GET['q'] ?? '') . '%';

        $sql = 'SELECT rfid, name, program, COUNT(*) AS visit_count, MAX(timestamp) AS last_visit
                FROM visit_log
                WHERE name LIKE ? OR rfid LIKE ? OR program LIKE ?
                GROUP BY rfid, name, program
                ORDER BY last_visit DESC';
        $stmt = $db->prepare($sql);
        $stmt->execute([$q, $q, $q]);
        $rows = $stmt->fetchAll();

        $blockedRfids = $db->query('SELECT rfid FROM blocked_visitors')->fetchAll(PDO::FETCH_COLUMN);
        $blockedSet   = array_flip($blockedRfids);
        foreach ($rows as &$row) {
            $row['is_blocked'] = isset($blockedSet[$row['rfid']]) ? 1 : 0;
        }

        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── ADMIN: get blocked list ───────────────────────────────────────────────
    case 'get_blocked':
        requireAdmin();
        $db   = getDB();
        $rows = $db->query('SELECT * FROM blocked_visitors ORDER BY blocked_at DESC')->fetchAll();
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    // ── ADMIN: block visitor ─────────────────────────────────────────────────
    case 'block_visitor':
        requireAdmin();
        $rfid    = trim($_POST['rfid']    ?? '');
        $name    = trim($_POST['name']    ?? '');
        $program = trim($_POST['program'] ?? '');

        if (!$rfid) { echo json_encode(['success' => false, 'message' => 'RFID required.']); exit; }

        $db   = getDB();
        $stmt = $db->prepare('INSERT IGNORE INTO blocked_visitors (rfid, name, program, block_reason) VALUES (?, ?, ?, ?)');
        $stmt->execute([$rfid, $name, $program, 'Admin restricted']);
        echo json_encode(['success' => true]);
        break;

    // ── ADMIN: unblock visitor ────────────────────────────────────────────────
    case 'unblock_visitor':
        requireAdmin();
        $rfid = trim($_POST['rfid'] ?? '');
        if (!$rfid) { echo json_encode(['success' => false, 'message' => 'RFID required.']); exit; }

        $db   = getDB();
        $stmt = $db->prepare('DELETE FROM blocked_visitors WHERE rfid = ?');
        $stmt->execute([$rfid]);
        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}

function requireAdmin(): void {
    if (empty($_SESSION['admin_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
        exit;
    }
}