<?php

require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
require_once dirname(__FILE__) . '/../../core/php/fitbit.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    $url = config::byKey('externalAddr');
    if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
        $url = 'http://' . $url;
    }
    if (config::byKey('externalPort') != '' && config::byKey('externalPort') != 80) {
        $url = $url . ':' . config::byKey('externalPort');
    }
    echo 'Vous ne pouvez appeller cette page sans être connecté. Veuillez vous connecter <a href=' . $url . '>ici</a> avant et refaire l\'opération de synchronisation';
    die();
}
require_once dirname(__FILE__) . '/fitbit.inc.php';
$fitbit = new FitBitPHP(config::byKey('client_key', 'fitbit'), config::byKey('secret_key', 'fitbit'));
$fitbit->initSession(fitbit::getCallbackUri());
config::save('fitbit_Token', $_SESSION['fitbit_Token'], 'fitbit');
config::save('fitbit_Secret', $_SESSION['fitbit_Secret'], 'fitbit');
fitbit::syncWithFitbit();

$url = config::byKey('externalAddr');
if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
    $url = 'http://' . $url;
}
if (config::byKey('externalPort') != '' && config::byKey('externalPort') != 80) {
    redirect($url . ':' . config::byKey('externalPort') . '?v=d&p=plugin&id=fitbit');
} else {
    redirect($url . '?v=d&p=plugin&id=fitbit');
}