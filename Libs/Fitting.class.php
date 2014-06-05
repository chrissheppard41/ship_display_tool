<?php
/**
 *  The Fitting class
 *  Organises the killmail fit into an organised array of objects to be read by the ship display engine
 *
 *	@author Chris Sheppard (Spark's)
 *  @version 1.0
 *  Code in this document belongs to:
 *  @copyright Chris Sheppard
 *  EvE online own all module/ships/other details rights
 *  Runs within EvE Dev killboard
 *
  */
class Fitting
{
	private $ignoreMods = array();
	public static $shipStats;
	public static $modSlots = array();

	private static $hig = 0;
	private static $mid = 0;
	private static $low = 0;
	private static $rig = 0;

	function __construct() {
		$this->ignoreMods = array(
			33400 //Bastion Module I
		);
		self::$shipStats = new Shipstats();
	}


/**
 * buildFit method
 * Takes the killmail fit, gets a list of typeID's, does 1 DB call to gather all the module stats then organises the killmail setup with slot positions and module data
 *
 * @param $_fit (Object)
 * @return (array)
 */
	public function buildFit($_fit) {
		$arr = array();

		if($_fit) {
			usort($_fit, function($a, $b) {
				return
					Statistics::slots($a->item_->getAttribute("itt_slot"), $a->item_->getAttribute('itl_flagText'), $a->item_->getAttribute('itt_cat'))
					-
					Statistics::slots($b->item_->getAttribute("itt_slot"), $b->item_->getAttribute('itl_flagText'), $b->item_->getAttribute('itt_cat'));
			});

			foreach($_fit as $head => $mods) {
				if(in_array($mods->item_->getAttribute("typeID"), $this->ignoreMods)) continue;
				$typids[] = $mods->item_->getAttribute("typeID");
			}

			$modData = array();
			$ammoData = array();

			$qry = new DBQuery();
			$qry->execute("SELECT
					a.typeID, a.value, b.attributeName, b.displayName, b.stackable, c.displayName as unit
				FROM kb3_dgmtypeattributes a
				INNER JOIN kb3_dgmattributetypes b
					ON a.attributeID = b.attributeID
				LEFT JOIN kb3_eveunits c
					ON b.unitID = c.unitID
				WHERE a.typeID = ".implode(" OR a.typeID = ", array_unique($typids))." ORDER BY b.attributeName ASC");

			while($row = $qry->getRow()) {
				$modData[$row["typeID"]][] = $row;
			}

			foreach($_fit as $k => $mods) {
				$slot = Statistics::slots($mods->item_->getAttribute('itt_slot'), $mods->item_->getAttribute('itl_flagText'), $mods->item_->getAttribute('itt_cat'));

				if($slot != 100) {
					switch($slot) {
						//drones
						case "6":
							if($ammoData[2][$mods->item_->getAttribute("typeID")]) {
								$ammoData[2][$mods->item_->getAttribute("typeID")]->quantity_ += $mods->item_->getAttribute('itd_quantity');
							} else {
								$ammoData[2][$mods->item_->getAttribute("typeID")] = $mods;
							}
						break;
						//mid slot charges
						case "11":
							$ammoData[1][$mods->item_->getAttribute("typeID")] = $mods;//$this->moduleInformation($slot, $mods, $modData);
						break;
						//high slot charges
						case "10":
							$ammoData[0][$mods->item_->getAttribute("typeID")] = $mods;//$this->moduleInformation($slot, $mods, $modData);
						break;
						//all modules
						default:
							if(self::advancedModuleSettings(strtolower($mods->item_->getAttribute("typeName"))) == "mwd") {
								self::$shipStats->setIsMWD(true);
							}
							if(self::advancedModuleSettings(strtolower($mods->item_->getAttribute("typeName"))) == "ab") {
								self::$shipStats->setIsAB(true);
							}
							for($i = 0; $i < $mods->item_->getAttribute('itd_quantity'); $i++) {
								self::$modSlots[$slot][self::$shipStats->moduleCount] = $this->moduleInformation($slot, $mods);
								self::buildSettings($slot, $mods, $modData);
							}
						break;
					};

				}
			}

			//build up the ammo information based on what ammo goes in what gun
			if($ammoData[0]) {
				foreach($ammoData[0] as $am => $ammo) {
					if($ammo->item_->getAttribute("usedlauncher") == 483 // Modulated Deep Core Miner II, Modulated Strip Miner II and Modulated Deep Core Strip Miner II
					|| $ammo->item_->getAttribute("usedlauncher") == 53 // Laser Turrets
					|| $ammo->item_->getAttribute("usedlauncher") == 55 // Projectile Turrets
					|| $ammo->item_->getAttribute("usedlauncher") == 74 // Hybrid Turrets
					|| ($ammo->item_->getAttribute("usedlauncher") >= 506 && $ammo->item_->getAttribute("usedlauncher") <= 511) // Some Missile Lauchers
					|| $ammo->item_->getAttribute("usedlauncher") == 481 // Probe Launchers
					|| $ammo->item_->getAttribute("usedlauncher") == 899 // Warp Disruption Field Generator I
					|| $ammo->item_->getAttribute("usedlauncher") == 771 // Heavy Assault Missile Launchers
					|| $ammo->item_->getAttribute("usedlauncher") == 589 // Interdiction Sphere Lauchers
					|| $ammo->item_->getAttribute("usedlauncher") == 524 // Citadel Torpedo Launchers
					) {
						$ammocharge = $ammo->item_->getAttribute("usedlauncher");

						foreach(self::$modSlots[1] as $m => $module) {
							if($ammocharge == $module["groupID"] || ($module["groupID"] == 511 && $ammocharge == 509)) {// Assault Missile Lauchers uses same ammo as Standard Missile Lauchers
								self::$modSlots[10][$m] = $this->moduleInformation(10, $ammo);
								self::buildSettings(10, $ammo, $modData);
							}
						}
					}

				}
			}

			//build up the charge information based on what charge goes in what module
			if($ammoData[1]) {
				foreach($ammoData[1] as $ch => $charge) {
					if($charge->item_->getAttribute("usedlauncher") == 76 // Capacitor Boosters
					|| $charge->item_->getAttribute("usedlauncher") == 208 // Remote Sensor Dampeners
					|| $charge->item_->getAttribute("usedlauncher") == 212 // Sensor Boosters
					|| $charge->item_->getAttribute("usedlauncher") == 291 // Tracking Disruptors
					|| $charge->item_->getAttribute("usedlauncher") == 213 // Tracking Computers
					|| $charge->item_->getAttribute("usedlauncher") == 209 // Tracking Links
					|| $charge->item_->getAttribute("usedlauncher") == 290 // Remote Sensor Boosters
					) {
						foreach(self::$modSlots[2] as $m => $module) {
							if($charge->item_->getAttribute("usedlauncher") == $module["groupID"]) {
								self::$modSlots[11][self::$shipStats->moduleCount] = $this->moduleInformation(11, $charge);
								self::buildSettings(11, $charge, $modData);
							}
						}
					}

				}
			}
			//build up the drone information based on what drone is set int he dron bay
			if($ammoData[2]) {
				foreach($ammoData[2] as $d => $drone) {
					self::$modSlots[6][self::$shipStats->moduleCount] = $this->moduleInformation(6, $drone, $drone->quantity_);
					self::buildSettings(6, $drone, $modData);
				}
			}
			if(Fitting::$modSlots[1]) {
				usort(Fitting::$modSlots[1], function ($a, $b){
					return strcmp($a['name'], $b['name']);
				});
			}
			if(Fitting::$modSlots[2]) {
				usort(Fitting::$modSlots[2], function ($a, $b){
					return strcmp($a['name'], $b['name']);
				});
			}
			if(Fitting::$modSlots[3]) {
				usort(Fitting::$modSlots[3], function ($a, $b){
					return strcmp($a['name'], $b['name']);
				});
			}
			if(Fitting::$modSlots[5]) {
				usort(Fitting::$modSlots[5], function ($a, $b){
					return strcmp($a['name'], $b['name']);
				});
			}


			$arr = self::$shipStats->getShipSlots();
			for($h = self::$hig; $h < $arr['hislots']; $h++) {
				self::$modSlots[1][] = array('id'=> 0,'name'=> 'Empty High Slot', 'iconloc'=> ((Misc::$simpleurl)?Misc::curPageURL():"").'mods/ship_display_tool/images/equipment/icon00_hig.png', 'metaLevel' => 0, 'techLevel' => 0, 'capacity' => 0, 'volume' => 0, 'mass' => 0);
			}

			for($m = self::$mid; $m < $arr['medslots']; $m++) {
				self::$modSlots[2][] = array('id'=> 0,'name'=> 'Empty Mid Slot', 'iconloc'=> ((Misc::$simpleurl)?Misc::curPageURL():"").'mods/ship_display_tool/images/equipment/icon00_mid.png', 'metaLevel' => 0, 'techLevel' => 0, 'capacity' => 0, 'volume' => 0, 'mass' => 0);
			}

			for($l = self::$low; $l < $arr['lowslots']; $l++) {
				self::$modSlots[3][] = array('id'=> 0,'name'=> 'Empty Low Slot', 'iconloc'=> ((Misc::$simpleurl)?Misc::curPageURL():"").'mods/ship_display_tool/images/equipment/icon00_low.png', 'metaLevel' => 0, 'techLevel' => 0, 'capacity' => 0, 'volume' => 0, 'mass' => 0);
			}

			for($r = self::$rig; $r < $arr['rigslots']; $r++) {
				self::$modSlots[5][] = array('id'=> 0,'name'=> 'Empty Rig Slot', 'iconloc'=> ((Misc::$simpleurl)?Misc::curPageURL():"").'mods/ship_display_tool/images/equipment/icon00_rig.png', 'metaLevel' => 0, 'techLevel' => 0, 'capacity' => 0, 'volume' => 0, 'mass' => 0);
			}

			if(self::$modSlots[0]) {
				foreach(self::$modSlots[0] as $i => $value) {
					self::subsystemaddon($value['name']);
				}
			}
		}
	}

/**
 * moduleInformation method
 * Simple array output module to print unit stats in an organised array
 *
 * @param $slot (int)
 * @param $mods (Object)
 * @return (array)
 */
	private function moduleInformation($slot, $mods, $quality = false) {
		return array(
			'id'		=> $mods->item_->getAttribute('typeID'),
			'name'		=> $mods->item_->getAttribute("typeName"),
			'groupID'	=> $mods->item_->getAttribute('groupID'),
			'usedlauncher'	=> $mods->item_->getAttribute('usedlauncher'),
			'iconloc'	=> imageURL::getURL('InventoryType', $mods->item_->getAttribute('typeID'), 64),
			'metaLevel' => $mods->item_->getAttribute('metalevel'),
			'techLevel' => $mods->item_->getAttribute('techlevel'),
			'capacity' 	=> $mods->item_->getAttribute('capacity'),
			'volume' 	=> $mods->item_->getAttribute('volume'),
			'mass' 		=> $mods->item_->getAttribute('mass'),
			'quantity' 	=> ($quality === false)?$mods->item_->getAttribute('itd_quantity'):$quality
		);
	}

/**
 * buildSettings method
 * Sets up the module informationa gainst ship stats
 *
 * @param $slot (int)
 * @param $mods (Object)
 * @return (array)
 */
	private function buildSettings($_slot, $_mods, $_modData) {
		if(!in_array($_mods->item_->getAttribute("typeID"), $this->ignoreMods)) {
			foreach($_modData[$_mods->item_->getAttribute('typeID')] as $att => $modAttributes) {
				if($modAttributes['unit'] == "%") $type = $modAttributes['unit'];
				else $type = "+";
				$neg = self::negRules($modAttributes['stackable'], $modAttributes['unit']);
				if($_slot == 0) {
					self::getttShipstatsfrommods(strtolower($modAttributes['attributeName']), abs($modAttributes['value']));
				} else if($_slot != 6) {
					self::applyShipSkills(
						abs($modAttributes['value']),
						"+",
						$type,
						strtolower($modAttributes['attributeName']),
						false,
						1,
						$neg,
						$_mods->item_->getAttribute('groupID'),
						(($_slot==10 || $_slot==11)?$_mods->item_->getAttribute('volume'):$_mods->item_->getAttribute('capacity')),
						strtolower($_mods->item_->getAttribute("typeName")),
						$_mods->item_->getAttribute('techlevel'),
						$_slot,
						$_mods->item_->getAttribute('mass')
					);
				} else {
					self::applyDroneSkills(
						abs($modAttributes['value']),
						strtolower($modAttributes['attributeName']),
						strtolower($_mods->item_->getAttribute("typeName")),
						$_mods->item_->getAttribute('techlevel'),
						$_mods->quantity_
					);
				}
			}
		}
		if($_slot != 10) {
			self::$shipStats->moduleCount++;
		}
		if($_slot == 1) {
			self::$hig++;
		} else if($_slot == 2) {
			self::$mid++;
		} else if($_slot == 3) {
			self::$low++;
		} else if($_slot == 5) {
			self::$rig++;
		}
	}

/**
 * setShipCharacteristics method
 * The bonus' for the ship and creates rules for them
 *
 * @param $_typeID (int)
 * @return (array)
 */
	public function setShipCharacteristics($_typeID) {
		$arr = array();
		$qry2 = new DBQuery();
		$qry2->execute("SELECT
				a.skillID, a.bonus, a.bonusText, b.displayName
			FROM kb3_invtraits a
			LEFT JOIN kb3_eveunits b
			ON b.unitID = a.unitID
			WHERE a.typeID = ".$_typeID);


		while($row = $qry2->getRow()) {
			$bonus_text = strtolower(strip_tags($row["bonusText"]));
			$effect = ShipEffect::findEffectName($bonus_text, $row["bonus"]);

			if($effect == null) {
				continue;
			}

			$arr = array_merge($arr, $effect);
		}
		//Misc::pre($arr);
		return $arr;
	}

/**
 * getShipStats getttShipstatsfrommods
 * Used to get the base stats for the ship by adding the tech 3 module subsystem, stores it into the object variable
 *
 * @param $_attributeName (string)
 * @param $_value (int)
 * @return
 */
	public function getttShipstatsfrommods($_attributeName, $_value) {

		switch($_attributeName) {
			case "shieldcapacity":
				self::$shipStats->setShieldAmount(self::$shipStats->getShieldAmount() + $_value);
			break;
			case "armorhp":
				self::$shipStats->setArmorAmount(self::$shipStats->getArmorAmount() + $_value);
			break;
			case "armorhpbonusadd":
				self::$shipStats->setArmorAmount(self::$shipStats->getArmorAmount() + $_value);
			break;
			case "hp":
				self::$shipStats->setHullAmount(self::$shipStats->getHullAmount() + $_value);
			break;

			case "scanradarstrength":
				if($_value > 0) {
					self::$shipStats->setSensorType(self::getSensorTypeImg('radar'));
					self::$shipStats->setSensorAmount($_value);
				}
			break;
			case "scanladarstrength":
				if($_value > 0) {
					self::$shipStats->setSensorType(self::getSensorTypeImg('ladar'));
					self::$shipStats->setSensorAmount($_value);
				}
			break;
			case "scanmagnetometricstrength":
				if($_value > 0) {
					self::$shipStats->setSensorType(self::getSensorTypeImg('magnetometric'));
					self::$shipStats->setSensorAmount($_value);
				}
			break;
			case "scangravimetricstrength":
				if($_value > 0) {
					self::$shipStats->setSensorType(self::getSensorTypeImg('gravimetric'));
					self::$shipStats->setSensorAmount($_value);
				}
			break;


			case "passiveshieldemdamageresonance":
				self::$shipStats->setShieldEM((1-$_value)*100);
			break;
			case "passiveshieldthermaldamageresonance":
				self::$shipStats->setShieldTh((1-$_value)*100);
			break;
			case "passiveshieldkineticdamageresonance":
				self::$shipStats->setShieldKi((1-$_value)*100);
			break;
			case "passiveshieldexplosivedamageresonance":
				self::$shipStats->setShieldEx((1-$_value)*100);
			break;
			case "shieldrechargerate":
				self::$shipStats->setShieldRecharge($_value/1000);
			break;

			case "passivearmoremdamageresonance ":
				self::$shipStats->setArmorEM((1-$_value)*100);
			break;
			case "passivearmorthermaldamageresonance":
				self::$shipStats->setArmorTh((1-$_value)*100);
			break;
			case "passivearmorkineticdamageresonance":
				self::$shipStats->setArmorKi((1-$_value)*100);
			break;
			case "passivearmorexplosivedamageresonance":
				self::$shipStats->setArmorEx((1-$_value)*100);
			break;

			case "passiveemdamageresonance":
				self::$shipStats->setHullEM((1-$_value)*100);
			break;
			case "passivethermaldamageresonance":
				self::$shipStats->setHullTh((1-$_value)*100);
			break;
			case "passivekineticdamageresonance":
				self::$shipStats->setHullKi((1-$_value)*100);
			break;
			case "passiveexplosivedamageresonance":
				self::$shipStats->setHullEx((1-$_value)*100);
			break;

			case "maxvelocity":
				self::$shipStats->setShipSpeed(self::$shipStats->getShipSpeed() + $_value);
			break;
			case "signatureradius":
				self::$shipStats->setSigRadius(self::$shipStats->getSigRadius() + $_value);
			break;
			case "scanresolution":
				self::$shipStats->setScan(self::$shipStats->getScan() + $_value);
			break;

			case "maxtargetrange":
				self::$shipStats->setDistance(self::$shipStats->getDistance() + $_value);
			break;
			case "maxlockedtargets":
				self::$shipStats->setTarget(self::$shipStats->getTarget() + $_value);
			break;

			case "capacitorcapacity":
				self::$shipStats->setCapAmount(self::$shipStats->getCapAmount() + $_value);
			break;
			case "rechargerate":
				self::$shipStats->setCapRecharge(self::$shipStats->getCapRecharge() + $_value);
			break;
			case "rigsize":
				self::$shipStats->setRSize(self::returnShipSize($_value));
			break;


			case "lowslots":
				$arr = self::$shipStats->getShipSlots();
				$arr['lowslots'] = $_value;
				self::$shipStats->setShipSlots($arr);
			break;
			case "medslots":
				$arr = self::$shipStats->getShipSlots();
				$arr['medslots'] = $_value;
				self::$shipStats->setShipSlots($arr);
			break;
			case "hislots":
				$arr = self::$shipStats->getShipSlots();
				$arr['hislots'] = $_value;
				self::$shipStats->setShipSlots($arr);
			break;
			case "rigslots":
				$arr = self::$shipStats->getShipSlots();
				$arr['rigslots'] += $_value;
				self::$shipStats->setShipSlots($arr);
			break;

			case "upgradecapacity":
				self::$shipStats->setCalAmount($_value);
			break;
			case "cpuoutput":
				self::$shipStats->setCpuAmount(self::$shipStats->getCpuAmount() + $_value);
			break;
			case "poweroutput":
				self::$shipStats->setPrgAmount(self::$shipStats->getPrgAmount() +$_value);
			break;
			case "turretslotsleft":
				self::$shipStats->setTurAmount($_value);
				self::$shipStats->setTurUsed($_value);
			break;
			case "launcherslotsleft":
				self::$shipStats->setMisAmount($_value);
				self::$shipStats->setMisUsed($_value);
			break;
		}
	}

/**
 * getShipStats method
 * Used to get the base stats for the ship by doing a db call on the ship name, stores it into the object variable
 *
 * @param $param_ship (string)
 * @return
 */
	public function getShipStats($param_ship) {
		//global $shipStats;
		$qry = new DBQuery();
		$qry->execute("SELECT
				a.value, b.attributeName, d.typeID, d.mass, d.description
			FROM kb3_dgmtypeattributes a
			INNER JOIN kb3_dgmattributetypes b
				ON a.attributeID = b.attributeID
			LEFT JOIN kb3_eveunits c
				ON b.unitID = c.unitID
			RIGHT JOIN kb3_invtypes d
				ON d.typeID = a.typeID
			WHERE d.typeName = '".$qry->escape($param_ship)."'");

		while($row = $qry->getRow()) {
			if(self::$shipStats->getShipIcon() == "") {
				self::$shipStats->setShipIcon($row['typeID']);
				self::$shipStats->setShipDesc($row['description']);
				self::$shipStats->setMass(Calculations::calculateMass($row['mass']));
			}

			switch(strtolower($row['attributeName'])) {
				case "shieldcapacity":
					self::$shipStats->setShieldAmount($row['value']);
				break;
				case "armorhp":
					self::$shipStats->setArmorAmount($row['value']);
				break;
				case "hp":
					self::$shipStats->setHullAmount($row['value']);
				break;

				case "scanradarstrength":
					if($row['value'] > 0) {
						self::$shipStats->setSensorType(self::getSensorTypeImg('radar'));
						self::$shipStats->setSensorAmount($row['value']);
					}
				break;
				case "scanladarstrength":
					if($row['value'] > 0) {
						self::$shipStats->setSensorType(self::getSensorTypeImg('ladar'));
						self::$shipStats->setSensorAmount($row['value']);
					}
				break;
				case "scanmagnetometricstrength":
					if($row['value'] > 0) {
						self::$shipStats->setSensorType(self::getSensorTypeImg('magnetometric'));
						self::$shipStats->setSensorAmount($row['value']);
					}
				break;
				case "scangravimetricstrength":
					if($row['value'] > 0) {
						self::$shipStats->setSensorType(self::getSensorTypeImg('gravimetric'));
						self::$shipStats->setSensorAmount($row['value']);
					}
				break;


				case "shieldemdamageresonance":
					self::$shipStats->setShieldEM((1-$row['value'])*100);
				break;
				case "shieldthermaldamageresonance":
					self::$shipStats->setShieldTh((1-$row['value'])*100);
				break;
				case "shieldkineticdamageresonance":
					self::$shipStats->setShieldKi((1-$row['value'])*100);
				break;
				case "shieldexplosivedamageresonance":
					self::$shipStats->setShieldEx((1-$row['value'])*100);
				break;
				case "shieldrechargerate":
					self::$shipStats->setShieldRecharge($row['value']/1000);
				break;

				case "armoremdamageresonance":
					self::$shipStats->setArmorEM((1-$row['value'])*100);
				break;
				case "armorthermaldamageresonance":
					self::$shipStats->setArmorTh((1-$row['value'])*100);
				break;
				case "armorkineticdamageresonance":
					self::$shipStats->setArmorKi((1-$row['value'])*100);
				break;
				case "armorexplosivedamageresonance":
					self::$shipStats->setArmorEx((1-$row['value'])*100);
				break;

				case "emdamageresonance":
					self::$shipStats->setHullEM((1-$row['value'])*100);
				break;
				case "thermaldamageresonance":
					self::$shipStats->setHullTh((1-$row['value'])*100);
				break;
				case "kineticdamageresonance":
					self::$shipStats->setHullKi((1-$row['value'])*100);
				break;
				case "explosivedamageresonance":
					self::$shipStats->setHullEx((1-$row['value'])*100);
				break;

				case "maxvelocity":
					self::$shipStats->setShipSpeed($row['value']);
				break;
				case "signatureradius":
					self::$shipStats->setSigRadius($row['value']);
				break;
				case "scanresolution":
					self::$shipStats->setScan($row['value']);
				break;

				case "maxtargetrange":
					self::$shipStats->setDistance($row['value']);
				break;
				case "maxlockedtargets":
					self::$shipStats->setTarget($row['value']);
				break;

				case "capacitorcapacity":
					self::$shipStats->setCapAmount($row['value']);
				break;
				case "rechargerate":
					self::$shipStats->setCapRecharge($row['value']);
				break;
				case "rigsize":
					self::$shipStats->setRSize(self::returnShipSize($row['value']));
				break;


				case "lowslots":
					$arr = self::$shipStats->getShipSlots();
					$arr['lowslots'] = $row['value'];
					self::$shipStats->setShipSlots($arr);
				break;
				case "medslots":
					$arr = self::$shipStats->getShipSlots();
					$arr['medslots'] = $row['value'];
					self::$shipStats->setShipSlots($arr);
				break;
				case "hislots":
					$arr = self::$shipStats->getShipSlots();
					$arr['hislots'] = $row['value'];
					self::$shipStats->setShipSlots($arr);
				break;
				case "rigslots":
					$arr = self::$shipStats->getShipSlots();
					$arr['rigslots'] = $row['value'];
					self::$shipStats->setShipSlots($arr);
				break;

				case "upgradecapacity":
					self::$shipStats->setCalAmount($row['value']);
				break;
				case "cpuoutput":
					self::$shipStats->setCpuAmount($row['value']);
				break;
				case "poweroutput":
					self::$shipStats->setPrgAmount($row['value']);
				break;
				case "turretslotsleft":
					self::$shipStats->setTurAmount($row['value']);
					self::$shipStats->setTurUsed($row['value']);
				break;
				case "launcherslotsleft":
					self::$shipStats->setMisAmount($row['value']);
					self::$shipStats->setMisUsed($row['value']);
				break;
			}
		}
	}

/**
 * getSensorTypeImg method
 * Gets the type of sensor for the race of ship
 *
 * @param $sensor_param (string)
 * @return (string)
 */
	private function getSensorTypeImg($sensor_param) {

		switch($sensor_param) {
			case "radar":
				return "Icon63_16";
			break;
			case "ladar":
				return "Icon63_15";
			break;
			case "magnetometric":
				return "Icon63_14";
			break;
			case "gravimetric":
				return "Icon63_13";
			break;
			default:
				return "icon04_12";
			break;
		}
	}

/**
 * returnShipSize method
 * Based on the size of the rig return the size of the ship
 *
 * @param $input (int)
 * @return
 */
	private function returnShipSize($input) {
		switch($input) {
			case "1":
				return "Small";
			break;
			case "2":
				return "Medium";
			break;
			case "3":
				return "Large";
			break;
			default:
				return "X-Large";
			break;
		}
	}

/**
 * negRules method
 *
 *
 * @param $param_input (string)
 * @param $param_unit (string)
 * @return
 */
	private function negRules($param_input, $param_unit) {

		switch($param_input) {
			case 'true':
				return self::negConditions($param_unit);
				//return 1;
			break;
			default:
				return 0;
			break;
		}
	}

/**
 * negConditions method
 *
 *
 * @param $param_input (string)
 * @return
 */
	private function negConditions($param_input) {
		switch(strtolower($param_input)) {
			case "hp":
				return 0;
			break;
			default:
				return 1;
			break;
		}
	}

/**
 * setTank method
 * Tries to get the type of tank from the modules on the ship
 *
 * @param $module_param (string)
 * @return
 */
	private function setTank($module_param) {
		if(strstr($module_param, "shield booster")
		|| strstr($module_param, "shield overload")
		|| strstr($module_param, "clarity ward")
		|| strstr($module_param, "converse ")
		|| strstr($module_param, "saturation in")) {
			self::$shipStats->setTankType("shield");
		} else if(strstr($module_param, "armor repair")
		|| strstr($module_param, "vestment reconstructer")
		|| strstr($module_param, "carapace restoration")
		|| strstr($module_param, "armor regenerator")) {
			if(!strstr($module_param, "remote")) {
				self::$shipStats->setTankType("armor");
			}
		} else if(strstr($module_param, "hull repair")
		|| strstr($module_param, "hull reconstructer")
		|| strstr($module_param, "structural restoration")
		|| strstr($module_param, "structural regenerator")) {
			if(!strstr($module_param, "remote")) {
				self::$shipStats->setTankType("hull");
			}
		}
	}

/**
 * isReactor method
 * Is a reactor control module
 *
 * @param $modName (string)
 * @return (bool)
 */
	private function isReactor($modName) {
		if(strstr($modName,"reactor control")
		|| strstr($modName,"reaction control")) {
			return true;
		}
		return false;
	}

/**
 * Stat
 * shipEffects method
 * Applies the bonus of the effects
 *
 * @param
 * @return
 */
	public function shipEffects() {
		if(self::$shipStats->getShipEffects()) {
			foreach(self::$shipStats->getShipEffects() as $i => $value) {
				self::applyShipSkills($value['bonus'], $value['type'], "%", strtolower($value['effect']), true, 5, 1, "", 0, "", 0, 0, 0);
			}
		}
	}
/**
 * advancedModuleSettings method
 * Trys and figures out if the module is a After burner or a Micro wardrive
 *
 * @param $param_input (string)
 * @return
 */
	public function advancedModuleSettings($param_input) {
		if(strstr($param_input, "microwarpdrive")
		|| strstr($param_input, "digital booster")
		|| strstr($param_input, "y-t8 ")
		|| strstr($param_input, "catalyzed ")
		|| strstr($param_input, "phased m")
		|| strstr($param_input, "quad ")) {
			return "mwd";
		} else if(strstr($param_input, "afterburner")
		|| strstr($param_input, "analog booster")
		|| strstr($param_input, "y-s8 ")
		|| strstr($param_input, "cold-gas ")
		|| strstr($param_input, "lif fueled ")
		|| strstr($param_input, "monopropellant ")) {
			return "ab";
		}
		return "";
	}

/**
 * applyShipSkills method
 * Applies module bonus' to the ship
 *
 * @param $bonus (string)
 * @param $type (string)
 * @param $mode (string)
 * @param $effect (string)
 * @param $shipEff (string)
 * @param $skillBonus (string)
 * @param $negEffect (string)
 * @param $groupID (string)
 * @param $capacity (string)
 * @param $modName (string)
 * @param $techLevel (string)
 * @param $moduleLevel (string)
 * @param $mass (string)
 * @return
 */
	private function applyShipSkills($bonus, $type, $mode, $effect, $shipEff, $skillBonus, $negEffect, $groupID, $capacity, $modName, $techLevel, $moduleLevel, $mass) {
		self::setTank($modName);

		//echo $modName." | ".$bonus." | ".$effect." | ".$groupID."<br />";

		//ordering
		if($moduleLevel == 5) {//rig
			$slotOrder = 2;
		} else if($moduleLevel == 2) {//mid
			$slotOrder = 4;
		} else if($moduleLevel == 3) {//low
			$slotOrder = 3;
		} else if($moduleLevel == 4) {//sub
			$slotOrder = 1;
		}

		if(($modName == "siege module i" || $modName == "siege module ii") && ($effect != "cpu" && $effect != "power")) {
		} else if($effect == "shieldcapacity" || $effect == "capacitybonus" || $effect == "shieldcapacitybonus" || $effect == "shieldcapacitymultiplier") {
			//echo $modName." <br/>";
			if($bonus < 1 && $bonus != 0) {
				$bonus = (1-$bonus)*100;
			} else if($bonus == 1) {
				$bonus = 0;
			}
			if($bonus != 0) {
				if($groupID == "137" || $groupID == "766") {
					if(!self::isReactor($modName)) {
						$bonus = ($bonus-1)*100;
						self::$shipStats->setShieldAmount(Calculations::statOntoShip(self::$shipStats->getShieldAmount(), $bonus, $type, $mode, $negEffect));
					}
				} else {
					self::$shipStats->setShieldAmount(Calculations::statOntoShip(self::$shipStats->getShieldAmount(), $bonus, $type, $mode, $negEffect));
				}
			}
		} else if($effect == "armorhp" || $effect == "armorhpbonusadd" || $effect == "armorhpbonus") {
			//self::$shipStats->setArmorAmount($row['value']);
			self::$shipStats->setArmorAmount(Calculations::statOntoShip(self::$shipStats->getArmorAmount(), $bonus, $type, $mode, $negEffect));
		} else if($effect == "structurehpmultiplier") {
			//self::$shipStats->setHullAmount($row['value']);

			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}

			self::$shipStats->setHullAmount(Calculations::statOntoShip(self::$shipStats->getHullAmount(), $bonus, "-", $mode, 1));
			self::$shipStats->structure++;
		} else if($effect == "scanradarstrength" && $bonus > 0 && $moduleLevel == 7) {
			self::$shipStats->setSensorType(self::getSensorTypeImg('radar'));
			self::$shipStats->setSensorAmount($bonus);
		} else if($effect == "scanladarstrength" && $bonus > 0 && $moduleLevel == 7) {
			self::$shipStats->setSensorType(self::getSensorTypeImg('ladar'));
			self::$shipStats->setSensorAmount($bonus);
		} else if($effect == "scanmagnetometricstrength" && $bonus > 0 && $moduleLevel == 7) {
			self::$shipStats->setSensorType(self::getSensorTypeImg('magnetometric'));
			self::$shipStats->setSensorAmount($bonus);
		} else if($effect == "scangravimetricstrength" && $bonus > 0 && $moduleLevel == 7) {
			self::$shipStats->setSensorType(self::getSensorTypeImg('gravimetric'));
			self::$shipStats->setSensorAmount($bonus);
		} else if($effect == "shieldemdamageresonance" || $effect == "emdamageresistancebonus") {
			if($bonus < 1 && $bonus != 0) {
				$bonus = (1-$bonus)*100;
			}

			if(!$negEffect && $groupID != "60") {
				if($groupID == "98" || $groupID == "328" || $groupID == "326" || $groupID == "773") {
					if(strstr($modName, "hardener")) {
						$emBonus = $bonus;
						$order = 1;
					} else if(strstr($modName, "reactive membrane")
					|| strstr($modName, "reactive plating")
					|| strstr($modName, "reflective membrane")
					|| strstr($modName, "reflective plating")
					|| strstr($modName, "thermic membrane")
					|| strstr($modName, "thermic plating")
					|| strstr($modName, "magnetic membrane")
					|| strstr($modName, "magnetic plating")
					|| strstr($modName, "regenerative membrane")
					|| strstr($modName, "regenerative plating")) {
						$emBonus = Calculations::statOntoShip($bonus, (5*5), "+", "%", 1);
						$order = 1;
					} else if($groupID == "773") {
						$emBonus = $bonus;
						$order = 1;
					} else {
						$emBonus = Calculations::statOntoShip($bonus, (5*5), "+", "%", 1);

						if($groupID == "98") {
							$order = 5;
						} else if($groupID == "326") {
							$order = 2;
						}
					}

					if($bonus != 0) {
						self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", $modName, "em", $emBonus, $type, $order));
					}
				} else {
					if($groupID == "77") {
						$order = 2;
						$emBonus = $bonus;
					} else if($groupID == "774") {
						$emBonus = $bonus;
						$order = 2;
					} else {
						$order = 1;
						$emBonus = Calculations::statOntoShip($bonus, (5*5), "+", "%", 1);
					}

					if($bonus != 0) {
						self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", $modName, "em", ($emBonus*$skillBonus), $type, $order));
					}
				}

			} else {
				if($bonus != 0) {
					self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", $modName, "em", ($bonus*$skillBonus), $type, 3));
				}
			}
		} else if($effect == "passivearmoremdamageresonance" && $moduleLevel == 7) {
			if($bonus < 1 && $bonus != 0) {
				$bonus = (1-$bonus)*100;
			}
			if($bonus != 0) {
				self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", $modName, "em", $bonus, $type, 4));
			}
		} else if($effect == "passivearmorthermaldamageresonance" && $moduleLevel == 7) {
			if($bonus < 1 && $bonus != 0) {
				$bonus = (1-$bonus)*100;
			}
			if($bonus != 0) {
				self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", $modName, "th", $bonus, $type, 4));
			}
		} else if($effect == "passivearmorkineticdamageresonance" && $moduleLevel == 7) {
			if($bonus < 1 && $bonus != 0) {
				$bonus = (1-$bonus)*100;
			}
			if($bonus != 0) {
				self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", $modName, "ki", $bonus, $type, 4));
			}
		} else if($effect == "passivearmorexplosivedamageresonance" && $moduleLevel == 7) {
			if($bonus < 1 && $bonus != 0) {
				$bonus = (1-$bonus)*100;
			}
			if($bonus != 0) {
				self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", $modName, "ex", $bonus, $type, 4));
			}
		} else if($effect == "passiveshieldemdamageresonance" && $moduleLevel == 7) {
			if($bonus < 1 && $bonus != 0) {
				$bonus = (1-$bonus)*100;
			}
			if($bonus != 0) {
				self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", $modName, "em", $bonus, $type, 4));
			}
		} else if($effect == "passiveshieldthermaldamageresonance" && $moduleLevel == 7) {
			if($bonus < 1 && $bonus != 0) {
				$bonus = (1-$bonus)*100;
			}
			if($bonus != 0) {
				self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", $modName, "th", $bonus, $type, 4));
			}
		} else if($effect == "passiveshieldkineticdamageresonance" && $moduleLevel == 7) {
			if($bonus < 1 && $bonus != 0) {
				$bonus = (1-$bonus)*100;
			}
			if($bonus != 0) {
				self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", $modName, "ki", $bonus, $type, 4));
			}
		} else if($effect == "passiveshieldexplosivedamageresonance" && $moduleLevel == 7) {
			if($bonus < 1 && $bonus != 0) {
				$bonus = (1-$bonus)*100;
			}
			if($bonus != 0) {
				self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", $modName, "ex", $bonus, $type, 4));
			}
		} else if($effect == "shieldthermaldamageresonance" || $effect == "thermaldamageresistancebonus") {
			if($bonus < 1 && $bonus != 0) {
				$bonus = (1-$bonus)*100;
			}
			if(!$negEffect && $groupID != "60") {

				if($groupID == "98" || $groupID == "328" || $groupID == "326" || $groupID == "773") {
					if(strstr($modName, "hardener")) {
						$thBonus = $bonus;
						$order = 1;
					} else if(strstr($modName, "reactive membrane")
					|| strstr($modName, "reactive plating")
					|| strstr($modName, "reflective membrane")
					|| strstr($modName, "reflective plating")
					|| strstr($modName, "thermic membrane")
					|| strstr($modName, "thermic plating")
					|| strstr($modName, "magnetic membrane")
					|| strstr($modName, "magnetic plating")
					|| strstr($modName, "regenerative membrane")
					|| strstr($modName, "regenerative plating")) {
						$thBonus = Calculations::statOntoShip($bonus, (5*5), "+", "%", 1);
						$order = 1;
					} else if($groupID == "773") {
						$thBonus = $bonus;
						$order = 1;
					} else {
						$thBonus = Calculations::statOntoShip($bonus, (5*5), "+", "%", 1);
						if($groupID == "98") {
							$order = 5;
						} else {
							$order = 2;
						}
					}

					if($bonus != 0) {
						self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", $modName, "th", $thBonus, $type, $order));
					}
				} else {
					if($groupID == "77"
					|| $groupID == "774") {
						$thBonus = $bonus;
						$order = 2;
					} else {
						$order = 1;
						$thBonus = Calculations::statOntoShip($bonus, (5*5), "+", "%", 1);
					}

					if($bonus != 0) {
						self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", $modName, "th", ($thBonus*$skillBonus), $type, $order));
					}
				}

			} else {
				if($bonus != 0) {
					self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", $modName, "th", ($bonus*$skillBonus), $type, 3));
				}
			}
		} else if($effect == "shieldkineticdamageresonance" || $effect == "kineticdamageresistancebonus") {
			if($bonus < 1 && $bonus != 0) {
				$bonus = (1-$bonus)*100;
			}
			if(!$negEffect && $groupID != "60") {
				//
				if($groupID == "98" || $groupID == "328" || $groupID == "326" || $groupID == "773") {
					if(strstr($modName, "hardener")) {
						$kiBonus = $bonus;
						$order = 1;
					} else if(strstr($modName, "reactive membrane")
					|| strstr($modName, "reactive plating")
					|| strstr($modName, "reflective membrane")
					|| strstr($modName, "reflective plating")
					|| strstr($modName, "thermic membrane")
					|| strstr($modName, "thermic plating")
					|| strstr($modName, "magnetic membrane")
					|| strstr($modName, "magnetic plating")
					|| strstr($modName, "regenerative membrane")
					|| strstr($modName, "regenerative plating")) {
						$kiBonus = Calculations::statOntoShip($bonus, (5*5), "+", "%", 1);
						$order = 1;
					} else if($groupID == "773") {
						$kiBonus = $bonus;
						$order = 1;
					} else {
						$kiBonus = Calculations::statOntoShip($bonus, (5*5), "+", "%", 1);
						if($groupID == "98") {
							$order = 5;
						} else if($groupID == "326") {
							$order = 2;
						}
					}

					if($bonus != 0) {
						self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", $modName, "ki", $kiBonus, $type, $order));
					}
				} else {
					if($groupID == "77"
					|| $groupID == "774") {
						$order = 2;
						$kiBonus = $bonus;
					} else {
						$order = 1;
						$kiBonus = Calculations::statOntoShip($bonus, (5*5), "+", "%", 1);
					}

					if($bonus != 0) {
						self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", $modName, "ki", ($kiBonus*$skillBonus), $type, $order));
					}
				}

			} else {
				if($bonus != 0) {
					self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", $modName, "ki", ($bonus*$skillBonus), $type, 3));
				}
			}
		} else if($effect == "shieldexplosivedamageresonance" || $effect == "explosivedamageresistancebonus") {
			if($bonus < 1 && $bonus != 0) {
				$bonus = (1-$bonus)*100;
			}
			if(!$negEffect && $groupID != "60") {

				if($groupID == "98" || $groupID == "328" || $groupID == "326" || $groupID == "773") {
					if(strstr($modName, "hardener")) {
						$exBonus = $bonus;
						$order = 1;
					} else if(strstr($modName, "reactive membrane")
					|| strstr($modName, "reactive plating")
					|| strstr($modName, "reflective membrane")
					|| strstr($modName, "reflective plating")
					|| strstr($modName, "thermic membrane")
					|| strstr($modName, "thermic plating")
					|| strstr($modName, "magnetic membrane")
					|| strstr($modName, "magnetic plating")
					|| strstr($modName, "regenerative membrane")
					|| strstr($modName, "regenerative plating")) {
						$exBonus = Calculations::statOntoShip($bonus, (5*5), "+", "%", 1);
						$order = 1;
					} else if($groupID == "773") {
						$exBonus = $bonus;
						$order = 1;
					} else {
						$exBonus = Calculations::statOntoShip($bonus, (5*5), "+", "%", 1);
						//$order = 2;
						if($groupID == "98") {
							$order = 5;
						} else if($groupID == "326") {
							$order = 2;
						}
					}

					if($bonus != 0) {
						self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", $modName, "ex", $exBonus, $type, $order));
					}
				} else {
					if($groupID == "77"
					|| $groupID == "774") {
						$order = 2;
						$exBonus = $bonus;
					} else {
						$order = 1;
						$exBonus = Calculations::statOntoShip($bonus, (5*5), "+", "%", 1);
					}

					if($bonus != 0) {
						self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", $modName, "ex", ($exBonus*$skillBonus), $type, $order));
					}
				}
			} else {
				if($bonus != 0) {
					self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", $modName, "ex", ($bonus*$skillBonus), $type, 3));
				}
			}
		} else if($effect == "shieldrechargerate") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			self::$shipStats->setShieldRecharge($bonus/1000);

		} else if($effect == "armoremdamageresonance") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			$order = 3;
			if(!$negEffect && $groupID != "60") {
				$order = 2;
			}

			if($bonus != 0) {
				self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", $modName, "em", ($bonus*$skillBonus), $type, $order));
			}
		} else if($effect == "armorthermaldamageresonance") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			$order = 3;
			if(!$negEffect && $groupID != "60") {
				$order = 2;
			}

			if($bonus != 0) {
				self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", $modName, "th", ($bonus*$skillBonus), $type, $order));
			}
		} else if($effect == "armorkineticdamageresonance") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			$order = 3;
			if(!$negEffect && $groupID != "60") {
				$order = 2;
			}

