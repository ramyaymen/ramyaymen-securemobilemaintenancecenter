<?php

require_once __DIR__ . '/../../middleware/admin.php';

require_once __DIR__ . '/../../config/database.php';

require_once __DIR__ . '/../../models/User.php';

require_once __DIR__ . '/../../models/Device.php';

$user_model =
    new User($connection);

$device_model =
    new Device($connection);

$total_users =
    $user_model
        ->getUsersCount();

$total_devices =
    $device_model
        ->getDevicesCount();

$pending_devices =
    $device_model
        ->getDevicesCountByStatus(
            'Pending'
        );

$in_progress_devices =
    $device_model
        ->getDevicesCountByStatus(
            'In Progress'
        );

$completed_devices =
    $device_model
        ->getDevicesCountByStatus(
            'Completed'
        );

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>

        Admin Dashboard

    </title>

</head>

<body>

    <h1>

        Admin Dashboard

    </h1>

    <hr>

    <h3>

        Welcome

        <?= htmlspecialchars(
            $_SESSION['full_name']
        ); ?>

    </h3>

    <hr>

    <p>

        Total Users:

        <?= $total_users['total_users']; ?>

    </p>

    <p>

        Total Devices:

        <?= $total_devices['total_devices']; ?>

    </p>

    <p>

        Pending Devices:

        <?= $pending_devices['total']; ?>

    </p>

    <p>

        In Progress Devices:

        <?= $in_progress_devices['total']; ?>

    </p>

    <p>

        Completed Devices:

        <?= $completed_devices['total']; ?>

    </p>

    <hr>

    <ul>

        <li>

            <a href="/SecureMobileMaintenanceCenter/admin/users">

                Manage Users

            </a>

        </li>

        <li>

            <a href="/SecureMobileMaintenanceCenter/admin/devices">

                Manage Devices

            </a>

        </li>

        <li>

            <a href="/SecureMobileMaintenanceCenter/logout">

                Logout

            </a>

        </li>

    </ul>

</body>

</html>