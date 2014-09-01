<?php
include("Libs/Fitting.class.php");
include('Libs/Shipstats.class.php');
include('Libs/ShipEffects.class.php');
include('Libs/Calculations.class.php');
include('Libs/Statistics.class.php');
include('Libs/Misc.class.php');
edkloader::register('ItemList', dirname(__FILE__).'/EDKlibs/class.itemlist.php');

define('DS', DIRECTORY_SEPARATOR);
/**
 *  The fitting tool main engine
 *  Based on a killmail ID, retrieve ship and modules and their stats to build a rough estimate of their fitting stats
 *
 *	@author Chris Sheppard (Spark's)
 *  @version 3.1
 *  Code in this document belongs to:
 *  @copyright Chris Sheppard
 *  EvE online own all module/ships/other details rights
 *  Runs within EvE Dev killboard
 *
 *  @todo take a look into removing all icon references to something more reliable
 *	@todo take a look at kills with multiple ammo types in the highslots, try and figure out more evenily
 *	@todo take a look at torp damage, looks incorrect
 *	@todo applyskills switch to switch statement
 *	@todo applyship skills take a look at
 *	@todo refactor method to handle apply skills with encapsulation
 *  @todo setCapStatus check for hitting the hourstominutes to many?
  */
/**
 *  EvE killboard descriptions
  */
$modInfo['ship_display_tool']['name'] = "Ship Display Tool";
$modInfo['ship_display_tool']['abstract'] = "Displays Ship stats on the kill detials page";
$modInfo['ship_display_tool']['about'] = "by Spark's";

$operation = true;

/**
 *  EvE killboard mod registrations, add the fitting tool and remove the final blow boxes
  */
event::register("killDetail_assembling", "FittingTools::addFitting");
event::register("killDetail_context_assembling", "FittingTools::RemoveContextFinalBlowTopDamage");

