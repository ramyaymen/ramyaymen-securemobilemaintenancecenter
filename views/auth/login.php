<?php

require_once 'config/session.php';
require_once 'config/database.php';
require_once 'controllers/AuthController.php';

$auth_controller =
    new AuthController(
        $connection
    );

$auth_controller->login();

?>

<!DOCTYPE html>

<html lang="en">

<head>

 
    <meta charset="UTF-8">

    <title>Login</title>


</head>

<body>

    
    <h1>Login</h1>

    <form method="POST">

        <div>

            <label>Email</label>

            <br>

            <input type="email" name="email" required>

        </div>

        <br>

        <div>

            <label>Password</label>

            <br>

            <input type="password" name="password" required>

        </div>

        <br>

        <button type="submit">

            Login

        </button>

    </form>
  

</body>

</html>