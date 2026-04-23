<?php

require_once __DIR__ . '/../config/connect.php';

class Auth
{
    public $conn;

    public function __construct()
    {
        try {
            $this->conn = (new Database())->getCon();
        } catch (\Throwable $e) {
            $this->conn = null;
            error_log("[" . date("Y-m-d H:i:s") . "] Connection failed: " . $e->getMessage() . "\r\n", 3, __DIR__ . '/../logs/error.log');
        }
    }

    public function __destruct()
    {
        if ($this->conn !== null) {
            pg_close($this->conn);
        }
    }

    public function getUserByEmail($email)
    {
        try {
            if ($this->conn === null) {
                throw new Exception("Database connection failed");
            }
            $query = "SELECT * FROM auth where email=$1";

            pg_prepare($this->conn, "get_user_by_email", $query);
            $result = pg_execute($this->conn, "get_user_by_email", [$email]);

            $data =  pg_fetch_all($result);
            return $data;
        } catch (\Throwable $e) {
            error_log("[" . date("Y-m-d H:i:s") . "] Query failed: " . $e->getMessage() . "\r\n", 3, __DIR__ . '/../logs/error.log');
            return null;
        }
    }
}
