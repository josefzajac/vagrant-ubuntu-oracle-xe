<?php

require __DIR__ . '/../vendor/autoload.php';

if (!function_exists('dump')) {
    function dump($args)
    {
        return \Tracy\Debugger::dump($args);
    }

    function d($args)
    {
        return dump($args);
    }

    function dd($args)
    {
        return dump($args);
        die();
    }
}
if (!function_exists('barDump')) {
    function barDump($args)
    {
        return \Tracy\Debugger::barDump($args);
    }

    function bd($args)
    {
        return barDump($args);
    }
}

$configurator = new Nette\Configurator();

$configurator->setDebugMode(\Tracy\Debugger::PRODUCTION);

$configurator->enableDebugger(__DIR__ . '/../log', 'josef.zajac@gmail.com');

$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->addDirectory(__DIR__ . '/../libs')
    ->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

return $container;
