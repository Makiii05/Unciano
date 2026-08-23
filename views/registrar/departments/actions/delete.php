<?php
require_once __DIR__ . '/../../../../bootstrap.php';

use App\Controllers\DepartmentController;

$controller = new DepartmentController();
$controller->destroy();