class FittingTools {
	public static $currentversion 	= "3.5";
	private static $extid;

/**
 * Core
 * addFitting method
 * Init. the application by removing the old fitting window and replaces it with the display tool
 *
 * @param $home (Object)
 * @return
 */
	public function addFitting($home) {
		$home->delete("fitting");
		$home->delete("victim");
		$home->delete("victimShip");
		$home->addBehind("start", "FittingTools::displayFitting");
		//$home->replace("top", "FittingTools::displayFitting");
	}

/**
 * Core
 * RemoveContextFinalBlowTopDamage method
 * Removes the top damage and final blow left ahnd side boxes
 *
 * @param $home (Object)
 * @return
 */
	public function RemoveContextFinalBlowTopDamage($home) {
		$home->delete("damageBox");
	}

/**
 * Core
 * displayFitting method
 * The main driver for the application, it calls the fitting class which gets the killmail (the ship, modules) and retrieves other information like location,
 * pilot, corp etc and stores this information to be displayed later (when displaying the stats) apart from the ship stats, it populates a object variable.
 * After that it gets all the module information and applies it to the ship stats
 *
 * @param $home (Object)
 * @return (string) the html output
 */
	public function displayFitting($home) {
		global $smarty;
		$kll_id = $home->kll_id;

		$urlsettings = edkURI::parseURI();
		Misc::$simpleurl = $urlsettings[0][2];
		//echo "-> ".self::$simpleurl;

		if ($home->kll_id) {
			$km = Cacheable::factory('Kill', $home->kll_id);
		} else {
			$km = new Kill($home->kll_external_id, true);
			$km = $home->kill->getID();
		}

		if (!$km->exists()) {
			$html = "That kill doesn't exist.";
			$home->page->setContent($html);
			$home->page->generate($html);
			exit;
		}

		self::source($km, $home->kll_id);

		$corp = "";
		$alli = "";
		$char = "";
		$ship = "";
		$weap = "";

		$topdamage = 0;
		$maxdamage = -1;
		foreach($km->getInvolved() as $inv) {
			/*echo "<pre>";
			print_r($inv);
			echo "</pre>";*/
			if($inv->getDamageDone() > $maxdamage) {

				if($km->getFBPilotID() == $inv->getPilotID()) {
					$maxdamage = $inv->getDamageDone();
					//$topdamage = $inv;

					$corp = $inv->getCorpID();
					$alli = $inv->getAllianceID();
					$char = $inv->getPilotID();
					$ship = $inv->getShipID();
					$weap = $inv->getWeaponID();

					$fcorp = $inv->getCorpID();
					$falli = $inv->getAllianceID();
					$fchar = $inv->getPilotID();
					$fship = $inv->getShipID();
					$fweap = $inv->getWeaponID();

				} else {
					$maxdamage = $inv->getDamageDone();
					//$topdamage = $inv;

					$corp = $inv->getCorpID();
					$alli = $inv->getAllianceID();
					$char = $inv->getPilotID();
					$ship = $inv->getShipID();
					$weap = $inv->getWeaponID();
				}


			} else {
				if($km->getFBPilotID() == $inv->getPilotID()) {

					$fcorp = $inv->getCorpID();
					$falli = $inv->getAllianceID();
					$fchar = $inv->getPilotID();
					$fship = $inv->getShipID();
					$fweap = $inv->getWeaponID();

				}
			}
		}


		$plt = new Pilot($char);
		$charIcon = $plt->getPortraitURL(64);
		$charName = $plt->getName();
		$charURL = edkURI::page('pilot_detail', $char, 'plt_id');

		$clt = new Corporation($corp, false);
		$corpIcon = $clt->getPortraitURL(32);
		$corpName = $clt->getName();
		$corpURL = edkURI::page('corp_detail', $corp, 'crp_id');

		$alt = new Alliance($alli, false);
		$alliIcon = $alt->getPortraitURL(32);
		$alliName = $alt->getName();
		$alliURL = edkURI::page('alliance_detail', $corp, 'all_id');

		$slt = new Ship($ship);
		$shipIcon = $slt->getImage(32);
		$shipName = $slt->getName();
		$shipURL = edkURI::page('invtype', $ship, 'id');

		$ilt = new Item($weap);
		$weapIcon = $ilt->getIcon(32);
		$weapURL = edkURI::page('invtype', $weap, 'id');

		$smarty->assign('topgetCorpID', $corp);
		$smarty->assign('topgetAllianceID', $alli);
		$smarty->assign('topgetPilotID', $char);
		$smarty->assign('topgetShipID', $ship);
		$smarty->assign('topgetWeaponID', $weap);
		$smarty->assign('topgetCorpIcon', $corpIcon);
		$smarty->assign('topgetAllianceIcon', $alliIcon);
		$smarty->assign('topgetPilotIcon', $charIcon);
		$smarty->assign('topgetShipIcon', $shipIcon);
		$smarty->assign('topgetWeaponIcon', $weapIcon);
		$smarty->assign('topgetCorpName', $corpName);
		$smarty->assign('topgetAllianceName', $alliName);
		$smarty->assign('topgetPilotName', $charName);
		$smarty->assign('topgetShipName', $shipName);
		$smarty->assign('topgetCorpURL', $corpURL);
		$smarty->assign('topgetAllianceURL', $alliURL);
		$smarty->assign('topgetPilotURL', $charURL);
		$smarty->assign('topgetShipURL', $shipURL);
		$smarty->assign('topgetWeaponURL', $weapURL);

		$fplt = new Pilot($fchar);
		$fcharIcon = $fplt->getPortraitURL(64);
		$fcharName = $fplt->getName();
		$fcharURL = edkURI::page('pilot_detail', $fchar, 'plt_id');

		$fclt = new Corporation($fcorp, false);
		$fcorpIcon = $fclt->getPortraitURL(32);
		$fcorpName = $fclt->getName();
		$fcorpURL = edkURI::page('corp_detail', $fcorp, 'crp_id');

		$falt = new Alliance($falli, false);
		$falliIcon = $falt->getPortraitURL(32);
		$falliName = $falt->getName();
		$falliURL = edkURI::page('alliance_detail', $fcorp, 'all_id');

		$fslt = new Ship($fship);
		$fshipIcon = $fslt->getImage(32);
		$fshipName = $fslt->getName();
		$fshipURL = edkURI::page('invtype', $fship, 'id');

		$filt = new Item($fweap);
		$fweapIcon = $filt->getIcon(32);
		$fweapURL = edkURI::page('invtype', $fweap, 'id');

		$smarty->assign('fingetCorpID', $fcorp);
		$smarty->assign('fingetAllianceID', $falli);
		$smarty->assign('fingetPilotID', $fchar);
		$smarty->assign('fingetShipID', $fship);
		$smarty->assign('fingetWeaponID', $fweap);
		$smarty->assign('fingetCorpIcon', $fcorpIcon);
		$smarty->assign('fingetAllianceIcon', $falliIcon);
		$smarty->assign('fingetPilotIcon', $fcharIcon);
		$smarty->assign('fingetShipIcon', $fshipIcon);
		$smarty->assign('fingetWeaponIcon', $fweapIcon);
		$smarty->assign('fingetCorpName', $fcorpName);
		$smarty->assign('fingetAllianceName', $falliName);
		$smarty->assign('fingetPilotName', $fcharName);
		$smarty->assign('fingetShipName', $fshipName);
		$smarty->assign('fingetCorpURL', $fcorpURL);
		$smarty->assign('fingetAllianceURL', $falliURL);
		$smarty->assign('fingetPilotURL', $fcharURL);
		$smarty->assign('fingetShipURL', $fshipURL);
		$smarty->assign('fingetWeaponURL', $fweapURL);

		$plt = new Pilot($km->getVictimID());
		$victimPortrait = $plt->getPortraitURL(64);

		$victimURL = edkURI::page('pilot_detail', $km->getVictimID(), 'plt_id');
		$victimExtID = $plt->getExternalID();

		$victimCorpURL = edkURI::page('corp_detail', $km->getVictimCorpID(), 'crp_id');
		$victimCorpName = $km->getVictimCorpName();
		$victimAllianceURL = edkURI::page('alliance_detail', $km->getVictimAllianceID(), 'all_id');
		$victimAllianceName = $km->getVictimAllianceName();

		$victimDamageTaken = $km->getDamageTaken();
		$getISKLoss = number_format($km->getISKLoss());
		$smarty->assign('victimDamageTaken', $victimDamageTaken);


		$corp = new Corporation($km->getVictimCorpID(), false);

		$alliance = new Alliance($km->getVictimAllianceID(), false);

		if ($km->isClassified())
		{
		//Admin is able to see classified Systems
			if ($home->page->isAdmin())
			{
				$system = $km->getSystem()->getName() . ' (Classified)';
				$region = $km->getSystem()->getRegionName();
				$systemURL = edkURI::page('system_detail', $km->getSystem()->getID(), 'sys_id');
				$systemSecurity = $km->getSystem()->getSecurity(true);
			}
			else
			{
				$system = 'Classified';
				$region = $km->getSystem()->getRegionName();
				$systemURL = "";
				$systemSecurity = '0.0';
			}
		}
		else
		{
			$system = $km->getSystem()->getName();
			$region = $km->getSystem()->getRegionName();
			$systemURL = edkURI::page('system_detail', $km->getSystem()->getID(), 'sys_id');
			$systemSecurity = $km->getSystem()->getSecurity(true);
		}

		// Ship detail
		$ship=$km->getVictimShip();
		$shipclass=$ship->getClass();
		$shipname = $ship->getName();



		$fitter = new Fitting($kll_id);
		$fitter->getShipStats($shipname);
		$fitter->buildFit(array_merge($km->getDestroyedItems(), $km->getDroppedItems()));

		$victimShipClassName = $shipclass->getName();
		$timeStamp = $km->getTimeStamp();
		$victimShipID = edkURI::page('invtype', $ship->getExternalID(), 'id');

		if($home->page->isAdmin()) $smarty->assign('ship', $ship);



		Fitting::$shipStats->setPilotName($km->getVictimName());
		Fitting::$shipStats->setPilotCorp($victimCorpName);
		Fitting::$shipStats->setPilotAlliance($victimAllianceName);
		Fitting::$shipStats->setPilotShip(Misc::ShortenText($shipname, 30));
		Fitting::$shipStats->setPilotLoc($system);
		Fitting::$shipStats->setPilotLocReg($region);
		Fitting::$shipStats->setPilotLocSec($systemSecurity);
		Fitting::$shipStats->setPilotDate($timeStamp);
		Fitting::$shipStats->setPilotDam($victimDamageTaken);
		Fitting::$shipStats->setPilotCos($getISKLoss);
		Fitting::$shipStats->setPilotShipClass($victimShipClassName);

		Fitting::$shipStats->setCorpPort($corp->getPortraitURL(32));
		Fitting::$shipStats->setAlliPort($alliance->getPortraitURL(32));

		Fitting::$shipStats->setPilotPort($victimPortrait);
		Fitting::$shipStats->setPilotNameURL($victimURL);
		Fitting::$shipStats->setPilotCorpURL($victimCorpURL);
		Fitting::$shipStats->setPilotAllianceURL($victimAllianceURL);
		Fitting::$shipStats->setPilotShipURL($victimShipID);
		Fitting::$shipStats->setPilotLocURL($systemURL);


		//self::getShipStats($shipname);
		//self::moduleInfo();

		//self::returnShipSkills();
		$arr_s = $fitter->setShipCharacteristics(Fitting::$shipStats->getShipIcon());
		Fitting::$shipStats->setShipEffects(array_merge($arr_s, Fitting::$shipStats->getShipEffects()));
		Fitting::shipEffects();
		self::setlevel5Skills();
		self::getExtraStats();

		self::getCPUandPowerValues();

		$html = self::displayShipStats($shipname, 100, 100);

		return $html;
	}

/**
 * Data
 * source method
 * Used when trying to determine if this was a manual post/API call/Feed call, stores the response in the smarty variable system
 *
 * @param $p_kll_id (int)
 * @param $p_ex_id (int)
 * @return
 */
	public function source($kill, $kill_id) {
		global $smarty;

		$verification = false;
		if($kill->getExternalID() != 0)
		{
			$verification = true;
			$smarty->assign('api_verified_id', $kill->getExternalID());
		}
		$time = time();
		$offset = 172800;
		$timestamp = $kill->getTimeStamp();
		if (strtotime($timestamp) < ($time-$offset))
			$verification = true;

		$smarty->assign('extid', (bool)$verification);

		$qry = DBFactory::getDBQuery();
		$sql = "SELECT log_ip_address, log_timestamp FROM kb3_log WHERE"
				." log_kll_id = ".$kill_id;
		$qry->execute($sql);
		if (!$row = $qry->getRow()) {
			return "";
		}
		$source = $row['log_ip_address'];
		$posteddate = $row['log_timestamp'];

		if (preg_match("/^\d+/", $source)
				|| preg_match("/^IP/", $source)) {
			$type = "IP";
			$source = substr($source, 3);

		} else if (preg_match("/^API/", $source)) {
			$type = "API";
			$source = $p_ex_id;
		} else if (preg_match("/^http/", $source)) {
			$type = "URL";
		} else if (preg_match("/^ID:http/", $source)) {
			$type = "URL";
			$source = substr($source, 3);
		} else if (preg_match("/^ZKB:http/", $source)) {
			$type = "CREST";
			$source = substr($source, 4);
		} else {
			$type = "unknown";
		}

		$smarty->assign('extid', (bool)$verification);
		$smarty->assign("source", htmlentities($source));
		$smarty->assign("type", $type);
		$smarty->assign("postedDate", $posteddate);
	}

/**
 * Data
 * getCPUandPowerValues method
 * A method to retrieve the ships power and cpu use, it adds it all the modules fitting use (taking into consideration bonus' to fitting use, skills and ship bonus)
 * and stores it for later use
 *
 * @param
 * @return
 */
	private function getCPUandPowerValues() {
		$stack = 1;

		foreach(Fitting::$shipStats->loadPowerAdd as $value) {
			Fitting::$shipStats->setPrgAmount(Calculations::statOntoShip(Fitting::$shipStats->getPrgAmount(), $value["power"], $value["type"], $value["mode"], 1));
			$stack++;
		}
		$stack = 1;
		foreach(Fitting::$shipStats->loadCPUAdd as $value) {
			Fitting::$shipStats->setCpuAmount(Calculations::statOntoShip(Fitting::$shipStats->getCpuAmount(), $value["cpu"], $value["type"], $value["mode"], 1));
		}

		Fitting::$shipStats->setCpuAmount(Calculations::statOntoShip(Fitting::$shipStats->getCpuAmount(), 25, "+", "%", 1));
		Fitting::$shipStats->setPrgAmount(Calculations::statOntoShip(Fitting::$shipStats->getPrgAmount(), 25, "+", "%", 1));

		$arr_cpu = array_values(Fitting::$shipStats->getCpuUsed());
		$arr_prg = array_values(Fitting::$shipStats->getPrgUsed());

		for($i = 0; $i < count($arr_cpu); $i++) {
			foreach(Fitting::$shipStats->getShipEffects() as $value2) {
				if($arr_cpu[$i]['effect'] == $value2["effect"]) {
					if($arr_cpu[$i]['effect'] == "covert_cloak"
					|| $arr_cpu[$i]['effect'] == "heavy_cpu"
					|| $arr_cpu[$i]['effect'] == "war_bonus"
					|| $arr_cpu[$i]['effect'] == "shield_transCPU"
					|| $arr_cpu[$i]['effect'] == "capital_cpu") {
						$arr_cpu[$i]['cpu'] = Calculations::statOntoShip($arr_cpu[$i]['cpu'], $value2["bonus"], $value2["type"], "%", 1);
						break;
					}
				}
			}
		}
		for($i = 0; $i < count($arr_prg); $i++) {
			foreach(Fitting::$shipStats->getShipEffects() as $value2) {
				if($arr_prg[$i]['effect'] == $value2["effect"]) {
					if($arr_prg[$i]['effect'] == "seige_power"
					|| $arr_prg[$i]['effect'] == "heavy_power"
					|| $arr_prg[$i]['effect'] == "cap_transPower"
					|| $arr_prg[$i]['effect'] == "armor_transPower") {
						$arr_prg[$i]['power'] = Calculations::statOntoShip($arr_prg[$i]['power'], $value2["bonus"], $value2["type"], "%", 1);
						break;
					}
				}
			}
		}


		for($i = 0; $i < count($arr_cpu); $i++) {
			if($arr_cpu[$i]['effect'] == "weapon"
			|| $arr_cpu[$i]['effect'] == "heavy_cpu"
			|| $arr_cpu[$i]['effect'] == "cpu_use") {
				$arr_cpu[$i]['cpu'] = Calculations::statOntoShip($arr_cpu[$i]['cpu'], 25, "-", "%", 1);
				Fitting::$shipStats->loadCPU += $arr_cpu[$i]['cpu'];
			} else {
				Fitting::$shipStats->loadCPU += $arr_cpu[$i]['cpu'];
			}
		}
		for($i = 0; $i < count($arr_prg); $i++) {
			if($arr_prg[$i]['effect'] == "weapon"
			|| $arr_prg[$i]['effect'] == "seige_power"
			|| $arr_prg[$i]['effect'] == "heavy_power") {
				$arr_prg[$i]['power'] = Calculations::statOntoShip($arr_prg[$i]['power'], 10, "-", "%", 1);
				Fitting::$shipStats->loadPower += $arr_prg[$i]['power'];
			} else if($arr_prg[$i]['effect'] == "shield"
			|| $arr_prg[$i]['effect'] == "power_use") {
				$arr_prg[$i]['power'] = Calculations::statOntoShip($arr_prg[$i]['power'], 25, "-", "%", 1);
				Fitting::$shipStats->loadPower += $arr_prg[$i]['power'];
			} else {
				Fitting::$shipStats->loadPower += $arr_prg[$i]['power'];
			}
		}




		/*echo "<pre>";
		print_r($arr_prg);
		echo "</pre>";*/

		/*echo "<pre>";
		print_r($arr_cpu);
		echo "</pre>";*/

		/*echo "<pre>";
		print_r(Fitting::$shipStats->getCpuUsed());
		echo "</pre>";*/

		/*echo "<pre>";
		print_r(Fitting::$shipStats->getPrgUsed());
		echo "</pre>";*/

		/*echo "<pre>";
		print_r(Fitting::$shipStats->getShipEffects());
		echo "</pre>";*/

		/*echo "<pre>";
		print_r(Fitting::$shipStats->loadPowerAdd);
		echo "</pre>";*/

		/*echo "<pre>";
		print_r(Fitting::$shipStats->loadCPUAdd);
		echo "</pre>";*/
	}

/**
 * Data
 * setlevel5Skills method
 * Based on a few of the ships attributes that level 5 skills apply to, assign their true value
 *
 * @param
 * @return
 */
	private function setlevel5Skills() {
		//shield amount
		Fitting::$shipStats->setShieldAmount(Calculations::statOntoShip(Fitting::$shipStats->getShieldAmount(), 25, "+", "%", 1));

		//shield recharge
		Fitting::$shipStats->setShieldRecharge(Calculations::statOntoShip(Fitting::$shipStats->getShieldRecharge(), 25, "-", "%", 1));

		//armour amount
		Fitting::$shipStats->setArmorAmount(Calculations::statOntoShip(Fitting::$shipStats->getArmorAmount(), 25, "+", "%", 1));

		//hull amount
		Fitting::$shipStats->setHullAmount(Calculations::statOntoShip(Fitting::$shipStats->getHullAmount(), 25, "+", "%", 1));

		//cap amount

		Fitting::$shipStats->setCapAmount(Calculations::statOntoShip(Fitting::$shipStats->getCapAmount(), 25, "+", "%", 1));

		//cap recharge
		Fitting::$shipStats->setCapRecharge(Calculations::statOntoShip(Fitting::$shipStats->getCapRecharge(), 25, "-", "%", 1));
		//speed
		Fitting::$shipStats->setShipSpeed(Calculations::statOntoShip(Fitting::$shipStats->getShipSpeed(), 25, "+", "%", 1));
		//target range
		Fitting::$shipStats->setDistance(Calculations::statOntoShip(Fitting::$shipStats->getDistance(), 25, "+", "%", 1));

		//scan resolution
		Fitting::$shipStats->setScan(Calculations::statOntoShip(Fitting::$shipStats->getScan(), 25, "+", "%", 1));

		//ECCM
		Fitting::$shipStats->setSensorAmount(Calculations::statOntoShip(Fitting::$shipStats->getSensorAmount($row['value']), 20, "+", "%", 1));
	}

/**
 * Data
 * getExtraStats method
 * Based on module enhanced attributes recalculate the ship stats
 *
 * @param
 * @return
 */
	private function getExtraStats() {
		//sort resists
		self::setAndOrderShipResists();

		switch(Fitting::$shipStats->getTankType()) {
			case "shield":
				$boost = self::returnTankResults("shield");
				Fitting::$shipStats->setTankAmount(Calculations::tankAbleDPS(Calculations::peakShieldRecharge(Fitting::$shipStats->getShieldAmount(),Fitting::$shipStats->getShieldRecharge())+$boost, Fitting::$shipStats->getShieldEM(),Fitting::$shipStats->getShieldTh(),Fitting::$shipStats->getShieldKi(),Fitting::$shipStats->getShieldEx()));
				Fitting::$shipStats->setTankType("act");
			break;
			case "armor":
				$boost = self::returnTankResults("armor");
				Fitting::$shipStats->setTankAmount(Calculations::tankAbleDPS($boost, Fitting::$shipStats->getArmorEM(),Fitting::$shipStats->getArmorTh(),Fitting::$shipStats->getArmorKi(),Fitting::$shipStats->getArmorEx()));
				Fitting::$shipStats->setTankType("arm");
			break;
			case "hull":
				$boost = self::returnTankResults("hull");
				Fitting::$shipStats->setTankAmount(Calculations::tankAbleDPS($boost, Fitting::$shipStats->getHullEM(),Fitting::$shipStats->getHullTh(),Fitting::$shipStats->getHullKi(),Fitting::$shipStats->getHullEx()));
				Fitting::$shipStats->setTankType("arm");
			break;
			default:
				Fitting::$shipStats->setTankAmount(Calculations::tankAbleDPS(Calculations::peakShieldRecharge(Fitting::$shipStats->getShieldAmount(),Fitting::$shipStats->getShieldRecharge()), Fitting::$shipStats->getShieldEM(),Fitting::$shipStats->getShieldTh(),Fitting::$shipStats->getShieldKi(),Fitting::$shipStats->getShieldEx()));
				Fitting::$shipStats->setTankType("pass");
			break;
		}


		Fitting::$shipStats->setEffectiveShield(Calculations::effectHP(Fitting::$shipStats->getShieldAmount(),Fitting::$shipStats->getShieldEM(),Fitting::$shipStats->getShieldTh(),Fitting::$shipStats->getShieldKi(),Fitting::$shipStats->getShieldEx()));
		Fitting::$shipStats->setEffectiveArmor(Calculations::effectHP(Fitting::$shipStats->getArmorAmount(),Fitting::$shipStats->getArmorEM(),Fitting::$shipStats->getArmorTh(),Fitting::$shipStats->getArmorKi(),Fitting::$shipStats->getArmorEx()));
		Fitting::$shipStats->setEffectiveHull(Calculations::effectHP(Fitting::$shipStats->getHullAmount(),Fitting::$shipStats->getHullEM(),Fitting::$shipStats->getHullTh(),Fitting::$shipStats->getHullKi(),Fitting::$shipStats->getHullEx()));

		Fitting::$shipStats->setCapRechargeRate(Calculations::peakShieldRecharge(Fitting::$shipStats->getCapAmount(), Fitting::$shipStats->getCapRecharge()/1000));
		//echo Calculations::peakShieldRecharge(Fitting::$shipStats->getCapAmount(), Fitting::$shipStats->getCapRecharge()/1000);

		if(Fitting::$shipStats->getIsMWD()) {
			Fitting::$shipStats->mwdActive = round(Calculations::getShipSpeed(Fitting::$shipStats->getShipSpeed(), Fitting::$shipStats->getMwdBoost(), Fitting::$shipStats->getMwdThrust(), Fitting::$shipStats->getMass()+Fitting::$shipStats->getMwdMass()));

			if(Fitting::$shipStats->getMwdSigRed()) {
				Fitting::$shipStats->setMwdSig((Fitting::$shipStats->getMwdSig()-(Fitting::$shipStats->getMwdSig()/100)*(Fitting::$shipStats->getMwdSigRed()*5)));
			}

			Fitting::$shipStats->mwdSigature = round(((Fitting::$shipStats->getMwdSig()/100)*Fitting::$shipStats->getSigRadius())+Fitting::$shipStats->getSigRadius());
		}
		if(Fitting::$shipStats->getIsAB()) {
			Fitting::$shipStats->abActive = round(Calculations::getShipSpeed(Fitting::$shipStats->getShipSpeed(), Fitting::$shipStats->getABBoost(), Fitting::$shipStats->getABThrust(), Fitting::$shipStats->getMass()+Fitting::$shipStats->getABMass()));
		}

		Fitting::$shipStats->droneMax = self::getShipDrone(strtolower(Fitting::$shipStats->getPilotShip()))+Fitting::$shipStats->droneAdd;
		Fitting::$shipStats->setDamageGun(self::turretMods());
		Fitting::$shipStats->setDroneDamage(self::getDroneSkillDamage());
		self::getDPSAndVolley();
		Fitting::$shipStats->setDamage(self::getDPS());
		Fitting::$shipStats->setVolley(self::getVolley());

		Fitting::$shipStats->setCapGJ(self::capacitorUsage());
		Fitting::$shipStats->setCapInj(self::capacitorInjector());
		Fitting::$shipStats->setTransCap(self::remoteRepStats());

		$capUse = self::totalCapUse();
		$capPlus = self::totalCapInjected();
		if(Calculations::isCapStable((Fitting::$shipStats->getCapRechargeRate()+$capPlus), $capUse)) {
			//$shipStats->setCapStatus(round(capUsage($shipStats->getCapAmount(), 0, $shipStats->getCapRechargeRate())));

			$cap = Fitting::$shipStats->getCapAmount();
			$recharge = (Fitting::$shipStats->getCapRecharge()/1000);
			$k = 5;
			$tau = $recharge/$k;
			$squt = sqrt($cap);

			$regen = 0;
			if($capUse != 0) {
				for($i = 1; $i < 1000; $i++) {
					$cappersecondadd = $cap*(pow((1+((sqrt((0/$cap))-1)*exp(((0-$i)/$tau)))),2));
					$capThatCycle = $cappersecondadd/$i;
					$capPerSecond = ((-2*$cappersecondadd)/$tau)+(((2*$squt)*sqrt($cappersecondadd))/$tau);
					$percentage = ($cappersecondadd/$cap)*100;

					if($regen < $capPerSecond) {
						$regen = $capPerSecond;
					} else {
						if($capUse > $capPerSecond) {
							break;
						}
						if($percentage > 100) {
							break;
						}
						if($cappersecondadd > $cap) {
							break;
						}
					}
				}


			} else {
				$percentage = 100;
			}

			Fitting::$shipStats->setCapStatus($percentage);

			Fitting::$shipStats->setCapStable(1);
		} else {
			$seconds = round(Calculations::capUsage(Fitting::$shipStats->getCapAmount(), $capUse, (Fitting::$shipStats->getCapRechargeRate()+$capPlus), Fitting::$shipStats->getCapRecharge()));
			Fitting::$shipStats->setCapStatus(Calculations::toMinutesAndHours($seconds));
			Fitting::$shipStats->setCapStable(0);
		}

		self::sensorBoosterAdd();

		Fitting::$shipStats->setEffectiveHp(Fitting::$shipStats->getEffectiveShield()+Fitting::$shipStats->getEffectiveArmor()+Fitting::$shipStats->getEffectiveHull());
		if(!Fitting::$shipStats->getSensorType()) {
			Fitting::$shipStats->setSensorType("icon04_12");
		}

		self::setSigBoostforWarpDis();
	}

/**
 * Data
 * getShipDrone method
 * Based on the ship name, get the total amount of drones a ship can hold
 *
 * @param $shipname (string)
 * @return
 */
	private function getShipDrone($shipname) {

		if(strstr($shipname, "nyx")
		|| strstr($shipname, "aeon")
		|| strstr($shipname, "wyvern")
		|| strstr($shipname, "hel")) {
			return 20;
		} else if(strstr($shipname, "archon")
		|| strstr($shipname, "chimera")
		|| strstr($shipname, "thanatos")
		|| strstr($shipname, "nidgoggur")
		|| strstr($shipname, "revelation")
		|| strstr($shipname, "phoenix")
		|| strstr($shipname, "moros")
		|| strstr($shipname, "naglfar")) {
			return 10;
		}

		return 5;
	}

/**
 * Data
 * setAndOrderShipResists method
 * This method orders the stacking of module bonus' given to resits across the board (armour, shield and hull)
 *
 * @param
 * @return
 */
	private function setAndOrderShipResists() {

		$orderSystem = array(
			4 => "subsystem",
			1 => "Active",
			2 => "Passive",
			5 => "StrongPassive",
			3 => "DamCom"
		);

		if(Fitting::$shipStats->getShipResists()) {
			foreach($orderSystem as $i => $order) {
				foreach(Fitting::$shipStats->getShipResists() as $j => $value) {

					if($i == $value['order']) {
						if($value['section'] == "armor") {
							if($value['order'] == 3) {
								if($value['resist'] == "em") {
									Fitting::$shipStats->setArmorEm(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getArmorEm(), $value['amount'], $value['type'], 1));
									//self::$emArmor++;
								} else if($value['resist'] == "ex") {
									Fitting::$shipStats->setArmorEx(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getArmorEx(), $value['amount'], $value['type'], 1));
									//self::$exArmor++;
								} else if($value['resist'] == "ki") {
									Fitting::$shipStats->setArmorKi(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getArmorKi(), $value['amount'], $value['type'], 1));
									//self::$kiArmor++;
								} else if($value['resist'] == "th") {
									Fitting::$shipStats->setArmorTh(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getArmorTh(), $value['amount'], $value['type'], 1));
									//self::$thArmor++;
								}
							} else if($value['order'] == 4) {
								if($value['resist'] == "em") {
									Fitting::$shipStats->setArmorEm(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getArmorEm(), $value['amount'], $value['type'], 1));
								} else if($value['resist'] == "ex") {
									Fitting::$shipStats->setArmorEx(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getArmorEx(), $value['amount'], $value['type'], 1));
								} else if($value['resist'] == "ki") {
									Fitting::$shipStats->setArmorKi(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getArmorKi(), $value['amount'], $value['type'], 1));
								} else if($value['resist'] == "th") {
									Fitting::$shipStats->setArmorTh(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getArmorTh(), $value['amount'], $value['type'], 1));
								}
							} else {
								if($value['resist'] == "em") {
									Fitting::$shipStats->setArmorEm(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getArmorEm(), $value['amount'], $value['type'], Fitting::$shipStats->emArmor));
									Fitting::$shipStats->emArmor++;
								} else if($value['resist'] == "ex") {
									Fitting::$shipStats->setArmorEx(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getArmorEx(), $value['amount'], $value['type'], Fitting::$shipStats->exArmor));
									Fitting::$shipStats->exArmor++;
								} else if($value['resist'] == "ki") {
									Fitting::$shipStats->setArmorKi(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getArmorKi(), $value['amount'], $value['type'], Fitting::$shipStats->kiArmor));
									Fitting::$shipStats->kiArmor++;
								} else if($value['resist'] == "th") {
									Fitting::$shipStats->setArmorTh(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getArmorTh(), $value['amount'], $value['type'], Fitting::$shipStats->thArmor));
									Fitting::$shipStats->thArmor++;
								}
							}

						} else if($value['section'] == "shield") {
							if($value['order'] == 3 || $value['order'] == 4) {
								if($value['resist'] == "em") {
									Fitting::$shipStats->setShieldEm(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getShieldEm(), $value['amount'], $value['type'], 1));
								} else if($value['resist'] == "ex") {
									Fitting::$shipStats->setShieldEx(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getShieldEx(), $value['amount'], $value['type'], 1));
								} else if($value['resist'] == "ki") {
									Fitting::$shipStats->setShieldKi(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getShieldKi(), $value['amount'], $value['type'], 1));
								} else if($value['resist'] == "th") {
									Fitting::$shipStats->setShieldTh(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getShieldTh(), $value['amount'], $value['type'], 1));
								}
							} else {
								if($value['resist'] == "em") {
									Fitting::$shipStats->setShieldEm(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getShieldEm(), $value['amount'], $value['type'], Fitting::$shipStats->emShield));
									Fitting::$shipStats->emShield++;
								} else if($value['resist'] == "ex") {
									Fitting::$shipStats->setShieldEx(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getShieldEx(), $value['amount'], $value['type'], Fitting::$shipStats->exShield));
									Fitting::$shipStats->exShield++;
								} else if($value['resist'] == "ki") {
									Fitting::$shipStats->setShieldKi(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getShieldKi(), $value['amount'], $value['type'], Fitting::$shipStats->kiShield));
									Fitting::$shipStats->kiShield++;
								} else if($value['resist'] == "th") {
									Fitting::$shipStats->setShieldTh(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getShieldTh(), $value['amount'], $value['type'], Fitting::$shipStats->thShield));
									Fitting::$shipStats->thShield++;
								}
							}
						} else if($value['section'] == "hull") {
							if($value['order'] == 3) {
								if($value['resist'] == "em") {
									Fitting::$shipStats->setHullEm(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getHullEm(), $value['amount'], $value['type'], 1));
									Fitting::$shipStats->emHull++;
								} else if($value['resist'] == "ex") {
									Fitting::$shipStats->setHullEx(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getHullEx(), $value['amount'], $value['type'], 1));
									Fitting::$shipStats->exHull++;
								} else if($value['resist'] == "ki") {
									Fitting::$shipStats->setHullKi(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getHullKi(), $value['amount'], $value['type'], 1));
									Fitting::$shipStats->kiHull++;
								} else if($value['resist'] == "th") {
									Fitting::$shipStats->setHullTh(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getHullTh(), $value['amount'], $value['type'], 1));
									Fitting::$shipStats->thHull++;
								}
							} else {
								if($value['resist'] == "em") {
									Fitting::$shipStats->setHullEm(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getHullEm(), $value['amount'], $value['type'], Fitting::$shipStats->emHull));
									Fitting::$shipStats->emHull++;
								} else if($value['resist'] == "ex") {
									Fitting::$shipStats->setHullEx(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getHullEx(), $value['amount'], $value['type'], Fitting::$shipStats->exHull));
									Fitting::$shipStats->exHull++;
								} else if($value['resist'] == "ki") {
									Fitting::$shipStats->setHullKi(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getHullKi(), $value['amount'], $value['type'], Fitting::$shipStats->kiHull));
									Fitting::$shipStats->kiHull++;
								} else if($value['resist'] == "th") {
									Fitting::$shipStats->setHullTh(Calculations::getLevel5SkillsPlus(Fitting::$shipStats->getHullTh(), $value['amount'], $value['type'], Fitting::$shipStats->thHull));
									Fitting::$shipStats->thHull++;
								}
							}
						}
					}
				}
			}
		}
	}

