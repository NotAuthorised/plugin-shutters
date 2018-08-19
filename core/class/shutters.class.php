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
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once 'shuttersCmd.class.php';

class shutters extends eqLogic
{
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {

      }
     */



    /*     * *********************Méthodes 
    d'instance************************* */

    public function preInsert()
    {
        $this->setConfiguration('isObjectCreated', false);
    }

    public function postInsert()
    {

    }

    public function preSave()
    {

    }

    public function postSave()
    {
   
    }

    public function preUpdate()
    {
        $exceptionMessage = NULL;

        $openingTypeList = array('window', 'door');
        $dawnTypeList =  array('sunrise', 'civilDawn', 'nauticalDawn', 'astronomicalDawn');
        $duskTypeList = array('sunset', 'civilDusk', 'nauticalDusk', 'astronomicalDusk');
        $angleUnitList = array('deg', 'gon');

        $objectType = $this->getConfiguration('objectType', null);
        $isObjectCreated = $this->getConfiguration('isObjectCreated', false);

        $incomingAzimuthAngle = $this->getConfiguration('outgoingAzimuthAngle', null);
        $outgoingAzimuthAngle = $this->getConfiguration('outgoingAzimuthAngle', null);
        $shutterArea = $this->getConfiguration('shutterArea', null);

        if (empty($objectType)) {
            throw new \Exception (__('Le type d\'équipement doit être renseigné!', __FILE__));
            return;
        }
        if ($isObjectCreated) {
            log::add('shutters','debug', '[isObjectCreated] => '.$isObjectCreated);
            if ($objectType === 'externalInfo') {
                $cmd = $this->getConfiguration('absenceInfoCmd', null);
                if (!empty($cmd)) {
                    $cmdId=cmd::byId(str_replace('#','',$cmd));
                    if (!is_object($cmdId)) {
                        throw new \Exception (__('[Information d\'absence] La commande suivante est inconnue : ', __FILE__) . $cmd);
                        return;
                    }
                    if (empty($this->getConfiguration('absenceInfoCmdStatus'))) {
                        throw new \Exception (__('[Information d\'absence] Veuillez valider le statut de la commande ' . $cmdName, __FILE__) . $cmd);
                        return;
                    } 
                }
                $cmd = $this->getConfiguration('presenceInfoCmd', null);
                if (!empty($cmd)) {
                    $cmdId=cmd::byId(str_replace('#','',$cmd));
                    if (!is_object($cmdId)) {
                        throw new \Exception (__('[Information de présence] La commande suivante est inconnue : ', __FILE__) . $cmd);
                        return;
                    }
                    if (empty($this->getConfiguration('presenceInfoCmdStatus'))) {
                        throw new \Exception (__('[Information de présence] Veuillez valider le statut de la commande ' . $cmdName, __FILE__) . $cmd);
                        return;
                    } 
                }
                $cmd = $this->getConfiguration('fireDetectionCmd', null);
                if (!empty($cmd)) {
                    $cmdId=cmd::byId(str_replace('#','',$cmd));
                    if (!is_object($cmdId)) {
                        throw new \Exception (__('[Détection incendie] La commande suivante est inconnue : ', __FILE__) . $cmd);
                        return;
                    }
                    if (empty($this->getConfiguration('fireDetectionCmdStatus'))) {
                        throw new \Exception (__('[Détection incendie] Veuillez valider le statut de la commande ' . $cmdName, __FILE__) . $cmd);
                        return;
                    } 
                }
                $cmd = $this->getConfiguration('outdoorLuminosityCmd', null);
                if (!empty($cmd)) {
                    $cmdId=cmd::byId(str_replace('#','',$cmd));
                    if (!is_object($cmdId)) {
                        throw new \Exception (__('[Luminosité extérieure] La commande suivante est inconnue : ', __FILE__) . $cmd);
                        return;
                    }
                    if (empty($this->getConfiguration('outdoorLuminosityCmdStatus'))) {
                        throw new \Exception (__('[Luminosité extérieure] Veuillez valider le statut de la commande ' . $cmdName, __FILE__) . $cmd);
                        return;
                    } 
                }
                $cmd = $this->getConfiguration('outdoorTemperatureCmd', null);
                if (!empty($cmd)) {
                    $cmdId=cmd::byId(str_replace('#','',$cmd));
                    if (!is_object($cmdId)) {
                        throw new \Exception (__('[Température extérieure] La commande suivante est inconnue : ', __FILE__) . $cmd);
                        return;
                    }
                    if (empty($this->getConfiguration('outdoorTemperatureCmdStatus'))) {
                        throw new \Exception (__('[Température extérieure] Veuillez valider le statut de la commande ' . $cmdName, __FILE__) . $cmd);
                        return;
                    } 
                }
    
            } elseif($objectType === 'heliotropeZone') {
                $heliotrope = eqLogic::byId($this->getConfiguration('heliotrope'));
                if (!(is_object($heliotrope) && $heliotrope->getEqType_name() == 'heliotrope')) {
                    throw new \Exception (__('L\'équipement héliotrope doit être renseigné!', __FILE__));
                    return;
                }        

                if (!in_array($this->getConfiguration('dawnType'), $dawnTypeList, true)) {
                    throw new \Exception (__('Le lever du soleil doit être renseigné!', __FILE__));
                    return;
                }        
                if (!in_array($this->getConfiguration('duskType'), $duskTypeList, true)) {
                    throw new \Exception (__('La coucher du soleil doit être renseigné!', __FILE__));
                    return;
                } 
                $wallAngleUnit = $this->getConfiguration('wallAngleUnit');
                $wallAngle = $this->getConfiguration('wallAngle');
                if (!in_array($wallAngleUnit, $angleUnitList, true)) {
                    throw new \Exception (__('L\'unité de l\'angle doit être renseignée!', __FILE__));
                    return;
                } 
                if ($wallAngleUnit == 'deg' && ($wallAngle < 0 || $wallAngle > 360)) {
                    throw new \Exception (__('L\'angle de la façade par rapport au nord doit être renseigné et compris entre 0 et 360°!', __FILE__));
                    return;
                }
                if ($wallAngleUnit == 'gon' && ($wallAngle < 0 || $wallAngle > 400)) {
                    throw new \Exception (__('L\'angle de la façade par rapport au nord doit être renseigné et compris entre 0 et 400gon!', __FILE__));
                    return;
                }
            
            } elseif($objectType === 'shutter') {
                if (!in_array($this->getConfiguration('openingType'), $openingTypeList, true)) {
                    throw new \Exception (__('Le type d\'ouvrant associé au volet doit être renseigné!', __FILE__));
                    return;
                }
                if (!empty($this->getConfiguration('openOpeningInfo'))) {
                    $cmdId=cmd::byId(str_replace('#','',$this->getConfiguration('openOpeningInfo')));
                    if (!is_object($cmdId)) {
                        throw new \Exception (__('[Information ouvrant ouvert] La commande suivante sélectionnée est inconnue : ', __FILE__));
                        return;
                    }
                }
                $shutterPositionType = $this->getConfiguration('shutterPositionType');
                if ($shutterPositionType === 'analogPosition') {
                    $cmd = $this->getConfiguration('shutterAnalogPositionCmd', null);
                    if (!empty($cmd)) {
                        $cmdId=cmd::byId(str_replace('#','',$cmd));
                        if (!is_object($cmdId)) {
                            throw new \Exception (__('[Position du volet] La commande suivante est inconnue : ', __FILE__) . $cmd);
                            return;
                        }
                        if ($cmdId->getSubType() !== 'numeric') {
                            throw new \Exception (__('[Position du volet] La commande suivante n\'est pas de type numeric : ', __FILE__) . $cmd);
                            return;
                        }
                    } else {
                        throw new \Exception (__('[Position du volet] La commande doit être renseignée!', __FILE__));
                        return;
                    }
                    $analogClosedPosition = $this->getConfiguration('analogClosedPosition');
                    $min = (int)(str_replace('%','',$this->getConfiguration('analogClosedPositionMin')));
                    $max = (int)(str_replace('%','',$this->getConfiguration('analogClosedPositionMax')));
                    if ($analogClosedPosition < $min || $analogClosedPosition > $max) {
                        throw new \Exception (__('La position volet fermé doit être renseignée et comprise dans la plage ', __FILE__) . '[' . $min . '% - ' . $max . '%]');
                        return;
                    }        
                    $analogOpenedPosition = $this->getConfiguration('analogOpenedPosition');
                    $min = (int)(str_replace('%','',$this->getConfiguration('analogOpenedPositionMin')));
                    $max = (int)(str_replace('%','',$this->getConfiguration('analogOpenedPositionMax')));
                    if ($analogOpenedPosition < $min || $analogOpenedPosition > $max) {
                        throw new \Exception (__('La position volet ouvert doit être renseignée et comprise dans la plage ', __FILE__) .  '[' . $min . '% - ' . $max . '%]');
                        return;
                    }        
                } 
                if ($shutterPositionType == 'openedClosedPositions' || $shutterPositionType == 'closedPosition') {
                    $cmd = $this->getConfiguration('closedPositionCmd', null);
                    if (!empty($cmd)) {
                        $cmdId=cmd::byId(str_replace('#','',$cmd));
                        if (!is_object($cmdId)) {
                            throw new \Exception (__('[Position volet fermé] La commande suivante est inconnue : ', __FILE__) . $cmd);
                            return;
                        }
                    } else {
                        throw new \Exception (__('[Position volet fermé] La commande doit être renseignée!', __FILE__));
                        return;
                    }
                } 
                if ($shutterPositionType == 'openedClosedPositions' || $shutterPositionType == 'openedPosition') {
                    $cmd = $this->getConfiguration('openedPositionCmd', null);
                    if (!empty($cmd)) {
                        $cmdId=cmd::byId(str_replace('#','',$cmd));
                        if (!is_object($cmdId)) {
                            throw new \Exception (__('[Position volet ouvert] La commande suivante est inconnue : ', __FILE__) . $cmd);
                            return;
                        }
                    } else {
                        throw new \Exception (__('[Position volet ouvert] La commande doit être renseignée!', __FILE__));
                        return;
                    }
                }
            } elseif ($objectType === 'shuttersGroup') {
    
                       
            } 
    
        }

        if (!empty($objectType) && !$isObjectCreated) {
            $this->setConfiguration('isObjectCreated', true);
        }

    }
    

