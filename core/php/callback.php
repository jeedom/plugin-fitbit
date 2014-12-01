<?php

require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    echo 'Vous ne pouvez appeller cette page sans être connecté. Veuillez vous connecter <a href=' . fitbit::getCallbackUri('/index.php') . '>ici</a> avant et refaire l\'opération de synchronisation';
    die();
}
require_once dirname(__FILE__) . '/fitbit.inc.php';
$eqLogic = eqLogic::byId(init('eqLogic_id'));
if (!is_object($eqLogic)) {
    echo 'Impossible de trouver l\'équipement correspondant à : ' . init('eqLogic_id');
    exit();
}
$fitbit = new FitBitPHP(config::byKey('client_key', 'fitbit'), config::byKey('secret_key', 'fitbit'));
$fitbit->initSession(fitbit::getCallbackUri() . '?eqLogic_id=' . $eqLogic->getId());
$users = json_decode(json_encode($fitbit->getProfile()),true);

$eqLogic->setConfiguration('encodedId', $users['user']['encodedId']);
$eqLogic->setConfiguration('token', $_SESSION['fitbit_Token']);
$eqLogic->setConfiguration('secret', $_SESSION['fitbit_Secret']);
$eqLogic->save();
redirect(fitbit::getCallbackUri('/index.php?v=d&p=fitbit&m=fitbit&id=' . $eqLogic->getId()));