/**
 * Data
 * returnTankResults method
 * Return the boost amount from modules like armour repairer, shield booster and hull repairer modules
 *
 * @param $tankType (string)
 * @return
 */
	private function returnTankResults($tankType) {
		/*echo "<pre>";
		print_r(Fitting::$shipStats->getTankBoost());
		echo "</pre>";*/

		$total = 0;
		$dur = 1;
		$boost = 0;
		if(Fitting::$shipStats->getTankBoost()) {
			foreach(Fitting::$shipStats->getTankBoost() as $i => $value) {
				if($value['type'] == $tankType) {
					$boost = $value['boost'];
					if($value['type'] == "shield") {
						$boost = self::ampBooster($boost,$value["icon"], $value['type']);
						$dur = self::boostDuration($tankType);
					} else if($value['type'] == "armor") {
						$boost = self::ampBooster($boost,$value["icon"], $value['type']);
						$dur = self::boostDuration($tankType);
					} else if($value['type'] == "hull") {
						$dur = self::boostDuration($tankType);
					}
					$total += $boost;
				}
			}
		}
		//echo $total." ".$dur."<br />";
		return $total/$dur;
	}

/**
 * Data
 * boostDuration method
 * Calculates the boost duration for the modules based on skills and module attributes
 *
 * @param $type (string)
 * @return
 */
	private function boostDuration($type) {
		$j = 0;
		if(Fitting::$shipStats->getTankBoost()) {
			foreach(Fitting::$shipStats->getTankBoost() as $i => $value) {
				if($value['type'] == $type) {
					$dur = $value['dur'];
					if($type == "armor") {
						$dur = self::getSkillset("armor repair", "duration", $dur);
						$dur = self::ampDur($dur, $type);
						$total += $dur;
						$j++;
					} else if($type == "shield") {
						$dur = self::ampDur($dur, $type);
						$dur = self::getSkillset("shield booster", "duration", $dur);

						if($value['icon'] == "105_4") {
							$total += 60/($dur+$value['amount']);
						} else {
							$total += $dur;
						}
						$j++;
					} else if($type == "hull") {
						$dur = self::getSkillset("armor repair", "duration", $dur);
						$total += $dur;
						$j++;
					}

				}

			}
		}
		return ($total/$j);
	}

