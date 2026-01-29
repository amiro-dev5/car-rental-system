<?php

$host_name = $_SERVER['SERVER_NAME'];

if ($host_name == "localhost" || $host_name == "127.0.0.1") {

    $host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "gp3_db";
} else {
   
    $host = "sql310.infinityfree.com"; 
    $db_user = "if0_40786420";
    $db_pass = "QKGGEBvvYZe";
    $db_name = "if0_40786420_group3_db";
}

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
  
    die("Database connection failed. Please try again later.");
}

$conn->set_charset("utf8mb4");

?>