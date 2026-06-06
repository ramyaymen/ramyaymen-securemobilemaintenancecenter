<?php

require_once 'config/database.php';

require_once 'controllers/AuthController.php';

$auth_controller =
    new AuthController(
        $connection
    );

$auth_controller->register();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register</title>

</head>

<body>

    <h1>Create Account</h1>

    <form action="" method="POST">

        <div>

            <label>
                Full Name
            </label>

            <br>

            <input type="text" name="full_name" required>

        </div>

        <br>

        <div>

            <label>
                Email
            </label>

            <br>

            <input type="email" name="email" required>

        </div>

        <br>

        <div>

            <label>
                Phone Number
            </label>

            <br>

            <input type="text" name="phone" required>

        </div>

        <br>

        <div>

            <label>
                Password
            </label>

            <br>

            <input type="password" name="password" required>

        </div>

        <br>

        <button type="submit">

            Register

        </button>

    </form>

</body>

</html>