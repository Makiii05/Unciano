<?php
require_once __DIR__ . '/../../../../bootstrap.php';
use App\Controllers\GradingSystemController;
(new GradingSystemController())->update();
