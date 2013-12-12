#!/usr/bin/env php
<?php
// app/console

use Czenker\Wichtlr\Command\GoCommand;
use Czenker\Wichtlr\Command\RecoverCommand;
use Czenker\Wichtlr\ConsoleHelper\Reindeer;
use Symfony\Component\Console\Application;

require_once(__DIR__ . '/../vendor/autoload.php');

$application = new Application();
$application->add(new GoCommand());
$application->add(new RecoverCommand());
$application->getHelperSet()->set(new Reindeer());
$application->run();