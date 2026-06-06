<?php

require_once __DIR__ . '/../../middleware/auth.php';

require_once __DIR__ . '/../../config/database.php';

require_once __DIR__ . '/../../models/Device.php';

$device_model =
    new Device($connection);

if (!isset($_GET['id'])) {
    header('Location: my-devices');
    exit();
}

$device =
    $device_model
        ->getDeviceByIdAndUserId(
            $_GET['id'],
            $_SESSION['user_id']
        );

if (!$device) {
    die('Access Denied');
}

$device_model
    ->deleteDevice(
        $_GET['id']
    );

header('Location: my-devices');

exit();
