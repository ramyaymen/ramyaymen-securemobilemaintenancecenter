<?php

class Device
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function createDevice(
        $user_id,
        $device_name,
        $device_model,
        $problem_description
    ) {
        $query =
            "
            INSERT INTO devices
            (
                user_id,
                device_name,
                device_model,
                problem_description
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
            "isss",
            $user_id,
            $device_name,
            $device_model,
            $problem_description
        );

        return $statement->execute();
    }

    public function getDevicesByUserId($user_id)
    {
        $query =
            "
                SELECT *
                FROM devices
                WHERE user_id = ?
                ORDER BY id DESC
                ";


        $statement =
            $this->connection->prepare($query);

        $statement->bind_param(
            "i",
            $user_id
        );

        $statement->execute();

        $result =
            $statement->get_result();

        return $result;


    }

    public function getDeviceById($device_id)
    {
        $query =
            "
            SELECT *
            FROM devices
            WHERE id = ?
            ";

        $statement =
            $this->connection->prepare($query);

        $statement->bind_param(
            "i",
            $device_id
        );

        $statement->execute();

        $result =
            $statement->get_result();

        return $result->fetch_assoc();

    }

    public function updateDevice(
        $device_id,
        $device_name,
        $device_model,
        $problem_description
    ) {
        $query =
            "
            UPDATE devices
            SET
            device_name = ?,
            device_model = ?,
            problem_description = ?
            WHERE id = ?
            ";


        $statement =
            $this->connection->prepare($query);

        $statement->bind_param(
            "sssi",
            $device_name,
            $device_model,
            $problem_description,
            $device_id
        );

        return $statement->execute();


    }

    public function deleteDevice(
        $device_id
    ) {
        $query =
            "
            DELETE FROM devices
            WHERE id = ?
            ";

        $statement =
            $this->connection->prepare($query);

        $statement->bind_param(
            "i",
            $device_id
        );

        return $statement->execute();

    }

    public function getDeviceByIdAndUserId(
        $device_id,
        $user_id
    ) {
        $query =
            "
            SELECT *
            FROM devices
            WHERE id = ?
            AND user_id = ?
            ";


        $statement =
            $this->connection->prepare($query);

        $statement->bind_param(
            "ii",
            $device_id,
            $user_id
        );

        $statement->execute();

        $result =
            $statement->get_result();

        return $result->fetch_assoc();


    }

    public function getAllDevices()
    {
        $query =
            "
            SELECT
            devices.*,
            users.full_name
            FROM devices


                INNER JOIN users
                ON users.id = devices.user_id

                ORDER BY devices.id DESC
            ";

        return
            $this->connection->query($query);


    }

    public function updateDeviceStatus(
        $device_id,
        $status
    ) {
        $query =
            "
            UPDATE devices
            SET status = ?
            WHERE id = ?
            ";


        $statement =
            $this->connection->prepare($query);

        $statement->bind_param(
            "si",
            $status,
            $device_id
        );

        return $statement->execute();


    }


    public function addRepairNote(
        $device_id,
        $note
    ) {
        $query =
            "
            INSERT INTO repair_notes
            (
            device_id,
            note
            )
            VALUES
            (
            ?,
            ?
            )
            ";


        $statement =
            $this->connection->prepare($query);

        $statement->bind_param(
            "is",
            $device_id,
            $note
        );

        return $statement->execute();


    }

    public function getRepairNotes(
        $device_id
    ) {
        $query =
            "
            SELECT *
            FROM repair_notes
            WHERE device_id = ?
            ORDER BY id DESC
            ";


        $statement =
            $this->connection->prepare($query);

        $statement->bind_param(
            "i",
            $device_id
        );

        $statement->execute();

        return
            $statement
                ->get_result();


    }

    public function getDevicesCount()
    {
        $query =
            "
        SELECT COUNT(*) AS total_devices
        FROM devices
    ";

        $result =
            $this->connection->query($query);

        return
            $result->fetch_assoc();
    }


    public function getDevicesCountByStatus(
        $status
    ) {
        $query =
            "
        SELECT COUNT(*) AS total
        FROM devices
        WHERE status = ?
    ";

        $statement =
            $this->connection->prepare($query);

        $statement->bind_param(
            "s",
            $status
        );

        $statement->execute();

        $result =
            $statement->get_result();

        return
            $result->fetch_assoc();
    }
}
