<?php

require_once __DIR__ . "/../models/Dashboard.php";
require_once __DIR__ . '/../helpers/helpers.php';

class DashboardController
{
    private $dashboard;

    public function __construct()
    {
        $this->dashboard = new Dashboard();
    }

    public function getAdminDashboard()
    {
        requireRole('admin');
        try {
            $result = $this->dashboard->adminDashboard();

            if (empty($result)) {
                sendResponse("error", 404, "Data not found");
            }
            sendResponse("success", 200, "successfully get data", $result);
        } catch (\Throwable $e) {
            sendResponse("error", 500, $e->getMessage());
        }
    }

    public function getPenyewaDashboard()
    {
        requireRole('penyewa');
        try {
            $result = $this->dashboard->penyewaDashboard();
            if (empty($result)) {
                sendResponse("error", 404, "Data not found");
            }
            sendResponse("success", 200, "successfully get data", $result);
        } catch (\Throwable $e) {
            sendResponse("error", 500, $e->getMessage());
        }
    }
}
