<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../core/php/fitbit.inc.php';

class fitbit extends eqLogic {
	/*     * *************************Attributs****************************** */

	public static $_widgetPossibility = array('custom' => true);

	/*     * ***********************Methode static*************************** */

	public static function cron30() {
		foreach (self::byType('fitbit') as $fitbit) {
			if ($fitbit->getIsEnable() == 1) {
				try {
					$fitbit->syncWithFitbit();
					if ($fitbit->getConfiguration('fitbitNumberFailed', 0) > 0) {
						$fitbit->setConfiguration('fitbitNumberFailed', 0);
						$fitbit->save();
					}
				} catch (Exception $e) {
					if ($fitbit->getConfiguration('fitbitNumberFailed', 0) > 3) {
						log::add('fitbit', 'error', __('Erreur sur ', __FILE__) . $fitbit->getHumanName() . ' (' . $fitbit->getConfiguration('fitbitNumberFailed', 0) . ') : ' . $e->getMessage());
					} else {
						$fitbit->setConfiguration('fitbitNumberFailed', $fitbit->getConfiguration('fitbitNumberFailed', 0) + 1);
						$fitbit->save();
					}
				}
			}
		}
	}

	/*     * *********************Methode d'instance************************* */

	public function linkToUser() {
		@session_start();
		$factory = new \Fitbit\ApiGatewayFactory;
		$factory->setCallbackURL(network::getNetworkAccess() . '/plugins/fitbit/core/php/callback.php?eqLogic_id=' . $this->getId());
		$factory->setCredentials(config::byKey('client_key', 'fitbit'), config::byKey('secret_key', 'fitbit'));
		$adapter = new \OAuth\Common\Storage\Session();
		$factory->setStorageAdapter($adapter);
		$auth_gateway = $factory->getAuthenticationGateway();
		return utils::o2a($auth_gateway->initiateLogin());
	}

	public function getFactory() {
		$factory = new \Fitbit\ApiGatewayFactory;
		$factory->setCallbackURL(network::getNetworkAccess() . '/plugins/fitbit/core/php/callback.php?eqLogic_id=' . $this->getId());
		$factory->setCredentials(config::byKey('client_key', 'fitbit'), config::byKey('secret_key', 'fitbit'));
		$token = new \OAuth\OAuth1\Token\StdOAuth1Token();
		$token->setRequestToken($this->getConfiguration('token'));
		$token->setRequestTokenSecret($this->getConfiguration('secret'));
		$token->setAccessToken($this->getConfiguration('token'));
		$token->setAccessTokenSecret($this->getConfiguration('secret'));
		$adapter = new \OAuth\Common\Storage\Memory();
		$adapter->storeAccessToken('FitBit', $token);
		$factory->setStorageAdapter($adapter);
		return $factory;
	}

	public function toHtml($_version = 'dashboard') {
		$replace = $this->preToHtml($_version, array('#background-color#' => '#2f3f43'));
		if (!is_array($replace)) {
			return $replace;
		}
		$_version = jeedom::versionAlias($_version);
		foreach ($this->getCmd('info') as $cmd) {
			$replace['#' . $cmd->getLogicalId() . '_history#'] = '';
			if ($cmd->getIsVisible() == 1 && $cmd->getDisplay('hideOn' . $_version) != 1) {
				$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
				$replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
				$replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
				if ($cmd->getIsHistorized() == 1) {
					$replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
				}
			} else {
				$replace['#' . $cmd->getLogicalId() . '#'] = '';
			}
		}
		$refresh = $this->getCmd(null, 'refresh');
		if (is_object($refresh)) {
			$replace['#refresh_id#'] = $refresh->getId();
		}
		$html = template_replace($replace, getTemplate('core', $_version, 'fitbit', 'fitbit'));
		cache::set('widgetHtml' . $_version . $this->getId(), $html, 0);
		return $html;
	}

