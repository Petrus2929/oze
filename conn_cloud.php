<?php
$servername = "localhost";
$username = "root";
$password = "Ks-eRLd9.EzZ";
$dbname= "oze";
// Create connection
$conn = new mysqli($servername, $username, $password,$dbname);
// Check connection
if ($conn->connect_error) {
 die("Connection failed: " . $conn->connect_error);
}
?>