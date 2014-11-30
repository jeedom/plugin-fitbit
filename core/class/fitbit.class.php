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
        $fitbit = self::initFitbitOAuth();
        $json = $fitbit->getProfile();
        foreach ($json as $user) {
            $eqLogic = fitbit::byLogicalId($user['encodedId'], 'fitbit');
            if (!is_object($eqLogic)) {
                continue;
            }
            $fitbit->setUser($user['encodedId']);
            $activity = $fitbit->getActivities(new DateTime());
            $weight = $eqLogic->getCmd(null, 'weight');
            if (is_object($weight)) {
                if ($weight->execCmd() != $weight->formatValue($user['weight'])) {
                    $weight->setCollectDate('');
                    $weight->event($user['weight']);
                }
            }

            $height = $eqLogic->getCmd(null, 'height');
            if (is_object($height)) {
                if ($height->execCmd() != $height->formatValue($user['height'])) {
                    $height->setCollectDate('');
                    $height->event($user['height']);
                }
            }

            $step = $eqLogic->getCmd(null, 'step');
            if (is_object($step)) {
                if ($step->execCmd() != $step->formatValue($activity['summary']['step'])) {
                    $step->setCollectDate('');
                    $step->event($activity['summary']['step']);
                }
            }

            $caloriesOut = $eqLogic->getCmd(null, 'caloriesOut');
            if (is_object($caloriesOut)) {
                if ($caloriesOut->execCmd() != $caloriesOut->formatValue($activity['summary']['caloriesOut'])) {
                    $caloriesOut->setCollectDate('');
                    $caloriesOut->event($activity['summary']['caloriesOut']);
                }
            }

            $veryActiveMinutes = $eqLogic->getCmd(null, 'veryActiveMinutes');
            if (is_object($veryActiveMinutes)) {
                if ($veryActiveMinutes->execCmd() != $veryActiveMinutes->formatValue($activity['summary']['veryActiveMinutes'])) {
                    $veryActiveMinutes->setCollectDate('');
                    $veryActiveMinutes->event($activity['summary']['veryActiveMinutes']);
                }
            }

            $distance = $eqLogic->getCmd(null, 'distance');
            if (is_object($distance)) {
                if ($distance->execCmd() != $distance->formatValue($activity['summary']['distances'][0]['distance'])) {
                    $distance->setCollectDate('');
                    $distance->event($activity['summary']['distances'][0]['distance']);
                }
            }
            $mc = cache::byKey('fitbitWidgetmobile' . $eqLogic->getId());
            $mc->remove();
            $mc = cache::byKey('fitbitWidgetdashboard' . $eqLogic->getId());
            $mc->remove();
            $eqLogic->toHtml('mobile');
            $eqLogic->toHtml('dashboard');
            $eqLogic->refreshWidget();
        }
    }

    public static function getCallbackUri($_arg = '/plugins/fitbit/core/php/callback.php') {
        $url = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if (strpos($url, '/plugins') !== false) {
            $url = substr($url, 0, strpos($url, '/plugins'));
        } else {
            $url = substr($url, 0, strpos($url, '/index.php'));
        }
        return $url . $_arg;
    }

    public static function initFitbitOAuth($_new = false) {
        $fitbit = new FitBitPHP(config::byKey('client_key', 'fitbit'), config::byKey('secret_key', 'fitbit'), 1, null, 'json');
        if (!$_new) {
            $fitbit->setOAuthDetails(config::byKey('fitbit_Token', 'fitbit'), config::byKey('fitbit_Secret', 'fitbit'));
        } else {
            if (!self::testFitbitConnexion()) {
                return $fitbit->initSession(self::getCallbackUri());
            } else {
                $fitbit->setOAuthDetails(config::byKey('fitbit_Token', 'fitbit'), config::byKey('fitbit_Secret', 'fitbit'));
            }
        }
        return $fitbit;
    }

    public static function testFitbitConnexion() {
        try {
            $fitbit = self::initFitbitOAuth();
            $fitbit->getProfile();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    public static function syncWithFitbit() {
        $fitbit = self::initFitbitOAuth();
        $json = $fitbit->getProfile();
        foreach ($json as $user) {
            $eqLogic = fitbit::byLogicalId($user['encodedId'], 'fitbit');
            $fitbit->setUser($user['encodedId']);
            $activity = $fitbit->getActivities(new DateTime());
            if (!is_object($eqLogic)) {
                $eqLogic = new fitbit();
                $eqLogic->setName($user['displayName']);
                $eqLogic->setEqType_name('fitbit');
                $eqLogic->setIsVisible(1);
                $eqLogic->setIsEnable(1);
                $eqLogic->setLogicalId($user['encodedId']);
                $eqLogic->save();
            }

            $weight = $eqLogic->getCmd(null, 'weight');
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
            $weight->setEqLogic_id($eqLogic->getId());
            $weight->save();

            $height = $eqLogic->getCmd(null, 'height');
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
            $height->setEqLogic_id($eqLogic->getId());
            $height->save();

            $step = $eqLogic->getCmd(null, 'step');
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
            $step->setEqLogic_id($eqLogic->getId());
            $step->setConfiguration('goal', $activity['goals']['steps']);
            $step->save();

            $caloriesOut = $eqLogic->getCmd(null, 'caloriesOut');
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
            $caloriesOut->setEqLogic_id($eqLogic->getId());
            $caloriesOut->setConfiguration('goal', $activity['goals']['caloriesOut']);
            $caloriesOut->save();

            $veryActiveMinutes = $eqLogic->getCmd(null, 'veryActiveMinutes');
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
            $veryActiveMinutes->setEqLogic_id($eqLogic->getId());
            $veryActiveMinutes->setConfiguration('goal', $activity['goals']['activeMinutes']);
            $veryActiveMinutes->save();

            $distance = $eqLogic->getCmd(null, 'distance');
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
            $distance->setEqLogic_id($eqLogic->getId());
            $distance->setConfiguration('goal', $activity['goals']['distance']);
            $distance->save();
        }
        fitbit::pull();
    }

    /*     * *********************Methode d'instance************************* */

    public function toHtml($_version = 'dashboard') {
        if ($this->getIsEnable() != 1) {
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
        return '';
    }

    /*     * **********************Getteur Setteur*************************** */
}