	public function postSave() {
		$weight = $this->getCmd(null, 'weight');
		if (!is_object($weight)) {
			$weight = new fitbitCmd();
			$weight->setLogicalId('weight');
			$weight->setIsVisible(1);
			$weight->setName(__('Poids', __FILE__));
			$weight->setTemplate('dashboard', 'tile');
			$weight->setTemplate('mobile', 'tile');
		}
		$weight->setType('info');
		$weight->setSubType('numeric');
		$weight->setUnite('kg');
		$weight->setEqLogic_id($this->getId());
		$weight->save();

		$fat = $this->getCmd(null, 'fat');
		if (!is_object($fat)) {
			$fat = new fitbitCmd();
			$fat->setLogicalId('fat');
			$fat->setIsVisible(1);
			$fat->setName(__('Masse grasse', __FILE__));
			$fat->setTemplate('dashboard', 'tile');
			$fat->setTemplate('mobile', 'tile');
		}
		$fat->setType('info');
		$fat->setSubType('numeric');
		$fat->setUnite('%');
		$fat->setEqLogic_id($this->getId());
		$fat->save();

		$bmi = $this->getCmd(null, 'bmi');
		if (!is_object($bmi)) {
			$bmi = new fitbitCmd();
			$bmi->setLogicalId('bmi');
			$bmi->setIsVisible(1);
			$bmi->setName(__('IMC', __FILE__));
			$bmi->setTemplate('dashboard', 'tile');
			$bmi->setTemplate('mobile', 'tile');
		}
		$bmi->setType('info');
		$bmi->setSubType('numeric');
		$bmi->setEqLogic_id($this->getId());
		$bmi->save();

		$refresh = $this->getCmd(null, 'refresh');
		if (!is_object($refresh)) {
			$refresh = new fitbitCmd();
			$refresh->setLogicalId('refresh');
			$refresh->setIsVisible(1);
			$refresh->setName(__('Rafraîhir', __FILE__));
		}
		$refresh->setType('action');
		$refresh->setSubType('other');
		$refresh->setEqLogic_id($this->getId());
		$refresh->save();

		$height = $this->getCmd(null, 'height');
		if (!is_object($height)) {
			$height = new fitbitCmd();
			$height->setLogicalId('height');
			$height->setIsVisible(1);
			$height->setName(__('Taille', __FILE__));
		}
		$height->setType('info');
		$height->setSubType('numeric');
		$height->setUnite('cm');
		$height->setEqLogic_id($this->getId());
		$height->save();

		$step = $this->getCmd(null, 'step');
		if (!is_object($step)) {
			$step = new fitbitCmd();
			$step->setLogicalId('step');
			$step->setIsVisible(1);
			$step->setName(__('Pas', __FILE__));
		}

		$step->setType('info');
		$step->setSubType('numeric');
		$step->setEqLogic_id($this->getId());
		$step->save();

		$caloriesOut = $this->getCmd(null, 'caloriesOut');
		if (!is_object($caloriesOut)) {
			$caloriesOut = new fitbitCmd();
			$caloriesOut->setLogicalId('caloriesOut');
			$caloriesOut->setIsVisible(1);
			$caloriesOut->setName(__('Calorie', __FILE__));
		}
		$caloriesOut->setType('info');
		$caloriesOut->setSubType('numeric');
		$caloriesOut->setEqLogic_id($this->getId());
		$caloriesOut->save();

		$veryActiveMinutes = $this->getCmd(null, 'veryActiveMinutes');
		if (!is_object($veryActiveMinutes)) {
			$veryActiveMinutes = new fitbitCmd();
			$veryActiveMinutes->setLogicalId('veryActiveMinutes');
			$veryActiveMinutes->setIsVisible(1);
			$veryActiveMinutes->setName(__('Minute active', __FILE__));
		}
		$veryActiveMinutes->setType('info');
		$veryActiveMinutes->setSubType('numeric');
		$veryActiveMinutes->setUnite('min');
		$veryActiveMinutes->setEqLogic_id($this->getId());
		$veryActiveMinutes->save();

		$distance = $this->getCmd(null, 'distance');
		if (!is_object($distance)) {
			$distance = new fitbitCmd();
			$distance->setLogicalId('distance');
			$distance->setIsVisible(1);
			$distance->setName(__('Distance', __FILE__));
		}
		$distance->setType('info');
		$distance->setSubType('numeric');
		$distance->setUnite('km');
		$distance->setEqLogic_id($this->getId());
		$distance->save();

		$elevation = $this->getCmd(null, 'elevation');
		if (!is_object($elevation)) {
			$elevation = new fitbitCmd();
			$elevation->setLogicalId('elevation');
			$elevation->setIsVisible(1);
			$elevation->setName(__('Elevation', __FILE__));
		}
		$elevation->setType('info');
		$elevation->setSubType('numeric');
		$distance->setUnite('m');
		$elevation->setEqLogic_id($this->getId());
		$elevation->save();

		$awakeCount = $this->getCmd(null, 'awakeCount');
		if (!is_object($awakeCount)) {
			$awakeCount = new fitbitCmd();
			$awakeCount->setLogicalId('awakeCount');
			$awakeCount->setIsVisible(1);
			$awakeCount->setName(__('Nombre de reveils', __FILE__));
		}
		$awakeCount->setType('info');
		$awakeCount->setSubType('numeric');
		$awakeCount->setEqLogic_id($this->getId());
		$awakeCount->save();

		$awakeDuration = $this->getCmd(null, 'awakeDuration');
		if (!is_object($awakeDuration)) {
			$awakeDuration = new fitbitCmd();
			$awakeDuration->setLogicalId('awakeDuration');
			$awakeDuration->setIsVisible(1);
			$awakeDuration->setName(__('Durée réveils', __FILE__));
		}
		$awakeDuration->setType('info');
		$awakeDuration->setSubType('numeric');
		$distance->setUnite('min');
		$awakeDuration->setEqLogic_id($this->getId());
		$awakeDuration->save();

		$sleepDuration = $this->getCmd(null, 'sleepDuration');
		if (!is_object($sleepDuration)) {
			$sleepDuration = new fitbitCmd();
			$sleepDuration->setLogicalId('sleepDuration');
			$sleepDuration->setIsVisible(1);
			$sleepDuration->setName(__('Durée de sommeil', __FILE__));
		}
		$sleepDuration->setType('info');
		$sleepDuration->setSubType('numeric');
		$sleepDuration->setUnite('min');
		$sleepDuration->setEqLogic_id($this->getId());
		$sleepDuration->save();

		$restlessDuration = $this->getCmd(null, 'restlessDuration');
		if (!is_object($restlessDuration)) {
			$restlessDuration = new fitbitCmd();
			$restlessDuration->setLogicalId('restlessDuration');
			$restlessDuration->setIsVisible(1);
			$restlessDuration->setName(__('Sommeil agité', __FILE__));
		}
		$restlessDuration->setType('info');
		$restlessDuration->setSubType('numeric');
		$restlessDuration->setUnite('min');
		$restlessDuration->setEqLogic_id($this->getId());
		$restlessDuration->save();

		$minutesToFallAsleep = $this->getCmd(null, 'minutesToFallAsleep');
		if (!is_object($minutesToFallAsleep)) {
			$minutesToFallAsleep = new fitbitCmd();
			$minutesToFallAsleep->setLogicalId('minutesToFallAsleep');
			$minutesToFallAsleep->setIsVisible(1);
			$minutesToFallAsleep->setName(__('Temps pour s\'endormir', __FILE__));
		}
		$minutesToFallAsleep->setType('info');
		$minutesToFallAsleep->setSubType('numeric');
		$minutesToFallAsleep->setUnite('min');
		$minutesToFallAsleep->setEqLogic_id($this->getId());
		$minutesToFallAsleep->save();

		if ($this->getConfiguration('encodedId') != '') {
			$this->syncWithFitbit();
		}
	}