/**
 * Data
 * ampDur method
 * Calculates the modules that gives boosts to the shield/armour repair
 *
 * @param $dur (int)
 * @param $type (string)
 * @return
 */
	private function ampDur($dur, $type) {
		$total = $dur;
		if($type == "armor") {
			if(Fitting::$shipStats->armorDur) {
				foreach(Fitting::$shipStats->armorDur as $i => $value) {
					$total = Calculations::statOntoShip($total, $value['dur'], $value['type'], "%", $value['neg']);
				}
			}
		} else if($type == "shield") {
			if(Fitting::$shipStats->shieldDur) {
				foreach(Fitting::$shipStats->shieldDur as $i => $value) {
					$total = Calculations::statOntoShip($total, $value['dur'], $value['type'], "%", $value['neg']);
				}
			}
		}

		return $total;
	}

/**
 * Data
 * ampBooster method
 * Calculates the modules that gives boosts to the shield/armour booster
 *
 * @param $boostAmount (int)
 * @param $type (string)
 * @return
 */
	private function ampBooster($boostAmount, $icon, $type) {
		$total = $boostAmount;

		if($type == "shield") {
			if($icon == "105_4") {
				$total = Calculations::statOntoShip($total, 25, "+", "%", 1);
			}

			if(Fitting::$shipStats->getTankAmpShield()) {
				foreach(Fitting::$shipStats->getTankAmpShield() as $i => $value) {
					$total = Calculations::statOntoShip($total, $value['boost'], $value['type'], "%", $value['neg']);
				}
			}

			if(Fitting::$shipStats->getShipEffects()) {
				foreach(Fitting::$shipStats->getShipEffects() as $j => $effect) {
					if($effect['effect'] == "shieldBoost") {
						$total = Calculations::statOntoShip($total, (5*$effect['bonus']),$effect['type'],"%", 1);
					}
				}
			}
		} else if($type == "armor") {
			if(Fitting::$shipStats->getTankAmpArmor()) {
				foreach(Fitting::$shipStats->getTankAmpArmor() as $i => $value) {
					$total = Calculations::statOntoShip($total, $value['boost'], $value['type'], "%", $value['neg']);
				}
			}

			if(Fitting::$shipStats->getShipEffects()) {
				foreach(Fitting::$shipStats->getShipEffects() as $j => $effect) {
					if($effect['effect'] == "armorBoost") {
						$total = Calculations::statOntoShip($total, (5*$effect['bonus']),$effect['type'],"%", 1);
					}
				}
			}
		}

		return $total;
	}

/**
 * Data
 * setSigBoostforWarpDis method
 *
 *
 * @param
 * @return
 */
	private function setSigBoostforWarpDis() {
		if(Fitting::$shipStats->getSigRadiusBoost()) {
			foreach(Fitting::$shipStats->getSigRadiusBoost() as $i => $value) {
				if($value != 0) {
					Fitting::$shipStats->setSigRadius(Calculations::statOntoShip(Fitting::$shipStats->getSigRadius(), $value['sigAdd'], "+", "%", 0));
				}
			}
		}
	}

/**
 * Data
 * sensorBoosterAdd method
 * Increases the sensor booster type modules boost to the ship scan resolution
 *
 * @param
 * @return
 */
	private function sensorBoosterAdd() {
		Fitting::$shipStats->scan = 0;
		Fitting::$shipStats->range = 0;
		$arrRange = array();
		$arrScan = array();

		$modSlotOrderRange = array(
			7 => "Sub",
			3 => "Low",
			4 => "Mid",
			2 => "Rig",
			1 => "high"
		);
		$k = 0;
		if(Fitting::$shipStats->getSensorBooster()) {
			foreach($modSlotOrderRange as $i => $order) {
				foreach(Fitting::$shipStats->getSensorBooster() as $j => $value) {
					//echo $i." ".$value['order']."<br />";
					if($i == $value['order']) {
						if($value['range'] != 0) {
							$arrRange[$k]['range'] = $value['range'];
							$arrRange[$k]['negra'] = $value['negra'];
							$arrRange[$k]['order'] = $value['order'];
							$arrRange[$k]['type'] = $value['type'];
						}

						$k++;
					}
				}
			}
		}

		if($arrRange) {
			foreach($arrRange as $i => $value) {
				if($value['range'] != 0) {
					$arr[$i]['range'] = $value['range'];
					$arr[$i]['type'] = $value['type'];
					Fitting::$shipStats->range++;
					$arr[$i]['negra'] = Fitting::$shipStats->range;
				}
			}
		}
		$arrRange = $arr;

		$modSlotOrderScan = array(
			7 => "Sub",
			4 => "Mid",
			3 => "Low",

			1 => "high",
			2 => "Rig"
		);
		$k = 0;
		if(Fitting::$shipStats->getSensorBooster()) {
			foreach($modSlotOrderScan as $i => $order) {
				foreach(Fitting::$shipStats->getSensorBooster() as $j => $value) {

					if($i == $value['order']) {
						//echo $i." ".$value['order']."<br />";
						if($value['scan'] != 0) {
							$arrScan[$k]['scan'] = $value['scan'];
							$arrScan[$k]['negsc'] = $value['negsc'];
							$arrScan[$k]['order'] = $value['order'];
							$arrScan[$k]['type'] = $value['type'];
						}


						$k++;
					}
				}
			}
		}

		if($arrScan) {
			foreach($arrScan as $i => $value) {

				if($value['scan'] != 0) {
					$arrS[$i]['scan'] = $value['scan'];
					$arrS[$i]['type'] = $value['type'];
					Fitting::$shipStats->scan++;
					$arrS[$i]['negsc'] = Fitting::$shipStats->scan;
				}


			}
		}
		//Fitting::$shipStats->setSensorBooster($arr);
		$arrScan = $arrS;

		if($arrRange) {
			foreach($arrRange as $i => $value) {
				if($value['range']) {
					Fitting::$shipStats->setDistance(Calculations::statOntoShip(Fitting::$shipStats->getDistance(), $value['range'], $value['type'], "%", $value['negra']));
				}
			}
		}

		if($arrScan) {
			foreach($arrScan as $i => $value) {
				if($value['scan']) {
					Fitting::$shipStats->setScan(Calculations::statOntoShip(Fitting::$shipStats->getScan(), $value['scan'], $value['type'], "%", $value['negsc']));
				}
			}
		}
	}

/**
 * Data
 * getDroneSkillDamage method
 * Gets the dps for the drones (this includes drone amps, rigs and ship bonus')
 *
 * @param
 * @return
 */
	private function getDroneSkillDamage() {
		$k = 0;
		if(Fitting::$shipStats->getDroneDamage()) {
			foreach(Fitting::$shipStats->getDroneDamage() as $i => $value) {

				if(strstr($value['name'], "Bouncer")
				|| strstr($value['name'], "Curator")
				|| strstr($value['name'], "Garde")
				|| strstr($value['name'], "Warden")) {
					$tech = 3;
				} else {
					$tech = $value['techlevel'];
				}

				$damMod = self::setDamageModSkills("damageDr", $value['damageDr'], $tech);
				if(Fitting::$shipStats->getShipEffects()) {
					foreach(Fitting::$shipStats->getShipEffects() as $j => $effect) {
						if($effect['effect'] == "damagedr") {
							$damMod = Calculations::statOntoShip($damMod, (5*$effect['bonus']),$effect['type'],"%", 1);
						}
					}
				}

				if(Fitting::$shipStats->getDroneDamageMod()) {
					foreach(Fitting::$shipStats->getDroneDamageMod() as $k => $damAdd) {
						if($damAdd['damagedr']) {
							if($damAdd['type'] == $tech || $damAdd['type'] == 0) {
								$damMod = Calculations::statOntoShip($damMod, $damAdd['damagedr'],"+","%", $damAdd['neg']);
							}

						}
					}
				}

				$total = ($value['emDamage']+$value['exDamage']+$value['kiDamage']+$value['thDamage'])*$damMod;
				$rof = $value['rofDr'];

				$arr[$i]['name'] = $value['name'];
				$arr[$i]['rofDr'] = $rof;
				$arr[$i]['techlevel'] = $value['techlevel'];
				$arr[$i]['damageDr'] = $damMod;
				$arr[$i]['emDamage'] = $value['emDamage'];
				$arr[$i]['exDamage'] = $value['exDamage'];
				$arr[$i]['kiDamage'] = $value['kiDamage'];
				$arr[$i]['thDamage'] = $value['thDamage'];
				$arr[$i]['count'] = $value['count'];
				$arr[$i]['volley'] = $total;
				if($total == 0 || $rof == 0) {
					$arr[$i]['dps'] = 0;
				} else {
					$arr[$i]['dps'] = $total/$rof;
				}

				$k++;
			}
		}

		return $arr;
	}

