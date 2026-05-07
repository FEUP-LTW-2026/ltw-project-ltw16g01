<?php

// Creates users with hashed passwords.
// This file should run before the SQL inserts that depend on users.

require_once __DIR__ . '/connection.php';
$pdo = getDatabaseConnection();

$insertUser = $pdo->prepare("INSERT INTO users (name, username, email, password, role) VALUES (?, ?, ?, ?, ?)");

// Admin
$insertUser->execute(['Admin User', 'admin', 'admin@fitpeak.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);

// Trainers
$trainers = [ ['Ana Silva', 'anasilva', 'ana@fitpeak.com'], ['Bruno Costa', 'brunocosta', 'bruno@fitpeak.com'], ['Carla Mendes', 'carlamendes', 'carla@fitpeak.com'], ['Miguel Rocha', 'miguelrocha', 'miguel@fitpeak.com'],];

foreach ($trainers as $trainer) {
    $insertUser->execute([$trainer[0], $trainer[1], $trainer[2], password_hash('trainer123', PASSWORD_DEFAULT), 'trainer']);
}

// Members
$members = [['Sofia Almeida', 'sofiaalmeida', 'sofia@gmail.com'], ['Rui Tavares', 'ruitavares', 'rui@gmail.com'], ['Inês Correia', 'inescorreia', 'ines@gmail.com'], ['Marco Ferreira', 'marcoferreira', 'marco@gmail.com'], ['Lara Pinheiro', 'larapinheiro', 'lara@gmail.com'], ['João Ferreira', 'joaoferreira', 'joao@email.com'], ['Maria Santos', 'mariasantos', 'maria@email.com'], ['Pedro Rodrigues', 'pedrorodrigues', 'pedro@email.com'],];

foreach ($members as $member) {
    $insertUser->execute([$member[0], $member[1], $member[2], password_hash('member123', PASSWORD_DEFAULT), 'member']);
}