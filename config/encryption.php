<?php

define(
    'ENCRYPTION_KEY',
    'secure_mobile_center_2026_secret_key'
);

define(
    'ENCRYPTION_METHOD',
    'AES-256-CBC'
);

function encryptData($data)
{
    $initialization_vector =
    openssl_random_pseudo_bytes(16);

    $encrypted_data =
    openssl_encrypt(
        $data,
        ENCRYPTION_METHOD,
        ENCRYPTION_KEY,
        0,
        $initialization_vector
    );

    return base64_encode(
        $initialization_vector .
        $encrypted_data
    );
}

function decryptData($encrypted_data)
{
    $decoded_data =
    base64_decode($encrypted_data);

    $initialization_vector =
    substr(
        $decoded_data,
        0,
        16
    );

    $encrypted_text =
    substr(
        $decoded_data,
        16
    );

    return openssl_decrypt(
        $encrypted_text,
        ENCRYPTION_METHOD,
        ENCRYPTION_KEY,
        0,
        $initialization_vector
    );
}