/**
 * Data
 * remoteRepStats method
 * Calculates the remote rep cap usage based on ship bonus' and skills
 *
 * @param
 * @return
 */
	private function remoteRepStats() {

		foreach(Fitting::$shipStats->getTransCap() as $i => $value) {
			$cap = $value['capNeeded'];

			if(Fitting::$shipStats->getTransCapEff()) {
				foreach(Fitting::$shipStats->getTransCapEff() as $k => $capne) {
					if($value['type'] == $capne['type']) {
						$cap = Calculations::statOntoShip($cap, $capne['amount'], "-", "%", 1);
					}

				}
			}
			$cap = self::getSkillset(strtolower($value['type']), "capNeeded", $cap);

			if(Fitting::$shipStats->getShipEffects()) {
				foreach(Fitting::$shipStats->getShipEffects() as $j => $effect) {
					if($value['type'] == $effect['effect']) {
						$cap = Calculations::statOntoShip($cap, (5*$effect['bonus']),$effect['type'],"%", 1);
					}
				}
			}

			$arr[$i]['capNeeded'] = $cap;
			$arr[$i]['type'] = $value['type'];
			$arr[$i]['duration'] = $value['duration'];

			if($cap != 0 && $value['duration'] != 0) {
				$arr[$i]['use'] = $cap/$value['duration'];
			} else {
				$arr[$i]['use'] = 0;
			}


		}

		return $arr;
	}

/**
 * Data
 * getDPS method
 * Calculates the total dps for the ship
 *
 * @param
 * @return
 */
	private function getDPS() {
		$total = 0;

		if(Fitting::$shipStats->getDamageGun()) {
			foreach(Fitting::$shipStats->getDamageGun() as $i => $value) {
				$total += $value['dps'];
				if(array_key_exists("damageM", $value)) {
					Fitting::$shipStats->setMisUsed(Fitting::$shipStats->getMisUsed()-1);
					Fitting::$shipStats->setMissileDPS(Fitting::$shipStats->getMissileDPS()+$value['dps']);
				} else {
					Fitting::$shipStats->setTurUsed(Fitting::$shipStats->getTurUsed()-1);
					Fitting::$shipStats->setTurretDPS(Fitting::$shipStats->getTurretDPS()+$value['dps']);
				}
			}
		}
		//drone damage
		$dronecount=0;
		if(Fitting::$shipStats->getDroneDamage()) {
			foreach(Fitting::$shipStats->getDroneDamage() as $i => $value) {
				for($j = 0; $j < $value['count']; $j++) {
					if($dronecount < Fitting::$shipStats->droneMax) {
						$total += $value['dps'];
						Fitting::$shipStats->setDroneDPS(Fitting::$shipStats->getDroneDPS()+$value['dps']);
						$dronecount++;
					} else {
						break;
					}
				}
			}
		}

		return $total;
	}

/**
 * Data
 * getVolley method
 * Calculates the total volley for the ship
 *
 * @param
 * @return
 */
	private function getVolley() {
		$total = 0;
		if(Fitting::$shipStats->getDamageGun()) {
			foreach(Fitting::$shipStats->getDamageGun() as $i => $value) {
				$total += $value['volley'];
			}
		}
		return $total;
	}

/**
 * Data
 * getDPSAndVolley method
 * Takes the DPS and volley to gather total dps amounts
 *
 * @param
 * @return
 */
	private function getDPSAndVolley() {
		$avDPS;
		$condition = "";
		$i = 0;

		$arr = Fitting::$shipStats->getDamageGun();
		if($arr) {
			foreach($arr as $i => $value) {
				if($value['rofP']) {
					$dex = "P";
					$reload = 10;
				} else if($value['rofL']) {
					$dex = "L";
					$reload = 0;
				} else if($value['rofH']) {
					$dex = "H";
					$reload = 10;
				} else if($value['rofM']) {
					$dex = "M";
					$reload = 10;
				} else if($value['rof']) {
					$dex = "";
					$reload = 0;
				}

				$total = ($value['emDamage']+$value['exDamage']+$value['kiDamage']+$value['thDamage'])*$value['damage'.$dex];

				if(strstr(strtolower($value['name']), "civilian")) {
					$averagedps = (1*($value['rof'.$dex])+$reload)/1;
				} else {
					if($total != 0) {

						if($value['capacity']) {
							$capa = $value['capacity'];
						} else {
							$capa = 1;
						}

						if($value['ammoCap']) {
							$ammocap = $value['ammoCap'];
						} else {
							$ammocap = 1;
						}

						$averagedps = ((($capa/$ammocap)*$value['rof'.$dex])+$reload)/(($capa/$ammocap));
					}

				}

				$arr[$i]['volley'] = $total;
				$arr[$i]['averageReload'] = $averagedps;

				if($total != 0) {
					$arr[$i]['dps'] = ($total/$averagedps);
				}
				//\Misc::pre($arr);
				Fitting::$shipStats->setDamageGun($arr);
			}
		}
	}

/**
 * Data
 * turretMods method
 * This method handles retrieving information turret weapon damage based on what is fitted to the ship
 *
 * @param
 * @return
 */
	private function turretMods() {

		foreach(Fitting::$shipStats->getDamageGun() as $i => $value) {

			if($value['rofP']) {
				$dex = "P";
			} else if($value['rofL']) {
				$dex = "L";
			} else if($value['rofH']) {
				$dex = "H";
			} else if($value['rofM']) {
				$dex = "M";
			} else if($value['rof']) {
				$dex = "";
			}

			if($dex != "M") {
				$rof = self::setDamageModSkills("rof".$dex, $value['rof'.$dex], $value['techlevel']);
				$dMod = self::setDamageModSkills("damage".$dex, $value['damage'.$dex], $value['techlevel']);
				//echo $rof." - ".$dMod." - ".$value['damage'.$dex]."<br />";

				$em = $value['emDamage'];
				$ex = $value['exDamage'];
				$ki = $value['kiDamage'];
				$th = $value['thDamage'];

				if($value['capNeed']) {
					$cap = self::getSkillset("turretcap", "turretCap", $value['capNeed']);
					$cap = Calculations::statOntoShip($cap, $value['capNeededBonus'],"-","%", 1);
				}

				if(Fitting::$shipStats->getShipIcon() == 4302
				|| Fitting::$shipStats->getShipIcon() == 4306
				|| Fitting::$shipStats->getShipIcon() == 4310
				|| Fitting::$shipStats->getShipIcon() == 4308) {
					Fitting::$shipStats->setRSize("Large");
				}
				if(self::isSmartBomb(strtolower($value['name']))) {
					$rof = Calculations::statOntoShip($rof, 25,"-","%", 1);
				}

				if(Fitting::$shipStats->getRSize() == $value['type'] || $value['type'] == "X-Large") {
					if(Fitting::$shipStats->getShipEffects()) {
						foreach(Fitting::$shipStats->getShipEffects() as $j => $effect) {
							if($effect['effect'] == "rofP"
							|| $effect['effect'] == "rofH"
							|| $effect['effect'] == "rofL") {
								if($dex == "P") {
									if($effect['type'] == "=") {
										$rof = Calculations::statOntoShip($rof, $effect['bonus'],"-","%", 1);
									} else {
										$rof = Calculations::statOntoShip($rof, (5*$effect['bonus']),"-","%", 1);
									}
								}
								if($dex == "H") {
									if($effect['type'] == "=") {
										$rof = Calculations::statOntoShip($rof, $effect['bonus'],"-","%", 1);
									} else {
										$rof = Calculations::statOntoShip($rof, (5*$effect['bonus']),"-","%", 1);
									}
								}
								if($dex == "L") {
									if($effect['type'] == "=") {
										$rof = Calculations::statOntoShip($rof, $effect['bonus'],"-","%", 1);
									} else {
										$rof = Calculations::statOntoShip($rof, (5*$effect['bonus']),"-","%", 1);
									}
								}
							} else if($effect['effect'] == "damageP"
								   || $effect['effect'] == "damageH"
								   || $effect['effect'] == "damageL") {

								if($dex == "P") {
									if($effect['type'] == "=") {
										$dMod = Calculations::statOntoShip($dMod, $effect['bonus'],"+","%", 1);
									} else {
										$dMod = Calculations::statOntoShip($dMod, (5*$effect['bonus']),$effect['type'],"%", 1);
									}
								}
								if($dex == "H") {
									if($effect['type'] == "=") {
										$dMod = Calculations::statOntoShip($dMod, $effect['bonus'],"+","%", 1);
									} else {
										$dMod = Calculations::statOntoShip($dMod, (5*$effect['bonus']),$effect['type'],"%", 1);
									}
								}
								if($dex == "L") {
									if($effect['type'] == "=") {
										$dMod = Calculations::statOntoShip($dMod, $effect['bonus'],"+","%", 1);
									} else {
										$dMod = Calculations::statOntoShip($dMod, (5*$effect['bonus']),$effect['type'],"%", 1);
									}
								}
							} else if($effect['effect'] == "turretCap") {
								$cap = Calculations::statOntoShip($cap, (5*$effect['bonus']),"-","%", 1);
							}
						}
					}
				}

				if(Fitting::$shipStats->getDamageModules()) {
					foreach(Fitting::$shipStats->getDamageModules() as $j => $damMod) {
						if($damMod['damage'.$dex]) {
							$dMod = Calculations::statOntoShip($dMod, $damMod['damage'.$dex], "+","%", $damMod['neg']);
						}
						if($damMod['rof'.$dex]) {
							$rof = Calculations::statOntoShip($rof, $damMod['rof'.$dex], "-","%", $damMod['neg']);
						}
					}
				}

			} else {
				$rof = self::setDamageModSkills("rof".$dex, $value['rof'.$dex], $value['techlevel']);
				$dMod = 1;
				$em = self::setDamageModSkills("damage".$dex, $value['emDamage'], $value['techlevel']);
				$ex = self::setDamageModSkills("damage".$dex, $value['exDamage'], $value['techlevel']);
				$ki = self::setDamageModSkills("damage".$dex, $value['kiDamage'], $value['techlevel']);
				$th = self::setDamageModSkills("damage".$dex, $value['thDamage'], $value['techlevel']);

				if(Fitting::$shipStats->getShipIcon() == 12038
				|| Fitting::$shipStats->getShipIcon() == 12032
				|| Fitting::$shipStats->getShipIcon() == 11377
				|| Fitting::$shipStats->getShipIcon() == 12034) {
					Fitting::$shipStats->setRSize("Large");
				}

				if(Fitting::$shipStats->getRSize() == $value['type']) {
					if(Fitting::$shipStats->getShipEffects()) {
						foreach(Fitting::$shipStats->getShipEffects() as $j => $effect) {

							switch($effect['effect']) {
								case "damageem":
									if($em != 0) {
										$em = Calculations::statOntoShip($em, (5*$effect['bonus']),$effect['type'],"%", 1);
									}
								break;
								case "damageex":
									if($ex != 0) {
										$ex = Calculations::statOntoShip($ex, (5*$effect['bonus']),$effect['type'],"%", 1);
									}
								break;
								case "damageki":
									if($ki != 0) {
										$ki = Calculations::statOntoShip($ki, (5*$effect['bonus']),$effect['type'],"%", 1);
									}
								break;
								case "damageth":
									if($th != 0) {
										$th = Calculations::statOntoShip($th, (5*$effect['bonus']),$effect['type'],"%", 1);
									}
								break;
								case "rofM":

									if($effect['bonus'] == 60) {
										$rof = Calculations::statOntoShip($rof, (5*5),"-","%", 1);
									} else {
										$rof = Calculations::statOntoShip($rof, (5*$effect['bonus']),"-","%", 1);
									}
								break;
							}

						}
					}
				}

				if(Fitting::$shipStats->getDamageModules()) {
					foreach(Fitting::$shipStats->getDamageModules() as $j => $damMod) {
						if($damMod['damageM']) {
							$em = Calculations::statOntoShip($em, $damMod['damageM'], "+","%", $damMod['neg']);
							$ex = Calculations::statOntoShip($ex, $damMod['damageM'], "+","%", $damMod['neg']);
							$ki = Calculations::statOntoShip($ki, $damMod['damageM'], "+","%", $damMod['neg']);
							$th = Calculations::statOntoShip($th, $damMod['damageM'], "+","%", $damMod['neg']);
						}
						if($damMod['rofM']) {
							$rof = Calculations::statOntoShip($rof, $damMod['rofM'], "-","%", $damMod['neg']);
						}
					}
				}
			}

			if(strstr(strtolower($value['name']), "civilian gatling pulse")) {
				$em = 3;
				$ex = 0;
				$ki = 0;
				$th = 2;
			} else if(strstr(strtolower($value['name']), "civilian gatling autocannon")) {
				$em = 0;
				$ex = 3;
				$ki = 2;
				$th = 0;
			} else if(strstr(strtolower($value['name']), "civilian gatling railgun")) {
				$em = 0;
				$ex = 0;
				$ki = 2;
				$th = 3;
			} else if(strstr(strtolower($value['name']), "civilian light")) {
				$em = 0;
				$ex = 0;
				$ki = 2;
				$th = 3;
			}

			$arr[$i]['name'] = $value['name'];
			$arr[$i]['rof'.$dex] = $rof;
			$arr[$i]['type'] = $value['type'];
			$arr[$i]['capacity'] = $value['capacity'];
			$arr[$i]['techlevel'] = $value['techlevel'];
			$arr[$i]['damage'.$dex] = $dMod;
			$arr[$i]['emDamage'] = $em;
			$arr[$i]['exDamage'] = $ex;
			$arr[$i]['kiDamage'] = $ki;
			$arr[$i]['thDamage'] = $th;
			$arr[$i]['ammoCap'] = $value['ammoCap'];
			if($value['capNeed']) {
				$arr[$i]['capNeed'] = $cap;
				$arr[$i]['use'] = $cap/$rof;
			}

			$dex = "";
		}
		/*echo "<pre>";
		print_r($arr);
		echo "</pre>";*/
		return $arr;
	}

