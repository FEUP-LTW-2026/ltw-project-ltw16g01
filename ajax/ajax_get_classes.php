<?php
// ajax_get_classes.php
require_once '../db/connection.php';
require_once '../db/classes.php';
require_once '../template/classes.php';

$pdo = getDatabaseConnection();

// Recolher filtros
$filters = [
    'type'    => $_GET['type'] ?? '',
    'trainer' => $_GET['trainer'] ?? '',
    'day'     => $_GET['day'] ?? ''
];

$classes = getAllClasses($pdo, $filters);

// Devolver apenas a lista de aulas (sem header/footer)
output_class_list($classes);