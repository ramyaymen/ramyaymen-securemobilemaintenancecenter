<?php

require_once 'middleware/auth.php';

?>

<!DOCTYPE html>

<html lang="en">

<head>


<meta charset="UTF-8">

<title>Profile</title>


</head>

<body>


<h1>My Profile</h1>

<hr>

<p>

    Name:

    <?= $_SESSION['full_name']; ?>

</p>

<p>

    Role:

    <?= $_SESSION['role']; ?>

</p>

<a href="dashboard">

    Back To Dashboard

</a>


</body>

</html>
