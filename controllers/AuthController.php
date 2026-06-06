<?php

require_once 'config/encryption.php';
require_once 'models/User.php';

class AuthController
{
    private $user_model;

    public function __construct($connection)
    {
        $this->user_model =
            new User($connection);
    }

    public function register()
    {
        if (
            $_SERVER['REQUEST_METHOD']
            !==
            'POST'
        ) {
            return;
        }

        $full_name =
            trim($_POST['full_name']);

        $email =
            trim($_POST['email']);

        $phone =
            trim($_POST['phone']);

        $password =
            $_POST['password'];

        if (
            empty($full_name)
            ||
            empty($email)
            ||
            empty($phone)
            ||
            empty($password)
        ) {
            die('All fields are required');
        }

        if (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            die('Invalid Email');
        }
        if (
            !preg_match(
                '/^01[0-2,5][0-9]{8}$/',
                $phone
            )
        ) {
            die('Invalid Phone Number');
        }

        $existing_user =
            $this->user_model
                ->findUserByEmail($email);

        if ($existing_user) {
            die('Email Already Exists');
        }

        $hashed_password =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );

        $encrypted_phone =
            encryptData($phone);
        
        $created =
            $this->user_model
                ->createUser(
                    $full_name,
                    $email,
                    $encrypted_phone,
                    $hashed_password
                );

        if ($created) {
            header('Location: login');
            exit();
        }

        die('Registration Failed');
    }

    public function login()
    {
        if (
            $_SERVER['REQUEST_METHOD']
            !==
            'POST'
        ) {
            return;
        }


        $email =
            trim($_POST['email']);

        $password =
            $_POST['password'];

        if (
            empty($email)
            ||
            empty($password)
        ) {
            die('All fields are required');
        }

        $user =
            $this->user_model
                ->findUserByEmail($email);

        if (!$user) {
            die('Invalid Email Or Password');
        }

        if (
            !password_verify(
                $password,
                $user['password']
            )
        ) {
            die('Invalid Email Or Password');
        }
        session_regenerate_id(true);

        $_SESSION['user_id']
            =
            $user['id'];

        $_SESSION['full_name']
            =
            $user['full_name'];

        $_SESSION['role']
            =
            $user['role'];


        if (
            $user['role']
            ===
            'admin'
        ) {
            header(
                'Location: admin'
            );
        } else {
            header(
                'Location: dashboard'
            );
        }

        exit();



    }

}
