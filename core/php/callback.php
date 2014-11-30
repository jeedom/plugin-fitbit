<?php

require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
require_once dirname(__FILE__) . '/../../core/php/fitbit.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    echo 'Vous ne pouvez appeller cette page sans être connecté. Veuillez vous connecter <a href=' . fitbit::getCallbackUri('/index.php') . '>ici</a> avant et refaire l\'opération de synchronisation';
    die();
}
require_once dirname(__FILE__) . '/fitbit.inc.php';
$fitbit = new FitBitPHP(config::byKey('client_key', 'fitbit'), config::byKey('secret_key', 'fitbit'));
$fitbit->initSession(fitbit::getCallbackUri());
config::save('fitbit_Token', $_SESSION['fitbit_Token'], 'fitbit');
config::save('fitbit_Secret', $_SESSION['fitbit_Secret'], 'fitbit');
fitbit::syncWithFitbit();

redirect(fitbit::getCallbackUri('/index.php?v=d&p=plugin&id=fitbit'));
