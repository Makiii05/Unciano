<?php
require_once __DIR__ . '/../../../../bootstrap.php';
use App\Controllers\AcademicTermController;
(new AcademicTermController())->destroy();
