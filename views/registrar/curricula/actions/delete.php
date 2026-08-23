<?php
require_once __DIR__ . '/../../../../bootstrap.php';
use App\Controllers\CurriculumController;
(new CurriculumController())->destroy();
