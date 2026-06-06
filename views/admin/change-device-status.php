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
        ->updateDeviceStatus(
            $_POST['device_id'],
            $_POST['status']
        );

    header(
        'Location: devices'
    );

    exit();
}

?>

<!DOCTYPE html>

<html lang="en">

<head>


    <meta charset="UTF-8">

    <title>Change Status</title>


</head>

<body>


    <h1>Change Device Status</h1>

    <hr>

    <form method="POST">

        <input type="hidden" name="device_id" value="<?= $device['id']; ?>">

        <p>

            Device:

            <?= htmlspecialchars(
                $device['device_name']
            ); ?>

        </p>

        <br>

        <select name="status">

            <option value="Pending">

                Pending

            </option>

            <option value="In Progress">

                In Progress

            </option>

            <option value="Completed">

                Completed

            </option>

        </select>

        <br>
        <br>

        <button type="submit">

            Update Status

        </button>

    </form>


</body>

</html>