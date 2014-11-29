<?php

require_once dirname(__FILE__) . '/../../core/php/fitbit.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
require_once dirname(__FILE__) . '/fitbit.inc.php';
$fitbit = new FitBitPHP(config::byKey('client_key', 'fitbit'), config::byKey('secret_key', 'fitbit'));
$fitbit->initSession(fitbit::getCallbackUri());
config::save('fitbit_Token', $_SESSION['fitbit_Token'], 'fitbit');
config::save('fitbit_Secret', $_SESSION['fitbit_Secret'], 'fitbit');
fitbit::syncWithFitbit();
redirect(config::byKey('externalAddr') . '?v=d&p=plugin&id=fitbit');