/**
 * Data
 * setDamageModSkills method
 * Calculates the weapon stats based on level 5 skills
 *
 * @param $param_type (string)
 * @param $param_input (int)
 * @param $param_techModLevel (int)
 * @return
 */
	private function setDamageModSkills($param_type, $param_input, $param_techModLevel) {
		if($param_type == "rofP" || $param_type == "rofL" || $param_type == "rofH") $param_type = "rofT";
		if($param_type == "damageP" || $param_type == "damageL" || $param_type == "damageH") $param_type = "damageT";

		switch($param_type) {
			case "rofT":
				//Gunnery skill
				$param_input = Calculations::statOntoShip($param_input, (5*2),"-","%", 1);

				//Rapid firing
				$param_input = Calculations::statOntoShip($param_input, (5*4),"-","%", 1);
				//trace(turretType,modifier);
			break;
			case "damageT":
				//Surgical Strike
				$param_input = Calculations::statOntoShip($param_input, (5*3),"+","%", 1);

				//Projectile Skill
				$param_input = Calculations::statOntoShip($param_input, (5*5),"+","%", 1);

				if($param_techModLevel == 2) {
					//Specialization skill
					$param_input = Calculations::statOntoShip($param_input, (5*2),"+","%", 1);
				}
			break;
			case "rofM":
				//missile launcher skill
				$param_input = Calculations::statOntoShip($param_input, (5*2),"-","%", 1);

				//Rapid launch
				$param_input = Calculations::statOntoShip($param_input, (5*3),"-","%", 1);

				if($param_techModLevel == 2) {
					//Specialization
					$param_input = Calculations::statOntoShip($param_input, (5*2),"-","%", 1);
				}
			break;
			case "damageM":
				//Warhead upgrade
				$param_input = Calculations::statOntoShip($param_input, (5*2),"+","%", 1);

				//Missile specific
				$param_input = Calculations::statOntoShip($param_input, (5*5),"+","%", 1);

			break;
			case "damageDr":
				//Combat drone operation
				$param_input = Calculations::statOntoShip($param_input, (5*5),"+","%", 1);

				//Drone Interfacing
				$param_input = Calculations::statOntoShip($param_input, (5*20),"+","%", 1);

				if($param_techModLevel == 2) {
					//Drone Specialization
					$param_input = Calculations::statOntoShip($param_input, (5*2),"+","%", 1);
				}

			break;
		}

		return $param_input;
	}

/**
 * Data
 * totalCapUse method
 * Gets the total use of cap within the system
 *
 * @param
 * @return
 */
	private function totalCapUse() {
		$total = 0;
		if(Fitting::$shipStats->getCapGJ()) {
			foreach(Fitting::$shipStats->getCapGJ() as $i => $value) {
				$total += $value['use'];
				//echo "".$total."<br />";
			}
		}
		if(Fitting::$shipStats->getTransCap()) {
			foreach(Fitting::$shipStats->getTransCap() as $i => $value) {
				$total += $value['use'];
			}
		}
		if(Fitting::$shipStats->getDamageGun()) {
			foreach(Fitting::$shipStats->getDamageGun() as $i => $value) {
				$total += $value['use'];
			}
		}
		return $total;
	}

/**
 * Data
 * totalCapInjected method
 * Gets the total use of cap with the assistance of cap injectors
 *
 * @param
 * @return
 */
	private function totalCapInjected() {
		$total = 0;
		if(Fitting::$shipStats->getCapInj()) {
			foreach(Fitting::$shipStats->getCapInj() as $i => $value) {
				$total += $value['use'];
			}
		}
		//any nos effects
		if(Fitting::$shipStats->getCapGJ()) {
			foreach(Fitting::$shipStats->getCapGJ() as $i => $value) {
				if($value['capAdd']) {
					$total += $value['capAdd'];
				}
			}
		}
		return $total;
	}

/**
 * Data
 * capacitorInjector method
 * Gets the total use of cap with the assistance of cap injectors
 *
 * @param
 * @return
 */
	private function capacitorInjector() {

		foreach(Fitting::$shipStats->getCapInj() as $i => $value) {
			$arr[$i]['duration'] = $value['duration'];
			$arr[$i]['capacity'] = $value['capacity'];

			if(!$value['amount'] || !$value['vol']) {
				$arrWithBooster = self::capInjEmpty($value['capacity']);

				$arr[$i]['amount'] = $arrWithBooster['amount'];
				$arr[$i]['vol'] = $arrWithBooster['vol'];
				//echo "here";
				$arr[$i]['use'] = Calculations::capInjector($arrWithBooster['amount'], $value['capacity'], $arrWithBooster['vol'], $value['duration']);
			} else {
				$arr[$i]['amount'] = $value['amount'];
				$arr[$i]['vol'] = $value['vol'];
				$arr[$i]['use'] = Calculations::capInjector($value['amount'], $value['capacity'], $value['vol'], $value['duration']);
			}
		}

		return $arr;
	}

/**
 * Data
 * capacitorUsage method
 * Figures out how much cap is being used by the modules
 *
 * @param
 * @return
 */
	private function capacitorUsage() {
		$arr = array();

		if(Fitting::$shipStats->getCapGJ()) {
			foreach(Fitting::$shipStats->getCapGJ() as $i => $value) {
				$dur = self::getSkillset(strtolower($value['name']), "duration", $value['duration']);
				$cap = self::getSkillset(strtolower($value['name']), "capNeeded", $value['capNeeded']);

				if($value['react']) {
					$rea = self::getSkillset(strtolower($value['name']), "react", $value['react']);
					$dur = $dur+$rea;
				}

				$arr[$i]['name'] = $value['name'];
				if($value['capNeededBonus']) {
					$arr[$i]['capNeeded'] = $cap-(($cap/100)*$value['capNeededBonus']);
					$cap = $cap-(($cap/100)*$value['capNeededBonus']);
				} else {
					$arr[$i]['capNeeded'] = $cap;
				}

				if($value['capNeededBonus']) {
					$arr[$i]['duration'] = $dur-(($dur/100)*$value['durationBonus']);
					$dur = $dur-(($dur/100)*$value['durationBonus']);
				} else {
					$arr[$i]['duration'] = $dur;
				}

				if($cap == 0 || $dur == 0) {
					$arr[$i]['use'] = 0;
				} else {
					$arr[$i]['use'] = ($cap/$dur);
				}

				if($value['capAdd']) {
					$arr[$i]['capAdd'] = $value['capAdd']/$dur;
				}

				//HERE HERE
				//echo $arr[$i]['name']." ".$arr[$i]['capNeeded']." ".$arr[$i]['duration']." ".$arr[$i]['use']." -> ".$cap." -- ".$dur."<br />";
			}
		}

		return $arr;
	}

/**
 * Data
 * getSkillset method
 * Apply the skills to the module
 *
 * @param $param_module (string)
 * @param $param_type (string)
 * @param $param_value (int)
 * @return
 */
	private function getSkillset($param_module, $param_type, $param_value) {

		if(Fitting::advancedModuleSettings($param_module) == "mwd") {
			if($param_type == "duration") {
				return $param_value;
			} else {
				$output = $param_value;
				$output = Calculations::statOntoShip($output, 25, "-", "%", 1);

				if(Fitting::$shipStats->getSpeedT3Cap()) {
					$output = Calculations::statOntoShip($output, Fitting::$shipStats->getSpeedT3Cap(), "-", "%", 1);
				}
				return $output;
			}
		}
		if(Fitting::advancedModuleSettings($param_module) == "ab") {
			if($param_type == "duration") {
				return Calculations::statOntoShip($param_value, 50, "+", "%", 1);
			} else {
				$output = $param_value;
				$output = Calculations::statOntoShip($output, 50, "-", "%", 1);

				if(Fitting::$shipStats->getSpeedT3Cap()) {
					$output = Calculations::statOntoShip($output, Fitting::$shipStats->getSpeedT3Cap(), "-", "%", 1);
				}
				return $output;
			}
		}

		if(strstr($param_module, "disruption")
		|| strstr($param_module, "disruptor")) {
			if($param_type == "capNeeded") {
				$cap = Calculations::statOntoShip($param_value, 25, "-", "%", 1);

				if(Fitting::$shipStats->getShipEffects()) {
					foreach(Fitting::$shipStats->getShipEffects() as $j => $effect) {
						if($effect['effect'] == "propJamming") {
							$cap = Calculations::propCeptorBonus($cap, $effect['bonus']);
						}
					}
				}

				return $cap;
			}
		}

		if(strstr($param_module, "web")
		|| strstr($param_module, "x5 ")
		|| strstr($param_module, "langour")
		|| strstr($param_module, "fleeting propulsion")) {
			if($param_type == "capNeeded") {
				$cap = Calculations::statOntoShip($param_value, 25, "-", "%", 1);

				if(Fitting::$shipStats->getShipEffects()) {
					foreach(Fitting::$shipStats->getShipEffects() as $j => $effect) {
						if($effect['effect'] == "propJamming") {
							$cap = Calculations::propCeptorBonus($cap, $effect['bonus']);
						}
					}
				}

				return $cap;
			}
		}

		if(strstr($param_module, "scram")) {
			if($param_type == "capNeeded") {
				$cap = Calculations::statOntoShip($param_value, 25, "-", "%", 1);

				if(Fitting::$shipStats->getShipEffects()) {
					foreach(Fitting::$shipStats->getShipEffects() as $j => $effect) {
						if($effect['effect'] == "propJamming") {
							$cap = Calculations::propCeptorBonus($cap, $effect['bonus']);
						}
					}
				}

				return $cap;
			}
		}

		if(strstr($param_module, "shieldtrans")
		|| strstr($param_module, "armortrans")
		|| strstr($param_module, "energytrans")) {
			if($param_type == "capNeeded") {
				return Calculations::statOntoShip($param_value, 25, "-", "%", 1);
			}
		}

		if(strstr($param_module, "remote ecm burst")) {
			if($param_type == "react") {
				$param_value = Calculations::statOntoShip($param_value, 25, "-", "%", 1);
				$param_value = $param_value/1000;
			}
			return $param_value;
		}

		if(strstr($param_module, "ecm")) {
			if($param_type == "capNeeded") {
				return Calculations::statOntoShip($param_value, 25, "-", "%", 1);
			}
		}

		if(strstr($param_module, "neutralizer")
		|| strstr($param_module, "w infectious")
		|| strstr($param_module, "power core disruptor")
		|| strstr($param_module, "destabilizer")
		|| strstr($param_module, "unstable power")) {
			if($param_type == "capNeeded") {
				return Calculations::statOntoShip($param_value, 25, "-", "%", 1);
			}
		}

		if(strstr($param_module, "target painter")
		|| strstr($param_module, "weapon navigation")) {
			if($param_type == "capNeeded") {
				return Calculations::statOntoShip($param_value, 25, "-", "%", 1);
			}
		}

		if(strstr($param_module, "sensor dampener")
		|| strstr($param_module, "sensor disruptor")
		|| strstr($param_module, "sensor suppressor")
		|| strstr($param_module, "scanning dampening")
		|| strstr($param_module, "sensor disruptor")
		|| strstr($param_module, "sensor array")) {
			if($param_type == "capNeeded") {
				return Calculations::statOntoShip($param_value, 25, "-", "%", 1);
			}
		}

		if(strstr($param_module, "turretcap")) {
			if($param_type == "turretCap") {
				return Calculations::statOntoShip($param_value, 25, "-", "%", 1);
			}
		}

		if(strstr($param_module, "cargo scan")
		|| strstr($param_module, "cargo ident")
		|| strstr($param_module, "shipment pr")
		|| strstr($param_module, "freight sen")) {
			if($param_type == "duration") {
				return Calculations::statOntoShip($param_value, 25, "-", "%", 1);
			}
		}

		if(strstr($param_module, "shield booster")
		|| strstr($param_module, "shield overload")
		|| strstr($param_module, "clarity ward")
		|| strstr($param_module, "converse ")
		|| strstr($param_module, "saturation in")) {
			if($param_type == "capNeeded") {
				return Calculations::statOntoShip($param_value, 10, "-", "%", 1);
			}
			if($param_type == "duration") {
				return Calculations::statOntoShip($param_value, 10, "-", "%", 1);
			}
		}

		if(strstr($param_module, "hull repair")
		|| strstr($param_module, "hull reconstructer")
		|| strstr($param_module, "structural restoration")
		|| strstr($param_module, "structural regenerator")) {
			if($param_type == "duration") {
				return Calculations::statOntoShip($param_value, 25, "-", "%", 1);
			}
		}

		if(strstr($param_module, "armor repair")
		|| strstr($param_module, "vestment reconstructer")
		|| strstr($param_module, "carapace restoration")
		|| strstr($param_module, "armor regenerator")) {
			if($param_type == "duration") {
				return Calculations::statOntoShip($param_value, 25, "-", "%", 1);
			}
		}

		if(self::isSmartBomb($param_module)) {
			if($param_type == "duration") {
				return Calculations::statOntoShip($param_value, 25, "-", "%", 1);
			}
		}




		return $param_value;
	}

	public static $modSlots;
	public static $droneSlots;
	public static $cargoSlots;

