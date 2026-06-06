<?php

require_once 'middleware/auth.php';

?>

<!DOCTYPE html>

<html lang="en">

<head>


<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

<title>User Dashboard</title>


</head>

<body>


<h1>

    Secure Mobile Maintenance System

</h1>

<hr>

<h2>

    Welcome

    <?=
    htmlspecialchars(
        $_SESSION['full_name']
    );
    ?>

</h2>

<p>

    Role:

    <?=
    htmlspecialchars(
        $_SESSION['role']
    );
    ?>

</p>

<hr>

<h3>

    User Menu

</h3>

<ul>

    <li>

        <a href="profile">

            My Profile

        </a>

    </li>

    <li>

        <a href="add-device">

            Add Device

        </a>

    </li>

    <li>

        <a href="my-devices">

            My Devices

        </a>

    </li>

    <li>

        <a href="logout">

            Logout

        </a>

    </li>

</ul>

<hr>

<h3>

    System Status

</h3>



</body>

</html>
