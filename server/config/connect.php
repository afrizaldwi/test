<?php

require_once __DIR__ . '/../helpers/helpers.php';

class Database
{
    private $DB_HOST;
    private $DB_USER;
    private $DB_PASS;
    private $DB_NAME;
    private $DB_PORT;
    public $conn;

    public function __construct()
    {
        $this->DB_HOST = $_ENV['DB_HOST'];
        $this->DB_USER = $_ENV['DB_USER'];
        $this->DB_PASS = $_ENV['DB_PASS'];
        $this->DB_NAME = $_ENV['DB_NAME'];
        $this->DB_PORT = $_ENV['DB_PORT'];
    }

    function getCon()
    {
        $this->conn = null;

        try {
            $connString = "host={$this->DB_HOST} port={$this->DB_PORT} dbname={$this->DB_NAME} user={$this->DB_USER} password={$this->DB_PASS}";

            $this->conn = pg_connect($connString);

            if (!$this->conn) {
                error_log("[" . date("Y-m-d H:i:s") . "] Connection failed\r\n", 3, __DIR__ . '/../logs/error.log');

                sendResponse("error", 500, "Database connection failed");
            }

            return $this->conn;
        } catch (\Throwable $th) {
            error_log("[" . date("Y-m-d H:i:s") . "] Connection failed: " . $th->getMessage() . "\r\n", 3, __DIR__ . '/../logs/error.log');

            sendResponse("error", 500, "Database connection failed");
        }
        return $conn;
    }
}