/**
 * Stat
 * capInjEmpty method
 * Based on the module calculate the size of boost avialable
 *
 * @param $modCap (int)
 * @return
 */
	private function capInjEmpty($modCap) {

		switch($modCap) {
			case "160":
				$arr['amount'] = "800";
				$arr['vol'] = "32";
			break;
			case "40":
				$arr['amount'] = "800";
				$arr['vol'] = "32";
			break;
			case "15":
				$arr['amount'] = "200";
				$arr['vol'] = "8";
			break;
			case "10":
				$arr['amount'] = "200";
				$arr['vol'] = "8";
			break;
		}

		return $arr;
	}

/**
 * Stat
 * isSmartBomb method
 * Method tries to figure out if the module is a smartbomb
 *
 * @param $param_module (string)
 * @return
 */
	private function isSmartBomb($param_module) {

		if(strstr($param_module, "smartbomb")
		|| strstr($param_module, "notos")
		|| strstr($param_module, "shockwave charge")
		|| strstr($param_module, "degenerative co")
		|| strstr($param_module, "yf-12a")
		|| strstr($param_module, "rudimentary co")) {
			return true;
		}
		return false;
	}

/**
 * Stat
 * getTechLevel method
 * Gets the tech level of a module by the tech input else name or meta will identify
 *
 * @param $tech (string)
 * @param $meta (string)
 * @param $name (string)
 * @return (string)
 */
	private function getTechLevel($tech, $meta, $name) {

		switch($tech) {
			case "1":
				 if(($meta == 6)) // is a storyline item?
				{
					return "storyline";
				}
				else if(($meta > 6) && ($meta < 10)) // is a faction item?
				{
					return "faction";
				}
			 	else if(($meta > 10) && strstr($name,"Modified")) // or it's an officer?
				{
					return "officer";
				}
				else if(($meta > 10) && (strstr($name,"-Type"))) // or it's just a deadspace item.
				{
					return "deadspace";
				}
				else if((
					strstr($name,"Blood ")
					|| strstr($name,"Sansha")
					|| strstr($name,"Arch")
					|| strstr($name,"Domination")
					|| strstr($name,"Republic")
					|| strstr($name,"Navy")
					|| strstr($name,"Guardian")
					|| strstr($name,"Guristas")
					|| strstr($name,"Shadow")
					)
					) // finally if it's a faction should have its prefix
				{
					return "faction";
				}
				else // but maybe it was only a T1 item :P
				{
					return "techI";
				}
			case "2":
				return "techII";
			break;
			case "3":
				return "techIII";
			break;
			default:
				return "techI";
			break;
		}
	}

