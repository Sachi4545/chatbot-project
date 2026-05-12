<?php

$conn = new mysqli(
    "yamabiko.proxy.rlwy.net",
    "root",
    "fdhKAdvYWlRpwNfQUGFpwwyNSLwZrsLK",
    "railway",
    17266
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>