			if($bonus != 0) {
				self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", $modName, "ki", ($bonus*$skillBonus), $type, $order));
			}
		} else if($effect == "armorexplosivedamageresonance") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			$order = 3;
			if(!$negEffect && $groupID != "60") {
				$order = 2;
			}

			if($bonus != 0) {
				self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", $modName, "ex", ($bonus*$skillBonus), $type, $order));
			}
		} else if($effect == "hullemdamageresonance") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			//self::$shipStats->setHullEM(Calculations::getLevel5SkillsPlus(self::$shipStats->getHullEM(), ($bonus*$skillBonus), $type, 1));
			if($bonus != 0) {
				self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "hull", $modName, "em", ($bonus*$skillBonus), $type, 3));
			}
		} else if($effect == "hullthermaldamageresonance") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			//self::$shipStats->setHullTh(Calculations::getLevel5SkillsPlus(self::$shipStats->getHullTh(), ($bonus*$skillBonus), $type, 1));
			if($bonus != 0) {
				self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "hull", $modName, "th", ($bonus*$skillBonus), $type, 3));
			}
		} else if($effect == "hullkineticdamageresonance") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			//self::$shipStats->setHullKi(Calculations::getLevel5SkillsPlus(self::$shipStats->getHullKi(), ($bonus*$skillBonus), $type, 1));
			if($bonus != 0) {
				self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "hull", $modName, "ki", ($bonus*$skillBonus), $type, 3));
			}
		} else if($effect == "hullexplosivedamageresonance") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			if($bonus != 0) {
				self::$shipStats->setShipResists(Statistics::modShipResists(self::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "hull", $modName, "ex", ($bonus*$skillBonus), $type, 3));
			}
		} else if($effect == "powerengineeringoutputbonus") {
			self::$shipStats->loadPowerAdd = Statistics::modShipEnergy(self::$shipStats->loadPowerAdd, self::$shipStats->moduleCount, "power", $modName, $bonus, "+", "%");
		} else if($effect == "poweroutputmultiplier") {
			self::$shipStats->loadPowerAdd = Statistics::modShipEnergy(self::$shipStats->loadPowerAdd, self::$shipStats->moduleCount, "power", $modName, (($bonus-1)*100), "+", "%");
		} else if($effect == "powerincrease") {
			self::$shipStats->loadPowerAdd = Statistics::modShipEnergy(self::$shipStats->loadPowerAdd, self::$shipStats->moduleCount, "power", $modName, $bonus, "+", "+");
		} else if($effect == "cpumultiplier") {
			self::$shipStats->loadCPUAdd = Statistics::modShipEnergy(self::$shipStats->loadCPUAdd, self::$shipStats->moduleCount, "cpu", $modName, (($bonus-1)*100), "+", "%");
		} else if($effect == "cpuoutputbonus2") {
			self::$shipStats->loadCPUAdd = Statistics::modShipEnergy(self::$shipStats->loadCPUAdd, self::$shipStats->moduleCount, "cpu", $modName, $bonus, "+", "%");
		} else if($effect == "cpuoutput") {
			self::$shipStats->setCpuAmount($bonus+self::$shipStats->getCpuAmount());
		} else if($effect == "drawback" && $groupID == "778") {
			self::$shipStats->loadCPUAdd = Statistics::modShipEnergy(self::$shipStats->loadCPUAdd, self::$shipStats->moduleCount, "cpu", $modName, ($bonus/2), "-", "%", 1);
		} else if($effect == "poweroutput") {
			if($bonus != 0) {
				self::$shipStats->setPrgAmount($bonus);
			}
		} else if($effect == "turrethardpointmodifier") {
			if($bonus != 0) {
				self::$shipStats->setTurAmount($bonus);
				self::$shipStats->setTurUsed($bonus);
			}
		} else if($effect == "launcherhardpointmodifier") {
			if($bonus != 0) {
				self::$shipStats->setMisAmount($bonus);
				self::$shipStats->setMisUsed($bonus);
			}
		} else if($effect == "cpu") {
			$arr = self::$shipStats->getCpuUsed();
			$arr[self::$shipStats->moduleCount]['name'] = $modName;
			$arr[self::$shipStats->moduleCount]['cpu'] = $bonus;

			if($groupID == "330" && $modName == "Covert Ops Cloaking Device II") {
				$arr[self::$shipStats->moduleCount]['effect'] = "covert_cloak";
			} else if($groupID == "55" && $mass == "2000") {//p
				$arr[self::$shipStats->moduleCount]['effect'] = "heavy_cpu";
			} else if($groupID == "53" && $mass == "2000") {;//l
				$arr[self::$shipStats->moduleCount]['effect'] = "heavy_cpu";
			} else if($groupID == "74" && $mass == "2000") {//h
				$arr[self::$shipStats->moduleCount]['effect'] = "heavy_cpu";
			} else if($groupID == "203" || $groupID == "285" || $groupID == "766" || $groupID == "767" || $groupID == "768") {
				$arr[self::$shipStats->moduleCount]['effect'] = "cpu_use";
			} else if($groupID == "769" || $groupID == "43") {//h
				$arr[self::$shipStats->moduleCount]['effect'] = "cpu_use";
			} else if($groupID == "316") {
				$arr[self::$shipStats->moduleCount]['effect'] = "war_bonus";
			} else if($groupID == "41") {
				$arr[self::$shipStats->moduleCount]['effect'] = "shield_transCPU";
			} else if($groupID == "55" || $groupID == "74"
			|| $groupID == "510"|| $groupID == "507"|| $groupID == "508"|| $groupID == "509" || $groupID == "511" || $groupID == "771" || $groupID == "506" || $groupID == "524" || $groupID == "53"
			|| $groupID == "72" || $groupID == "74" || ($groupID == "862" && $modName == "Bomb Launcher I")) {
				$arr[self::$shipStats->moduleCount]['effect'] = "weapon";
			} else if($groupID == "407") {
				$arr[self::$shipStats->moduleCount]['effect'] = "capital_cpu";
			} else {
				$arr[self::$shipStats->moduleCount]['effect'] = "base";
			}
			self::$shipStats->setCpuUsed($arr);
		} else if($effect == "power") {
			//echo strtolower($modName)." --- ".$groupID." -- ".$mass."<br />";
			$arr = self::$shipStats->getPrgUsed();
			$arr[self::$shipStats->moduleCount]['name'] = $modName;
			$arr[self::$shipStats->moduleCount]['power'] = $bonus;

			if($groupID == "38" || $groupID == "39") {
				$arr[self::$shipStats->moduleCount]['effect'] = "shield";
			} else if($groupID == "508" && strpos($modName, "torpedo") > -1) {
				$arr[self::$shipStats->moduleCount]['effect'] = "seige_power";
			} else if($groupID == "55" && $mass == "2000") {//p
				$arr[self::$shipStats->moduleCount]['effect'] = "heavy_power";
			} else if($groupID == "53" && $mass == "2000") {//l
				$arr[self::$shipStats->moduleCount]['effect'] = "heavy_power";
			} else if($groupID == "74" && $mass == "2000") {//h
				$arr[self::$shipStats->moduleCount]['effect'] = "heavy_power";
			} else if($groupID == "325") {
				$arr[self::$shipStats->moduleCount]['effect'] = "armor_transPower";
			} else if($groupID == "67") {
				$arr[self::$shipStats->moduleCount]['effect'] = "cap_transPower";
			} else if($groupID == "55" || $groupID == "74"
			|| $groupID == "510"|| $groupID == "507"|| $groupID == "508"|| $groupID == "509" || $groupID == "511" || $groupID == "771" || $groupID == "506" || $groupID == "524" || $groupID == "53"
			|| $groupID == "72" || $groupID == "74" || ($groupID == "862" && $modName == "Bomb Launcher I")) {
				$arr[self::$shipStats->moduleCount]['effect'] = "weapon";
			} else {
				$arr[self::$shipStats->moduleCount]['effect'] = "base";
			}

			self::$shipStats->setPrgUsed($arr);
		} else if($effect == "maxvelocity" && $moduleLevel != 10) {
			if($groupID == "12_06") {
			} else {
				self::$shipStats->setShipSpeed(Calculations::statOntoShip(self::$shipStats->getShipSpeed(), ($bonus*$skillBonus), $type, $mode, 1));
			}
		} else if($effect == "implantbonusvelocity" || $effect == "velocitybonus" && $moduleLevel != 10) {
			if($negEffect) {
				self::$shipStats->setShipSpeed(Calculations::statOntoShip(self::$shipStats->getShipSpeed(), $bonus, "+", $mode, self::$shipStats->speedV));
				self::$shipStats->speedV++;
			} else {
				self::$shipStats->setShipSpeed(Calculations::statOntoShip(self::$shipStats->getShipSpeed(), $bonus, "+", $mode, 0));
			}
		} else if($effect == "maxvelocitybonus" && $moduleLevel != 10) {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			if($groupID == "765") {
				self::$shipStats->setShipSpeed(Calculations::statOntoShip(self::$shipStats->getShipSpeed(), $bonus, "-", $mode, self::$shipStats->speedV));

				self::$shipStats->speedV++;
			} else if($groupID == "330") {
			} else {
				self::$shipStats->setShipSpeed(Calculations::statOntoShip(self::$shipStats->getShipSpeed(), $bonus, "+", $mode, 0));
			}
		} else if($effect == "signatureradius") {
			self::$shipStats->setSigRadius($bonus);
		} else if($effect == "scanresolution") {
			self::$shipStats->setScan($bonus);
		} else if($effect == "maxtargetrange") {
			self::$shipStats->setDistance($bonus);
		} else if($effect == "maxtargetrangebonus") {
			if($groupID == "212" || $groupID == "210") {
				$arr = self::$shipStats->getSensorBooster();
				$arr[self::$shipStats->moduleCount]['range'] = $bonus;
				$arr[self::$shipStats->moduleCount]['negra'] = self::$shipStats->range;
				$arr[self::$shipStats->moduleCount]['type'] = "+";
				$arr[self::$shipStats->moduleCount]['order'] = $slotOrder;
				self::$shipStats->setSensorBooster($arr);
				self::$shipStats->range++;
			} else if($groupID == "208") {
			} else if($groupID == "315") {
				self::$shipStats->setDistance(Calculations::statOntoShip(self::$shipStats->getDistance(), $bonus, "-", $mode, self::$shipStats->warpStab));
				self::$shipStats->warpStab++;
			} else {
				self::$shipStats->setDistance(Calculations::statOntoShip(self::$shipStats->getDistance(), $bonus, "+", $mode, self::$shipStats->range));
				self::$shipStats->range++;
			}
		} else if($effect == "maxtargetrangemultiplier") {
			if($groupID == "786") {
				self::$shipStats->setSensorBooster(Statistics::modScanning(self::$shipStats->getSensorBooster(), self::$shipStats->moduleCount, 'range', (($bonus-1)*100), 'negra', self::$shipStats->range, "+", $slotOrder));
			}
		} else if($effect == "scanresolutionbonus") {
			if($groupID == "212" || $groupID == "210") {
				self::$shipStats->setSensorBooster(Statistics::modScanning(self::$shipStats->getSensorBooster(), self::$shipStats->moduleCount, 'scan', $bonus, 'negsc', self::$shipStats->scan, "+", $slotOrder));
				self::$shipStats->sensorbooster[] = self::$shipStats->moduleCount;
			} else if($groupID == "208") {
			} else {
				self::$shipStats->setScan(Calculations::statOntoShip(self::$shipStats->getScan(), $bonus, "+", $mode, self::$shipStats->scan));
				self::$shipStats->scan++;
			}
		} else if($effect == "maxtargetrangebonusbonus") {
			if($groupID == "910") {
				Statistics::modOrdering(self::$shipStats->getSensorBooster(), self::$shipStats->sensorbooster, false, true);
			}
		} else if($effect == "scanresolutionbonusbonus") {
			if($groupID == "910") {
				Statistics::modOrdering(self::$shipStats->getSensorBooster(), self::$shipStats->sensorbooster, true, false);
			}
		} else if($effect == "maxlockedtargetsbonus") {
			self::$shipStats->setTarget(Calculations::statOntoShip(self::$shipStats->getTarget(), $bonus, "+", $mode, 0));
		} else if($effect == "maxlockedtargets") {
			self::$shipStats->setTarget($row['value']);
		} else if($effect == "maxactivedronebonus") {
			self::$shipStats->droneAdd++;
		} else if($effect == "capacitorcapacity" && $moduleLevel == 7) {
			if($bonus != 0) {
				self::$shipStats->setCapAmount(Calculations::statOntoShip(self::$shipStats->getCapAmount(), $bonus, "+", $mode, 0));
			}
		} else if($effect == "caprecharge") {
			if($bonus != 0) {
				self::$shipStats->setCapRecharge(Calculations::statOntoShip(self::$shipStats->getCapRecharge(), ($bonus*$skillBonus), "-", $mode, 0));
			}
		} else if($effect == "rechargerate" && $moduleLevel == 7) {
			if($bonus != 0) {
				self::$shipStats->setCapRecharge(Calculations::statOntoShip(self::$shipStats->getCapRecharge(), $bonus, "+", $mode, 0));
			}
		} else if($effect == "scanresolutionmultiplier") {
			if($groupID == "786") {
				self::$shipStats->setScan(Calculations::statOntoShip(self::$shipStats->getScan(), (($bonus-1)*100), "+", $mode, self::$shipStats->shieldHpRed));
				self::$shipStats->shieldHpRed++;
			} else if($groupID == "315") {
				if($bonus < 1) {
					$bonus = (1-$bonus)*100;
				}
				self::$shipStats->setScan(Calculations::statOntoShip(self::$shipStats->getScan(), $bonus, "-", $mode, self::$shipStats->warpStabScan));
				self::$shipStats->warpStabScan++;

			} else {
				if($bonus < 1) {
					$bonus = (1-$bonus)*100;
				}

				self::$shipStats->setScan(Calculations::statOntoShip(self::$shipStats->getScan(), $bonus, "-", $mode, self::$shipStats->warpStabScan));
				self::$shipStats->warpStabScan++;
			}
		} else if($effect == "capacitorcapacitymultiplier" || $effect == "capacitorcapacitybonus") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			if($groupID == "769" || $groupID == "766") {
				self::$shipStats->setCapAmount(Calculations::statOntoShip(self::$shipStats->getCapAmount(), (($bonus-1)*100), "+", $mode, 1));
			} else if($groupID == "781") {
				self::$shipStats->setCapAmount(Calculations::statOntoShip(self::$shipStats->getCapAmount(), $bonus, "+", $mode, 1));
			} else {
				if($bonus != 1) {
					self::$shipStats->setCapAmount(Calculations::statOntoShip(self::$shipStats->getCapAmount(), $bonus, "-", $mode, 1));
				}
			}
		} else if($effect == "signatureradiusadd") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			self::$shipStats->setSigRadius(Calculations::statOntoShip(self::$shipStats->getSigRadius(), $bonus, "+", $mode, 0));
		} else if(strtolower($effect) == "signatureradiusbonus") {
			if($groupID == "46") {
				if($bonus < 1) {
					$bonus = (1-$bonus)*100;
				}

				if(self::$shipStats->getMwdT3Sig()) {
					$bonus = Calculations::statOntoShip($bonus, self::$shipStats->getMwdT3Sig(), "-", "%", 1);
				}

				self::$shipStats->setMwdSig($bonus);
			} else if($groupID == "379") {

			} else if($groupID == "52") {

				$arr = self::$shipStats->getSigRadiusBoost();
				$arr[self::$shipStats->moduleCount]['sigAdd'] = $bonus;
				self::$shipStats->srBooster[] = self::$shipStats->moduleCount;
				self::$shipStats->setSigRadiusBoost($arr);
			} else {
				self::$shipStats->setSigRadius(Calculations::statOntoShip(self::$shipStats->getSigRadius(), $bonus, "+", $mode, self::$shipStats->sigRadius));
				self::$shipStats->sigRadius++;
			}
		} else if($effect == "signatureradiusbonusbonus") {

			if($groupID == "908") {

				$arr = self::$shipStats->getSigRadiusBoost();
				$arr[self::$shipStats->srBooster[0]]['sigAdd'] = "0";
				self::$shipStats->setSigRadiusBoost($arr);

				unset(self::$shipStats->srBooster[0]);
				self::$shipStats->srBooster = array_values(self::$shipStats->srBooster);
			}
		} else if($effect == "speedboostfactor") {
			if($groupID == "46") {
				if($bonus < 1) {
					$bonus = (1-$bonus)*100;
				}
				if(self::advancedModuleSettings($modName) == "mwd") {
					self::$shipStats->setMwdThrust($bonus);
				} else if(self::advancedModuleSettings($modName) == "ab") {
					self::$shipStats->setABThrust($bonus);
				}
			}
		} else if($effect == "massaddition") {
			if($groupID == "46") {
				if($bonus < 1) {
					$bonus = (1-$bonus)*100;
				}
				if(self::advancedModuleSettings($modName) == "mwd") {
					self::$shipStats->setMwdMass($bonus);
				} else if(self::advancedModuleSettings($modName) == "ab") {
					self::$shipStats->setABMass($bonus);
				}
			} else {
				self::$shipStats->setMass(Calculations::statOntoShip(self::$shipStats->getMass(), $bonus, "+", $mode, 0));
			}
		} else if($effect == "speedfactor") {
			if($groupID == "46") {
				if($bonus < 1) {
					$bonus = (1-$bonus)*100;
				}
				if(self::advancedModuleSettings($modName) == "mwd") {
					self::$shipStats->setMwdBoost($bonus);
				} else if(self::advancedModuleSettings($modName) == "ab") {
					if(self::$shipStats->getAbT3Boost()) {
						$bonus = Calculations::statOntoShip($bonus, self::$shipStats->getAbT3Boost(), "+", "%", 0);
					}
					self::$shipStats->setABBoost($bonus);
				}
			}
		} else if($effect == "signatureradiusmwd") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			self::$shipStats->setMwdSigRed($bonus);
			//echo self::$shipStats->getMwdSigRed();

		} else if($effect == "capacitorrechargeratemultiplier" || $effect == "caprechargebonus") {

			if(!self::isReactor($modName)) {
				if($bonus < 1) {
					$bonus = (1-$bonus)*100;
				} else if($bonus == 1) {
					$bonus = 0;
				}
				//echo $modName." -- ".$groupID." -- ".$bonus."<br />";
				if($bonus != 0) {
					if($groupID == "57") {
						self::$shipStats->setCapRecharge(Calculations::statOntoShip(self::$shipStats->getCapRecharge(), ($bonus-1)*100, "+", $mode, 0));
					} else {
						self::$shipStats->setCapRecharge(Calculations::statOntoShip(self::$shipStats->getCapRecharge(), $bonus, "-", $mode, 0));
					}
				}
			} else if($groupID == "43") {
				if($bonus < 1) {
					$bonus = (1-$bonus)*100;
				} else if($bonus == 1) {
					$bonus = 0;
				}

				if($bonus != 0) {
					//echo $modName." ".$bonus."2<br />";
					self::$shipStats->setCapRecharge(Calculations::statOntoShip(self::$shipStats->getCapRecharge(), $bonus, "-", $mode, 0));
				}
			} else if($groupID == "39") {
				$bonus = ($bonus-1)*100;
				self::$shipStats->setCapRecharge(Calculations::statOntoShip(self::$shipStats->getCapRecharge(), $bonus, "+", $mode, 0));
			}

		} else if($effect == "scanladarstrengthpercent") {

			if($bonus > 0) {
				self::$shipStats->setSensorAmount(Calculations::statOntoShip(self::$shipStats->getSensorAmount(), $bonus, "+", $mode, self::$shipStats->scanStrength));
				self::$shipStats->scanStrength++;
			}

		} else if($effect == "scangravimetricstrengthpercent") {

			if($bonus > 0) {
				self::$shipStats->setSensorAmount(Calculations::statOntoShip(self::$shipStats->getSensorAmount(), $bonus, "+", $mode, self::$shipStats->scanStrength));
				self::$shipStats->scanStrength++;
			}

		} else if($effect == "scanmagnetometricstrengthpercent") {
			if($bonus > 0) {
				self::$shipStats->setSensorAmount(Calculations::statOntoShip(self::$shipStats->getSensorAmount(), $bonus, "+", $mode, self::$shipStats->scanStrength));
				self::$shipStats->scanStrength++;
			}

		} else if($effect == "scanradarstrengthpercent") {
			if($bonus > 0) {
				self::$shipStats->setSensorAmount(Calculations::statOntoShip(self::$shipStats->getSensorAmount(), $bonus, "+", $mode, self::$shipStats->scanStrength));
				self::$shipStats->scanStrength++;
			}

		} else if($effect == "durationskillbonus") {
			if($groupID == "774") {
				self::$shipStats->shieldDur = Statistics::modShieldDur(self::$shipStats->shieldDur, self::$shipStats->moduleCount, $bonus, "-", self::$shipStats->shieldAmp, true);
			} else {
				self::$shipStats->armorDur = Statistics::modShieldDur(self::$shipStats->armorDur, self::$shipStats->moduleCount, $bonus, "-", 1);
			}
		} else if($effect == "drawback") {
			if($groupID == "773") {
			} else if($groupID == "774") {
				if($bonus < 1) {
					$bonus = (1-$bonus)*100;
				}
				self::$shipStats->setSigRadius(Calculations::statOntoShip(self::$shipStats->getSigRadius(), (($bonus/100)*(10*5)), "+", $mode, self::$shipStats->sigRadius));
				self::$shipStats->sigRadius++;
			} else if($groupID == "782") {
				self::$shipStats->setArmorAmount(Calculations::statOntoShip(self::$shipStats->getArmorAmount(), (($bonus/100)*(10*5)), "-", $mode, 1));
			} else if($groupID == "786") {
				self::$shipStats->setShieldAmount(Calculations::statOntoShip(self::$shipStats->getShieldAmount(), (($bonus/100)*(10*5)), "-", $mode, 1));
			}

		} else if($effect == "duration") {
			if($groupID == "76") {
				self::$shipStats->setCapInj(Statistics::modDuration(self::$shipStats->getCapInj(), self::$shipStats->moduleCount, 'capacity', $capacity, ($bonus/1000)));
				self::$shipStats->boosterPos[] = self::$shipStats->moduleCount;
			} else if($groupID == "41" && strstr($modName, "remote shield")) {
				self::$shipStats->setTransCap(Statistics::modDuration(self::$shipStats->getTransCap(), self::$shipStats->moduleCount, 'type', "shieldTrans", ($bonus/1000)));
			} else if($groupID == "325" && strstr($modName, "remote armor")) {
				self::$shipStats->setTransCap(Statistics::modDuration(self::$shipStats->getTransCap(), self::$shipStats->moduleCount, 'type', "armorTrans", ($bonus/1000)));
			} else if($groupID == "67" && strstr($modName, "remote capacitor")) {
				self::$shipStats->setTransCap(Statistics::modDuration(self::$shipStats->getTransCap(), self::$shipStats->moduleCount, 'type', "energyTrans", ($bonus/1000)));
			} else if($groupID == "1156") {
				self::$shipStats->setTankBoost(Statistics::modDurationBoost(self::$shipStats->getTankBoost(), self::$shipStats->moduleCount, ($bonus/1000)));
			} else if($groupID == "40" || $groupID == "1199" || $groupID == "62" || $groupID == "63") {
				self::$shipStats->setTankBoost(Statistics::modDurationBoost(self::$shipStats->getTankBoost(), self::$shipStats->moduleCount, ($bonus/1000)));
				self::$shipStats->setCapGJ(Statistics::modDuration(self::$shipStats->getCapGJ(), self::$shipStats->moduleCount, 'capacity', $capacity, ($bonus/1000)));
			} else if($groupID == "72") {
				$arr = self::$shipStats->getDamageGun();
				$arr[self::$shipStats->moduleCount]['name'] = $modName;
				$arr[self::$shipStats->moduleCount]['rof'] = ($bonus/1000);
				$arr[self::$shipStats->moduleCount]['type'] = "Large";
				$arr[self::$shipStats->moduleCount]['techlevel'] = $techLevel;
				$arr[self::$shipStats->moduleCount]['damage'] = 1;
				self::$shipStats->setDamageGun($arr);

				self::$shipStats->setCapGJ(Statistics::modDuration(self::$shipStats->getCapGJ(), self::$shipStats->moduleCount, 'capacity', 1, ($bonus/1000)));
			} else {
				self::$shipStats->setCapGJ(Statistics::modDuration(self::$shipStats->getCapGJ(), self::$shipStats->moduleCount, 'capacity', $capacity, ($bonus/1000)));
			}

		} else if($effect == "chargegroup2") {
			if($groupID == "1156"
			|| $groupID == "1199") {
				$arr = self::$shipStats->getTankBoost();
				if(strstr($modName, "x-large")) {
					$arr[self::$shipStats->moduleCount]['amount'] = 5;
				} else if(strstr($modName, "large")) {
					$arr[self::$shipStats->moduleCount]['amount'] = 7;
				} else if(strstr($modName, "medium")) {
					$arr[self::$shipStats->moduleCount]['amount'] = 9;
				} else if(strstr($modName, "small")) {
					$arr[self::$shipStats->moduleCount]['amount'] = 11;
				}
				self::$shipStats->setTankBoost($arr);
			}
		} else if($effect == "capacitorneed") {
			if($groupID == "67"
				|| $groupID == "325"
				|| $groupID == "41"
			&& strstr($modName, "transfer")
			|| strstr($modName, "power projector")
			|| strstr($modName, "energy succor")
			|| strstr($modName, "energy transmitter")
			|| strstr($modName, "power conduit")
				|| strstr($modName, "remote")
			|| strstr($modName, "regenerative projector")
				|| strstr($modName, "transporter")) {
				self::$shipStats->setTransCap(Statistics::modCapneed(self::$shipStats->getTransCap(), self::$shipStats->moduleCount, null, null, $bonus));
			}else if($groupID == "74" || $groupID == "53") {
				$arr = self::$shipStats->getDamageGun();
				$arr[self::$shipStats->moduleCount]['capNeed'] = $bonus;
				self::$shipStats->setDamageGun($arr);
			} else if($groupID == "72") {
				self::$shipStats->setCapGJ(Statistics::modCapneed(self::$shipStats->getCapGJ(), self::$shipStats->moduleCount, $modName, 1, $bonus));
			} else {
				self::$shipStats->setCapGJ(Statistics::modCapneed(self::$shipStats->getCapGJ(), self::$shipStats->moduleCount, $modName, null, $bonus));
			}

		} else if($effect == "modulereactivationdelay") {
			if($groupID == "70_09") {
				$arr = self::$shipStats->getCapGJ();
				$arr[self::$shipStats->moduleCount]['react'] = $bonus;
				self::$shipStats->setCapGJ($arr);
			}
		} else if($effect == "powertransferamount" && $groupID != "67") {
			$arr = self::$shipStats->getCapGJ();
			$arr[self::$shipStats->moduleCount]['capAdd'] = $bonus;
			self::$shipStats->setCapGJ($arr);

		} else if($effect == "capacitorbonus") {
			if($groupID == "61") {
				if($bonus != 0) {
					self::$shipStats->setCapAmount(Calculations::statOntoShip(self::$shipStats->getCapAmount(), $bonus, "+", $mode, 1));
				}
			} else  {
				$arr = self::$shipStats->getCapInj();
				$arr[self::$shipStats->boosterPos[0]]['amount'] = $bonus;
				$arr[self::$shipStats->boosterPos[0]]['vol'] = $capacity;
				self::$shipStats->setCapInj($arr);

				unset(self::$shipStats->boosterPos[0]);
				self::$shipStats->boosterPos = array_values(self::$shipStats->boosterPos);
			}

		} else if($effect == "durationbonus") {
			$arr = self::$shipStats->getCapGJ();

			if($groupID == "909") {
				foreach($arr as $i => $value) {
					if($value['name'] == "Warp Disruption Field Generator I" && $value['durationBonus'] == null) {
						$arr[$i]['durationBonus'] = $bonus;
						break;
					}
				}

			}
			self::$shipStats->setCapGJ($arr);
		} else if($effect == "capneedbonus") {

			$arr = self::$shipStats->getCapGJ();

			if($groupID == "909") {

				foreach($arr as $i => $value) {
					if($value['name'] == "Warp Disruption Field Generator I" && $value['capNeededBonus'] == null) {
						$arr[$i]['capNeededBonus'] = $bonus;
					}
				}

			} else if($groupID == "86") {
				$arr = self::$shipStats->getDamageGun();
				$arr[self::$shipStats->gunPosCap[0]]['capNeededBonus'] = $bonus;
				self::$shipStats->setDamageGun($arr);

				unset(self::$shipStats->gunPosCap[0]);
				self::$shipStats->gunPosCap = array_values(self::$shipStats->gunPosCap);
			} else if($groupID == "773") {
				$arr = self::$shipStats->getTransCapEff();
				$arr[self::$shipStats->moduleCount]['type'] = "armorTrans";
				$arr[self::$shipStats->moduleCount]['amount'] = $bonus;
				$arr[self::$shipStats->moduleCount]['neg'] = self::$shipStats->armorRR;
				self::$shipStats->setTransCapEff($arr);
				self::$shipStats->armorRR++;
			}

		} else if($effect == "speed") {
			if($groupID == "55" && (strpos($modName, "125") > -1 || strpos($modName, "150") > -1 || strpos($modName, "200") > -1 || strpos($modName, "250") > -1 || strpos($modName, "280") > -1)) {
				self::$shipStats->setDamageGun(Statistics::modSpeed(self::$shipStats->getDamageGun(), self::$shipStats->moduleCount, self::$shipStats->gunPos, self::$shipStats->gunPosCap, $modName, "rofP", ($bonus/1000), "Small", $capacity, $techLevel));
			} else if($groupID == "55" && (strpos($modName, "Dual 180") > -1 || strpos($modName, "220") > -1 || strpos($modName, "425") > -1 || strpos($modName, "650") > -1 || strpos($modName, "720") > -1)) {
				self::$shipStats->setDamageGun(Statistics::modSpeed(self::$shipStats->getDamageGun(), self::$shipStats->moduleCount, self::$shipStats->gunPos, self::$shipStats->gunPosCap, $modName, "rofP", ($bonus/1000), "Medium", $capacity, $techLevel));
			} else if($groupID == "55" && (strpos($modName, "Dual 425") > -1 || strpos($modName, "Dual 650") > -1 || strpos($modName, "800") > -1 || strpos($modName, "1200") > -1 || strpos($modName, "1400") > -1)) {
				self::$shipStats->setDamageGun(Statistics::modSpeed(self::$shipStats->getDamageGun(), self::$shipStats->moduleCount, self::$shipStats->gunPos, self::$shipStats->gunPosCap, $modName, "rofP", ($bonus/1000), "Large", $capacity, $techLevel));
			} else if($groupID == "55" && (strpos($modName, "2500") > -1 || strpos($modName, "3500") > -1)) {
				self::$shipStats->setDamageGun(Statistics::modSpeed(self::$shipStats->getDamageGun(), self::$shipStats->moduleCount, self::$shipStats->gunPos, self::$shipStats->gunPosCap, $modName, "rofP", ($bonus/1000), "X-Large", $capacity, $techLevel));
			}

			if($groupID == "74" && $mass == "500") {
				self::$shipStats->setDamageGun(Statistics::modSpeed(self::$shipStats->getDamageGun(), self::$shipStats->moduleCount, self::$shipStats->gunPos, self::$shipStats->gunPosCap, $modName, "rofH", ($bonus/1000), "Small", $capacity, $techLevel));
			} else if($groupID == "74" && $mass == "1000") {
				self::$shipStats->setDamageGun(Statistics::modSpeed(self::$shipStats->getDamageGun(), self::$shipStats->moduleCount, self::$shipStats->gunPos, self::$shipStats->gunPosCap, $modName, "rofH", ($bonus/1000), "Medium", $capacity, $techLevel));
			} else if($groupID == "74" && $mass == "2000") {
				self::$shipStats->setDamageGun(Statistics::modSpeed(self::$shipStats->getDamageGun(), self::$shipStats->moduleCount, self::$shipStats->gunPos, self::$shipStats->gunPosCap, $modName, "rofH", ($bonus/1000), "Large", $capacity, $techLevel));
			} else if($groupID == "74" && $mass == "40000") {
				self::$shipStats->setDamageGun(Statistics::modSpeed(self::$shipStats->getDamageGun(), self::$shipStats->moduleCount, self::$shipStats->gunPos, self::$shipStats->gunPosCap, $modName, "rofH", ($bonus/1000), "X-Large", $capacity, $techLevel));
			}


			if($groupID == "53" && $mass == "500") {
				self::$shipStats->setDamageGun(Statistics::modSpeed(self::$shipStats->getDamageGun(), self::$shipStats->moduleCount, self::$shipStats->gunPos, self::$shipStats->gunPosCap, $modName, "rofL", ($bonus/1000), "Small", $capacity, $techLevel));
			} else if($groupID == "53" && $mass == "1000") {
				self::$shipStats->setDamageGun(Statistics::modSpeed(self::$shipStats->getDamageGun(), self::$shipStats->moduleCount, self::$shipStats->gunPos, self::$shipStats->gunPosCap, $modName, "rofL", ($bonus/1000), "Medium", $capacity, $techLevel));
			} else if($groupID == "53" && $mass == "2000") {
				self::$shipStats->setDamageGun(Statistics::modSpeed(self::$shipStats->getDamageGun(), self::$shipStats->moduleCount, self::$shipStats->gunPos, self::$shipStats->gunPosCap, $modName, "rofL", ($bonus/1000), "Large", $capacity, $techLevel));
			} else if($groupID == "53" && $mass == "40000") {
				self::$shipStats->setDamageGun(Statistics::modSpeed(self::$shipStats->getDamageGun(), self::$shipStats->moduleCount, self::$shipStats->gunPos, self::$shipStats->gunPosCap, $modName, "rofL", ($bonus/1000), "X-Large", $capacity, $techLevel));
			}

			if($groupID == "507" || $groupID == "511" || $groupID == "509") {
				self::$shipStats->setDamageGun(Statistics::modSpeed(self::$shipStats->getDamageGun(), self::$shipStats->moduleCount, self::$shipStats->gunPos, self::$shipStats->gunPosCap, $modName, "rofM", ($bonus/1000), "Small", $capacity, $techLevel));
			} else if($groupID == "510"  || $groupID == "771") {
				self::$shipStats->setDamageGun(Statistics::modSpeed(self::$shipStats->getDamageGun(), self::$shipStats->moduleCount, self::$shipStats->gunPos, self::$shipStats->gunPosCap, $modName, "rofM", ($bonus/1000), "Medium", $capacity, $techLevel));
			} else if($groupID == "506" || $groupID == "508") {
				self::$shipStats->setDamageGun(Statistics::modSpeed(self::$shipStats->getDamageGun(), self::$shipStats->moduleCount, self::$shipStats->gunPos, self::$shipStats->gunPosCap, $modName, "rofM", ($bonus/1000), "Large", $capacity, $techLevel));
			} else if($groupID == "524") {
				self::$shipStats->setDamageGun(Statistics::modSpeed(self::$shipStats->getDamageGun(), self::$shipStats->moduleCount, self::$shipStats->gunPos, self::$shipStats->gunPosCap, $modName, "rofM", ($bonus/1000), "X-Large", $capacity, $techLevel));
			}

		} else if($effect == "damagemultiplier") {
			if($groupID == "55") {
				self::$shipStats->setDamageGun(Statistics::modDamage(self::$shipStats->getDamageGun(), self::$shipStats->moduleCount, 'damageP', $bonus));
			} else if($groupID == "74") {
				self::$shipStats->setDamageGun(Statistics::modDamage(self::$shipStats->getDamageGun(), self::$shipStats->moduleCount, 'damageH', $bonus));
			} else if($groupID == "53") {
				self::$shipStats->setDamageGun(Statistics::modDamage(self::$shipStats->getDamageGun(), self::$shipStats->moduleCount, 'damageL', $bonus));
			} else if($groupID == "776") {
				self::$shipStats->setDamageModules(Statistics::modDamageMods(self::$shipStats->getDamageModules(), self::$shipStats->moduleCount, $modName, self::$shipStats->hybridDam, 'damageH', (($bonus-1)*100)));
			} else if($groupID == "775") {
				self::$shipStats->setDamageModules(Statistics::modDamageMods(self::$shipStats->getDamageModules(), self::$shipStats->moduleCount, $modName, self::$shipStats->lazerDam, 'damageL', (($bonus-1)*100)));
			} else if($groupID == "777") {
				self::$shipStats->setDamageModules(Statistics::modDamageMods(self::$shipStats->getDamageModules(), self::$shipStats->moduleCount, $modName, self::$shipStats->projectileDam, 'damageP', (($bonus-1)*100)));
			} else if($groupID == "779") {
				self::$shipStats->setDamageModules(Statistics::modDamageMods(self::$shipStats->getDamageModules(), self::$shipStats->moduleCount, $modName, self::$shipStats->missileDam, 'damageM', (($bonus-1)*100)));
			} else if($groupID == "205") {
				if($bonus < 1) {
					$bonus = (1-$bonus)*100;
				}
				self::$shipStats->setDamageModules(Statistics::modDamageMods(self::$shipStats->getDamageModules(), self::$shipStats->moduleCount, $modName, self::$shipStats->lazerDam, 'damageL', (($bonus-1)*100), self::$shipStats->lazerRof));
			} else if($groupID == "302") {
				if($bonus < 1) {
					$bonus = (1-$bonus)*100;
				}
				self::$shipStats->setDamageModules(Statistics::modDamageMods(self::$shipStats->getDamageModules(), self::$shipStats->moduleCount, $modName, self::$shipStats->hybridDam, 'damageH', (($bonus-1)*100), self::$shipStats->hybridRof));
			} else if($groupID == "59") {
				if($bonus < 1) {
					$bonus = (1-$bonus)*100;
				}
				self::$shipStats->setDamageModules(Statistics::modDamageMods(self::$shipStats->getDamageModules(), self::$shipStats->moduleCount, $modName, self::$shipStats->projectileDam, 'damageP', (($bonus-1)*100), self::$shipStats->projectileRof));
			}
		} else if($effect == "emdamage"
		|| $effect == "explosivedamage"
		|| $effect == "kineticdamage"
		|| $effect == "thermaldamage") {
			$key = "emDamage";
			if($effect == "explosivedamage") $key = "exDamage";
			if($effect == "kineticdamage") $key = "kiDamage";
			if($effect == "thermaldamage") $key = "thDamage";

			if($groupID == "72") {
				$arr = self::$shipStats->getDamageGun();
				$arr[self::$shipStats->moduleCount][$key] = $bonus;
				self::$shipStats->setDamageGun($arr);
			} else {
				$arr = self::$shipStats->getDamageGun();
				$arr[self::$shipStats->gunPos[0]][$key] = $bonus;
				self::$shipStats->setDamageGun($arr);
				self::$shipStats->gunDamageCounter++;

				if(self::$shipStats->gunDamageCounter == 4) {
					if(is_array(self::$shipStats->gunPos)) {
						$arr[self::$shipStats->gunPos[0]]['ammoCap'] = $capacity;
						self::$shipStats->setDamageGun($arr);
						unset(self::$shipStats->gunPos[0]);
						self::$shipStats->gunPos = array_values(self::$shipStats->gunPos);
						self::$shipStats->gunDamageCounter = 0;
					}
				}
			}
		} else if($effect == "speedmultiplier") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}

			if($groupID == "776") {
				self::$shipStats->setDamageModules(Statistics::modDamageMods(self::$shipStats->getDamageModules(), self::$shipStats->moduleCount, $modName, self::$shipStats->hybridRof, 'rofH', $bonus));
			} else if($groupID == "775") {
				self::$shipStats->setDamageModules(Statistics::modDamageMods(self::$shipStats->getDamageModules(), self::$shipStats->moduleCount, $modName, self::$shipStats->lazerRof, 'rofL', $bonus));
			} else if($groupID == "777") {
				self::$shipStats->setDamageModules(Statistics::modDamageMods(self::$shipStats->getDamageModules(), self::$shipStats->moduleCount, $modName, self::$shipStats->projectileRof, 'rofP', $bonus));
			} else if($groupID == "779") {
				self::$shipStats->setDamageModules(Statistics::modDamageMods(self::$shipStats->getDamageModules(), self::$shipStats->moduleCount, $modName, self::$shipStats->missileRof, 'rofM', $bonus));
			} else {

				if(strstr($modName, "ballistic")
				|| strstr($modName, "bolt array")) {
					$dex = "M";
				} else if(strstr($modName, "magnetic field")
				|| strstr($modName, "Gauss field")
				|| strstr($modName, "insulated")
				|| strstr($modName, "linear flux")
				|| strstr($modName, "magnetic vortex")) {
					$dex = "H";
				} else if(strstr($modName, "gyrostabilizer")
				 || strstr($modName, "stabilization")
				 || strstr($modName, "stabilized")
				 || strstr($modName, "counterbalanced")
				 || strstr($modName, "inertial suspensor")) {
					$dex = "P";
				} else if(strstr($modName, "coolant")
				 || strstr($modName, "heat sink")
				 || strstr($modName, "thermal radiator")
				 || strstr($modName, "heat exhaust")) {
					$dex = "L";
				}

				self::$shipStats->setDamageModules(Statistics::modDamageMods(self::$shipStats->getDamageModules(), self::$shipStats->moduleCount, $modName, $p=0, 'rof'.$dex, $bonus));
			}

		} else if($effect == "missiledamagemultiplierbonus") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}

			self::$shipStats->setDamageModules(Statistics::modDamageMods(self::$shipStats->getDamageModules(), self::$shipStats->moduleCount, $modName, self::$shipStats->missileDam, 'damageM', (($bonus-1)*100), self::$shipStats->missileRof));
		} else if($effect == "damagemultiplierbonus") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}

			$type = 0;
			if(strstr($modName,"sentry")) {
				$type = 3;
			}
			self::$shipStats->setDroneDamageMod(Statistics::modDroneDamageMods(self::$shipStats->getDroneDamageMod(), self::$shipStats->moduleCount, $modName, self::$shipStats->droneDam, 0, $bonus, $type));
		} else if($effect == "dronedamagebonus") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}

			self::$shipStats->setDroneDamageMod(Statistics::modDroneDamageMods(self::$shipStats->getDroneDamageMod(), self::$shipStats->moduleCount, $modName, self::$shipStats->droneDam, 0, $bonus, 0));
		} else if($effect == "shieldrechargeratemultiplier" || $effect == "rechargeratebonus") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}

			if($groupID == "766") {
				if(!self::isReactor($modName)) {
					self::$shipStats->setShieldRecharge(Calculations::statOntoShip(self::$shipStats->getShieldRecharge(), $bonus, "-", $mode, 1));
				}
			} else if($groupID == "774" || $groupID == "36" || $groupID == "770" || $groupID == "57") {
				self::$shipStats->setShieldRecharge(Calculations::statOntoShip(self::$shipStats->getShieldRecharge(), $bonus, "-", $mode, 1));
			}

		} else if($effect == "shieldbonus") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}


			if($groupID == "40"
			|| $groupID == "1156") {
				self::$shipStats->setTankBoost(Statistics::modTankBoost(self::$shipStats->getTankBoost(), self::$shipStats->moduleCount, $bonus, "shield"));
			}
		} else if(strtolower($effect) == "armordamageamount") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}

			if($groupID == "62"
			|| $groupID == "1199") {
				self::$shipStats->setTankBoost(Statistics::modTankBoost(self::$shipStats->getTankBoost(), self::$shipStats->moduleCount, $bonus, "armor"));
			}

		} else if(strtolower($effect) == "structuredamageamount") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}

			if($groupID == "63") {
				self::$shipStats->setTankBoost(Statistics::modTankBoost(self::$shipStats->getTankBoost(), self::$shipStats->moduleCount, $bonus, "hull"));
			}

		} else if(strtolower($effect) == "shieldboostmultiplier") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}

			if($groupID == "338") {
				self::$shipStats->setTankAmpShield(Statistics::modTankBoost(self::$shipStats->getTankAmpShield(), self::$shipStats->moduleCount, $bonus, "+", self::$shipStats->shieldAmp));
			}

		} else if(strtolower($effect) == "repairbonus") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}

			self::$shipStats->setTankAmpArmor(Statistics::modTankBoost(self::$shipStats->getTankAmpArmor(), self::$shipStats->moduleCount, $bonus, "+", self::$shipStats->armorAmp));

		} else if(strtolower($effect) == "upgradecost") {
			self::$shipStats->setCalUsed($bonus+self::$shipStats->getCalUsed());
		}
	}

