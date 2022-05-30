<?php

require_once(dirname(__FILE__).'/../../config/config.inc.php');

$token = isset($argv[1]) ? $argv[1] : Tools::getValue('token');
$action = isset($argv[2]) ? $argv[2] : Tools::getValue('action');
//$step = isset($argv[3]) ? $argv[3] : Tools::getValue('step');

if ($token != Tools::encrypt('prestaseeder')) {
    exit;
}

if (!Module::isEnabled('prestaseeder')) {
    exit;
}

/** @var ITeam $moduleInstance */
$moduleInstance = Module::getInstanceByName('prestaseeder');
$moduleInstance->processCron($action);
exit;