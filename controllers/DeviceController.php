<?php

require_once 'models/Device.php';

class DeviceController
{
    private $device_model;

    public function __construct($connection)
    {
        $this->device_model =
            new Device($connection);
    }

    public function addDevice()
    {
        if (
            $_SERVER['REQUEST_METHOD']
            !==
            'POST'
        ) {
            return;
        }

        $device_name =
            trim($_POST['device_name']);

        $device_model =
            trim($_POST['device_model']);

        $problem_description =
            trim($_POST['problem_description']);

        if (
            empty($device_name)
            ||
            empty($device_model)
            ||
            empty($problem_description)
        ) {
            die('All fields are required');
        }
        if (
            strlen($device_name) > 100
        ) {
            die(
                'Device Name Is Too Long'
            );
        }

        if (
            strlen($device_model) > 100
        ) {
            die(
                'Device Model Is Too Long'
            );
        }
        $user_id =
            $_SESSION['user_id'];

        $created =
            $this->device_model
                ->createDevice(
                    $user_id,
                    $device_name,
                    $device_model,
                    $problem_description
                );

        if ($created) {
            header('Location: my-devices');
            exit();
        }

        die('Failed To Add Device');
    }
    public function getMyDevices()
    {
        $user_id =
            $_SESSION['user_id'];


        return
            $this->device_model
                ->getDevicesByUserId(
                    $user_id
                );


    }

    public function updateDevice()
    {
        if (
            $_SERVER['REQUEST_METHOD']
            !==
            'POST'
        ) {
            return;
        }


        $device_id =
            $_POST['device_id'];

        $device_name =
            trim($_POST['device_name']);

        $device_model =
            trim($_POST['device_model']);

        $problem_description =
            trim($_POST['problem_description']);

        $updated =
            $this->device_model
                ->updateDevice(
                    $device_id,
                    $device_name,
                    $device_model,
                    $problem_description
                );

        if ($updated) {
            header('Location: my-devices');
            exit();
        }


    }

    public function deleteDevice(
        $device_id
    ) {
        $this->device_model
            ->deleteDevice(
                $device_id
            );


        header('Location: my-devices');

        exit();


    }

    public function changeDeviceStatus()
    {
        if (
            $_SERVER['REQUEST_METHOD']
            !==
            'POST'
        ) {
            return;
        }


        $device_id =
            $_POST['device_id'];

        $status =
            $_POST['status'];

        $updated =
            $this->device_model
                ->updateDeviceStatus(
                    $device_id,
                    $status
                );

        if ($updated) {
            header(
                'Location: admin/devices'
            );

            exit();
        }

        die('Status Update Failed');


    }

    public function getRepairNotes(
        $device_id
    ) {
        return
            $this->device_model
                ->getRepairNotes(
                    $device_id
                );
    }



}
