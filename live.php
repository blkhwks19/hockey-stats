<?php
// LIVE
require('constants.php');
$dsn = 'mysql:host=' . HOST . ';port=' . PORT . ';dbname=' . DBNAME . ';';
try {
    $db = new PDO($dsn, USER, PASS);
} catch(PDOException $e) {
    die('Could not connect to the database:<br/>' . $e);
}
?>