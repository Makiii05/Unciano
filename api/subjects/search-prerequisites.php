<?php
require_once __DIR__ . '/../../bootstrap.php';
use App\Controllers\SubjectController;
(new SubjectController())->searchPrerequisites();
