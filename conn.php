<?php
$servername="localhost";
$username="root";
$password="Akamatsu";
$dbase="quiz_db";
$conn= new mysqli($servername, $username, $password,$dbase);

if ($conn->connect_error) {
    die("Connection failed: ".$conn->connect_error);
}
echo "Connected";

?>
