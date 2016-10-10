<?php
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
include_file('core', 'authentification', 'php');

$eqLogic = eqLogic::byId(init('eqLogic_id'));
if (!is_object($eqLogic)) {
	echo 'Impossible de trouver l\'équipement correspondant à : ' . init('eqLogic_id');
	exit();
}
if (init('update') == 1) {
	if (init('verify') != '') {
		if (init('verify') == $eqLogic->getConfiguration('subscription_code')) {
			http_response_code(204);
			die();
		} else {
			http_response_code(404);
			die();
		}
	}
	$eqLogic->subscribe();
	die();
}

if (!isConnect()) {
	echo 'Vous ne pouvez appeller cette page sans être connecté. Veuillez vous connecter <a href=' . network::getNetworkAccess() . '/index.php>ici</a> avant et refaire l\'opération de synchronisation';
	die();
}

$provider = $eqLogic->getProvider();

if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
	unset($_SESSION['oauth2state']);
	exit('Invalid state');
}
try {
	$accessToken = $provider->getAccessToken('authorization_code', [
		'code' => $_GET['code'],
	]);
	$eqLogic->setConfiguration('accessToken', $accessToken->jsonSerialize());
	$eqLogic->save();
	$eqLogic->syncWithFitbit();
	try {
		$eqLogic->subscribe();
	} catch (Exception $e) {

	}
	redirect(network::getNetworkAccess('external') . '/index.php?v=d&p=fitbit&m=fitbit&id=' . $eqLogic->getId());
} catch (Exception $e) {
	exit(print_r($e));
}
