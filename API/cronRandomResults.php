<?php

//require "../src/Service/DatabaseService.php";
require_once "/Users/Pierredck/Documents/Coding Factory/Betflix/src/Service/DatabaseService.php";

use App\Service\DatabaseService;
use Doctrine\ORM\EntityManagerInterface;
$service = new DatabaseService(EntityManagerInterface::class);

$service->setResults();

