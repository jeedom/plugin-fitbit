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
		$weight = $this->getCmd(null, 'weight');
		if (!is_object($weight)) {
			$weight = new fitbitCmd();
			$weight->setLogicalId('weight');
			$weight->setIsVisible(1);
			$weight->setOrder(1);
			$weight->setName(__('Poids', __FILE__));
			$weight->setTemplate('dashboard', 'line');
			$weight->setTemplate('mobile', 'line');
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
			$fat->setOrder(2);
			$fat->setName(__('Masse grasse', __FILE__));
			$fat->setTemplate('dashboard', 'line');
			$fat->setTemplate('mobile', 'line');
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
			$bmi->setOrder(3);
			$bmi->setName(__('IMC', __FILE__));
			$bmi->setTemplate('dashboard', 'line');
			$bmi->setTemplate('mobile', 'line');
		}
		$bmi->setType('info');
		$bmi->setSubType('numeric');
		$bmi->setEqLogic_id($this->getId());
		$bmi->save();

		$height = $this->getCmd(null, 'height');
		if (!is_object($height)) {
			$height = new fitbitCmd();
			$height->setLogicalId('height');
			$height->setIsVisible(1);
			$height->setOrder(4);
			$height->setName(__('Taille', __FILE__));
			$height->setTemplate('dashboard', 'line');
			$height->setTemplate('mobile', 'line');
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
			$step->setOrder(5);
			$step->setName(__('Pas', __FILE__));
			$step->setTemplate('dashboard', 'line');
			$step->setTemplate('mobile', 'line');
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
			$caloriesOut->setOrder(6);
			$caloriesOut->setName(__('Calorie', __FILE__));
			$caloriesOut->setTemplate('dashboard', 'line');
			$caloriesOut->setTemplate('mobile', 'line');
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
			$veryActiveMinutes->setOrder(7);
			$veryActiveMinutes->setName(__('Minute active', __FILE__));
			$veryActiveMinutes->setTemplate('dashboard', 'line');
			$veryActiveMinutes->setTemplate('mobile', 'line');
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
			$distance->setOrder(8);
			$distance->setName(__('Distance', __FILE__));
			$distance->setTemplate('dashboard', 'line');
			$distance->setTemplate('mobile', 'line');
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
			$elevation->setOrder(9);
			$elevation->setName(__('Elevation', __FILE__));
			$elevation->setTemplate('dashboard', 'line');
			$elevation->setTemplate('mobile', 'line');
		}
		$elevation->setType('info');
		$elevation->setSubType('numeric');
		$elevation->setUnite('m');
		$elevation->setEqLogic_id($this->getId());
		$elevation->save();

		$awakeCount = $this->getCmd(null, 'awakeCount');
		if (!is_object($awakeCount)) {
			$awakeCount = new fitbitCmd();
			$awakeCount->setLogicalId('awakeCount');
			$awakeCount->setIsVisible(1);
			$awakeCount->setOrder(10);
			$awakeCount->setName(__('Nombre de reveils', __FILE__));
			$awakeCount->setTemplate('dashboard', 'line');
			$awakeCount->setTemplate('mobile', 'line');
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
			$awakeDuration->setOrder(11);
			$awakeDuration->setName(__('Durée réveils', __FILE__));
			$awakeDuration->setTemplate('dashboard', 'line');
			$awakeDuration->setTemplate('mobile', 'line');
		}
		$awakeDuration->setType('info');
		$awakeDuration->setSubType('numeric');
		$awakeDuration->setUnite('min');
		$awakeDuration->setEqLogic_id($this->getId());
		$awakeDuration->save();

		$sleepDuration = $this->getCmd(null, 'sleepDuration');
		if (!is_object($sleepDuration)) {
			$sleepDuration = new fitbitCmd();
			$sleepDuration->setLogicalId('sleepDuration');
			$sleepDuration->setIsVisible(1);
			$sleepDuration->setOrder(12);
			$sleepDuration->setName(__('Durée de sommeil', __FILE__));
			$sleepDuration->setTemplate('dashboard', 'line');
			$sleepDuration->setTemplate('mobile', 'line');
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
			$restlessDuration->setOrder(13);
			$restlessDuration->setName(__('Sommeil agité', __FILE__));
			$restlessDuration->setTemplate('dashboard', 'line');
			$restlessDuration->setTemplate('mobile', 'line');
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
			$minutesToFallAsleep->setOrder(14);
			$minutesToFallAsleep->setName(__('Temps pour s\'endormir', __FILE__));
			$minutesToFallAsleep->setTemplate('dashboard', 'line');
			$minutesToFallAsleep->setTemplate('mobile', 'line');
		}
		$minutesToFallAsleep->setType('info');
		$minutesToFallAsleep->setSubType('numeric');
		$minutesToFallAsleep->setUnite('min');
		$minutesToFallAsleep->setEqLogic_id($this->getId());
		$minutesToFallAsleep->save();

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
	}

	public function syncWithFitbit() {
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