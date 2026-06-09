<?php
require 'includes/bootstrap.php';
$pdo = db();
$st = $pdo->query('SHOW COLUMNS FROM asistencias');
print_r($st->fetchAll(PDO::FETCH_COLUMN));
