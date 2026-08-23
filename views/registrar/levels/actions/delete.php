<?php
require_once __DIR__ . '/../../../../bootstrap.php';
use App\Controllers\LevelController;
(new LevelController())->destroy();
