<?php

error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use dbeurive\Trace\Dumper\Cli\Sqlite\TextSimple as SqliteTextSimple;

try {
    $application = new Application();
    $application->setAutoExit(true);
    $application->add(new SqliteTextSimple());
    $application->run();
} catch (\Exception $e) {
    print $e->getMessage() . "\n";
}



