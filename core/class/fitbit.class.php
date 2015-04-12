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

	/*     * ***********************Methode static*************************** */

	public static function pull() {
		foreach (self::byType('fitbit') as $fitbit) {
			if ($fitbit->getIsEnable() == 1) {
				$fitbit->syncWithFitbit();
			}
		}
	}

	/*     * *********************Methode d'instance************************* */

	public function linkToUser() {
		if (!class_exists('OAuth')) {
			throw new Exception('Classe OAuth non trouvée merci de l\'installer : "sudo apt-get update;sudo apt-get install -y php5-dev;sudo pecl install oauth;echo "extension=oauth.so" >> /etc/php5/cli/php.ini;echo "extension=oauth.so" >> /etc/php5/fpm/php.ini; sudo service php5-fpm restart"');
		}
		@session_start();
		$_SESSION['fitbit_Session'] = 0;
		$fitbit = new FitBitPHP(config::byKey('client_key', 'fitbit'), config::byKey('secret_key', 'fitbit'));
		return $fitbit->initSession(network::getNetworkAccess() . '/plugins/fitbit/core/php/callback.php?eqLogic_id=' . $this->getId());
	}

	public function getFitbit() {
		if (!class_exists('OAuth')) {
			throw new Exception('Classe OAuth non trouvée merci de l\'installer : "sudo apt-get update;sudo apt-get install -y php5-dev;sudo pecl install oauth;echo "extension=oauth.so" >> /etc/php5/cli/php.ini;echo "extension=oauth.so" >> /etc/php5/fpm/php.ini; sudo service php5-fpm restart"');
		}
		$fitbit = new FitBitPHP(config::byKey('client_key', 'fitbit'), config::byKey('secret_key', 'fitbit'));
		$fitbit->setOAuthDetails($this->getConfiguration('token'), $this->getConfiguration('secret'));
		return $fitbit;
	}

	public function toHtml($_version = 'dashboard') {
		if ($this->getIsEnable() != 1) {
			return '';
		}
		if (!$this->hasRight('r')) {
			return '';
		}
		$_version = jeedom::versionAlias($_version);
		$mc = cache::byKey('fitbitWidget' . $_version . $this->getId());
		if ($mc->getValue() != '') {
			return $mc->getValue();
		}
		$html = parent::toHtml($_version);
		cache::set('fitbitWidget' . $_version . $this->getId(), $html, 0);
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
		$weight->setEventOnly(1);
		$weight->setEqLogic_id($this->getId());
		$weight->save();

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
			$height->setIsVisible(0);
			$height->setName(__('Taille', __FILE__));
		}
		$height->setType('info');
		$height->setSubType('numeric');
		$height->setUnite('cm');
		$height->setEventOnly(1);
		$height->setEqLogic_id($this->getId());
		$height->save();

		$step = $this->getCmd(null, 'step');
		if (!is_object($step)) {
			$step = new fitbitCmd();
			$step->setLogicalId('step');
			$step->setIsVisible(1);
			$step->setName(__('Pas', __FILE__));
			$step->setTemplate('dashboard', 'fitbit_goal');
			$step->setTemplate('mobile', 'fitbit_goal');
		}

		$step->setType('info');
		$step->setSubType('numeric');
		$step->setEventOnly(1);
		$step->setEqLogic_id($this->getId());
		$step->save();

		$caloriesOut = $this->getCmd(null, 'caloriesOut');
		if (!is_object($caloriesOut)) {
			$caloriesOut = new fitbitCmd();
			$caloriesOut->setLogicalId('caloriesOut');
			$caloriesOut->setIsVisible(1);
			$caloriesOut->setName(__('Calorie', __FILE__));
			$caloriesOut->setTemplate('dashboard', 'fitbit_goal');
			$caloriesOut->setTemplate('mobile', 'fitbit_goal');
		}
		$caloriesOut->setType('info');
		$caloriesOut->setSubType('numeric');
		$caloriesOut->setEventOnly(1);
		$caloriesOut->setEqLogic_id($this->getId());
		$caloriesOut->save();

		$veryActiveMinutes = $this->getCmd(null, 'veryActiveMinutes');
		if (!is_object($veryActiveMinutes)) {
			$veryActiveMinutes = new fitbitCmd();
			$veryActiveMinutes->setLogicalId('veryActiveMinutes');
			$veryActiveMinutes->setIsVisible(1);
			$veryActiveMinutes->setName(__('Minute active', __FILE__));
			$veryActiveMinutes->setTemplate('dashboard', 'fitbit_goal');
			$veryActiveMinutes->setTemplate('mobile', 'fitbit_goal');
		}
		$veryActiveMinutes->setType('info');
		$veryActiveMinutes->setSubType('numeric');
		$veryActiveMinutes->setEventOnly(1);
		$veryActiveMinutes->setUnite('min');
		$veryActiveMinutes->setEqLogic_id($this->getId());
		$veryActiveMinutes->save();

		$distance = $this->getCmd(null, 'distance');
		if (!is_object($distance)) {
			$distance = new fitbitCmd();
			$distance->setLogicalId('distance');
			$distance->setIsVisible(1);
			$distance->setName(__('Distance', __FILE__));
			$distance->setTemplate('dashboard', 'fitbit_goal');
			$distance->setTemplate('mobile', 'fitbit_goal');
		}
		$distance->setType('info');
		$distance->setSubType('numeric');
		$distance->setEventOnly(1);
		$distance->setUnite('km');
		$distance->setEqLogic_id($this->getId());
		$distance->save();

		if ($this->getConfiguration('encodedId') != '') {
			$this->syncWithFitbit();
		}
	}

	public function syncWithFitbit() {
		$fitbit = $this->getFitbit();
		$fitbit->setUser($this->getConfiguration('encodedId'));
		$activity = json_decode(json_encode($fitbit->getActivities(new DateTime())), true);
		$users = json_decode(json_encode($fitbit->getProfile()), true);

		log::add('fitbit', 'debug', 'Activiy : ' . print_r($activity, true));
		log::add('fitbit', 'debug', 'Users : ' . print_r($users, true));

		$user = $users['user'];
		$weight = $this->getCmd(null, 'weight');
		if (is_object($weight)) {
			if ($weight->execCmd() != $weight->formatValue($user['weight'])) {
				$weight->setCollectDate('');
				$weight->event($user['weight']);
			}
		}

		$height = $this->getCmd(null, 'height');
		if (is_object($height)) {
			if ($height->execCmd() != $height->formatValue($user['height'])) {
				$height->setCollectDate('');
				$height->event($user['height']);
			}
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
			if ($distance->execCmd() != $distance->formatValue($activity['summary']['distances']['activityDistance'][0]['distance'])) {
				$distance->setCollectDate('');
				$distance->event($activity['summary']['distances']['activityDistance'][0]['distance']);
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

	public function toHtml($_version = 'dashboard', $options = '', $_cmdColor = NULL, $_cache = 2) {
		$html = parent::toHtml($_version, $options, $_cmdColor, $_cache);
		$replace = array(
			'#goal#' => $this->getConfiguration('goal'),
		);
		return template_replace($replace, $html);
	}

	public function execute($_options = null) {
		if ($this->getType() == '') {
			return '';
		}
		$eqLogic = $this->getEqlogic();
		$eqLogic->syncWithFitbit();
	}

	/*     * **********************Getteur Setteur*************************** */
}
