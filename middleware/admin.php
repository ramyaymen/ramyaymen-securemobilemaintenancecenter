<?php

require_once __DIR__ . '/../config/session.php';

if (
    !isset($_SESSION['user_id'])
)
{
    header('Location: login');
    exit();
}

if (
    $_SESSION['role']
    !==
    'admin'
)
{
    die('Access Denied');
}
