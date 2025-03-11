<?php
require 'vendor/autoload.php';
session_start();
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader('templates');
$twig = new Environment($loader);

$page = $_GET['page'] ?? 'form'; 


$pageFile = 'pages/' . $page . '.php';

if (file_exists($pageFile)) {

    include $pageFile;
} else {

    echo "Strona nie została znaleziona.";
}


?>
