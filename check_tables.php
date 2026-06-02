<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=im_indivproject', 'root', '');
    $stmt = $pdo->query('SHOW TABLES');
    while ($row = $stmt->fetch()) {
        echo $row[0] . "\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
