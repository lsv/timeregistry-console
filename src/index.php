<?php
namespace App;
use Timeregistry\Application;
use Timeregistry\Key\SetApiCommand;
use Timeregistry\SelfupdateCommand;
use Timeregistry\Time\TimeCommand;
use Timeregistry\Time\WorkHourCommand;

require 'vendor/autoload.php';

$app = new Application('Scandesigns Timelog', '0.1');
$app->add(new SelfupdateCommand());
$app->add(new TimeCommand());
$app->add(new SetApiCommand());
$app->add(new WorkHourCommand());
$app->run();