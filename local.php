<?php
// LOCAL
$dsn = 'mysql:host=localhost;port=3306;dbname=hockey;';
$username = 'root';
$password = '';
try {
    $db = new PDO($dsn, $username, $password);//, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
} catch(PDOException $e) {
    die('Could not connect to the database:<br/>' . $e);
}
?>