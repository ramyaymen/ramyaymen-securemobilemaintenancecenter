<?php

require_once __DIR__ . '/../../middleware/admin.php';

require_once __DIR__ . '/../../config/database.php';

require_once __DIR__ . '/../../models/User.php';

$user_model =
    new User($connection);

$users =
    $user_model->getAllUsers();

?>

<h1>Users Management</h1>

<hr>

<table border="1">

    <tr>


        <th>ID</th>

        <th>Name</th>

        <th>Email</th>

        <th>Role</th>


    </tr>

    <?php
    while (
        $user =
        $users->fetch_assoc()
    ) {
        ?>

        <tr>


            <td><?= $user['id']; ?></td>

            <td><?= htmlspecialchars($user['full_name']); ?></td>

            <td><?= htmlspecialchars($user['email']); ?></td>

            <td><?= htmlspecialchars($user['role']); ?></td>


        </tr>

        <?php
    }
    ?>

</table>