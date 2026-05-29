<?php
/**
 * connection.php – Returns a PDO connection to the SQLite database.
 */

function getDatabaseConnection() {
    return new PDO('sqlite:' . __DIR__ . '/gym.db');
}
?>