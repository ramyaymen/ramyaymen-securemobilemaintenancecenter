<?php
header('X-Frame-Options: DENY');

header('X-Content-Type-Options: nosniff');

header('Referrer-Policy: no-referrer');

header('X-XSS-Protection: 1; mode=block');

$url = '';

if (isset($_GET['url'])) {
    $url = trim($_GET['url'], '/');
}

switch ($url) {
    case '':
        require 'views/home.php';
        break;

    case 'register':
        require 'views/auth/register.php';
        break;

    case 'login':
        require 'views/auth/login.php';
        break;

    case 'logout':
        require 'logout.php';
        break;

    case 'dashboard':
        require 'views/user/dashboard.php';
        break;

    case 'profile':
        require 'views/user/profile.php';
        break;

    case 'add-device':
        require 'views/user/add-device.php';
        break;

    case 'my-devices':
        require 'views/user/my-devices.php';
        break;

    case 'admin':
        require 'views/admin/dashboard.php';
        break;

    case 'admin/users':
        require 'views/admin/users.php';
        break;

    case 'admin/devices':
        require 'views/admin/devices.php';
        break;

    case 'admin/notes':
        require 'views/admin/notes.php';
        break;


    case 'edit-device':
        require 'views/user/edit-device.php';
        break;

    case 'delete-device':
        require 'views/user/delete-device.php';
        break;

    case 'admin/change-device-status':
        require 'views/admin/change-device-status.php';
        break;

    case 'add-repair-note':
        require 'views/admin/add-repair-note.php';
        break;


    default:
        require 'views/errors/404.php';
}