/**
 * applyDroneSkills method
 * Applies drone bonus' to the drones
 *
 * @param $bonus (int)
 * @param $effect (string)
 * @param $modName (string)
 * @param $techLevel (int)
 * @return
 */
	private function applyDroneSkills($_bonus, $_effect, $_modName, $_techLevel, $_count) {
		//echo $_bonus." | ".$_effect." | ".$_modName." | ".$_techLevel."<br />";

		switch($_effect) {
			case "emdamage":
				$arr = self::$shipStats->getDroneDamage();
				$arr[self::$shipStats->moduleCount]['emDamage'] = $_bonus;
				self::$shipStats->setDroneDamage($arr);
			break;
			case "explosivedamage":
				$arr = self::$shipStats->getDroneDamage();
				$arr[self::$shipStats->moduleCount]['exDamage'] = $_bonus;
				self::$shipStats->setDroneDamage($arr);
			break;
			case "kineticdamage":
				$arr = self::$shipStats->getDroneDamage();
				$arr[self::$shipStats->moduleCount]['kiDamage'] = $_bonus;
				self::$shipStats->setDroneDamage($arr);
			break;
			case "thermaldamage":
				$arr = self::$shipStats->getDroneDamage();
				$arr[self::$shipStats->moduleCount]['thDamage'] = $_bonus;
				self::$shipStats->setDroneDamage($arr);
			break;
			case "speed":
				$arr = self::$shipStats->getDroneDamage();
				$arr[self::$shipStats->moduleCount]['name'] = $_modName;
				$arr[self::$shipStats->moduleCount]['rofDr'] = ($_bonus/1000);
				$arr[self::$shipStats->moduleCount]['techlevel'] = $_techLevel;
				$arr[self::$shipStats->moduleCount]['count'] = $_count;
				self::$shipStats->setDroneDamage($arr);
			break;
			case "damagemultiplier":
				$arr = self::$shipStats->getDroneDamage();
				$arr[self::$shipStats->moduleCount]['damageDr'] = $_bonus;
				self::$shipStats->setDroneDamage($arr);
			break;
		}
	}

