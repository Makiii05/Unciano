<?php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Controllers\SchoolYearController;

$controller = new SchoolYearController();
$controller->index();