/**
 * Output
 * displayShipStats method
 * method in controll of applying the stats tot he template and then passing that back into the killboard system to be displayed
 *
 * @param $param_ship (string)
 * @param $param_shipimgx (string)
 * @param $param_shipimgy (string)
 * @return (string)
 */
	private function displayShipStats($param_ship, $param_shipimgx, $param_shipimgy) {
		//global $shipStats;
		global $smarty;

		$simpleurlheader = Misc::curPageURL();

		if(Fitting::$shipStats->getShieldAmount() > 999999) {
			Fitting::$shipStats->setShieldAmount(round((Fitting::$shipStats->getShieldAmount()/1000000),1)." m");
		} else {
			Fitting::$shipStats->setShieldAmount(round(Fitting::$shipStats->getShieldAmount()));
		}
		if(Fitting::$shipStats->getArmorAmount() > 999999) {
			Fitting::$shipStats->setArmorAmount(round((Fitting::$shipStats->getArmorAmount()/1000000),1)." m");
		} else {
			Fitting::$shipStats->setArmorAmount(round(Fitting::$shipStats->getArmorAmount()));
		}
		if(Fitting::$shipStats->getHullAmount() > 999999) {
			Fitting::$shipStats->setHullAmount(round((Fitting::$shipStats->getHullAmount()/1000000),1)." m");
		} else {
			Fitting::$shipStats->setHullAmount(round(Fitting::$shipStats->getHullAmount()));
		}

		if(Fitting::$shipStats->getEffectiveShield() > 999999) {
			Fitting::$shipStats->setEffectiveShield(round((Fitting::$shipStats->getEffectiveShield()/1000000),1)." m");
		} else {
			Fitting::$shipStats->setEffectiveShield(round(Fitting::$shipStats->getEffectiveShield()));
		}
		if(Fitting::$shipStats->getEffectiveArmor() > 999999) {
			Fitting::$shipStats->setEffectiveArmor(round((Fitting::$shipStats->getEffectiveArmor()/1000000),1)." m");
		} else {
			Fitting::$shipStats->setEffectiveArmor(round(Fitting::$shipStats->getEffectiveArmor()));
		}
		if(Fitting::$shipStats->getEffectiveHull() > 999999) {
			Fitting::$shipStats->setEffectiveHull(round((Fitting::$shipStats->getEffectiveHull()/1000000),1)." m");
		} else {
			Fitting::$shipStats->setEffectiveHull(round(Fitting::$shipStats->getEffectiveHull()));
		}


		if(Fitting::$shipStats->getCapStable()) {
			$capSize = "capSizeGreen";
			$back = "shipcapright";
			$capAmountM =  round(Fitting::$shipStats->getCapStatus());
			$perc = "";
		} else {
			$capSize = "capSizeDarkRed";
			$back = "shipcaprightRed";
			$capAmountM = Fitting::$shipStats->getCapStatus();
			$perc = "";
		}
		$backdrop = "";

		if(config::get('ship_display_back')) {
			$smarty->assign('ship_display_back', config::get('ship_display_back'));
		} else {
			$smarty->assign('ship_display_back', "#222222");
		}

		//$smarty->assign('simpleurl', self::$simpleurl);
		if(Misc::$simpleurl) {
			$smarty->assign('simpleurlheader', $simpleurlheader);
		} else {
			$smarty->assign('simpleurlheader', config::get('cfg_kbhost'));
		}

		$smarty->assign('getShipIcon', Fitting::$shipStats->getShipIcon());
		$smarty->assign('backdropImgType', $backdropImgType);
		$smarty->assign('modSlotsh', Fitting::$modSlots[1]);
		$smarty->assign('modSlotsm', Fitting::$modSlots[2]);
		$smarty->assign('modSlotsl', Fitting::$modSlots[3]);
		$smarty->assign('modSlotsr', Fitting::$modSlots[5]);
		$smarty->assign('modSlotss', Fitting::$modSlots[0]);

		$smarty->assign('getPilotNameURL', Fitting::$shipStats->getPilotNameURL());
		$smarty->assign('getPilotPort', Fitting::$shipStats->getPilotPort());
		$smarty->assign('getPilotName', Misc::ShortenText(Fitting::$shipStats->getPilotName(),20));
		$smarty->assign('getPilotCorpURL', Fitting::$shipStats->getPilotCorpURL());
		$smarty->assign('getPilotCorpShort', Misc::ShortenText(Fitting::$shipStats->getPilotCorp(),20));
		$smarty->assign('getPilotCorp', Fitting::$shipStats->getPilotCorp());
		$smarty->assign('getPilotAllianceURL', Fitting::$shipStats->getPilotAllianceURL());
		$smarty->assign('getPilotAlliance', Misc::ShortenText(Fitting::$shipStats->getPilotAlliance(),20));
		$smarty->assign('getPilotDate', Fitting::$shipStats->getPilotDate());
		$smarty->assign('getPilotShipURL', Fitting::$shipStats->getPilotShipURL());
		$smarty->assign('getPilotShip', Misc::ShortenText(Fitting::$shipStats->getPilotShip(), 20));
		$smarty->assign('getPilotShipClass', Fitting::$shipStats->getPilotShipClass());
		$smarty->assign('getPilotLocURL', Fitting::$shipStats->getPilotLocURL());
		$smarty->assign('getPilotLoc', Fitting::$shipStats->getPilotLoc());
		$smarty->assign('getPilotLocReg', Fitting::$shipStats->getPilotLocReg());
		$smarty->assign('getPilotLocSec', Misc::getSystemColour(Fitting::$shipStats->getPilotLocSec()));

		$smarty->assign('totcal', Fitting::$shipStats->getCalAmount());
		$smarty->assign('usedcal', Fitting::$shipStats->getCalUsed());
		if(Fitting::$shipStats->getCalAmount() == 0) {
			$percal = 0;
		} else {
			$percal = ((Fitting::$shipStats->getCalUsed()/Fitting::$shipStats->getCalAmount()));
		}

		$ping = ($percal/2)*10;
		$startcalX = 61;
		$startcalY = 3;
		$endcalX = 10;
		$endcalY = 100;
		$controlcalX1 = 23;
		$controlcalY1 = 46;
		$newMoveTox = pow ( (1-$percal) , 2 ) * $startcalX + 2 * (1-$percal) * $percal * $controlcalX1 + pow ( $percal , 2 ) * $endcalX;
		$newMoveToy = pow ( (1-$percal) , 2 ) * $startcalY + 2 * (1-$percal) * $percal * $controlcalY1 + pow ( $percal , 2 ) * $endcalY;
		$percal = $percal/2;
		$newControlX = pow ( (1-$percal) , 2 ) * $startcalX + 2 * (1-$percal) * $percal * $controlcalX1 + pow ( $percal , 2 ) * $endcalX;
		$newControlY = pow ( (1-$percal) , 2 ) * $startcalY + 2 * (1-$percal) * $percal * $controlcalY1 + pow ( $percal , 2 ) * $endcalY;

		$smarty->assign('percalxs', $startcalX);
		$smarty->assign('percalys', $startcalY);
		$smarty->assign('percalx1', $newControlX-$ping);
		$smarty->assign('percaly1', $newControlY-$ping);
		$smarty->assign('percalxe', $newMoveTox);
		$smarty->assign('percalye', $newMoveToy);


		$smarty->assign('totcpu', round(Fitting::$shipStats->getCpuAmount(),2));
		if(Fitting::$shipStats->getCpuAmount() < Fitting::$shipStats->loadCPU) {
			if(Fitting::$shipStats->getCpuAmount() == 0) {
				$percpu = 0;
			} else {
				$percpu = ((Fitting::$shipStats->getCpuAmount()/Fitting::$shipStats->getCpuAmount()));
			}
			$smarty->assign('usedcpu', "<span style='color:#b00000;'>".round(Fitting::$shipStats->loadCPU,2)."</span>");

		} else {
			$smarty->assign('usedcpu', round(Fitting::$shipStats->loadCPU,2));
			if(Fitting::$shipStats->getCpuAmount() == 0) {
				$percpu = 0;
			} else {
				$percpu = ((Fitting::$shipStats->loadCPU/Fitting::$shipStats->getCpuAmount()));
			}
		}


		//$percpu = ((Fitting::$shipStats->loadCPU/Fitting::$shipStats->getCpuAmount()));
		$ping = 12*$percpu;
		$startcpuX = 6;
		$startcpuY = 149;
		$endcpuX = 67;
		$endcpuY = 2;
		$controlcpuX1 = 65;
		$controlcpuY1 = 87;
		$newMoveTocpux = pow ( (1-$percpu) , 2 ) * $startcpuX + 2 * (1-$percpu) * $percpu * $controlcpuX1 + pow ( $percpu , 2 ) * $endcpuX;
		$newMoveTocpuy = pow ( (1-$percpu) , 2 ) * $startcpuY + 2 * (1-$percpu) * $percpu * $controlcpuY1 + pow ( $percpu , 2 ) * $endcpuY;
		$percpu = $percpu/2;
		$newControlcpuX = pow ( (1-$percpu) , 2 ) * $startcpuX + 2 * (1-$percpu) * $percpu * $controlcpuX1 + pow ( $percpu , 2 ) * $endcpuX;
		$newControlcpuY = pow ( (1-$percpu) , 2 ) * $startcpuY + 2 * (1-$percpu) * $percpu * $controlcpuY1 + pow ( $percpu , 2 ) * $endcpuY;

		$smarty->assign('percpuxs', $startcpuX);
		$smarty->assign('percpuys', $startcpuY);
		$smarty->assign('percpux1', $newControlcpuX+$ping);
		$smarty->assign('percpuy1', $newControlcpuY+$ping);
		$smarty->assign('percpuxe', $newMoveTocpux);
		$smarty->assign('percpuye', $newMoveTocpuy);



		$smarty->assign('totprg', round(Fitting::$shipStats->getPrgAmount(),2));

		if(Fitting::$shipStats->getPrgAmount() < Fitting::$shipStats->loadPower) {
			if(Fitting::$shipStats->getPrgAmount() == 0) {
				$perprg = 0;
			} else {
				$perprg = ((Fitting::$shipStats->getPrgAmount()/Fitting::$shipStats->getPrgAmount()));
			}
			$smarty->assign('usedprg', "<span style='color:#b00000;'>".round(Fitting::$shipStats->loadPower,2)."</span>");

		} else {
			$smarty->assign('usedprg', round(Fitting::$shipStats->loadPower,2));

			if(Fitting::$shipStats->getPrgAmount() == 0) {
				$perprg = 0;
			} else {
				$perprg = ((Fitting::$shipStats->loadPower/Fitting::$shipStats->getPrgAmount()));
			}
		}

		//$perprg = ((Fitting::$shipStats->loadPower/Fitting::$shipStats->getPrgAmount()));
		$ping = 12*$perprg;//($perprg/2)*10;

		$startprgX = 149;
		$startprgY = 5;
		$endprgX = 0;
		$endprgY = 66;//65
		$controlprgX1 = 65;//85
		$controlprgY1 = 70;//62
		$newMoveToprgx = pow ( (1-$perprg) , 2 ) * $startprgX + 2 * (1-$perprg) * $perprg * $controlprgX1 + pow ( $perprg , 2 ) * $endprgX;
		$newMoveToprgy = pow ( (1-$perprg) , 2 ) * $startprgY + 2 * (1-$perprg) * $perprg * $controlprgY1 + pow ( $perprg , 2 ) * $endprgY;
		$perprg = $perprg/2;
		$newControlprgX = pow ( (1-$perprg) , 2 ) * $startprgX + 2 * (1-$perprg) * $perprg * $controlprgX1 + pow ( $perprg , 2 ) * $endprgX;
		$newControlprgY = pow ( (1-$perprg) , 2 ) * $startprgY + 2 * (1-$perprg) * $perprg * $controlprgY1 + pow ( $perprg , 2 ) * $endprgY;

		$smarty->assign('perprgxs', $startprgX);
		$smarty->assign('perprgys', $startprgY);
		$smarty->assign('perprgx1', $newControlprgX+$ping);
		$smarty->assign('perprgy1', $newControlprgY+$ping);
		$smarty->assign('perprgxe', $newMoveToprgx);
		$smarty->assign('perprgye', $newMoveToprgy);


		$smarty->assign('getCorpPort', Fitting::$shipStats->getCorpPort());
		$smarty->assign('getAlliPort', Fitting::$shipStats->getAlliPort());

		$smarty->assign('getEffectiveHp', number_format(Fitting::$shipStats->getEffectiveHp()));
		$smarty->assign('getPilotDam', number_format(Fitting::$shipStats->getPilotDam()));
		$smarty->assign('getPilotCos', Fitting::$shipStats->getPilotCos());

		$smarty->assign('getShieldAmount', Fitting::$shipStats->getShieldAmount());
		$smarty->assign('getArmorAmount', Fitting::$shipStats->getArmorAmount());
		$smarty->assign('getHullAmount', Fitting::$shipStats->getHullAmount());
		$smarty->assign('getEffectiveShield', Fitting::$shipStats->getEffectiveShield());
		$smarty->assign('getEffectiveArmor', Fitting::$shipStats->getEffectiveArmor());
		$smarty->assign('getEffectiveHull', Fitting::$shipStats->getEffectiveHull());
		$smarty->assign('getTankType', Fitting::$shipStats->getTankType());
		$smarty->assign('getSensorType', Fitting::$shipStats->getSensorType());
		$smarty->assign('getTankAmount', round(Fitting::$shipStats->getTankAmount()));
		$smarty->assign('getDamage', round(Fitting::$shipStats->getDamage(), 1));
		$smarty->assign('getDroneDamage', round(Fitting::$shipStats->getDroneDPS(), 1));
		$smarty->assign('getMissileDamage', round(Fitting::$shipStats->getMissileDPS(), 1));
		$smarty->assign('getTurretDamage', round(Fitting::$shipStats->getTurretDPS(), 1));
		$smarty->assign('getVolley', round(Fitting::$shipStats->getVolley(), 1));
		$smarty->assign('getSensorAmount', round(Fitting::$shipStats->getSensorAmount()));

		$smarty->assign('getTurUsed', (round(Fitting::$shipStats->getTurUsed()) > -1)?round(Fitting::$shipStats->getTurUsed()):0);
		$smarty->assign('getMisUsed', round(Fitting::$shipStats->getMisUsed()));


		$smarty->assign('getShieldEMPS', Calculations::returnPixelSize(Fitting::$shipStats->getShieldEM(), 36));
		$smarty->assign('getShieldThPS', Calculations::returnPixelSize(Fitting::$shipStats->getShieldTh(), 36));
		$smarty->assign('getShieldKiPS', Calculations::returnPixelSize(Fitting::$shipStats->getShieldKi(), 36));
		$smarty->assign('getShieldExPS', Calculations::returnPixelSize(Fitting::$shipStats->getShieldEx(), 36));
		$smarty->assign('getShieldEM', round(Fitting::$shipStats->getShieldEM()));
		$smarty->assign('getShieldTh', round(Fitting::$shipStats->getShieldTh()));
		$smarty->assign('getShieldKi', round(Fitting::$shipStats->getShieldKi()));
		$smarty->assign('getShieldEx', round(Fitting::$shipStats->getShieldEx()));
		$smarty->assign('getShieldRecharge', Calculations::toMinutesAndHours(round(Fitting::$shipStats->getShieldRecharge())));


		$smarty->assign('getArmorEMPS', Calculations::returnPixelSize(Fitting::$shipStats->getArmorEM(), 36));
		$smarty->assign('getArmorThPS', Calculations::returnPixelSize(Fitting::$shipStats->getArmorTh(), 36));
		$smarty->assign('getArmorKiPS', Calculations::returnPixelSize(Fitting::$shipStats->getArmorKi(), 36));
		$smarty->assign('getArmorExPS', Calculations::returnPixelSize(Fitting::$shipStats->getArmorEx(), 36));
		$smarty->assign('getArmorEM', round(Fitting::$shipStats->getArmorEM()));
		$smarty->assign('getArmorTh', round(Fitting::$shipStats->getArmorTh()));
		$smarty->assign('getArmorKi', round(Fitting::$shipStats->getArmorKi()));
		$smarty->assign('getArmorEx', round(Fitting::$shipStats->getArmorEx()));

		$smarty->assign('getHullEMPS', Calculations::returnPixelSize(Fitting::$shipStats->getHullEM(), 36));
		$smarty->assign('getHullThPS', Calculations::returnPixelSize(Fitting::$shipStats->getHullTh(), 36));
		$smarty->assign('getHullKiPS', Calculations::returnPixelSize(Fitting::$shipStats->getHullKi(), 36));
		$smarty->assign('getHullExPS', Calculations::returnPixelSize(Fitting::$shipStats->getHullEx(), 36));
		$smarty->assign('getHullEM', round(Fitting::$shipStats->getHullEM()));
		$smarty->assign('getHullTh', round(Fitting::$shipStats->getHullTh()));
		$smarty->assign('getHullKi', round(Fitting::$shipStats->getHullKi()));
		$smarty->assign('getHullEx', round(Fitting::$shipStats->getHullEx()));


		$smarty->assign('getShipSpeed', round(Fitting::$shipStats->getShipSpeed()));
		$smarty->assign('getMass', Fitting::$shipStats->getMass());
		$smarty->assign('getWarpSpeed', Fitting::$shipStats->getWarpSpeed());
		$smarty->assign('mwdActive', Fitting::$shipStats->mwdActive);
		$smarty->assign('mwdActiveAct', is_numeric(Fitting::$shipStats->mwdActive));
		$smarty->assign('abActive', Fitting::$shipStats->abActive);
		$smarty->assign('abActiveAct', is_numeric(Fitting::$shipStats->abActive));

		$smarty->assign('getSigRadius', round(Fitting::$shipStats->getSigRadius()));
		$smarty->assign('mwdSigature', Fitting::$shipStats->mwdSigature);
		$smarty->assign('mwdSigatureAct', is_numeric(Fitting::$shipStats->mwdSigature));
		$smarty->assign('getScan', round(Fitting::$shipStats->getScan()));
		$smarty->assign('getDistance', round(Fitting::$shipStats->getDistance()/1000, 2));
		$smarty->assign('getTarget', round(Fitting::$shipStats->getTarget()));




		$smarty->assign('back', $back);
		$smarty->assign('capSize', $capSize);
		$smarty->assign('getCapStatus', Calculations::returnPixelSize(Fitting::$shipStats->getCapStatus(), 245));
		$smarty->assign('capAmountMperc', $capAmountM.$perc);
		$smarty->assign('getCapStable', Fitting::$shipStats->getCapStable());
		$smarty->assign('getCapAmount', round(Fitting::$shipStats->getCapAmount()));
		$smarty->assign('getCapRecharge', Calculations::toMinutesAndHours(round((Fitting::$shipStats->getCapRecharge()/1000))));
		$smarty->assign('totalCapUse', round(self::totalCapUse(), 1));
		$smarty->assign('totalCapInjected', round((Fitting::$shipStats->getCapRechargeRate()+self::totalCapInjected()), 1));

		$smarty->assign('modSlotsd', Fitting::$modSlots[6]);
		$smarty->assign('modSlotsa', Fitting::$modSlots[10]);
		$smarty->assign('displayOutput', Misc::displayOutput());

		return $smarty->fetch("..".DS."..".DS."..".DS."mods".DS."ship_display_tool".DS."ship_display_tool.tpl");
	}

}
