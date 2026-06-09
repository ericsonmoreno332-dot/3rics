<?php
declare(strict_types=1);

$sql = file_get_contents(__DIR__ . '/../database/local_sistema_practicantes.sql');

preg_match_all('/CREATE TABLE `areas` \((.*?)\)/is', $sql, $matches, PREG_OFFSET_CAPTURE);
echo "Encuentros de CREATE TABLE `areas`:\n";
print_r($matches);

preg_match_all('/DROP TABLE IF EXISTS `areas`;/is', $sql, $matches2, PREG_OFFSET_CAPTURE);
echo "Encuentros de DROP TABLE IF EXISTS `areas`:\n";
print_r($matches2);
