<?php

require_once __DIR__ . '/../middlewares/AdminMiddleware.php';

class AdminController
{
    public function index()
    {
        AdminMiddleware::handle();

        $db = Database::connect();

        //  Search — column + keyword
        $search = trim($_GET['search'] ?? '');
        $searchCol = $_GET['search_col'] ?? 'all';

        $allowedCols = ['id', 'email', 'status', 'payment_status', 'payment_id', 'fastboost_order_id'];
        if (!in_array($searchCol, $allowedCols)) {
            $searchCol = 'all';
        }

        // Build WHERE for orders
        $where = '';
        $params = [];

        if ($search !== '') {
            if ($searchCol === 'all') {
                $where = "WHERE (o.id LIKE ? OR o.email LIKE ? OR o.status LIKE ?
                               OR o.payment_status LIKE ? OR p.payment_id LIKE ?
                               OR o.fastboost_order_id LIKE ?)";
                $like = "%$search%";
                $params = [$like, $like, $like, $like, $like, $like];
            } elseif (in_array($searchCol, ['id', 'status', 'payment_status', 'fastboost_order_id'])) {
                $where = "WHERE o.$searchCol LIKE ?";
                $params = ["%$search%"];
            } elseif ($searchCol === 'payment_id') {
                $where = "WHERE p.payment_id LIKE ?";
                $params = ["%$search%"];
            } else {
                $where = "WHERE o.$searchCol LIKE ?";
                $params = ["%$search%"];
            }
        }

        //  Orders pagination
        $limit = 10;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;

        $stmt = $db->prepare("
            SELECT o.*,
                   p.payment_id,
                   o.fastboost_order_id
            FROM orders o
            LEFT JOIN payments p ON p.order_id = o.id
            $where
            ORDER BY o.id DESC
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $db->prepare("
            SELECT COUNT(*)
            FROM orders o
            LEFT JOIN payments p ON p.order_id = o.id
            $where
        ");
        $countStmt->execute($params);
        $totalOrders = $countStmt->fetchColumn();
        $totalPages = max(1, (int) ceil($totalOrders / $limit));

        //  Payments
        $payments = $db->query("
            SELECT * FROM payments
            ORDER BY id DESC
            LIMIT 20
        ")->fetchAll(PDO::FETCH_ASSOC);

        //  Users
        $users = $db->query("
            SELECT id, name, email, role, is_active
            FROM users
            ORDER BY id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);


        $logSearch = trim($_GET['log_search'] ?? '');
        $apiLogSearch = trim($_GET['api_log_search'] ?? '');


        // AUTH LOGS
        $logLimit = 10;
        $logPage = max(1, (int) ($_GET['log_page'] ?? 1));
        $logOffset = ($logPage - 1) * $logLimit;

        $logWhere = "WHERE logs.type = 'auth'";
        $logParams = [];

        if ($logSearch !== '') {

            $like = "%{$logSearch}%";

            $logWhere .= "
        AND (
            logs.id LIKE ?
            OR logs.type LIKE ?
            OR logs.action LIKE ?
            OR logs.message LIKE ?
            OR logs.data LIKE ?
            OR logs.order_id LIKE ?
            OR users.email LIKE ?
        )
    ";

            $logParams = [
                $like,
                $like,
                $like,
                $like,
                $like,
                $like,
                $like
            ];
        }

        $countStmt = $db->prepare("
    SELECT COUNT(*)
    FROM logs
    LEFT JOIN users ON logs.user_id = users.id
    $logWhere
");

        $countStmt->execute($logParams);

        $totalLogs = $countStmt->fetchColumn();

        $totalLogPages = max(1, ceil($totalLogs / $logLimit));

        $stmt = $db->prepare("
    SELECT logs.*, users.email
    FROM logs
    LEFT JOIN users ON logs.user_id = users.id
    $logWhere
    ORDER BY logs.id DESC
    LIMIT $logLimit OFFSET $logOffset
");

        $stmt->execute($logParams);

        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // SYSTEM / API LOGS
        $apiLogLimit = 10;
        $apiLogPage = max(1, (int) ($_GET['api_log_page'] ?? 1));
        $apiLogOffset = ($apiLogPage - 1) * $apiLogLimit;

        $totalApiLogsStmt = $db->query("
    SELECT COUNT(*)
    FROM logs
    WHERE type <> 'auth'
");
        $totalApiLogs = $totalApiLogsStmt->fetchColumn();

        $totalApiLogPages = max(1, (int) ceil($totalApiLogs / $apiLogLimit));

        $apiLogs = $db->query("
    SELECT logs.*, users.email
    FROM logs
    LEFT JOIN users ON logs.user_id = users.id
    WHERE logs.type <> 'auth'
    ORDER BY logs.id DESC
    LIMIT $apiLogLimit OFFSET $apiLogOffset
")->fetchAll(PDO::FETCH_ASSOC);

        //  Coupons
        $coupons = $db->query("
            SELECT * FROM coupons
            ORDER BY id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        //  Settings
        $settings = $db->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../views/admin.php';
    }

    public function updateRole()
    {
        AdminMiddleware::handle();
        $db = Database::connect();

        $db->prepare("UPDATE users SET role=? WHERE id=?")
            ->execute([$_POST['role'], $_POST['user_id']]);

        header("Location: " . url('admin'));
    }

    public function updateSetting()
    {
        AdminMiddleware::handle();
        $db = Database::connect();

        $db->prepare("UPDATE settings SET value=? WHERE `key`=?")
            ->execute([$_POST['value'], $_POST['key']]);

        header("Location: " . url('admin'));
    }

    public function deleteUser()
    {
        AdminMiddleware::handle();
        $db = Database::connect();

        if ($_POST['id'] == $_SESSION['user']['id']) {
            die("You can't delete yourself");
        }

        $db->prepare("DELETE FROM users WHERE id=?")->execute([$_POST['id']]);

        header("Location: " . url('admin'));
    }

    public function deleteOrder()
    {
        AdminMiddleware::handle();
        $db = Database::connect();

        $db->prepare("DELETE FROM orders WHERE id=?")->execute([$_POST['id']]);

        header("Location: " . url('admin'));
    }

    public function deleteLog()
    {
        AdminMiddleware::handle();
        $db = Database::connect();

        $db->prepare("DELETE FROM logs WHERE id=?")->execute([$_POST['id']]);

        header("Location: " . url('admin'));
    }

    public function sendBoosts()
    {
        AdminMiddleware::handle();

        if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
            http_response_code(403);
            exit("Invalid CSRF");
        }

        $db = Database::connect();

        $orderId = (int) ($_POST['id'] ?? 0);
        if ($orderId <= 0) {
            die("Invalid order");
        }

        // get order
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            die("Order not found");
        }
        if (strtolower($order['status']) === 'pending') {
            die("Order still pending");
        }

        if ($order['status'] === 'processing') {
            die("Already processing");
        }

        if ($order['status'] === 'completed') {
            die("Already completed");
        }

        try {
            require_once __DIR__ . '/../services/BoostService.php';

            \App\Services\BoostService::execute($orderId, true, true);

            log_event('admin', 'manual_boost', "Boost sent manually for order #$orderId");

        } catch (Exception $e) {

            $db->prepare("
        UPDATE orders 
        SET status = 'failed', failure_reason = ?
        WHERE id = ?
    ")->execute([$e->getMessage(), $orderId]);

            log_event('admin', 'manual_boost_error', $e->getMessage());
        }

        header("Location: " . url('admin'));
    }
}
