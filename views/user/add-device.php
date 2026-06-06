<?php

require_once __DIR__ . '/../../middleware/auth.php';

require_once __DIR__ . '/../../config/database.php';

require_once __DIR__ . '/../../controllers/DeviceController.php';

$device_controller =
    new DeviceController(
        $connection
    );

$device_controller->addDevice();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    
    <meta charset="UTF-8">

    <title>Add Device</title>
    

</head>

<body>

    
    <h1>Add Device</h1>

    <hr>

    <form method="POST">

        <div>

            <label>

                Device Name

            </label>

            <br>

            <input type="text" name="device_name" required>

        </div>

        <br>

        <div>

            <label>

                Device Model

            </label>

            <br>

            <input type="text" name="device_model" required>

        </div>

        <br>

        <div>

            <label>

                Problem Description

            </label>

            <br>

            <textarea name="problem_description" rows="5" cols="40" required></textarea>

        </div>

        <br>

        <button type="submit">

            Submit Device

        </button>

    </form>

    <br>

    <a href="dashboard">

        Back To Dashboard

    </a>
    

</body>

</html>