    public function postUpdate()
    {
        $this->loadCmdFromConfFile($this->getConfiguration('objectType', null));
    }

    public function preRemove()
    {
        
    }

    public function postRemove()
    {
        
    }

    /**
     * Load commands from JSON file
     */
    public function loadCmdFromConfFile($objectType) {
        $file = dirname(__FILE__) . '/../config/devices/' . $objectType . '.json';
        if (!is_file($file)) {
			return;
		}
		$content = file_get_contents($file);
		if (!is_json($content)) {
			return;
		}
		$device = json_decode($content, true);
		if (!is_array($device) || !isset($device['commands'])) {
			return true;
		}
		foreach ($device['commands'] as $command) {
			$cmd = null;
			foreach ($this->getCmd() as $existingCmd) {
				if ((isset($command['logicalId']) && $existingCmd->getLogicalId() == $command['logicalId'])
					|| (isset($command['name']) && $existingCmd->getName() == $command['name'])) {
					$cmd = $existingCmd;
					break;
				}
			}
			if ($cmd == null || !is_object($cmd)) {
				$cmd = new shuttersCmd();
				$cmd->setEqLogic_id($this->getId());
				utils::a2o($cmd, $command);
				$cmd->save();
			}
		}
    }
    
    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous 
     en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après 
     modification de variable de configuration
      public static function postConfig_<Variable>() {
      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant 
     modification de variable de configuration
      public static function preConfig_<Variable>() {
      }
     */

    /*     * **********************Getteur Setteur*************************** */
}