	public function syncWithFitbit() {
		$factory = $this->getFactory();
		$user_gateway = $factory->getUserGateway();
		$users = json_decode(json_encode($user_gateway->getProfile()), true);
		$activity_gateway = $factory->getActivityGateway();
		$activity = json_decode(json_encode($activity_gateway->getActivities(new DateTime())), true);
		$sleep_gateway = $factory->getSleepGateway();
		$sleeps = json_decode(json_encode($sleep_gateway->getSleep(new DateTime())), true);
		$body_gateway = $factory->getBodyGateway();
		$bodies = json_decode(json_encode($body_gateway->getBody(new DateTime())), true);

		$user = $users['user'];
		$height = $this->getCmd(null, 'height');
		if (is_object($height) && $height->execCmd() != $height->formatValue($user['height'])) {
			$height->setCollectDate('');
			$height->event($user['height']);
		}

		if (isset($sleeps['sleep'][0])) {
			$sleep = $sleeps['sleep'][0];
			$awakeCount = $this->getCmd(null, 'awakeCount');
			if (is_object($awakeCount) && $awakeCount->execCmd() != $awakeCount->formatValue($sleep['awakeCount'])) {
				$awakeCount->setCollectDate('');
				$awakeCount->event($sleep['awakeCount']);
			}

			$awakeDuration = $this->getCmd(null, 'awakeDuration');
			if (is_object($awakeCount) && $awakeDuration->execCmd() != $awakeCount->formatValue($sleep['awakeDuration'])) {
				$awakeDuration->setCollectDate('');
				$awakeDuration->event($sleep['awakeDuration']);
			}

			$restlessDuration = $this->getCmd(null, 'restlessDuration');
			if (is_object($restlessDuration) && $restlessDuration->execCmd() != $restlessDuration->formatValue($sleep['restlessDuration'])) {
				$restlessDuration->setCollectDate('');
				$restlessDuration->event($sleep['restlessDuration']);
			}

			$minutesToFallAsleep = $this->getCmd(null, 'minutesToFallAsleep');
			if (is_object($minutesToFallAsleep) && $minutesToFallAsleep->execCmd() != $minutesToFallAsleep->formatValue($sleep['minutesToFallAsleep'] + 1)) {
				$minutesToFallAsleep->setCollectDate('');
				$minutesToFallAsleep->event($sleep['minutesToFallAsleep'] + 1);
			}

			$sleepDuration = $this->getCmd(null, 'sleepDuration');
			if (is_object($sleepDuration) && $sleepDuration->execCmd() != $sleepDuration->formatValue($sleep['minutesAsleep'])) {
				$sleepDuration->setCollectDate('');
				$sleepDuration->event($sleep['minutesAsleep']);
			}

		}

		$body = $bodies['body'];
		$weight = $this->getCmd(null, 'weight');
		if ($body['weight'] != 0 && is_object($weight) && $weight->execCmd() != $weight->formatValue($body['weight'])) {
			$weight->setCollectDate('');
			$weight->event($body['weight']);
		}

		$fat = $this->getCmd(null, 'fat');
		if ($body['fat'] != 0 && is_object($fat) && $fat->execCmd() != $fat->formatValue($body['fat'])) {
			$fat->setCollectDate('');
			$fat->event($body['fat']);
		}

		$bmi = $this->getCmd(null, 'bmi');
		if ($body['bmi'] != 0 && is_object($bmi) && $bmi->execCmd() != $bmi->formatValue($body['bmi'])) {
			$bmi->setCollectDate('');
			$bmi->event($body['bmi']);
		}

		$step = $this->getCmd(null, 'step');
		if (is_object($step)) {
			if ($step->getConfiguration('goal') != $activity['goals']['steps']) {
				$step->setConfiguration('goal', $activity['goals']['steps']);
				$step->save();
			}
			if ($step->execCmd() != $step->formatValue($activity['summary']['steps'])) {
				$step->setCollectDate('');
				$step->event($activity['summary']['steps']);
			}
		}

		$elevation = $this->getCmd(null, 'elevation');
		if (is_object($elevation) && $elevation->execCmd() != $step->formatValue($activity['summary']['elevation'])) {
			$elevation->setCollectDate('');
			$elevation->event($activity['summary']['elevation']);
		}

		$caloriesOut = $this->getCmd(null, 'caloriesOut');
		if (is_object($caloriesOut)) {
			if ($caloriesOut->getConfiguration('goal') != $activity['goals']['caloriesOut']) {
				$caloriesOut->setConfiguration('goal', $activity['goals']['caloriesOut']);
				$caloriesOut->save();
			}
			if ($caloriesOut->execCmd() != $caloriesOut->formatValue($activity['summary']['caloriesOut'])) {
				$caloriesOut->setCollectDate('');
				$caloriesOut->event($activity['summary']['caloriesOut']);
			}
		}

		$veryActiveMinutes = $this->getCmd(null, 'veryActiveMinutes');
		if (is_object($veryActiveMinutes)) {
			if ($veryActiveMinutes->getConfiguration('goal') != $activity['goals']['activeMinutes']) {
				$veryActiveMinutes->setConfiguration('goal', $activity['goals']['activeMinutes']);
				$veryActiveMinutes->save();
			}
			if ($veryActiveMinutes->execCmd() != $veryActiveMinutes->formatValue($activity['summary']['veryActiveMinutes'])) {
				$veryActiveMinutes->setCollectDate('');
				$veryActiveMinutes->event($activity['summary']['veryActiveMinutes']);
			}
		}

		$distance = $this->getCmd(null, 'distance');
		if (is_object($distance)) {
			if ($distance->getConfiguration('goal') != $activity['goals']['distance']) {
				$distance->setConfiguration('goal', $activity['goals']['distance']);
				$distance->save();
			}
			if ($distance->execCmd() != $distance->formatValue($activity['summary']['distances'][0]['distance'])) {
				$distance->setCollectDate('');
				$distance->event($activity['summary']['distances'][0]['distance']);
			}
		}
		$mc = cache::byKey('fitbitWidgetmobile' . $this->getId());
		$mc->remove();
		$mc = cache::byKey('fitbitWidgetdashboard' . $this->getId());
		$mc->remove();
		$this->toHtml('mobile');
		$this->toHtml('dashboard');
		$this->refreshWidget();
	}

	/*     * **********************Getteur Setteur*************************** */
}

class fitbitCmd extends cmd {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	public function execute($_options = null) {
		if ($this->getType() == '') {
			return '';
		}
		$eqLogic = $this->getEqlogic();
		$eqLogic->syncWithFitbit();
	}

	/*     * **********************Getteur Setteur*************************** */
}
