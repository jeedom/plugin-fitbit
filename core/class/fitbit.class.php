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


class fitbit extends eqLogic {
	/*     * *************************Attributs****************************** */
	
	/*     * ***********************Methode static*************************** */
	
	public static function cron30() {
		foreach (self::byType('fitbit') as $fitbit) {
			if ($fitbit->getIsEnable() == 1) {
				try {
					$fitbit->refreshData();
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
	
	public static function getValue($_key,$_data,$_default = null){
		$datetime = date('Y-m-d H:i:s');
		$value = $_data;
		$keys = explode('::',$_key);
		foreach ($keys as &$key) {
			if(!isset($value[$key])){
				return array('value' => null);
			}
			$value = $value[$key];
			if(isset($value['date']) && isset($value['time'])){
				$datetime = $value['date'].' '.$value['time'];
			}
		}
		return array('value' => $value,'datetime' => $datetime);
	}
	
	public static function getData($_path,$_user_logical){
		$_path = str_replace('#date#',date('Y-m-d'),$_path);
		$url = config::byKey('service::cloud::url').'/service/fitbit?path='.urlencode($_path);
		$url .= '&user_logical='.$_user_logical;
		$request_http = new com_http($url);
		$cloudToken = null;
		if (is_callable(array('repo_market', 'getCloudToken'))) {
			try {
				$cloudToken = repo_market::getCloudToken();
			} catch (Exception $e) {
				if ($e->getCode() !== 401) {
					throw $e;
				}
			}
		}
		if ($cloudToken) {
			if (strtolower((string) parse_url($request_http->getUrl(), PHP_URL_SCHEME)) !== 'https') {
				throw new Exception(__('Le service Cloud doit utiliser HTTPS.', __FILE__));
			}
			$request_http->setNoSslCheck(false);
		}
		$request_http->setHeader(array('Content-Type: application/json',$cloudToken ? 'Authorization: Bearer ' . $cloudToken : 'Autorization: '.sha512(mb_strtolower(config::byKey('market::username')).':'.config::byKey('market::password'))));
		$result = json_decode($request_http->exec(30,1),true);
		if(!is_array($result)){
			throw new Exception(__('[Fitbit] Erreur lors de la récuperation des données : ',__FILE__).$result);
		}
		return $result;
	}
	
	/*     * *********************Methode d'instance************************* */
	
	public function refreshData(){
		$data = array();
		foreach ($this->getCmd('info') as $cmd) {
			if(!isset($data[$cmd->getConfiguration('path')])){
				$data[$cmd->getConfiguration('path')] = self::getData($cmd->getConfiguration('path'),$this->getId());
				$data[$cmd->getConfiguration('path')]['datetime'] = date('Y-m-d H:i:s');
				if(isset($data[$cmd->getConfiguration('path')]['date']) && isset($data[$cmd->getConfiguration('path')]['time'])){
					$data[$cmd->getConfiguration('path')]['datetime'] = $data[$cmd->getConfiguration('path')]['date'].' '.$data[$cmd->getConfiguration('path')]['time'];
				}
				if(isset($data[$cmd->getConfiguration('path')][0]) && $data[$cmd->getConfiguration('path')][0]['date'] && $data[$cmd->getConfiguration('path')][0]['time']){
					$data[$cmd->getConfiguration('path')][0]['datetime'] = $data[$cmd->getConfiguration('path')][0]['date'].' '.$data[$cmd->getConfiguration('path')]['time'];
				}
			}
			$value = self::getValue($cmd->getLogicalId(),$data[$cmd->getConfiguration('path')]);
			if($value['value'] !== null && $value['value'] != ''){
				$this->checkAndUpdateCmd($cmd, $value['value'],$value['datetime']);
			}
		}
	}
	
	public function postInsert() {
		$device = json_decode(file_get_contents(__DIR__.'/../config/fitbit.json'),true);
		if (is_array($device)) {
			$this->import($device);
		}
	}
	
	/*     * **********************Getteur Setteur*************************** */
}

class fitbitCmd extends cmd {
	/*     * *************************Attributs****************************** */
	
	/*     * ***********************Methode static*************************** */
	
	/*     * *********************Methode d'instance************************* */
	
	public function execute($_options = null) {
		$this->getEqlogic()->refreshData();
	}
	
	/*     * **********************Getteur Setteur*************************** */
}