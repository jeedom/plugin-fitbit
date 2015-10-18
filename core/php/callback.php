<?php

require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
include_file('core', 'authentification', 'php');
if (!isConnect()) {
	echo 'Vous ne pouvez appeller cette page sans être connecté. Veuillez vous connecter <a href=' . network::getNetworkAccess() . '/index.php>ici</a> avant et refaire l\'opération de synchronisation';
	die();
}
require_once dirname(__FILE__) . '/fitbit.inc.php';
$eqLogic = eqLogic::byId(init('eqLogic_id'));
if (!is_object($eqLogic)) {
	echo 'Impossible de trouver l\'équipement correspondant à : ' . init('eqLogic_id');
	exit();
}
$factory = new \Fitbit\ApiGatewayFactory;
$factory->setCallbackURL(network::getNetworkAccess() . '/plugins/fitbit/core/php/callback.php?eqLogic_id=' . $eqLogic->getId());
$factory->setCredentials(config::byKey('client_key', 'fitbit'), config::byKey('secret_key', 'fitbit'));
$adapter = new \OAuth\Common\Storage\Session();
$factory->setStorageAdapter($adapter);
$auth_gateway = $factory->getAuthenticationGateway();
$auth_gateway->authenticateUser($_GET['oauth_token'], $_GET['oauth_verifier']);
$user_gateway = $factory->getUserGateway();
$user_profile = $user_gateway->getProfile();

$storage = $factory->getStorageAdapter();
$token = $storage->retrieveAccessToken('FitBit');

$eqLogic->setConfiguration('encodedId', $user_profile->user->encodedId);
$eqLogic->setConfiguration('token', $token->getRequestToken());
$eqLogic->setConfiguration('secret', $token->getRequestTokenSecret());
$eqLogic->save();
redirect(network::getNetworkAccess() . '/index.php?v=d&p=fitbit&m=fitbit&id=' . $eqLogic->getId());