/**
 * Tech III
 * subsystemaddon method
 * Based on the module return the modules stats for tech III mods
 *
 * @param $modname_param (string)
 * @return (string)
 */
	private function subsystemaddon($modname_param) {

		switch($modname_param) {
			case "Legion Defensive - Adaptive Augmenter":
				Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", "Tech III", "em", 25, "+", 3));
				Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", "Tech III", "th", 25, "+", 3));
				Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", "Tech III", "ki", 25, "+", 3));
				Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", "Tech III", "ex", 25, "+", 3));
			break;
			case "Legion Defensive - Augmented Plating":
				Fitting::$shipStats->setArmorAmount(Calculations::statOntoShip(Fitting::$shipStats->getArmorAmount(), 50, "+", "%", 1));
			break;
			case "Legion Defensive - Nanobot Injector":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "armorBoost", 10, "+"));
			break;
			case "Legion Defensive - Warfare Processor":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "war_bonus", 99, "-"));
			break;

			case "Tengu Defensive - Adaptive Shielding":
				Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", "Tech III", "em", 25, "+", 3));
				Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", "Tech III", "th", 25, "+", 3));
				Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", "Tech III", "ki", 25, "+", 3));
				Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", "Tech III", "ex", 25, "+", 3));
			break;
			case "Tengu Defensive - Amplification Node":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "shieldBoost", 10, "+"));
			break;
			case "Tengu Defensive - Supplemental Screening":
				Fitting::$shipStats->setShieldAmount(Calculations::statOntoShip(Fitting::$shipStats->getShieldAmount(), 50, "+", "%", 1));
			break;
			case "Tengu Defensive - Warfare Processor":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "war_bonus", 99, "-"));
			break;

			case "Loki Defensive - Adaptive Shielding":
				Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", "Tech III", "em", 25, "+", 3));
				Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", "Tech III", "th", 25, "+", 3));
				Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", "Tech III", "ki", 25, "+", 3));
				Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "shield", "Tech III", "ex", 25, "+", 3));
			break;
			case "Loki Defensive - Adaptive Augmenter":
				Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", "Tech III", "em", 25, "+", 3));
				Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", "Tech III", "th", 25, "+", 3));
				Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", "Tech III", "ki", 25, "+", 3));
				Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", "Tech III", "ex", 25, "+", 3));
			break;
			case "Loki Defensive - Amplification Node":
				Fitting::$shipStats->setSigRadius(Calculations::statOntoShip(Fitting::$shipStats->getSigRadius(), (5*5), "+", "%", 1));
			break;
			case "Loki Defensive - Warfare Processor":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "war_bonus", 99, "-"));
			break;

			case "Proteus Defensive - Adaptive Augmenter":
					Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", "Tech III", "em", 25, "+", 3));
					Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", "Tech III", "th", 25, "+", 3));
					Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", "Tech III", "ki", 25, "+", 3));
					Fitting::$shipStats->setShipResists(Statistics::modShipResists(Fitting::$shipStats->getShipResists(), self::$shipStats->shieldResistPos, "armor", "Tech III", "ex", 25, "+", 3));
			break;
			case "Proteus Defensive - Nanobot Injector":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "armorboost", 50, "+"));
			break;
			case "Proteus Defensive - Augmented Plating":
				Fitting::$shipStats->setArmorAmount(Calculations::statOntoShip(Fitting::$shipStats->getArmorAmount(), (10*5), "+", "%", 1));
			break;
			case "Proteus Defensive - Warfare Processor":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "war_bonus", 99, "-"));
			break;
		}

		if(strtolower($effect) == "scanradarstrength" && $bonus > 0 && $moduleLevel == 7) {
			Fitting::$shipStats->setSensorType(self::getSensorTypeImg('radar'));
			Fitting::$shipStats->setSensorAmount($bonus);
		}
		if(strtolower($effect) == "scanladarstrength" && $bonus > 0 && $moduleLevel == 7) {
			Fitting::$shipStats->setSensorType(self::getSensorTypeImg('ladar'));
			Fitting::$shipStats->setSensorAmount($bonus);
		}
		if(strtolower($effect) == "scanmagnetometricstrength" && $bonus > 0 && $moduleLevel == 7) {
			Fitting::$shipStats->setSensorType(self::getSensorTypeImg('magnetometric'));
			Fitting::$shipStats->setSensorAmount($bonus);
		}
		if(strtolower($effect) == "scangravimetricstrength" && $bonus > 0 && $moduleLevel == 7) {
			Fitting::$shipStats->setSensorType(self::getSensorTypeImg('gravimetric'));
			Fitting::$shipStats->setSensorAmount($bonus);
		}


		switch($modname_param) {
			case "Legion Electronics - Dissolution Sequencer":
				Fitting::$shipStats->setSensorAmount(Calculations::statOntoShip(Fitting::$shipStats->getSensorAmount(), (15*5), "+", "%", 1));
				Fitting::$shipStats->setDistance(Calculations::statOntoShip(Fitting::$shipStats->getDistance(), (5*5), "+", "%", 1));
			break;
			case "Legion Electronics - Tactical Targeting Network":
				Fitting::$shipStats->setScan(Calculations::statOntoShip(Fitting::$shipStats->getScan(), (15*5), "+", "%", 1));
			break;
			case "Legion Electronics - Emergent Locus Analyzer":
			break;
			case "Legion Electronics - Energy Parasitic Complex":
			break;

			case "Tengu Electronics - Dissolution Sequencer":
				Fitting::$shipStats->setSensorAmount(Calculations::statOntoShip(Fitting::$shipStats->getSensorAmount(), (15*5), "+", "%", 1));
				Fitting::$shipStats->setDistance(Calculations::statOntoShip(Fitting::$shipStats->getDistance(), (5*5), "+", "%", 1));
			break;
			case "Tengu Electronics - Obfuscation Manifold":
			break;
			case "Tengu Electronics - CPU Efficiency Gate":
			break;
			case "Tengu Electronics - Emergent Locus Analyzer":
			break;

			case "Proteus Electronics - Dissolution Sequencer":
				Fitting::$shipStats->setSensorAmount(Calculations::statOntoShip(Fitting::$shipStats->getSensorAmount(), (15*5), "+", "%", 1));
				Fitting::$shipStats->setDistance(Calculations::statOntoShip(Fitting::$shipStats->getDistance(), (5*5), "+", "%", 1));
			break;
			case "Proteus Electronics - Friction Extension Processor":
			break;
			case "Proteus Electronics - CPU Efficiency Gate":
			break;
			case "Proteus Electronics - Emergent Locus Analyzer":
			break;

			case "Loki Electronics - Tactical Targeting Network":
				Fitting::$shipStats->setScan(Calculations::statOntoShip(Fitting::$shipStats->getScan(), (15*5), "+", "%", 1));
			break;
			case "Loki Electronics - Dissolution Sequencer":
				Fitting::$shipStats->setSensorAmount(Calculations::statOntoShip(Fitting::$shipStats->getSensorAmount(), (15*5), "+", "%", 1));
				Fitting::$shipStats->setDistance(Calculations::statOntoShip(Fitting::$shipStats->getDistance(), (5*5), "+", "%", 1));
			break;
			case "Loki Electronics - Immobility Drivers":
			break;
			case "Loki Electronics - Emergent Locus Analyzer":
			break;

		}

		switch($modname_param) {
			case "Legion Offensive - Drone Synthesis Projector":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "turretCap", 10, "-"));
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "damagedr", 10, "+"));
			break;
			case "Legion Offensive - Assault Optimization":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "damageM", 25, "+"));
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "rofM", 5, "-"));
			break;
			case "Legion Offensive - Liquid Crystal Magnifiers":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "damageL", 10, "+"));
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "turretCap", 10, "-"));
			break;
			case "Legion Offensive - Covert Reconfiguration":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "covert_cloak", 100, "-"));
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "turretCap", 10, "-"));
			break;

			case "Tengu Offensive - Accelerated Ejection Bay":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "damageki", 5, "+"));
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "rofM", 7.5, "-"));
			break;
			case "Tengu Offensive - Rifling Launcher Pattern":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "rofM", 5, "-"));
			break;
			case "Tengu Offensive - Magnetic Infusion Basin":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "damageH", 5, "+"));
			break;
			case "Tengu Offensive - Covert Reconfiguration":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "rofM", 5, "-"));
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "covert_cloak", 100, "-"));
			break;

			case "Proteus Offensive - Dissonic Encoding Platform":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "damageH", 10, "+"));
			break;
			case "Proteus Offensive - Hybrid Propulsion Armature":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "damageH", 10, "+"));
			break;
			case "Proteus Offensive - Drone Synthesis Projector":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "damageH", 5, "+"));
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "damagedr", 10, "+"));
			break;
			case "Proteus Offensive - Covert Reconfiguration":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "damageH", 5, "+"));
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "covert_cloak", 100, "-"));
			break;

			case "Loki Offensive - Turret Concurrence Registry":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "damageP", 10, "+"));
			break;
			case "Loki Offensive - Projectile Scoping Array":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "rofP", 7.5, "-"));
			break;
			case "Loki Offensive - Hardpoint Efficiency Configuration":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "rofP", 7.5, "-"));
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "rofM", 7.5, "-"));
			break;
			case "Loki Offensive - Covert Reconfiguration":
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "rofP", 5, "-"));
				Fitting::$shipStats->setShipEffects(Statistics::modShipeffects(Fitting::$shipStats->getShipEffects(), self::$shipStats->moduleCount, "covert_cloak", 100, "-"));
			break;

		}


		switch($modname_param) {
			case "Legion Propulsion - Chassis Optimization":
				Fitting::$shipStats->setShipSpeed(Calculations::statOntoShip(Fitting::$shipStats->getShipSpeed(), (5*5), "+", "%", 1));
			break;
			case "Legion Propulsion - Fuel Catalyst":
				if(Fitting::$shipStats->getABBoost()) {
					Fitting::$shipStats->setAbT3Boost(Calculations::statOntoShip(Fitting::$shipStats->getAbT3Boost(), (10*5), "+", "+", 1));
					$bonus = Calculations::statOntoShip(Fitting::$shipStats->getABBoost(), Fitting::$shipStats->getAbT3Boost(), "+", "%", 0);
					Fitting::$shipStats->setABBoost($bonus);
				} else {
					Fitting::$shipStats->setAbT3Boost(Calculations::statOntoShip(Fitting::$shipStats->getAbT3Boost(), (10*5), "+", "+", 1));
				}
			break;
			case "Legion Propulsion - Wake Limiter":

				if(Fitting::$shipStats->getMwdSig()) {
					Fitting::$shipStats->setMwdT3Sig(Calculations::statOntoShip(Fitting::$shipStats->getMwdT3Sig(), (5*5), "+", "+", 1));
					$bonus = Calculations::statOntoShip(Fitting::$shipStats->getMwdSig(), Fitting::$shipStats->getMwdT3Sig(), "-", "%", 1);
					Fitting::$shipStats->setMwdSig($bonus);
				} else {
					Fitting::$shipStats->setMwdT3Sig(Calculations::statOntoShip(Fitting::$shipStats->getMwdT3Sig(), (5*5), "+", "+", 1));
				}

			break;
			case "Legion Propulsion - Interdiction Nullifier":
			break;


			case "Tengu Propulsion - Fuel Catalyst":
				if(Fitting::$shipStats->getABBoost()) {
					Fitting::$shipStats->setAbT3Boost(Calculations::statOntoShip(Fitting::$shipStats->getAbT3Boost(), (10*5), "+", "+", 1));
					$bonus = Calculations::statOntoShip(Fitting::$shipStats->getABBoost(), Fitting::$shipStats->getAbT3Boost(), "+", "%", 0);
					Fitting::$shipStats->setABBoost($bonus);
				} else {
					Fitting::$shipStats->setAbT3Boost(Calculations::statOntoShip(Fitting::$shipStats->getAbT3Boost(), (10*5), "+", "+", 1));
				}
			break;
			case "Tengu Propulsion - Intercalated Nanofibers":
			break;
			case "Tengu Propulsion - Gravitational Capacitor":
			break;
			case "Tengu Propulsion - Interdiction Nullifier":
			break;

			case "Proteus Propulsion - Wake Limiter":
				if(Fitting::$shipStats->getMwdSig()) {
					Fitting::$shipStats->setMwdT3Sig(Calculations::statOntoShip(Fitting::$shipStats->getMwdT3Sig(), (5*5), "+", "+", 1));
					$bonus = Calculations::statOntoShip(Fitting::$shipStats->getMwdSig(), Fitting::$shipStats->getMwdT3Sig(), "-", "%", 1);
					Fitting::$shipStats->setMwdSig($bonus);
				} else {
					Fitting::$shipStats->setMwdT3Sig(Calculations::statOntoShip(Fitting::$shipStats->getMwdT3Sig(), (5*5), "+", "+", 1));
				}
			break;
			case "Proteus Propulsion - Localized Injectors":
				Fitting::$shipStats->setSpeedT3Cap(Calculations::statOntoShip(Fitting::$shipStats->getSpeedT3Cap(), (15*5), "+", "+", 1));
			break;
			case "Proteus Propulsion - Gravitational Capacitor":
			break;
			case "Proteus Propulsion - Interdiction Nullifier":
			break;

			case "Loki Propulsion - Interdiction Nullifier":
			break;
			case "Tengu Propulsion - Intercalated Nanofibers":
			break;
			case "Loki Propulsion - Chassis Optimization":
				Fitting::$shipStats->setShipSpeed(Calculations::statOntoShip(Fitting::$shipStats->getShipSpeed(), (5*5), "+", "%", 1));
			break;
			case "Loki Propulsion - Fuel Catalyst":

				if(Fitting::$shipStats->getABBoost()) {
					Fitting::$shipStats->setAbT3Boost(Calculations::statOntoShip(Fitting::$shipStats->getAbT3Boost(), (10*5), "+", "+", 1));
					$bonus = Calculations::statOntoShip(Fitting::$shipStats->getABBoost(), Fitting::$shipStats->getAbT3Boost(), "+", "%", 0);
					Fitting::$shipStats->setABBoost($bonus);
				} else {
					Fitting::$shipStats->setAbT3Boost(Calculations::statOntoShip(Fitting::$shipStats->getAbT3Boost(), (10*5), "+", "+", 1));
				}

			break;
		}



		switch($modname_param) {
			case "Tengu Engineering - Augmented Capacitor Reservoir":
				Fitting::$shipStats->setCapAmount(Calculations::statOntoShip(Fitting::$shipStats->getCapAmount(), (5*5), "+", "%", 1));
			break;
			case "Tengu Engineering - Capacitor Regeneration Matrix":
				Fitting::$shipStats->setCapRecharge(Calculations::statOntoShip(Fitting::$shipStats->getCapRecharge(), (5*5), "-", "%", 1));
			break;
			case "Tengu Engineering - Supplemental Coolant Injector":
			break;
			case "Tengu Engineering - Power Core Multiplier":
			break;

			case "Proteus Engineering - Augmented Capacitor Reservoir":
			break;
			case "Proteus Engineering - Capacitor Regeneration Matrix":
				Fitting::$shipStats->setCapRecharge(Calculations::statOntoShip(Fitting::$shipStats->getCapRecharge(), (5*5), "-", "%", 1));
			break;
			case "Proteus Engineering - Supplemental Coolant Injector":
			break;
			case "Proteus Engineering - Power Core Multiplier":
			break;

			case "Loki Engineering - Power Core Multiplier":
			break;
			case "Loki Engineering - Augmented Capacitor Reservoir":
				Fitting::$shipStats->setCapAmount(Calculations::statOntoShip(Fitting::$shipStats->getCapAmount(), (5*5), "+", "%", 1));
			break;
			case "Loki Engineering - Capacitor Regeneration Matrix":
				Fitting::$shipStats->setCapRecharge(Calculations::statOntoShip(Fitting::$shipStats->getCapRecharge(), (5*5), "-", "%", 1));
			break;
			case "Loki Engineering - Supplemental Coolant Injector":
			break;

			case "Legion Engineering - Power Core Multiplier":
			break;
			case "Legion Engineering - Augmented Capacitor Reservoir":
				Fitting::$shipStats->setCapAmount(Calculations::statOntoShip(Fitting::$shipStats->getCapAmount(), (5*5), "+", "%", 1));
			break;
			case "Legion Engineering - Capacitor Regeneration Matrix":
				Fitting::$shipStats->setCapRecharge(Calculations::statOntoShip(Fitting::$shipStats->getCapRecharge(), (5*5), "-", "%", 1));
			break;
			case "Legion Engineering - Supplemental Coolant Injector":
			break;
		}
	}
};
