<?php

class User
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function findUserByEmail($email)
    {
        $query =
            "
            SELECT *
            FROM users
            WHERE email = ?
        ";

        $statement =
            $this->connection->prepare($query);

        $statement->bind_param(
            "s",
            $email
        );

        $statement->execute();

        $result =
            $statement->get_result();

        return $result->fetch_assoc();
    }

    public function createUser(
        $full_name,
        $email,
        $phone,
        $hashed_password
    ) {
        $query =
            "
            INSERT INTO users
            (
                full_name,
                email,
                phone,
                password
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?
            )
        ";

        $statement =
            $this->connection->prepare($query);

        $statement->bind_param(
            "ssss",
            $full_name,
            $email,
            $phone,
            $hashed_password
        );

        return $statement->execute();
    }

    public function getAllUsers()
    {
        $query =
            "
            SELECT *
            FROM users
            ORDER BY id DESC
            ";


        return
            $this->connection->query($query);


    }

    public function getUsersCount()
    {
        $query =
            "
        SELECT COUNT(*) AS total_users
        FROM users
    ";

        $result =
            $this->connection->query($query);

        return
            $result->fetch_assoc();
    }
}