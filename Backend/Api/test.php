<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
} else {
    echo "Envía datos por POST";
}
?>
