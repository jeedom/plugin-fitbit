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
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

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

	public function getProvider() {
		return new FitbitProvider([
			'clientId' => $this->getConfiguration('client_id'),
			'clientSecret' => $this->getConfiguration('client_secret'),
			'redirectUri' => network::getNetworkAccess('external') . '/plugins/fitbit/core/php/callback.php?eqLogic_id=' . $this->getId(),
		]);
	}

	public function linkToUser() {
		@session_start();
		$provider = $this->getProvider();
		$authorizationUrl = $provider->getAuthorizationUrl();
		$_SESSION['oauth2state'] = $provider->getState();
		return $authorizationUrl;
	}

	public function getAccessToken() {
		$provider = $this->getProvider();
		$existingAccessToken = new AccessToken($this->getConfiguration('accessToken'));
		if ($existingAccessToken->hasExpired()) {
			$newAccessToken = $provider->getAccessToken('refresh_token', [
				'refresh_token' => $existingAccessToken->getRefreshToken(),
			]);
			$this->setConfiguration('accessToken', $newAccessToken->jsonSerialize());
			$this->save();
			return $newAccessToken;
		}
		return $existingAccessToken;
	}

	public function request($_type, $_request) {

		$headers = array(
			'Accept-Language' => 'fr_FR',
			'Accept-Locale' => 'fr_FR',
		);
		$provider = $this->getProvider();
		$request = $provider->getAuthenticatedRequest($_type, FitbitProvider::BASE_FITBIT_API_URL . $_request, $this->getAccessToken(), array('headers' => $headers));
		return $provider->getResponse($request);
	}

	public function postSave() {
		$cmd = $this->getCmd(null, 'weight');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('weight');
			$cmd->setIsVisible(1);
			$cmd->setOrder(1);
			$cmd->setName(__('Poids', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setUnite('kg');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'fat');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('fat');
			$cmd->setIsVisible(1);
			$cmd->setOrder(2);
			$cmd->setName(__('Masse grasse', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setUnite('%');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'bmi');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('bmi');
			$cmd->setIsVisible(1);
			$cmd->setOrder(3);
			$cmd->setName(__('IMC', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'height');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('height');
			$cmd->setIsVisible(1);
			$cmd->setOrder(4);
			$cmd->setName(__('Taille', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setUnite('cm');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'step');
		if (!is_object($cmd)) {
			$step = new fitbitCmd();
			$cmd->setLogicalId('step');
			$cmd->setIsVisible(1);
			$cmd->setOrder(5);
			$cmd->setName(__('Pas', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}

		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'caloriesOut');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('caloriesOut');
			$cmd->setIsVisible(1);
			$cmd->setOrder(6);
			$cmd->setName(__('Calorie', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'veryActiveMinutes');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('veryActiveMinutes');
			$cmd->setIsVisible(1);
			$cmd->setOrder(7);
			$cmd->setName(__('Minute active', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setUnite('min');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'restingHeartRate');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('restingHeartRate');
			$cmd->setIsVisible(1);
			$cmd->setOrder(7);
			$cmd->setName(__('Fréquence cardiaque au repos', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setUnite('bpm');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'heartOutOfRange');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('heartOutOfRange');
			$cmd->setIsVisible(1);
			$cmd->setOrder(7);
			$cmd->setName(__('Coeur temps au repos', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setUnite('min');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'heartFatBurn');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('heartFatBurn');
			$cmd->setIsVisible(1);
			$cmd->setOrder(7);
			$cmd->setName(__('Coeur temps perte de graisse', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setUnite('min');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'heartCardio');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('heartCardio');
			$cmd->setIsVisible(1);
			$cmd->setOrder(7);
			$cmd->setName(__('Coeur temps cardio', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setUnite('min');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'heartPeak');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('heartPeak');
			$cmd->setIsVisible(1);
			$cmd->setOrder(7);
			$cmd->setName(__('Coeur temps maximum', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setUnite('min');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'heartOutOfRangeCalories');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('heartOutOfRangeCalories');
			$cmd->setIsVisible(1);
			$cmd->setOrder(7);
			$cmd->setName(__('Coeur calories au repos', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setUnite('min');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'heartFatBurnCalories');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('heartFatBurnCalories');
			$cmd->setIsVisible(1);
			$cmd->setOrder(7);
			$cmd->setName(__('Coeur calories perte de graisse', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setUnite('min');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'heartCardioCalories');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('heartCardioCalories');
			$cmd->setIsVisible(1);
			$cmd->setOrder(7);
			$cmd->setName(__('Coeur calories cardio', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setUnite('min');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'heartPeakCalories');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('heartPeakCalories');
			$cmd->setIsVisible(1);
			$cmd->setOrder(7);
			$cmd->setName(__('Coeur calories maximum', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setUnite('min');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'distance');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('distance');
			$cmd->setIsVisible(1);
			$cmd->setOrder(8);
			$cmd->setName(__('Distance', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setUnite('km');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'elevation');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('elevation');
			$cmd->setIsVisible(1);
			$cmd->setOrder(9);
			$cmd->setName(__('Elevation', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setUnite('m');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'awakeCount');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('awakeCount');
			$cmd->setIsVisible(1);
			$cmd->setOrder(10);
			$cmd->setName(__('Nombre de reveils', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'awakeDuration');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('awakeDuration');
			$cmd->setIsVisible(1);
			$cmd->setOrder(11);
			$cmd->setName(__('Durée réveils', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setUnite('min');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'sleepDuration');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('sleepDuration');
			$cmd->setIsVisible(1);
			$cmd->setOrder(12);
			$cmd->setName(__('Durée de sommeil', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setUnite('min');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'restlessDuration');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('restlessDuration');
			$cmd->setIsVisible(1);
			$cmd->setOrder(13);
			$cmd->setName(__('Sommeil agité', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setUnite('min');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'minutesToFallAsleep');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('minutesToFallAsleep');
			$cmd->setIsVisible(1);
			$cmd->setOrder(14);
			$cmd->setName(__('Temps pour s\'endormir', __FILE__));
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setUnite('min');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'refresh');
		if (!is_object($cmd)) {
			$cmd = new fitbitCmd();
			$cmd->setLogicalId('refresh');
			$cmd->setIsVisible(1);
			$cmd->setName(__('Rafraîhir', __FILE__));
		}
		$cmd->setType('action');
		$cmd->setSubType('other');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
	}

	public function syncWithFitbit() {
		$hearts = $this->request('GET', '/1/user/-/activities/heart/date/' . date('Y-m-d') . '/1d.json');
		$users = $this->request('GET', '/1/user/-/profile.json');
		$activity = $this->request('GET', '/1/user/-/activities/date/' . date('Y-m-d') . '.json');
		$sleeps = $this->request('GET', '/1/user/-/sleep/date/' . date('Y-m-d') . '.json');
		$bodies = $this->request('GET', '/1/user/-/body/date/' . date('Y-m-d') . '.json');

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

		if (isset($hearts['activities-heart'][0]) && isset($hearts['activities-heart'][0]['value'])) {
			$heart = $hearts['activities-heart'][0]['value'];
			$restingHeartRate = $this->getCmd(null, 'restingHeartRate');
			if (is_object($restingHeartRate) && $restingHeartRate->execCmd() != $restingHeartRate->formatValue($heart['restingHeartRate'])) {
				$restingHeartRate->setCollectDate('');
				$restingHeartRate->event($heart['restingHeartRate']);
			}

			$heartOutOfRange = $this->getCmd(null, 'heartOutOfRange');
			if (is_object($heartOutOfRange) && $heartOutOfRange->execCmd() != $heartOutOfRange->formatValue($heart['heartRateZones'][0]['minutes'])) {
				$heartOutOfRange->setCollectDate('');
				$heartOutOfRange->event($heart['heartRateZones'][0]['minutes']);
			}

			$heartFatBurn = $this->getCmd(null, 'heartFatBurn');
			if (is_object($heartFatBurn) && $heartFatBurn->execCmd() != $heartFatBurn->formatValue($heart['heartRateZones'][1]['minutes'])) {
				$heartFatBurn->setCollectDate('');
				$heartFatBurn->event($heart['heartRateZones'][1]['minutes']);
			}

			$heartCardio = $this->getCmd(null, 'heartCardio');
			if (is_object($heartCardio) && $heartCardio->execCmd() != $heartCardio->formatValue($heart['heartRateZones'][2]['minutes'])) {
				$heartCardio->setCollectDate('');
				$heartCardio->event($heart['heartRateZones'][2]['minutes']);
			}

			$heartPeak = $this->getCmd(null, 'heartPeak');
			if (is_object($heartPeak) && $heartPeak->execCmd() != $heartPeak->formatValue($heart['heartRateZones'][3]['minutes'])) {
				$heartPeak->setCollectDate('');
				$heartPeak->event($heart['heartRateZones'][3]['minutes']);
			}

			$heartOutOfRangeCalories = $this->getCmd(null, 'heartOutOfRangeCalories');
			if (is_object($heartOutOfRangeCalories) && $heartOutOfRangeCalories->execCmd() != $heartOutOfRangeCalories->formatValue($heart['heartRateZones'][0]['caloriesOut'])) {
				$heartOutOfRangeCalories->setCollectDate('');
				$heartOutOfRangeCalories->event($heart['heartRateZones'][0]['caloriesOut']);
			}

			$heartFatBurnCalories = $this->getCmd(null, 'heartFatBurnCalories');
			if (is_object($heartFatBurnCalories) && $heartFatBurnCalories->execCmd() != $heartFatBurnCalories->formatValue($heart['heartRateZones'][1]['caloriesOut'])) {
				$heartFatBurnCalories->setCollectDate('');
				$heartFatBurnCalories->event($heart['heartRateZones'][1]['caloriesOut']);
			}

			$heartCardioCalories = $this->getCmd(null, 'heartCardioCalories');
			if (is_object($heartCardioCalories) && $heartCardioCalories->execCmd() != $heartCardioCalories->formatValue($heart['heartRateZones'][2]['caloriesOut'])) {
				$heartCardioCalories->setCollectDate('');
				$heartCardioCalories->event($heart['heartRateZones'][2]['caloriesOut']);
			}

			$heartPeakCalories = $this->getCmd(null, 'heartPeakCalories');
			if (is_object($heartPeakCalories) && $heartPeakCalories->execCmd() != $heartPeakCalories->formatValue($heart['heartRateZones'][3]['caloriesOut'])) {
				$heartPeakCalories->setCollectDate('');
				$heartPeakCalories->event($heart['heartRateZones'][3]['caloriesOut']);
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
		$this->refreshWidget();
	}

	/*     * **********************Getteur Setteur*************************** */
}

class fitbitCmd extends cmd {
	/*     * *************************Attributs****************************** */

	public static $_widgetPossibility = array('custom' => false);

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

class FitbitProvider extends AbstractProvider {
	use BearerAuthorizationTrait;
	const BASE_FITBIT_URL = 'https://www.fitbit.com';
	const BASE_FITBIT_API_URL = 'https://api.fitbit.com';

	public function getBaseAuthorizationUrl() {
		return static::BASE_FITBIT_URL . '/oauth2/authorize';
	}

	public function getBaseAccessTokenUrl(array $params) {
		return static::BASE_FITBIT_API_URL . '/oauth2/token';
	}

	public function getResourceOwnerDetailsUrl(AccessToken $token) {
		return static::BASE_FITBIT_API_URL . '/1/user/-/profile.json';
	}

	protected function getDefaultScopes() {
		return ['activity', 'heartrate', 'location', 'profile', 'settings', 'sleep', 'social', 'weight'];
	}

	protected function getScopeSeparator() {
		return ' ';
	}

	protected function getAuthorizationParameters(array $options) {
		$params = parent::getAuthorizationParameters($options);
		unset($params['approval_prompt']);
		if (!empty($options['prompt'])) {
			$params['prompt'] = $options['prompt'];
		}
		return $params;
	}

	protected function getAccessTokenOptions(array $params) {
		$options = parent::getAccessTokenOptions($params);
		$options['headers']['Authorization'] = 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret);
		return $options;
	}

	public function createResourceOwner(array $response, AccessToken $token) {
		return new FitbitOwnerInterface($response);
	}

	protected function checkResponse(ResponseInterface $response, $data) {
		if ($response->getStatusCode() >= 400) {
			$errorMessage = '';
			if (!empty($data['errors'])) {
				foreach ($data['errors'] as $error) {
					if (!empty($errorMessage)) {
						$errorMessage .= ' , ';
					}
					$errorMessage .= implode(' - ', $error);
				}
			} else {
				$errorMessage = $response->getReasonPhrase();
			}
			throw new IdentityProviderException(
				$errorMessage,
				$response->getStatusCode(),
				$response
			);
		}
	}

}

class FitbitOwnerInterface implements ResourceOwnerInterface {
	protected $userInfo = [];

	public function __construct(array $response) {
		$this->userInfo = $response['user'];
	}
	public function getId() {
		return $this->userInfo['encodedId'];
	}

	public function getDisplayName() {
		return $this->userInfo['displayName'];
	}

	public function toArray() {
		return $this->userInfo;
	}
}