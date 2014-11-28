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

    public static function getCallbackUri() {
        return config::byKey('externalAddr') . '/plugins/fitbit/core/php/callback.php';
    }

    public static function initFitbitOAuth($_new = false) {
        $fitbit = new FitBitPHP(config::byKey('client_key', 'fitbit'), config::byKey('secret_key', 'fitbit'), 1, null, 'json');
        if (!$_new) {
            $fitbit->setOAuthDetails(config::byKey('fitbit_Token', 'fitbit'), config::byKey('fitbit_Secret', 'fitbit'));
        } else {
            $fitbit->initSession(self::getCallbackUri());
        }
        return $fitbit;
    }

    public static function testFitbitConnexion() {
        try {
            $oauth = self::initFitbitOAuth();
            $oauth->getProfile();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    public static function syncWithFitbit() {
        $oauth = self::initFitbitOAuth();
        $json = $oauth->getProfile();
        foreach ($json['user'] as $user) {
            $eqLogic = fitbit::byLogicalId($user['encodedId'], 'fitbit');
            if (!is_object($eqLogic)) {
                $eqLogic = new fitbit();
                $eqLogic->setName($user['displayName']);
                $eqLogic->setEqType_name('fitbit');
                $eqLogic->setIsVisible(1);
                $eqLogic->setIsEnable(1);
                $eqLogic->setLogicalId($user['encodedId']);
                $eqLogic->save();
            }
            
             $hint = $eqLogic->getCmd(null, 'hint');
                if (!is_object($hint)) {
                    $hint = new fitbitCmd();
                    $hint->setLogicalId('hint');
                    $hint->setIsVisible(1);
                    $hint->setName(__('Conseil', __FILE__));
                    $hint->setTemplate('dashboard', 'koubachi_hint');
                    $hint->setTemplate('mobile', 'koubachi_hint');
                    $hint->setOrder(3);
                }
        }
    }

    /*     * *********************Methode d'instance************************* */

    /*     * **********************Getteur Setteur*************************** */
}

class fitbitCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    public function execute($_options = null) {
        return '';
    }

    /*     * **********************Getteur Setteur*************************** */
}
