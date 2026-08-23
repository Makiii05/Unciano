<?php
require_once __DIR__ . '/../../../../bootstrap.php';

use App\Controllers\AccountController;

$controller = new AccountController();
$controller->destroyStudentAccount();
