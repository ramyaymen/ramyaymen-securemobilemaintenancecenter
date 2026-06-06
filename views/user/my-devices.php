<?php

require_once __DIR__ . '/../../middleware/auth.php';

require_once __DIR__ . '/../../config/database.php';

require_once __DIR__ . '/../../controllers/DeviceController.php';

$device_controller =
    new DeviceController(
        $connection
    );

$devices =
    $device_controller
        ->getMyDevices();

?>

<!DOCTYPE html>

<html lang="en">

<head>


    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Devices</title>


</head>

<body>


    <h1>

        My Devices

    </h1>

    <hr>

    <a href="/SecureMobileMaintenanceCenter/dashboard">

        Back To Dashboard

    </a>

    <br>
    <br>

    <a href="/SecureMobileMaintenanceCenter/add-device">

        Add New Device

    </a>

    <br>
    <br>

    <table border="1" cellpadding="10">

        <tr>

            <th>ID</th>

            <th>Device Name</th>

            <th>Device Model</th>

            <th>Problem Description</th>

            <th>Status</th>

            <th>Repair Notes</th>

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

                    <?php

                    $notes =
                        $device_controller
                            ->getRepairNotes(
                                $device['id']
                            );

                    while (
                        $note =
                        $notes->fetch_assoc()
                    ) {
                        ?>

                        <p>

                            <?= htmlspecialchars(
                                $note['note']
                            ); ?>

                        </p>

                        <?php
                    }
                    ?>

                </td>

                <td>

                    <a href="/SecureMobileMaintenanceCenter/edit-device?id=<?= $device['id']; ?>">

                        Edit

                    </a>

                    |

                    <a href="/SecureMobileMaintenanceCenter/delete-device?id=<?= $device['id']; ?>"
                        onclick="return confirm('Are you sure?')">

                        Delete

                    </a>

                </td>

            </tr>

            <?php
        }
        ?>

    </table>


</body>

</html>