<?php

require_once __DIR__ . '/../../middleware/auth.php';

require_once __DIR__ . '/../../config/database.php';

require_once __DIR__ . '/../../models/Device.php';

$device_model =
    new Device($connection);

$device =
    $device_model
        ->getDeviceByIdAndUserId(
            $_GET['id'],
            $_SESSION['user_id']
        );

if (!$device) {
    die('Access Denied');
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $device_model
        ->updateDevice(
            $_POST['device_id'],
            $_POST['device_name'],
            $_POST['device_model'],
            $_POST['problem_description']
        );

    header('Location: my-devices');

    exit();
}

?>

<h1>Edit Device</h1>

<form method="POST">


    <input type="hidden" name="device_id" value="<?= $device['id']; ?>">

    <input type="text" name="device_name" value="<?= htmlspecialchars($device['device_name']); ?>">

    <br><br>

    <input type="text" name="device_model" value="<?= htmlspecialchars($device['device_model']); ?>">

    <br><br>

    <textarea name="problem_description"><?= htmlspecialchars($device['problem_description']); ?></textarea>

    <br><br>

    <button type="submit">

        Update Device

    </button>


</form>