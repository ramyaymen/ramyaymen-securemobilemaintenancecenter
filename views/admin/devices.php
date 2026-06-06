<?php

require_once __DIR__ . '/../../middleware/admin.php';

require_once __DIR__ . '/../../config/database.php';

require_once __DIR__ . '/../../models/Device.php';

$device_model =
    new Device($connection);

$devices =
    $device_model->getAllDevices();

?>

<!DOCTYPE html>

<html lang="en">

<head>


    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Devices Management</title>


</head>

<body>


    <h1>

        Devices Management

    </h1>

    <hr>

    <a href="/SecureMobileMaintenanceCenter/admin">

        Back To Admin Dashboard

    </a>

    <br>
    <br>

    <table border="1" cellpadding="10">

        <tr>

            <th>ID</th>

            <th>Customer Name</th>

            <th>Device Name</th>

            <th>Device Model</th>

            <th>Problem Description</th>

            <th>Status</th>

            <th>Actions</th>

        </tr>

        <?php
        while (
            $device =
            $devices->fetch_assoc()
        ) {
            ?>

            <tr>

                <td>

                    <?= $device['id']; ?>

                </td>

                <td>

                    <?= htmlspecialchars(
                        $device['full_name']
                    ); ?>

                </td>

                <td>

                    <?= htmlspecialchars(
                        $device['device_name']
                    ); ?>

                </td>

                <td>

                    <?= htmlspecialchars(
                        $device['device_model']
                    ); ?>

                </td>

                <td>

                    <?= htmlspecialchars(
                        $device['problem_description']
                    ); ?>

                </td>

                <td>

                    <?= htmlspecialchars(
                        $device['status']
                    ); ?>

                </td>

                <td>

                    <a href="/SecureMobileMaintenanceCenter/admin/change-device-status?id=<?= $device['id']; ?>">

                        Change Status

                    </a>

                    |

                    <a href="/SecureMobileMaintenanceCenter/admin/add-repair-note?id=<?= $device['id']; ?>">

                        Add Note

                    </a>

                </td>

            </tr>

            <?php
        }
        ?>

    </table>


</body>

</html>