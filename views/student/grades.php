<?php
require_once __DIR__ . '/../../bootstrap.php';

use App\Controllers\Student\GradeController;

(new GradeController())->index();
