<?php

require_once __DIR__ . '/../../middleware/admin.php';

require_once __DIR__ . '/../../config/database.php';

require_once __DIR__ . '/../../models/Device.php';

$device_model =
    new Device($connection);

$device =
    $device_model
        ->getDeviceById(
            $_GET['id']
        );

if (!$device) {
    die('Device Not Found');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $device_model
        ->addRepairNote(
            $_POST['device_id'],
            trim($_POST['note'])
        );

    header(
        'Location: admin/devices'
    );

    exit();
}

?>

<!DOCTYPE html>

<html lang="en">

<head>


    <meta charset="UTF-8">

    <title>Add Repair Note</title>


</head>

<body>


    <h1>Add Repair Note</h1>

    <hr>

    <p>

        Device:

        <?= htmlspecialchars(
            $device['device_name']
        ); ?>

    </p>

    <form method="POST">

        <input type="hidden" name="device_id" value="<?= $device['id']; ?>">

        <textarea name="note" rows="5" cols="50" required></textarea>

        <br>
        <br>

        <button type="submit">

            Save Note

        </button>

    </form>


</body>

</html>