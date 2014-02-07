<?php
$modInfo['ship_tool_kb']['name'] = "Ship Display Tool";
$modInfo['ship_tool_kb']['abstract'] = "Displays Ship stats on the kill detials page";
$modInfo['ship_tool_kb']['about'] = "by Spark's";

//require_once('common/includes/class.kill.php');
//require_once('common/includes/class.killsummarytable.php');
//require_once('common/includes/class.pilot.php');
//require_once('common/includes/class.corp.php');
//require_once('common/includes/class.alliance.php');


//$kll_id = intval($_GET['kll_id']);

$operation = true;

	include("fitting.class.php");
	include('class.shipstats.php');
	include('class.shipEffects.php');
	event::register("killDetail_assembling", "fittingTools::addFitting");
	event::register("killDetail_context_assembling", "fittingTools::RemoveContextFinalBlowTopDamage");

class fittingTools
{
		public static $shipStats;
		public static $speedV = 1;
		public static $speedB = 1;
		public static $sigRadius = 1;
		public static $shieldHpRed = 1;
		public static $structure = 1;
		public static $emArmor = 1;
		public static $thArmor = 1;
		public static $kiArmor = 1;
		public static $exArmor = 1;

		public static $emShield = 1;
		public static $thShield = 1;
		public static $kiShield = 1;
		public static $exShield = 1;

		public static $emHull = 1;
		public static $thHull = 1;
		public static $kiHull = 1;
		public static $exHull = 1;

		public static $scanStrength = 1;

		public static $missileDam = 1;
		public static $hybridDam = 1;
		public static $projectileDam = 1;
		public static $lazerDam = 1;

		public static $missileRof = 1;
		public static $hybridRof = 1;
		public static $projectileRof = 1;
		public static $lazerRof = 1;

		public static $mwdSigature = "No MWD";
		public static $mwdActive = "No MWD";
		public static $abActive = "No AB";

		public static $moduleCount = 0;
		public static $boosterPos;
		public static $gunPos;
		public static $gunPosCap;
		public static $gunDamageCounter = 0;

		public static $sensorbooster;
		public static $scan = 1;
		public static $range = 1;

		public static $warpStab = 1;
		public static $warpStabScan = 1;
		public static $interstab = 1;
		public static $sheildHPPD = 1;

		public static $srBooster = Array();

		public static $armorKiArr;
		public static $shieldAmp = 1;
		public static $armorDur = Array();
		public static $shieldDur = Array();
		public static $armorAmp = 1;
		public static $shieldAmpCap = 1;

		public static $shieldResistPos = 0;

		public static $droneDam = 1;
		public static $droneMax = 5;
		public static $droneAdd = 0;
		public static $droneArr = Array();
		public static $armorRR = 1;

		public static $capMultiEff = 1;

		public static $simpleurl;

		public static $extid;

		public static $loadCPU = 0;
		public static $loadPower = 0;

		public static $loadCPUAdd = Array();
		public static $loadPowerAdd = Array();
    function addFitting($home)
    {
			$home->delete("fitting");
			$home->delete("victim");
			$home->delete("victimShip");
			$home->addBehind("start", "fittingTools::displayFitting");
			//$home->replace("top", "fittingTools::displayFitting");
    }

	function RemoveContextFinalBlowTopDamage($home)
	{
		$home->delete("damageBox");
	}

    function displayFitting($home)
    {
    	global $smarty;
		//require_once("common/includes/class.dogma.php");
    	$kll_id = $home->kll_id;
		//echo "here";
		//echo self::$oper;



			self::$shipStats = new shipstats();


			$urlsettings = edkURI::parseURI();
			self::$simpleurl = $urlsettings[0][2];
			//echo "-> ".self::$simpleurl;


			$fitter = new fitting($kll_id);
			$theFit = $fitter->displayFitting();


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
			//echo "-->".$km->getExternalID()."<br />";

			if($km->getExternalID() != 0) {
				//echo "Yes<br />";
				self::$extid = $km->getExternalID();
			} else {
				//echo "No<br />";

				$qry = new DBQuery();
				$qry->execute("SELECT kll_external_id FROM kb3_kills WHERE kll_id = '".$home->kll_id."';");
				$row = $qry->getRow();
				if($row['kll_external_id'] != 0) {
					//echo "Yes ".$row['kll_external_id']."br />";
					self::$extid = $row['kll_external_id'];
				} else {
					//echo "No<br />";
					self::$extid = 0;
				}
			}
			fittingTools::source($kll_id,$km->getExternalID());


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




			//echo self::$extid."<br />";
			//echo "-> ".edkURI::page('pilot_detail', $km->getVictimID(), 'plt_id');


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
			//echo $corp->getPortraitURL(32);

			$alliance = new Alliance($km->getVictimAllianceID(), false);
			//echo $alliance->getPortraitURL(32);

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

			$victimShipClassName = $shipclass->getName();
			$timeStamp = $km->getTimeStamp();
			$victimShipID = edkURI::page('invtype', $ship->getExternalID(), 'id');

			if($home->page->isAdmin()) $smarty->assign('ship', $ship);



			self::$shipStats->setPilotName($km->getVictimName());
			self::$shipStats->setPilotCorp($victimCorpName);
			self::$shipStats->setPilotAlliance($victimAllianceName);
			self::$shipStats->setPilotShip($shipname);
			self::$shipStats->setPilotLoc($system);
			self::$shipStats->setPilotLocReg($region);
			self::$shipStats->setPilotLocSec($systemSecurity);
			self::$shipStats->setPilotDate($timeStamp);
			self::$shipStats->setPilotDam($victimDamageTaken);
			self::$shipStats->setPilotCos($getISKLoss);
			self::$shipStats->setPilotShipClass($victimShipClassName);

			self::$shipStats->setCorpPort($corp->getPortraitURL(32));
			self::$shipStats->setAlliPort($alliance->getPortraitURL(32));

			self::$shipStats->setPilotPort($victimPortrait);
			self::$shipStats->setPilotNameURL($victimURL);
			self::$shipStats->setPilotCorpURL($victimCorpURL);
			self::$shipStats->setPilotAllianceURL($victimAllianceURL);
			self::$shipStats->setPilotShipURL($victimShipID);
			self::$shipStats->setPilotLocURL($systemURL);


			fittingTools::getShipStats($shipname);
			fittingTools::moduleInfo($theFit);
			/*fittingTools::getModuleStats();*/

			fittingTools::returnShipSkills();
			fittingTools::shipEffects();
			fittingTools::setlevel5Skills();
			fittingTools::getExtraStats();

			fittingTools::getCPUandPowerValues();

			$html = fittingTools::displayShipStats($shipname, 100, 100);



		return $html;
    }

    public function source($p_kll_id, $p_ex_id) {
		global $smarty;

		if($p_ex_id != 0) {
			//echo "Yes<br />";
			self::$extid = $p_ex_id;
		} else {
			//echo "No<br />";

			$qry = new DBQuery();
			$qry->execute("SELECT kll_external_id FROM kb3_kills WHERE kll_id = '".$p_kll_id."';");
			$row = $qry->getRow();
			if($row['kll_external_id'] != 0) {
				//echo "Yes ".$row['kll_external_id']."br />";
				self::$extid = $row['kll_external_id'];
			} else {
				//echo "No<br />";
				self::$extid = 0;
			}
		}

		$qry = DBFactory::getDBQuery();
		$sql = "SELECT log_ip_address, log_timestamp FROM kb3_log WHERE"
				." log_kll_id = ".$p_kll_id;
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
		} else {
			$type = "unknown";
		}

		$smarty->assign("source", htmlentities($source));
		$smarty->assign("type", $type);
		$smarty->assign("postedDate", $posteddate);
		$smarty->assign('extid', self::$extid);
	}

	function getCPUandPowerValues() {

		$stack = 1;
		foreach(self::$loadPowerAdd as $value) {
			self::$shipStats->setPrgAmount(fittingTools::statOntoShip(self::$shipStats->getPrgAmount(), $value["power"], $value["type"], $value["mode"], 1));
			$stack++;
		}
		$stack = 1;
		foreach(self::$loadCPUAdd as $value) {
		//echo $value["cpu"]." here";
			/*if($value["stack"] == 1) {
				self::$shipStats->setCpuAmount(fittingTools::statOntoShip(self::$shipStats->getCpuAmount(), $value["cpu"], $value["type"], $value["mode"], $stack));
				$stack++;
			} else {*/
				self::$shipStats->setCpuAmount(fittingTools::statOntoShip(self::$shipStats->getCpuAmount(), $value["cpu"], $value["type"], $value["mode"], 1));
			//}
		}

		self::$shipStats->setCpuAmount(fittingTools::statOntoShip(self::$shipStats->getCpuAmount(), 25, "+", "%", 1));
		self::$shipStats->setPrgAmount(fittingTools::statOntoShip(self::$shipStats->getPrgAmount(), 25, "+", "%", 1));

		$arr_cpu = array_values(self::$shipStats->getCpuUsed());
		$arr_prg = array_values(self::$shipStats->getPrgUsed());

		for($i = 0; $i < count($arr_cpu); $i++) {
		//foreach($arr_cpu as $value) {
			foreach(self::$shipStats->getShipEffects() as $value2) {
				if($arr_cpu[$i]['effect'] == $value2["effect"]) {
					if($arr_cpu[$i]['effect'] == "covert_cloak") {
						$arr_cpu[$i]['cpu'] = fittingTools::statOntoShip($arr_cpu[$i]['cpu'], $value2["bonus"], $value2["type"], "%", 1);
						//self::$loadCPU += $arr_cpu[$i]['cpu'];
					} else if($arr_cpu[$i]['effect'] == "heavy_cpu") {
						$arr_cpu[$i]['cpu'] = fittingTools::statOntoShip($arr_cpu[$i]['cpu'], $value2["bonus"], $value2["type"], "%", 1);
						//self::$loadCPU += $arr_cpu[$i]['cpu'];
					} else if($arr_cpu[$i]['effect'] == "war_bonus") {
						$arr_cpu[$i]['cpu'] = fittingTools::statOntoShip($arr_cpu[$i]['cpu'], $value2["bonus"], $value2["type"], "%", 1);
						//self::$loadCPU += $arr_cpu[$i]['cpu'];
					} else if($arr_cpu[$i]['effect'] == "shield_transCPU") {
						$arr_cpu[$i]['cpu'] = fittingTools::statOntoShip($arr_cpu[$i]['cpu'], $value2["bonus"], $value2["type"], "%", 1);
						//self::$loadCPU += $arr_cpu[$i]['cpu'];
					} else if($arr_cpu[$i]['effect'] == "capital_cpu") {
						$arr_cpu[$i]['cpu'] = fittingTools::statOntoShip($arr_cpu[$i]['cpu'], $value2["bonus"], $value2["type"], "%", 1);
					}
				}
			}
		}

		for($i = 0; $i < count($arr_prg); $i++) {
		//foreach($arr_prg as $value) {
			foreach(self::$shipStats->getShipEffects() as $value2) {
				if($arr_prg[$i]['effect'] == $value2["effect"]) {
					if($arr_prg[$i]['effect'] == "seige_power") {
						$arr_prg[$i]['power'] = fittingTools::statOntoShip($arr_prg[$i]['power'], $value2["bonus"], $value2["type"], "%", 1);
						//self::$loadPower += $arr_prg[$i]['power'];
					} else if($arr_prg[$i]['effect'] == "heavy_power") {
						$arr_prg[$i]['power'] = fittingTools::statOntoShip($arr_prg[$i]['power'], $value2["bonus"], $value2["type"], "%", 1);
						//self::$loadPower += $arr_prg[$i]['power'];
					} else if($arr_prg[$i]['effect'] == "cap_transPower") {
						$arr_prg[$i]['power'] = fittingTools::statOntoShip($arr_prg[$i]['power'], $value2["bonus"], $value2["type"], "%", 1);
						//self::$loadPower += $arr_prg[$i]['power'];
					}
				}
			}
		}




		for($i = 0; $i < count($arr_cpu); $i++) {
		//foreach($arr_cpu as $value) {
			if($arr_cpu[$i]['effect'] == "weapon") {
				$arr_cpu[$i]['cpu'] = fittingTools::statOntoShip($arr_cpu[$i]['cpu'], 25, "-", "%", 1);
				self::$loadCPU += $arr_cpu[$i]['cpu'];
			} else if($arr_cpu[$i]['effect'] == "covert_cloak") {
				self::$loadCPU += $arr_cpu[$i]['cpu'];
			} else if($arr_cpu[$i]['effect'] == "heavy_cpu") {
				$arr_cpu[$i]['cpu'] = fittingTools::statOntoShip($arr_cpu[$i]['cpu'], 25, "-", "%", 1);
				self::$loadCPU += $arr_cpu[$i]['cpu'];
			} else if($arr_cpu[$i]['effect'] == "cpu_use") {
				$arr_cpu[$i]['cpu'] = fittingTools::statOntoShip($arr_cpu[$i]['cpu'], 25, "-", "%", 1);
				self::$loadCPU += $arr_cpu[$i]['cpu'];
			} else if($arr_cpu[$i]['effect'] == "war_bonus") {
				self::$loadCPU += $arr_cpu[$i]['cpu'];
			} else {
				self::$loadCPU += $arr_cpu[$i]['cpu'];
			}
		}
		for($i = 0; $i < count($arr_prg); $i++) {
		//foreach($arr_prg as $value) {
			if($arr_prg[$i]['effect'] == "weapon") {
				$arr_prg[$i]['power'] = fittingTools::statOntoShip($arr_prg[$i]['power'], 10, "-", "%", 1);
				self::$loadPower += $arr_prg[$i]['power'];
			} else if($arr_prg[$i]['effect'] == "shield") {
				$arr_prg[$i]['power'] = fittingTools::statOntoShip($arr_prg[$i]['power'], 25, "-", "%", 1);
				self::$loadPower += $arr_prg[$i]['power'];
			} else if($arr_prg[$i]['effect'] == "seige_power") {
				$arr_prg[$i]['power'] = fittingTools::statOntoShip($arr_prg[$i]['power'], 10, "-", "%", 1);
				self::$loadPower += $arr_prg[$i]['power'];
			} else if($arr_prg[$i]['effect'] == "heavy_power") {
				$arr_prg[$i]['power'] = fittingTools::statOntoShip($arr_prg[$i]['power'], 10, "-", "%", 1);
				self::$loadPower += $arr_prg[$i]['power'];
			} else if($arr_prg[$i]['effect'] == "power_use") {
				$arr_prg[$i]['power'] = fittingTools::statOntoShip($arr_prg[$i]['power'], 25, "-", "%", 1);
				self::$loadPower += $arr_prg[$i]['power'];
			} else {
				self::$loadPower += $arr_prg[$i]['power'];
			}
		}




		/*echo "<pre>";
		print_r($arr_prg);
		echo "</pre>";*/

		/*echo "<pre>";
		print_r($arr_cpu);
		echo "</pre>";*/

		/*echo "<pre>";
		print_r(self::$shipStats->getCpuUsed());
		echo "</pre>";*/

		/*echo "<pre>";
		print_r(self::$shipStats->getPrgUsed());
		echo "</pre>";*/

		/*echo "<pre>";
		print_r(self::$shipStats->getShipEffects());
		echo "</pre>";*/

		/*echo "<pre>";
		print_r(self::$loadPowerAdd);
		echo "</pre>";*/

		/*echo "<pre>";
		print_r(self::$loadCPUAdd);
		echo "</pre>";*/

	}


function getShipStats($param_ship) {
	//global $shipStats;

	$qry = new DBQuery();
	$qry->execute("select kb3_invtypes.typeID, kb3_invtypes.description from kb3_invtypes WHERE kb3_invtypes.typeName = '".$qry->escape($param_ship)."'");
	$typeID = $qry->getRow();


	self::$shipStats->setShipIcon($typeID['typeID']);
	self::$shipStats->setShipDesc($typeID['description']);

	//$itemquery = mysql_query("select kb3_invtypes.typeID from kb3_invtypes WHERE kb3_invtypes.typeName = '".$param_ship."'") or die(mysql_error());
	//$typeID = mysql_fetch_array($itemquery);


	$qry2 = new DBQuery();
	$qry2->execute("select kb3_dgmtypeattributes.value, kb3_dgmattributetypes.attributeName, kb3_dgmattributetypes.displayName, kb3_dgmattributetypes.stackable, kb3_eveunits.displayName as unit
from kb3_dgmtypeattributes
inner join kb3_dgmattributetypes on kb3_dgmtypeattributes.attributeID = kb3_dgmattributetypes.attributeID
left join kb3_eveunits on kb3_dgmattributetypes.unitID = kb3_eveunits.unitID
where typeID = ".$typeID['typeID']);
	//$row = $qry2->getRow();


	while($row = $qry2->getRow()) {

		if(strtolower($row['attributeName']) == "shieldcapacity") {
			self::$shipStats->setShieldAmount($row['value']);

		}
		if(strtolower($row['attributeName']) == "armorhp") {
			self::$shipStats->setArmorAmount($row['value']);
		}
		if(strtolower($row['attributeName']) == "hp") {
			self::$shipStats->setHullAmount($row['value']);
		}

		if(strtolower($row['attributeName']) == "scanradarstrength" && $row['value'] > 0) {
			self::$shipStats->setSensorType(fittingTools::getSensorTypeImg('radar'));
			self::$shipStats->setSensorAmount($row['value']);
		}
		if(strtolower($row['attributeName']) == "scanladarstrength" && $row['value'] > 0) {
			self::$shipStats->setSensorType(fittingTools::getSensorTypeImg('ladar'));
			self::$shipStats->setSensorAmount($row['value']);
		}
		if(strtolower($row['attributeName']) == "scanmagnetometricstrength" && $row['value'] > 0) {
			self::$shipStats->setSensorType(fittingTools::getSensorTypeImg('magnetometric'));
			self::$shipStats->setSensorAmount($row['value']);
		}
		if(strtolower($row['attributeName']) == "scangravimetricstrength" && $row['value'] > 0) {
			self::$shipStats->setSensorType(fittingTools::getSensorTypeImg('gravimetric'));
			self::$shipStats->setSensorAmount($row['value']);
		}

		if(strtolower($row['attributeName']) == "shieldemdamageresonance") {
			self::$shipStats->setShieldEM((1-$row['value'])*100);
		}
		if(strtolower($row['attributeName']) == "shieldthermaldamageresonance") {
			self::$shipStats->setShieldTh((1-$row['value'])*100);
		}
		if(strtolower($row['attributeName']) == "shieldkineticdamageresonance") {
			self::$shipStats->setShieldKi((1-$row['value'])*100);
		}
		if(strtolower($row['attributeName']) == "shieldexplosivedamageresonance") {
			self::$shipStats->setShieldEx((1-$row['value'])*100);
		}
		if(strtolower($row['attributeName']) == "shieldrechargerate") {
			self::$shipStats->setShieldRecharge($row['value']/1000);
		}

		if(strtolower($row['attributeName']) == "armoremdamageresonance") {
			self::$shipStats->setArmorEM((1-$row['value'])*100);
		}
		if(strtolower($row['attributeName']) == "armorthermaldamageresonance") {
			self::$shipStats->setArmorTh((1-$row['value'])*100);
		}
		if(strtolower($row['attributeName']) == "armorkineticdamageresonance") {
			self::$shipStats->setArmorKi((1-$row['value'])*100);
		}
		if(strtolower($row['attributeName']) == "armorexplosivedamageresonance") {
			self::$shipStats->setArmorEx((1-$row['value'])*100);
		}

		if(strtolower($row['attributeName']) == "emdamageresonance") {
			self::$shipStats->setHullEM((1-$row['value'])*100);
		}
		if(strtolower($row['attributeName']) == "thermaldamageresonance") {
			self::$shipStats->setHullTh((1-$row['value'])*100);
		}
		if(strtolower($row['attributeName']) == "kineticdamageresonance") {
			self::$shipStats->setHullKi((1-$row['value'])*100);
		}
		if(strtolower($row['attributeName']) == "explosivedamageresonance") {
			self::$shipStats->setHullEx((1-$row['value'])*100);
		}


		if(strtolower($row['attributeName']) == "maxvelocity") {
			self::$shipStats->setShipSpeed($row['value']);
		}

		if(strtolower($row['attributeName']) == "signatureradius") {
			self::$shipStats->setSigRadius($row['value']);
		}

		if(strtolower($row['attributeName']) == "scanresolution") {
			self::$shipStats->setScan($row['value']);
		}


		if(strtolower($row['attributeName']) == "maxtargetrange") {
			self::$shipStats->setDistance($row['value']);
		}

		if(strtolower($row['attributeName']) == "maxlockedtargets") {
			self::$shipStats->setTarget($row['value']);
		}


		if(strtolower($row['attributeName']) == "capacitorcapacity") {
			self::$shipStats->setCapAmount($row['value']);
		}

		if(strtolower($row['attributeName']) == "rechargerate") {
			self::$shipStats->setCapRecharge($row['value']);
		}

		if(strtolower($row['attributeName']) == "rigsize") {
			self::$shipStats->setRSize(fittingTools::returnShipSize($row['value']));
		}



		if(strtolower($row['attributeName']) == "lowslots") {
			$arr = self::$shipStats->getShipSlots();
			$arr['lowslots'] = $row['value'];
			self::$shipStats->setShipSlots($arr);
		}
		if(strtolower($row['attributeName']) == "medslots") {
			$arr = self::$shipStats->getShipSlots();
			$arr['medslots'] = $row['value'];
			self::$shipStats->setShipSlots($arr);
		}
		if(strtolower($row['attributeName']) == "hislots") {
			$arr = self::$shipStats->getShipSlots();
			$arr['hislots'] = $row['value'];
			self::$shipStats->setShipSlots($arr);
		}
		if(strtolower($row['attributeName']) == "rigslots") {
			$arr = self::$shipStats->getShipSlots();
			$arr['rigslots'] = $row['value'];
			self::$shipStats->setShipSlots($arr);
		}

		if(strtolower($row['attributeName']) == "upgradecapacity") {
			self::$shipStats->setCalAmount($row['value']);
		}

		if(strtolower($row['attributeName']) == "cpuoutput") {
			self::$shipStats->setCpuAmount($row['value']);
		}

		if(strtolower($row['attributeName']) == "poweroutput") {
			self::$shipStats->setPrgAmount($row['value']);
		}

		if(strtolower($row['attributeName']) == "turretslotsleft") {
			self::$shipStats->setTurAmount($row['value']);
			self::$shipStats->setTurUsed($row['value']);
		}

		if(strtolower($row['attributeName']) == "launcherslotsleft") {
			self::$shipStats->setMisAmount($row['value']);
			self::$shipStats->setMisUsed($row['value']);
		}
	}


	$qry3 = new DBQuery();
	$qry3->execute("select mass from kb3_invtypes left join kb3_item_types on itt_id = groupID where typeID = ".$typeID['typeID']);
	$row3 = $qry3->getRow();

	self::$shipStats->setMass(fittingTools::calculateMass($row3['mass']));

	//echo self::$shipStats->getMass();
}

function returnShipSize($input) {
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

function calculateMass($param_mass) {
	//1.482e+07
	$break = explode("e+",$param_mass);
	$exp = 1;
	for($e=0; $e < $break[1]; $e++) {
		$exp = $exp*10;
	}

	//echo $break[0]." ".($break[1]*10)." ".$exp;
	return ($break[0]*$exp);
}

function setlevel5Skills() {
	//global $shipStats;

	//shield amount
	self::$shipStats->setShieldAmount(fittingTools::statOntoShip(self::$shipStats->getShieldAmount(), 25, "+", "%", 1));

	//shield recharge
	self::$shipStats->setShieldRecharge(fittingTools::statOntoShip(self::$shipStats->getShieldRecharge(), 25, "-", "%", 1));

	//armour amount
	self::$shipStats->setArmorAmount(fittingTools::statOntoShip(self::$shipStats->getArmorAmount(), 25, "+", "%", 1));

	//hull amount
	self::$shipStats->setHullAmount(fittingTools::statOntoShip(self::$shipStats->getHullAmount(), 25, "+", "%", 1));

	//cap amount

	self::$shipStats->setCapAmount(fittingTools::statOntoShip(self::$shipStats->getCapAmount(), 25, "+", "%", 1));

	//cap recharge
	self::$shipStats->setCapRecharge(fittingTools::statOntoShip(self::$shipStats->getCapRecharge(), 25, "-", "%", 1));
	//speed
	self::$shipStats->setShipSpeed(fittingTools::statOntoShip(self::$shipStats->getShipSpeed(), 25, "+", "%", 1));

	//target range
	self::$shipStats->setDistance(fittingTools::statOntoShip(self::$shipStats->getDistance(), 25, "+", "%", 1));

	//scan resolution
	self::$shipStats->setScan(fittingTools::statOntoShip(self::$shipStats->getScan(), 25, "+", "%", 1));

	//signature
	//shipInfo.setOSig(statOntoShip(Number(shipInfo.getOSig()), 25, "+", "%", 1));

	//ECCM
	self::$shipStats->setSensorAmount(fittingTools::statOntoShip(self::$shipStats->getSensorAmount($row['value']), 20, "+", "%", 1));

}


function getExtraStats() {
	//global $shipStats;

	//sort resists
	fittingTools::setAndOrderShipResists();


	//passive shield tank
	if(self::$shipStats->getTankType() == "shield") {
		$boost = fittingTools::returnTankResults("shield");
		self::$shipStats->setTankAmount(fittingTools::tankAbleDPS(fittingTools::peakShieldRecharge(self::$shipStats->getShieldAmount(),self::$shipStats->getShieldRecharge())+$boost, self::$shipStats->getShieldEM(),self::$shipStats->getShieldTh(),self::$shipStats->getShieldKi(),self::$shipStats->getShieldEx()));
		self::$shipStats->setTankType("act");
	} else if(self::$shipStats->getTankType() == "armor") {
		$boost = fittingTools::returnTankResults("armor");
		self::$shipStats->setTankAmount(fittingTools::tankAbleDPS($boost, self::$shipStats->getArmorEM(),self::$shipStats->getArmorTh(),self::$shipStats->getArmorKi(),self::$shipStats->getArmorEx()));
		self::$shipStats->setTankType("arm");
	} else if(self::$shipStats->getTankType() == "hull") {
		$boost = fittingTools::returnTankResults("hull");
		self::$shipStats->setTankAmount(fittingTools::tankAbleDPS($boost, self::$shipStats->getHullEM(),self::$shipStats->getHullTh(),self::$shipStats->getHullKi(),self::$shipStats->getHullEx()));
		self::$shipStats->setTankType("arm");
	} else {
		self::$shipStats->setTankAmount(fittingTools::tankAbleDPS(fittingTools::peakShieldRecharge(self::$shipStats->getShieldAmount(),self::$shipStats->getShieldRecharge()), self::$shipStats->getShieldEM(),self::$shipStats->getShieldTh(),self::$shipStats->getShieldKi(),self::$shipStats->getShieldEx()));
		self::$shipStats->setTankType("pass");
	}


	self::$shipStats->setEffectiveShield(fittingTools::effectHP(self::$shipStats->getShieldAmount(),self::$shipStats->getShieldEM(),self::$shipStats->getShieldTh(),self::$shipStats->getShieldKi(),self::$shipStats->getShieldEx()));
	self::$shipStats->setEffectiveArmor(fittingTools::effectHP(self::$shipStats->getArmorAmount(),self::$shipStats->getArmorEM(),self::$shipStats->getArmorTh(),self::$shipStats->getArmorKi(),self::$shipStats->getArmorEx()));
	self::$shipStats->setEffectiveHull(fittingTools::effectHP(self::$shipStats->getHullAmount(),self::$shipStats->getHullEM(),self::$shipStats->getHullTh(),self::$shipStats->getHullKi(),self::$shipStats->getHullEx()));



	self::$shipStats->setCapRechargeRate(fittingTools::peakShieldRecharge(self::$shipStats->getCapAmount(), self::$shipStats->getCapRecharge()/1000));
	//echo fittingTools::peakShieldRecharge(self::$shipStats->getCapAmount(), self::$shipStats->getCapRecharge()/1000);
	if(self::$shipStats->getIsMWD()) {
		self::$mwdActive = round(fittingTools::getShipSpeed(self::$shipStats->getShipSpeed(), self::$shipStats->getMwdBoost(), self::$shipStats->getMwdThrust(), self::$shipStats->getMass()+self::$shipStats->getMwdMass()));

		if(self::$shipStats->getMwdSigRed()) {
			self::$shipStats->setMwdSig((self::$shipStats->getMwdSig()-(self::$shipStats->getMwdSig()/100)*(self::$shipStats->getMwdSigRed()*5)));
		}

		self::$mwdSigature = round(((self::$shipStats->getMwdSig()/100)*self::$shipStats->getSigRadius())+self::$shipStats->getSigRadius());
	}
	if(self::$shipStats->getIsAB()) {
		self::$abActive = round(fittingTools::getShipSpeed(self::$shipStats->getShipSpeed(), self::$shipStats->getABBoost(), self::$shipStats->getABThrust(), self::$shipStats->getMass()+self::$shipStats->getABMass()));
	}







	//set the skill set

	// get drone count


	self::$droneMax = fittingTools::getShipDrone(self::$shipStats->getPilotShip())+self::$droneAdd;
	self::$shipStats->setDamageGun(fittingTools::turretMods());
	self::$shipStats->setDroneDamage(fittingTools::getDroneSkillDamage());
	fittingTools::getDPSAndVolley();
	self::$shipStats->setDamage(fittingTools::getDPS());
	self::$shipStats->setVolley(fittingTools::getVolley());

	/*echo "<pre>";
	print_r(self::$shipStats->getDamageGun());
	print_r(self::$shipStats->getDamageModules());
	//print_r(self::$shipStats->getDroneDamage());
	//print_r(self::$shipStats->getDroneDamageMod());
	echo "</pre>";*/









	//print_r(self::$shipStats->getCapGJ());

	self::$shipStats->setCapGJ(fittingTools::capacitorUsage());
	self::$shipStats->setCapInj(fittingTools::capacitorInjector());
	self::$shipStats->setTransCap(fittingTools::remoteRepStats());

	/*echo "<pre>";
	print_r(self::$shipStats->getCapGJ());
	print_r(self::$shipStats->getTransCap());
	print_r(self::$shipStats->getCapInj());
	echo "</pre>";*/





	$capUse = fittingTools::totalCapUse();
	$capPlus = fittingTools::totalCapInjected();
	if(fittingTools::isCapStable((self::$shipStats->getCapRechargeRate()+$capPlus), $capUse)) {
		//$shipStats->setCapStatus(round(capUsage($shipStats->getCapAmount(), 0, $shipStats->getCapRechargeRate())));

		$cap = self::$shipStats->getCapAmount();
		$recharge = (self::$shipStats->getCapRecharge()/1000);
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
				//echo $i." --- ".$regen." ------ ".$capUse." -- ".$cappersecondadd." -- ".$capThatCycle." -- ".$capPerSecond." -- ".$percentage."<br />";

				/*echo $cappersecondadd." --- ";
				echo $cap."<br />";*/


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

		self::$shipStats->setCapStatus($percentage);

		self::$shipStats->setCapStable(1);
	} else {

		$seconds = round(fittingTools::capUsage(self::$shipStats->getCapAmount(), $capUse, (self::$shipStats->getCapRechargeRate()+$capPlus), self::$shipStats->getCapRecharge()));
		self::$shipStats->setCapStatus(fittingTools::toMinutesAndHours($seconds));
		self::$shipStats->setCapStable(0);
	}










	fittingTools::sensorBoosterAdd();
	/*echo "<pre>";
	print_r(self::$shipStats->getSensorBooster());
	echo "</pre>";*/




	self::$shipStats->setEffectiveHp(self::$shipStats->getEffectiveShield()+self::$shipStats->getEffectiveArmor()+self::$shipStats->getEffectiveHull());
	if(!self::$shipStats->getSensorType()) {
		self::$shipStats->setSensorType("icon04_12");
	}



	/*echo "<pre>";
	print_r(self::$shipStats->getSigRadiusBoost());
	echo "</pre>";*/

	fittingTools::setSigBoostforWarpDis();



	/*echo "<pre>";
	print_r(self::$shipStats->getShipSlots());
	echo "</pre>";*/

}

function getShipDrone($shipname) {

	if(strstr(strtolower($shipname), "nyx")
	|| strstr(strtolower($shipname), "aeon")
	|| strstr(strtolower($shipname), "wyvern")
	|| strstr(strtolower($shipname), "hel")) {
		return 20;
	} else if(strstr(strtolower($shipname), "archon")
	|| strstr(strtolower($shipname), "chimera")
	|| strstr(strtolower($shipname), "thanatos")
	|| strstr(strtolower($shipname), "nidgoggur")
	|| strstr(strtolower($shipname), "revelation")
	|| strstr(strtolower($shipname), "phoenix")
	|| strstr(strtolower($shipname), "moros")
	|| strstr(strtolower($shipname), "naglfar")) {
		return 10;
	} else {
		return 5;
	}

}

function setAndOrderShipResists() {

	$orderSystem = Array(
	4 => "subsystem",
	1 => "Active",
	2 => "Passive",
	5 => "StrongPassive",
	3 => "DamCom"
	);

	/*echo "<pre>";
	print_r(self::$shipStats->getShipResists());
	echo "</pre>";*/


	if(self::$shipStats->getShipResists()) {
		foreach($orderSystem as $i => $order) {
			foreach(self::$shipStats->getShipResists() as $j => $value) {

				if($i == $value['order']) {
					if($value['section'] == "armor") {
						if($value['order'] == 3) {
							if($value['resist'] == "em") {
								self::$shipStats->setArmorEm(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorEm(), $value['amount'], $value['type'], 1));
								//self::$emArmor++;
							} else if($value['resist'] == "ex") {
								self::$shipStats->setArmorEx(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorEx(), $value['amount'], $value['type'], 1));
								//self::$exArmor++;
							} else if($value['resist'] == "ki") {
								self::$shipStats->setArmorKi(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorKi(), $value['amount'], $value['type'], 1));
								//self::$kiArmor++;
							} else if($value['resist'] == "th") {
								self::$shipStats->setArmorTh(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorTh(), $value['amount'], $value['type'], 1));
								//self::$thArmor++;
							}
						} else if($value['order'] == 4) {
							if($value['resist'] == "em") {
								self::$shipStats->setArmorEm(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorEm(), $value['amount'], $value['type'], 1));
							} else if($value['resist'] == "ex") {
								self::$shipStats->setArmorEx(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorEx(), $value['amount'], $value['type'], 1));
							} else if($value['resist'] == "ki") {
								self::$shipStats->setArmorKi(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorKi(), $value['amount'], $value['type'], 1));
							} else if($value['resist'] == "th") {
								self::$shipStats->setArmorTh(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorTh(), $value['amount'], $value['type'], 1));
							}
						} else {
							if($value['resist'] == "em") {
								self::$shipStats->setArmorEm(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorEm(), $value['amount'], $value['type'], self::$emArmor));
								self::$emArmor++;
							} else if($value['resist'] == "ex") {
								self::$shipStats->setArmorEx(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorEx(), $value['amount'], $value['type'], self::$exArmor));
								self::$exArmor++;
							} else if($value['resist'] == "ki") {
								self::$shipStats->setArmorKi(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorKi(), $value['amount'], $value['type'], self::$kiArmor));
								self::$kiArmor++;
							} else if($value['resist'] == "th") {
								self::$shipStats->setArmorTh(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorTh(), $value['amount'], $value['type'], self::$thArmor));
								self::$thArmor++;
							}
						}

					} else if($value['section'] == "shield") {
						if($value['order'] == 3 || $value['order'] == 4) {
							if($value['resist'] == "em") {
								self::$shipStats->setShieldEm(fittingTools::getLevel5SkillsPlus(self::$shipStats->getShieldEm(), $value['amount'], $value['type'], 1));
							} else if($value['resist'] == "ex") {
								self::$shipStats->setShieldEx(fittingTools::getLevel5SkillsPlus(self::$shipStats->getShieldEx(), $value['amount'], $value['type'], 1));
							} else if($value['resist'] == "ki") {
								self::$shipStats->setShieldKi(fittingTools::getLevel5SkillsPlus(self::$shipStats->getShieldKi(), $value['amount'], $value['type'], 1));
							} else if($value['resist'] == "th") {
								self::$shipStats->setShieldTh(fittingTools::getLevel5SkillsPlus(self::$shipStats->getShieldTh(), $value['amount'], $value['type'], 1));
							}
						} else {
							if($value['resist'] == "em") {
								self::$shipStats->setShieldEm(fittingTools::getLevel5SkillsPlus(self::$shipStats->getShieldEm(), $value['amount'], $value['type'], self::$emShield));
								self::$emShield++;
							} else if($value['resist'] == "ex") {
								self::$shipStats->setShieldEx(fittingTools::getLevel5SkillsPlus(self::$shipStats->getShieldEx(), $value['amount'], $value['type'], self::$exShield));
								self::$exShield++;
							} else if($value['resist'] == "ki") {
								self::$shipStats->setShieldKi(fittingTools::getLevel5SkillsPlus(self::$shipStats->getShieldKi(), $value['amount'], $value['type'], self::$kiShield));
								self::$kiShield++;
							} else if($value['resist'] == "th") {
								self::$shipStats->setShieldTh(fittingTools::getLevel5SkillsPlus(self::$shipStats->getShieldTh(), $value['amount'], $value['type'], self::$thShield));
								self::$thShield++;
							}
						}
					} else if($value['section'] == "hull") {
						if($value['order'] == 3) {
							if($value['resist'] == "em") {
								self::$shipStats->setHullEm(fittingTools::getLevel5SkillsPlus(self::$shipStats->getHullEm(), $value['amount'], $value['type'], 1));
								self::$emHull++;
							} else if($value['resist'] == "ex") {
								self::$shipStats->setHullEx(fittingTools::getLevel5SkillsPlus(self::$shipStats->getHullEx(), $value['amount'], $value['type'], 1));
								self::$exHull++;
							} else if($value['resist'] == "ki") {
								self::$shipStats->setHullKi(fittingTools::getLevel5SkillsPlus(self::$shipStats->getHullKi(), $value['amount'], $value['type'], 1));
								self::$kiHull++;
							} else if($value['resist'] == "th") {
								self::$shipStats->setHullTh(fittingTools::getLevel5SkillsPlus(self::$shipStats->getHullTh(), $value['amount'], $value['type'], 1));
								self::$thHull++;
							}
						} else {
							if($value['resist'] == "em") {
								self::$shipStats->setHullEm(fittingTools::getLevel5SkillsPlus(self::$shipStats->getHullEm(), $value['amount'], $value['type'], self::$emHull));
								self::$emHull++;
							} else if($value['resist'] == "ex") {
								self::$shipStats->setHullEx(fittingTools::getLevel5SkillsPlus(self::$shipStats->getHullEx(), $value['amount'], $value['type'], self::$exHull));
								self::$exHull++;
							} else if($value['resist'] == "ki") {
								self::$shipStats->setHullKi(fittingTools::getLevel5SkillsPlus(self::$shipStats->getHullKi(), $value['amount'], $value['type'], self::$kiHull));
								self::$kiHull++;
							} else if($value['resist'] == "th") {
								self::$shipStats->setHullTh(fittingTools::getLevel5SkillsPlus(self::$shipStats->getHullTh(), $value['amount'], $value['type'], self::$thHull));
								self::$thHull++;
							}
						}
					}

				}

			}
		}
	}

}


function returnTankResults($tankType) {

	/*echo "<pre>";
	print_r(self::$shipStats->getTankBoost());
	echo "</pre>";*/

	$total = 0;
	$dur = 1;
	$boost = 0;
	if(self::$shipStats->getTankBoost()) {
		foreach(self::$shipStats->getTankBoost() as $i => $value) {
			if($value['type'] == $tankType) {
				$boost = $value['boost'];
				if($value['type'] == "shield") {
					$boost = fittingTools::shieldAmpBooster($boost,$value["icon"]);
					$dur = fittingTools::boostDuration($tankType);
				} else if($value['type'] == "armor") {
					$boost = fittingTools::armorAmpBooster($boost);
					$dur = fittingTools::boostDuration($tankType);
				} else if($value['type'] == "hull") {
					$dur = fittingTools::boostDuration($tankType);
				}
				$total += $boost;
			}
		}
	}
	//echo $total." ".$dur."<br />";
	return $total/$dur;
}

function boostDuration($type) {
	$j = 0;
	if(self::$shipStats->getTankBoost()) {
		foreach(self::$shipStats->getTankBoost() as $i => $value) {
			if($value['type'] == $type) {
				$dur = $value['dur'];
				if($type == "armor") {
					$dur = fittingTools::getSkillset("armor repair", "duration", $dur);
					$dur = fittingTools::armorAmpDur($dur);
					$total += $dur;
					$j++;
				} else if($type == "shield") {
					$dur = fittingTools::shieldAmpDur($dur);
					$dur = fittingTools::getSkillset("shield booster", "duration", $dur);

					if($value['icon'] == "105_4") {
						$total += 60/($dur+$value['amount']);
					} else {
						$total += $dur;
					}
					$j++;
				} else if($type == "hull") {
					$dur = fittingTools::getSkillset("armor repair", "duration", $dur);
					$total += $dur;
					$j++;
				}

			}

		}
	}
	return ($total/$j);
}

function armorAmpDur($dur) {

	$total = $dur;

	/*echo "<pre>";
	print_r(self::$armorDur);
	echo "</pre>";*/

	if(self::$armorDur) {
		foreach(self::$armorDur as $i => $value) {

			$total = fittingTools::statOntoShip($total, $value['dur'], $value['type'], "%", $value['neg']);

		}
	}
	return $total;
}

function shieldAmpDur($dur) {

	$total = $dur;

	/*echo "<pre>";
	print_r(self::$shieldDur);
	echo "</pre>";*/

	if(self::$shieldDur) {
		foreach(self::$shieldDur as $i => $value) {

			$total = fittingTools::statOntoShip($total, $value['dur'], $value['type'], "%", $value['neg']);

		}
	}
	return $total;
}

function shieldAmpBooster($boostAmount,$icon) {
	$total = $boostAmount;

	if($icon == "105_4") {
		/*echo "<pre>";
		print_r(self::$shipStats->getTankBoost());
		echo "</pre>";*/
		$total = fittingTools::statOntoShip($total, 25, "+", "%", 1);
	}

	if(self::$shipStats->getTankAmpShield()) {
		foreach(self::$shipStats->getTankAmpShield() as $i => $value) {

			$total = fittingTools::statOntoShip($total, $value['boost'], $value['type'], "%", $value['neg']);

		}
	}

	/*echo "<pre>";
	print_r(self::$shipStats->getShipEffects());
	echo "</pre>";*/

	if(self::$shipStats->getShipEffects()) {
		foreach(self::$shipStats->getShipEffects() as $j => $effect) {
			if($effect['effect'] == "shieldBoost") {
				$total = fittingTools::statOntoShip($total, (5*$effect['bonus']),$effect['type'],"%", 1);
			}
		}
	}

	return $total;
}

function armorAmpBooster($boostAmount) {

	$total = $boostAmount;

	/*echo "<pre>";
	print_r(self::$shipStats->getTankAmpArmor());
	echo "</pre>";*/
	if(self::$shipStats->getTankAmpArmor()) {
		foreach(self::$shipStats->getTankAmpArmor() as $i => $value) {
			$total = fittingTools::statOntoShip($total, $value['boost'], $value['type'], "%", $value['neg']);
		}
	}

	/*echo "<pre>";
	print_r(self::$shipStats->getShipEffects());
	echo "</pre>";*/

	if(self::$shipStats->getShipEffects()) {
		foreach(self::$shipStats->getShipEffects() as $j => $effect) {
			if($effect['effect'] == "armorBoost") {
				$total = fittingTools::statOntoShip($total, (5*$effect['bonus']),$effect['type'],"%", 1);
			}
		}
	}

	return $total;
}

function setSigBoostforWarpDis() {

	if(self::$shipStats->getSigRadiusBoost()) {
		foreach(self::$shipStats->getSigRadiusBoost() as $i => $value) {
			if($value != 0) {
				self::$shipStats->setSigRadius(fittingTools::statOntoShip(self::$shipStats->getSigRadius(), $value['sigAdd'], "+", "%", 0));
			}
		}
	}


}


function sensorBoosterAdd() {
	self::$scan = 0;
	self::$range = 0;
	$arrRange = Array();
	$arrScan = Array();

	$modSlotOrderRange = Array(
	7 => "Sub",
	3 => "Low",
	4 => "Mid",
	2 => "Rig",
	1 => "high"
	);
	$k = 0;
	if(self::$shipStats->getSensorBooster()) {
		foreach($modSlotOrderRange as $i => $order) {
			foreach(self::$shipStats->getSensorBooster() as $j => $value) {
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

			//$arr[self::$moduleCount]['order'] = $slotOrder;

			if($value['range'] != 0) {
				$arr[$i]['range'] = $value['range'];
				$arr[$i]['type'] = $value['type'];
				self::$range++;
				$arr[$i]['negra'] = self::$range;
			}

			/*if($value['scan'] != 0) {
				$arr[$i]['scan'] = $value['scan'];
				self::$scan++;
				$arr[$i]['negsc'] = self::$scan;
			}*/


		}
	}
	//self::$shipStats->setSensorBooster($arr);
	$arrRange = $arr;




	$modSlotOrderScan = Array(
	7 => "Sub",
	4 => "Mid",
	3 => "Low",

	1 => "high",
	2 => "Rig"
	);
	$k = 0;
	if(self::$shipStats->getSensorBooster()) {
		foreach($modSlotOrderScan as $i => $order) {
			foreach(self::$shipStats->getSensorBooster() as $j => $value) {

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
				self::$scan++;
				$arrS[$i]['negsc'] = self::$scan;
			}


		}
	}
	//self::$shipStats->setSensorBooster($arr);
	$arrScan = $arrS;













	/*echo "<pre>";
	//print_r($arrRange);
	print_r($arrScan);
	echo "</pre>";*/


	if($arrRange) {
		foreach($arrRange as $i => $value) {
			if($value['range']) {
				//echo self::$shipStats->getDistance()." - ";
				self::$shipStats->setDistance(fittingTools::statOntoShip(self::$shipStats->getDistance(), $value['range'], $value['type'], "%", $value['negra']));
				//echo self::$shipStats->getDistance()."<br />";
			}
		}
	}

	if($arrScan) {
		foreach($arrScan as $i => $value) {
			if($value['scan']) {
				//echo self::$shipStats->getScan()." - ".$value['scan']." - ".$value['type']." - ".$value['negsc']." - ";
				self::$shipStats->setScan(fittingTools::statOntoShip(self::$shipStats->getScan(), $value['scan'], $value['type'], "%", $value['negsc']));
				//echo self::$shipStats->getScan()."<br />";
			}
		}
	}
}





function getDroneSkillDamage() {
	$k = 0;
	if(self::$shipStats->getDroneDamage()) {
		foreach(self::$shipStats->getDroneDamage() as $i => $value) {

			if(strstr($value['name'], "Bouncer")
			|| strstr($value['name'], "Curator")
			|| strstr($value['name'], "Garde")
			|| strstr($value['name'], "Warden")) {
				$tech = 3;
			} else {
				$tech = $value['techlevel'];
			}

			$damMod = fittingTools::setDamageModSkills("damageDr", $value['damageDr'], $tech);
			if(self::$shipStats->getShipEffects()) {
				foreach(self::$shipStats->getShipEffects() as $j => $effect) {
					if($effect['effect'] == "damagedr") {
						$damMod = fittingTools::statOntoShip($damMod, (5*$effect['bonus']),$effect['type'],"%", 1);
					}
				}
			}

			if(self::$shipStats->getDroneDamageMod()) {
				foreach(self::$shipStats->getDroneDamageMod() as $k => $damAdd) {
					if($damAdd['damagedr']) {
						if($damAdd['type'] == $tech || $damAdd['type'] == 0) {
							$damMod = fittingTools::statOntoShip($damMod, $damAdd['damagedr'],"+","%", $damAdd['neg']);
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
			$arr[$i]['volley'] = $total;
			if($total == 0 || $rof == 0) {
				$arr[$i]['dps'] = 0;
			} else {
				$arr[$i]['dps'] = $total/$rof;
			}

			foreach(self::$droneArr as $k => $dronecount) {
				if($dronecount['name'] == $value['name']) {
					$arr[$i]['count'] = $dronecount['count'];
					break;
				}
			}




			$k++;
		}
	}


	return $arr;
}


function remoteRepStats() {

	foreach(self::$shipStats->getTransCap() as $i => $value) {
		$cap = $value['capNeeded'];

		if(self::$shipStats->getTransCapEff()) {
			foreach(self::$shipStats->getTransCapEff() as $k => $capne) {
				if($value['type'] == $capne['type']) {
					$cap = fittingTools::statOntoShip($cap, $capne['amount'], "-", "%", 1);
				}

			}
		}
		$cap = fittingTools::getSkillset($value['type'], "capNeeded", $cap);

		if(self::$shipStats->getShipEffects()) {
			foreach(self::$shipStats->getShipEffects() as $j => $effect) {
				if($value['type'] == $effect['effect']) {
					$cap = fittingTools::statOntoShip($cap, (5*$effect['bonus']),$effect['type'],"%", 1);
				}
			}
		}

		$arr[$i]['capNeeded'] = $cap;
		$arr[$i]['type'] = $value['type'];
		$arr[$i]['duration'] = $value['duration'];
		$arr[$i]['use'] = $cap/$value['duration'];
	}

	return $arr;

}

function getDPS() {
	$total = 0;
	/*echo "<pre>";
	print_r(self::$shipStats->getDamageGun());
	echo "</pre>";*/
	if(self::$shipStats->getDamageGun()) {
		foreach(self::$shipStats->getDamageGun() as $i => $value) {
			$total += $value['dps'];
			if(array_key_exists("damageM", $value)) {
				self::$shipStats->setMisUsed(self::$shipStats->getMisUsed()-1);
				self::$shipStats->setMissileDPS(self::$shipStats->getMissileDPS()+$value['dps']);
			} else {
				self::$shipStats->setTurUsed(self::$shipStats->getTurUsed()-1);
				self::$shipStats->setTurretDPS(self::$shipStats->getTurretDPS()+$value['dps']);
			}
		}
	}
	//drone damage
	$dronecount=0;
	if(self::$shipStats->getDroneDamage()) {
		foreach(self::$shipStats->getDroneDamage() as $i => $value) {

			for($j = 0; $j < $value['count']; $j++) {
				if($dronecount < self::$droneMax) {
					$total += $value['dps'];
					self::$shipStats->setDroneDPS(self::$shipStats->getDroneDPS()+$value['dps']);
					$dronecount++;
				} else {
					break;
				}
			}
		}
	}
	return $total;
}

function getVolley() {
	$total = 0;
	if(self::$shipStats->getDamageGun()) {
		foreach(self::$shipStats->getDamageGun() as $i => $value) {
			$total += $value['volley'];
		}
	}
	return $total;
}

function getDPSAndVolley() {

	$avDPS;
	$condition = "";
	$i = 0;

	$arr = self::$shipStats->getDamageGun();

	if($arr) {
		foreach($arr as $i => $value) {

			/*if($value['name'] == $condition) {

				$i++;
			} else {
				$i = 0;
				$condition = $value['name'];


				$i++;
			}*/


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
			self::$shipStats->setDamageGun($arr);
		}
	}
}

function turretMods() {

	foreach(self::$shipStats->getDamageGun() as $i => $value) {

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

		/*echo "<pre>";
		print_r($value);
		echo "</pre>";*/

		if($dex != "M") {
			$rof = fittingTools::setDamageModSkills("rof".$dex, $value['rof'.$dex], $value['techlevel']);
			$dMod = fittingTools::setDamageModSkills("damage".$dex, $value['damage'.$dex], $value['techlevel']);
			//echo $rof." - ".$dMod;

			$em = $value['emDamage'];
			$ex = $value['exDamage'];
			$ki = $value['kiDamage'];
			$th = $value['thDamage'];

			if($value['capNeed']) {
				$cap = fittingTools::getSkillset("turretCap", "turretCap", $value['capNeed']);
				$cap = fittingTools::statOntoShip($cap, $value['capNeededBonus'],"-","%", 1);
			}

			if(self::$shipStats->getShipIcon() == 4302
			|| self::$shipStats->getShipIcon() == 4306
			|| self::$shipStats->getShipIcon() == 4310
			|| self::$shipStats->getShipIcon() == 4308) {
				self::$shipStats->setRSize("Large");
			}

			if(fittingTools::isSmartBomb($value['name'])) {
				$rof = fittingTools::statOntoShip($rof, 25,"-","%", 1);
			}

			/*echo "<pre>";
			print_r(self::$shipStats->getShipEffects());
			echo "</pre>";*/

			if(self::$shipStats->getRSize() == $value['type'] || $value['type'] == "X-Large") {
				if(self::$shipStats->getShipEffects()) {
					foreach(self::$shipStats->getShipEffects() as $j => $effect) {
						if($effect['effect'] == "rofP") {
							if($dex == "P") {
								$rof = fittingTools::statOntoShip($rof, (5*$effect['bonus']),"-","%", 1);
							}
						} else if($effect['effect'] == "damageP") {
							if($dex == "P") {
								if($effect['type'] == "=") {
									$dMod = fittingTools::statOntoShip($dMod, $effect['bonus'],"+","%", 1);
								} else {
									$dMod = fittingTools::statOntoShip($dMod, (5*$effect['bonus']),$effect['type'],"%", 1);
								}
								//$dMod = fittingTools::statOntoShip($dMod, (5*$effect['bonus']),$effect['type'],"%", 1);
							}
						} else if($effect['effect'] == "turretCap") {
							$cap = fittingTools::statOntoShip($cap, (5*$effect['bonus']),"-","%", 1);
						} else if($effect['effect'] == "rofL") {
							if($dex == "L") {
								$rof = fittingTools::statOntoShip($rof, (5*$effect['bonus']),"-","%", 1);
							}
						} else if($effect['effect'] == "damageL") {
							if($dex == "L") {
								if($effect['type'] == "=") {
									$dMod = fittingTools::statOntoShip($dMod, $effect['bonus'],"+","%", 1);
								} else {
									$dMod = fittingTools::statOntoShip($dMod, (5*$effect['bonus']),$effect['type'],"%", 1);
								}
								//$dMod = fittingTools::statOntoShip($dMod, (5*$effect['bonus']),$effect['type'],"%", 1);
							}
						} else if($effect['effect'] == "rofH") {
							if($dex == "H") {
								$rof = fittingTools::statOntoShip($rof, (5*$effect['bonus']),"-","%", 1);
							}
						} else if($effect['effect'] == "damageH") {
							if($dex == "H") {
								if($effect['type'] == "=") {
									$dMod = fittingTools::statOntoShip($dMod, $effect['bonus'],"+","%", 1);
								} else {
									$dMod = fittingTools::statOntoShip($dMod, (5*$effect['bonus']),$effect['type'],"%", 1);
								}
							}

						}
					}

				}

			}
			/*echo $dMod." - ".$rof."<br />";
			echo "<pre>";
			print_r(self::$shipStats->getDamageModules());
			echo "</pre>";*/
			if(self::$shipStats->getDamageModules()) {
				foreach(self::$shipStats->getDamageModules() as $j => $damMod) {
					if($damMod['damage'.$dex]) {
						$dMod = fittingTools::statOntoShip($dMod, $damMod['damage'.$dex], "+","%", $damMod['neg']);
					}
					if($damMod['rof'.$dex]) {
						$rof = fittingTools::statOntoShip($rof, $damMod['rof'.$dex], "-","%", $damMod['neg']);
					}
				}
			}
			//echo $dMod." - ".$rof."<br />";
		} else {
			$rof = fittingTools::setDamageModSkills("rof".$dex, $value['rof'.$dex], $value['techlevel']);
			$dMod = 1;
			$em = fittingTools::setDamageModSkills("damage".$dex, $value['emDamage'], $value['techlevel']);
			$ex = fittingTools::setDamageModSkills("damage".$dex, $value['exDamage'], $value['techlevel']);
			$ki = fittingTools::setDamageModSkills("damage".$dex, $value['kiDamage'], $value['techlevel']);
			$th = fittingTools::setDamageModSkills("damage".$dex, $value['thDamage'], $value['techlevel']);

			if(self::$shipStats->getShipIcon() == 12038
			|| self::$shipStats->getShipIcon() == 12032
			|| self::$shipStats->getShipIcon() == 11377
			|| self::$shipStats->getShipIcon() == 12034) {
				self::$shipStats->setRSize("Large");
			}

			if(self::$shipStats->getRSize() == $value['type']) {
				if(self::$shipStats->getShipEffects()) {
					foreach(self::$shipStats->getShipEffects() as $j => $effect) {
						//echo $effect['effect']. " " .$effect['bonus']."<br />";
						if($effect['effect'] == "damageem") {
							if($em != 0) {
								$em = fittingTools::statOntoShip($em, (5*$effect['bonus']),$effect['type'],"%", 1);
							}
						} else if($effect['effect'] == "damageex") {
							if($ex != 0) {
								$ex = fittingTools::statOntoShip($ex, (5*$effect['bonus']),$effect['type'],"%", 1);
							}
						} else if($effect['effect'] == "damageki") {
							if($ki != 0) {
								$ki = fittingTools::statOntoShip($ki, (5*$effect['bonus']),$effect['type'],"%", 1);
							}
						} else if($effect['effect'] == "damageth") {
							if($th != 0) {
								$th = fittingTools::statOntoShip($th, (5*$effect['bonus']),$effect['type'],"%", 1);
							}
						} else if($effect['effect'] == "rofM") {

							if($effect['bonus'] == 60) {
								$rof = fittingTools::statOntoShip($rof, (5*5),"-","%", 1);
							} else {
								$rof = fittingTools::statOntoShip($rof, (5*$effect['bonus']),"-","%", 1);
							}

						}

					}
				}
			}

			if(self::$shipStats->getDamageModules()) {
				foreach(self::$shipStats->getDamageModules() as $j => $damMod) {
					if($damMod['damageM']) {
						$em = fittingTools::statOntoShip($em, $damMod['damageM'], "+","%", $damMod['neg']);
						$ex = fittingTools::statOntoShip($ex, $damMod['damageM'], "+","%", $damMod['neg']);
						$ki = fittingTools::statOntoShip($ki, $damMod['damageM'], "+","%", $damMod['neg']);
						$th = fittingTools::statOntoShip($th, $damMod['damageM'], "+","%", $damMod['neg']);
					}
					if($damMod['rofM']) {
						$rof = fittingTools::statOntoShip($rof, $damMod['rofM'], "-","%", $damMod['neg']);
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


function setDamageModSkills($param_type, $param_input, $param_techModLevel) {

	if($param_type == "rofP" || $param_type == "rofL" || $param_type == "rofH") {

		//Gunnery skill
		$param_input = fittingTools::statOntoShip($param_input, (5*2),"-","%", 1);

		//Rapid firing
		$param_input = fittingTools::statOntoShip($param_input, (5*4),"-","%", 1);
		//trace(turretType,modifier);
	}

	if($param_type == "damageP" || $param_type == "damageL" || $param_type == "damageH") {

		//Surgical Strike
		$param_input = fittingTools::statOntoShip($param_input, (5*3),"+","%", 1);

		//Projectile Skill
		$param_input = fittingTools::statOntoShip($param_input, (5*5),"+","%", 1);

		if($param_techModLevel == 2) {
			//Specialization skill
			$param_input = fittingTools::statOntoShip($param_input, (5*2),"+","%", 1);
		}

	}

	if($param_type == "rofM") {

		//missile launcher skill
		$param_input = fittingTools::statOntoShip($param_input, (5*2),"-","%", 1);

		//Rapid launch
		$param_input = fittingTools::statOntoShip($param_input, (5*3),"-","%", 1);

		if($param_techModLevel == 2) {
			//Specialization
			$param_input = fittingTools::statOntoShip($param_input, (5*2),"-","%", 1);
		}
	}

	if($param_type == "damageM") {

		//Warhead upgrade
		$param_input = fittingTools::statOntoShip($param_input, (5*2),"+","%", 1);

		//Missile specific
		$param_input = fittingTools::statOntoShip($param_input, (5*5),"+","%", 1);
	}


	if($param_type == "damageDr") {

		//Combat drone operation
		$param_input = fittingTools::statOntoShip($param_input, (5*5),"+","%", 1);

		//Drone Interfacing
		$param_input = fittingTools::statOntoShip($param_input, (5*20),"+","%", 1);

		if($param_techModLevel == 2) {
			//Drone Specialization
			$param_input = fittingTools::statOntoShip($param_input, (5*2),"+","%", 1);
		}
	}




	return $param_input;
}


function totalCapUse() {

	$total = 0;
	if(self::$shipStats->getCapGJ()) {
		foreach(self::$shipStats->getCapGJ() as $i => $value) {
			$total += $value['use'];
			//echo "".$total."<br />";
		}
	}
	if(self::$shipStats->getTransCap()) {
		foreach(self::$shipStats->getTransCap() as $i => $value) {
			$total += $value['use'];
		}
	}
	if(self::$shipStats->getDamageGun()) {
		foreach(self::$shipStats->getDamageGun() as $i => $value) {
			$total += $value['use'];
			//echo "".$total."<br />";
		}
	}

	//echo "<br /><br /><br />".$total."<br />";
	return $total;
}

function totalCapInjected() {

	$total = 0;
	if(self::$shipStats->getCapInj()) {
		foreach(self::$shipStats->getCapInj() as $i => $value) {
			$total += $value['use'];
		}
	}

	//any nos effects
	if(self::$shipStats->getCapGJ()) {
		foreach(self::$shipStats->getCapGJ() as $i => $value) {
			if($value['capAdd']) {
				$total += $value['capAdd'];
			}
		}
	}

	return $total;
}

function capacitorInjector() {

	//print_r(self::$shipStats->getCapInj());

	foreach(self::$shipStats->getCapInj() as $i => $value) {
		//echo $value['duration']." ".$value['capacity']." ".$value['amount']." ".$value['vol']."<br />";

		//echo fittingTools::capInjector($value['amount'], $value['capacity'], $value['vol'], $value['duration']);

		$arr[$i]['duration'] = $value['duration'];
		$arr[$i]['capacity'] = $value['capacity'];


		if(!$value['amount'] || !$value['vol']) {
			$arrWithBooster = fittingTools::capInjEmpty($value['capacity']);

			$arr[$i]['amount'] = $arrWithBooster['amount'];
			$arr[$i]['vol'] = $arrWithBooster['vol'];
			//echo "here";
			$arr[$i]['use'] = fittingTools::capInjector($arrWithBooster['amount'], $value['capacity'], $arrWithBooster['vol'], $value['duration']);
		} else {
			$arr[$i]['amount'] = $value['amount'];
			$arr[$i]['vol'] = $value['vol'];
			$arr[$i]['use'] = fittingTools::capInjector($value['amount'], $value['capacity'], $value['vol'], $value['duration']);
		}


	}

	return $arr;
}

function capInjEmpty($modCap) {

	if($modCap == "160") {
		$arr['amount'] = "800";
		$arr['vol'] = "32";
	} else if($modCap == "40"){
		$arr['amount'] = "800";
		$arr['vol'] = "32";
	} else if($modCap == "15"){
		$arr['amount'] = "200";
		$arr['vol'] = "8";
	} else if($modCap == "10"){
		$arr['amount'] = "200";
		$arr['vol'] = "8";
	}
	return $arr;
}

function capacitorUsage() {

	foreach(self::$shipStats->getCapGJ() as $i => $value) {
		$dur = fittingTools::getSkillset($value['name'], "duration", $value['duration']);
		$cap = fittingTools::getSkillset($value['name'], "capNeeded", $value['capNeeded']);

		if($value['react']) {
			$rea = fittingTools::getSkillset($value['name'], "react", $value['react']);
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

	return $arr;
}

function propCeptorBonus($cap, $off) {

	return $cap-(($cap/100)*$off);

}

function getSkillset($param_module, $param_type, $param_value) {


	if(fittingTools::advancedModuleSettings($param_module) == "mwd") {
		if($param_type == "duration") {
			return $param_value;
		} else {
			$output = $param_value;
			$output = fittingTools::statOntoShip($output, 25, "-", "%", 1);

			if(self::$shipStats->getSpeedT3Cap()) {
				$output = fittingTools::statOntoShip($output, self::$shipStats->getSpeedT3Cap(), "-", "%", 1);
			}
			return $output;
		}
	}
	if(fittingTools::advancedModuleSettings($param_module) == "ab") {
		if($param_type == "duration") {
			return fittingTools::statOntoShip($param_value, 50, "+", "%", 1);
		} else {
			$output = $param_value;
			$output = fittingTools::statOntoShip($output, 50, "-", "%", 1);

			if(self::$shipStats->getSpeedT3Cap()) {
				$output = fittingTools::statOntoShip($output, self::$shipStats->getSpeedT3Cap(), "-", "%", 1);
			}
			return $output;
		}
	}

	if(strstr(strtolower($param_module), "disruption") || strstr(strtolower($param_module), "disruptor")) {
		if($param_type == "capNeeded") {
			$cap = fittingTools::statOntoShip($param_value, 25, "-", "%", 1);

			if(self::$shipStats->getShipEffects()) {
				foreach(self::$shipStats->getShipEffects() as $j => $effect) {
					if($effect['effect'] == "propJamming") {
						$cap = fittingTools::propCeptorBonus($cap, $effect['bonus']);
					}
				}
			}

			return $cap;
		}
	}

	if(strstr(strtolower($param_module), "web") || strstr(strtolower($param_module), "x5 ") || strstr(strtolower($param_module), "langour") || strstr(strtolower($param_module), "fleeting propulsion")) {
		if($param_type == "capNeeded") {
			$cap = fittingTools::statOntoShip($param_value, 25, "-", "%", 1);

			if(self::$shipStats->getShipEffects()) {
				foreach(self::$shipStats->getShipEffects() as $j => $effect) {
					if($effect['effect'] == "propJamming") {
						$cap = fittingTools::propCeptorBonus($cap, $effect['bonus']);
					}
				}
			}

			return $cap;
		}
	}

	if(strstr(strtolower($param_module), "scram")) {
		if($param_type == "capNeeded") {
			$cap = fittingTools::statOntoShip($param_value, 25, "-", "%", 1);

			if(self::$shipStats->getShipEffects()) {
				foreach(self::$shipStats->getShipEffects() as $j => $effect) {
					if($effect['effect'] == "propJamming") {
						$cap = fittingTools::propCeptorBonus($cap, $effect['bonus']);
					}
				}
			}

			return $cap;
		}
	}

	if(strstr(strtolower($param_module), "shieldtrans") || strstr(strtolower($param_module), "armortrans") || strstr(strtolower($param_module), "energytrans")) {
		if($param_type == "capNeeded") {
			return fittingTools::statOntoShip($param_value, 25, "-", "%", 1);
		}
	}

	if(strstr(strtolower($param_module), "remote ecm burst")) {
		if($param_type == "react") {
			$param_value = fittingTools::statOntoShip($param_value, 25, "-", "%", 1);
			$param_value = $param_value/1000;
		}
		return $param_value;
	}

	if(strstr(strtolower($param_module), "ecm")) {
		if($param_type == "capNeeded") {
			return fittingTools::statOntoShip($param_value, 25, "-", "%", 1);
		}
	}

	if(strstr(strtolower($param_module), "neutralizer")
	|| strstr(strtolower($param_module), "w infectious")
	|| strstr(strtolower($param_module), "power core disruptor")
	|| strstr(strtolower($param_module), "destabilizer")
	|| strstr(strtolower($param_module), "unstable power")) {
		if($param_type == "capNeeded") {
			return fittingTools::statOntoShip($param_value, 25, "-", "%", 1);
		}
	}

	if(strstr(strtolower($param_module), "target painter")
	|| strstr(strtolower($param_module), "weapon navigation")) {
		if($param_type == "capNeeded") {
			return fittingTools::statOntoShip($param_value, 25, "-", "%", 1);
		}
	}

	if(strstr(strtolower($param_module), "sensor dampener")
	|| strstr(strtolower($param_module), "sensor disruptor")
	|| strstr(strtolower($param_module), "sensor suppressor")
	|| strstr(strtolower($param_module), "scanning dampening")
	|| strstr(strtolower($param_module), "sensor disruptor")
	|| strstr(strtolower($param_module), "sensor array")) {
		if($param_type == "capNeeded") {
			return fittingTools::statOntoShip($param_value, 25, "-", "%", 1);
		}
	}

	if(strstr(strtolower($param_module), "turretcap")) {
		if($param_type == "turretCap") {
			return fittingTools::statOntoShip($param_value, 25, "-", "%", 1);
		}
	}

	if(strstr(strtolower($param_module), "cargo scan")
	|| strstr(strtolower($param_module), "cargo ident")
	|| strstr(strtolower($param_module), "shipment pr")
	|| strstr(strtolower($param_module), "freight sen")) {
		if($param_type == "duration") {
			return fittingTools::statOntoShip($param_value, 25, "-", "%", 1);
		}
	}

	if(strstr(strtolower($param_module), "shield booster")
	|| strstr(strtolower($param_module), "shield overload")
	|| strstr(strtolower($param_module), "clarity ward")
	|| strstr(strtolower($param_module), "converse ")
	|| strstr(strtolower($param_module), "saturation in")) {
		if($param_type == "capNeeded") {
			return fittingTools::statOntoShip($param_value, 10, "-", "%", 1);
		}
		if($param_type == "duration") {
			return fittingTools::statOntoShip($param_value, 10, "-", "%", 1);
		}
	}

	if(strstr(strtolower($param_module), "hull repair")
	|| strstr(strtolower($param_module), "hull reconstructer")
	|| strstr(strtolower($param_module), "structural restoration")
	|| strstr(strtolower($param_module), "structural regenerator")) {
		if($param_type == "duration") {
			return fittingTools::statOntoShip($param_value, 25, "-", "%", 1);
		}
	}

	if(strstr(strtolower($param_module), "armor repair")
	|| strstr(strtolower($param_module), "vestment reconstructer")
	|| strstr(strtolower($param_module), "carapace restoration")
	|| strstr(strtolower($param_module), "armor regenerator")) {
		if($param_type == "duration") {
			return fittingTools::statOntoShip($param_value, 25, "-", "%", 1);
		}
	}

	if(fittingTools::isSmartBomb($param_module)) {
		if($param_type == "duration") {
			return fittingTools::statOntoShip($param_value, 25, "-", "%", 1);
		}
	}




	return $param_value;
}

function isSmartBomb($param_module) {

	if(strstr(strtolower($param_module), "smartbomb")
	|| strstr(strtolower($param_module), "notos")
	|| strstr(strtolower($param_module), "shockwave charge")
	|| strstr(strtolower($param_module), "degenerative co")
	|| strstr(strtolower($param_module), "yf-12a")
	|| strstr(strtolower($param_module), "rudimentary co")) {
		return true;
	}
	return false;
}

function getShipSpeed($shipSpeed, $boost, $thrust, $mass) {
	return $shipSpeed*(1+(($boost/100)*(1+5*0.05)*($thrust/$mass)));
}

public static $modSlots;
public static $droneSlots;
public static $cargoSlots;

function moduleInfo($param_moduleArray) {

	$low = 0;
	$mid = 0;
	$hig = 0;
	$rig = 0;

	$slots = array(
	7 => "",
	3 => "[empty low slot]",
	2 => "[empty mid slot]",
	1 => "[empty high slot]",
	5 => "[empty rig slot]",
	6 => "",
	10 => "",
	11 => "");

	/*echo "<pre>";
	print_r($param_moduleArray);
	echo "</pre>";*/

	/*

	[0] => Array
    (
		[name] => E500 Prototype Energy Vampire
		[groupID] => 68
		[chargeSize] =>
		[itemid] => 16501
		[id] => 16501
		[capacity] => 0
		[mass] => 1000
		[volume] => 5
		[icon] => 01_03
		[slot] => 1
    )

	*/

	foreach($slots as $j => $slot) {
		$moduleArr = null;
		$moduleArr = Array();

		if($param_moduleArray[$j]) {
			foreach($param_moduleArray[$j] as $i => $value) {
				//echo $value['itemid']." -> <br />";

				$item = new Item($value[itemid]);

				if(array_key_exists ($value['itemid'], $moduleArr)) {
					//echo $value[itemid]." -> ".$moduleArr[$value[itemid]]['name']." - > ".self::$moduleCount." -> ".$moduleArr[$value[itemid]]["ignore"]."<br />";
					if($moduleArr[$value[itemid]]["ignore"] !== true) {
						self::$modSlots[$j][] = array('id'=> $moduleArr[$value[itemid]]['id'],'name'=> $moduleArr[$value[itemid]]['name'], 'groupID'=> $moduleArr[$value[itemid]]['groupID'], 'icon'=> $moduleArr[$value[itemid]]['icon'], 'iconloc'=> $moduleArr[$value[itemid]]['iconloc'], 'metaLevel' => $moduleArr[$value[itemid]]["metaLevel"], 'techLevel' => $moduleArr[$value[itemid]]["techLevel"], 'capacity' => $moduleArr[$value[itemid]]['capacity'], 'volume' => $moduleArr[$value[itemid]]['volume'], 'mass' => $moduleArr[$value[itemid]]['mass']);


						$valueinput 		= explode(",",$moduleArr[$value[itemid]]['value']);
						$attributeName 		= explode(",",$moduleArr[$value[itemid]]['attributeName']);
						$displayName 		= explode(",",$moduleArr[$value[itemid]]['displayName']);
						$stackable 			= explode(",",$moduleArr[$value[itemid]]['stackable']);
						$unit 				= explode(",",$moduleArr[$value[itemid]]['unit']);

						//echo $moduleArr[$value[itemid]]['name']." -> <br />";
						for($k = 0; $k < count($valueinput); $k++) {

							if($valueinput != "") {
								if($unit[$k] == "%") {
									$type = $unit[$k];
								} else {
									$type = "+";
								}


								$neg = fittingTools::negRules($stackable[$k],$unit[$k]);
								if($j == 6) {
									fittingTools::applyDroneSkills(abs($valueinput[$k]), "+", $type, $attributeName[$k], false, 1, $neg, $moduleArr[$value[itemid]]['groupID'], $moduleArr[$value[itemid]]['capacity'], $moduleArr[$value[itemid]]['name'], $moduleArr[$value[itemid]]["techLevel"], $j);
								} else {
									//echo $moduleArr[$value[itemid]]['name']."<br />";
									fittingTools::applyShipSkills(abs($valueinput[$k]), "+", $type, $attributeName[$k], false, 1, $neg, $moduleArr[$value[itemid]]['groupID'], $moduleArr[$value[itemid]]['capacity'], $moduleArr[$value[itemid]]['name'], $moduleArr[$value[itemid]]["techLevel"], $j, $moduleArr[$value[itemid]]['mass']);
								}
							}
						}
					} else {
						if(self::$droneArr[$value[itemid]]['name']) {
							self::$droneArr[$value[itemid]]['count']++;
						}
					}

				} else {


					if(fittingTools::advancedModuleSettings($value['name']) == "mwd") {
					//i removed icon thing here :: note
						self::$shipStats->setIsMWD(true);
					}
					if(fittingTools::advancedModuleSettings($value['name']) == "ab") {
						self::$shipStats->setIsAB(true);
					}
					//self::$modSlots[$j][] = array('id'=> $value['id'],'name'=> $value['name'], 'icon'=> $value['itemid'], 'metaLevel' => $value["meta"], 'techLevel' => $value["tech"], 'capacity' => $value["capacity"], 'volume' => $value["volume"], 'mass' => $value["mass"]);
					if($j == 10 || $j == 6) {
						self::$modSlots[$j][] = array('id'=> $value['id'],'name'=> $value['name'], 'groupID'=> $value['groupID'], 'icon'=> $value['icon'], 'iconloc'=> $item->getIcon(32), 'metaLevel' => $value["meta"], 'techLevel' => $value["tech"], 'capacity' => $value["capacity"], 'volume' => $value["volume"], 'mass' => $value["mass"]);
					} else {
						self::$modSlots[$j][] = array('id'=> $value['id'],'name'=> $value['name'], 'groupID'=> $value['groupID'], 'icon'=> $value['icon'], 'iconloc'=> $item->getIcon(64,false), 'metaLevel' => $value["meta"], 'techLevel' => $value["tech"], 'capacity' => $value["capacity"], 'volume' => $value["volume"], 'mass' => $value["mass"]);
					}
					/*echo "<br/>-----------------------<br/>";
					echo "<pre>";
					print_r($value);
					echo "</pre>";*/
					//echo "not found -> ".$typeID['typeName']." ".$value['itemid']."<br />";

					if($j == 7) {
						self::$shipStats->setMass(fittingTools::statOntoShip(self::$shipStats->getMass(), fittingTools::calculateMass($value['mass']), "+", "+", 1));
					}

					////////////////////////////////////////

					//add in functionality here
					//get module stats on the fly
					//see how it displays
					//limit the amount of queries here


					$qry2 = new DBQuery();
					$qry2->execute("select kb3_dgmtypeattributes.value, kb3_dgmattributetypes.attributeName, kb3_dgmattributetypes.displayName, kb3_dgmattributetypes.stackable, kb3_eveunits.displayName as unit
from kb3_dgmtypeattributes
inner join kb3_dgmattributetypes on kb3_dgmtypeattributes.attributeID = kb3_dgmattributetypes.attributeID
left join kb3_eveunits on kb3_dgmattributetypes.unitID = kb3_eveunits.unitID
where typeID = ".$value['itemid']);



					while($row = $qry2->getRow()) {
						//echo $row['value']." ".$row['attributeName']."<br/>";
						//echo abs($row['value'])." "+" ".$row['unit']." ".$row['attributeName']." ".false." 1 ".$row['stackable']."<br />";
						if($row['unit'] == "%") {
							$type = $row['unit'];
						} else {
							$type = "+";
						}

						$neg = fittingTools::negRules($row['stackable'],$row['unit']);

						if($j == 6) {
							//applyDroneSkills
							//echo fittingTools::dumpDrones($typeID['typeName'])."<br />";
							if(fittingTools::applyDroneSkills(abs($row['value']), "+", $type, $row['attributeName'], false, 1, $neg, $value['groupID'], $value['volume'], $value['name'], $value["tech"], 6)){

								$moduleArr[$value[itemid]]['value'] .= $row['value'].",";
								$moduleArr[$value[itemid]]['attributeName'] .= $row['attributeName'].",";
								$moduleArr[$value[itemid]]['displayName'] .= $row['displayName'].",";
								$moduleArr[$value[itemid]]['stackable'] .= $row['stackable'].",";
								$moduleArr[$value[itemid]]['unit'] .= $row['unit'].",";

								$moduleArr[$value[itemid]]['id'] 		= $value['id'];
								$moduleArr[$value[itemid]]['name'] 		= $value['name'];
								$moduleArr[$value[itemid]]['groupID'] 	= $value['groupID'];
								$moduleArr[$value[itemid]]['techLevel'] = $value['tech'];
								$moduleArr[$value[itemid]]['metaLevel'] = $value['meta'];
								$moduleArr[$value[itemid]]['icon'] 		= $value['icon'];
								$moduleArr[$value[itemid]]['iconloc'] 		= $item->getIcon(32);
								$moduleArr[$value[itemid]]['capacity'] 	= $value['capacity'];
								$moduleArr[$value[itemid]]['volume'] 	= $value['volume'];
								$moduleArr[$value[itemid]]['mass'] 		= $value['mass'];

								self::$droneArr[$value[itemid]]['name'] = $value['name'];
								self::$droneArr[$value[itemid]]['count'] = 1;

							} else {

								$moduleArr[$value[itemid]]['ignore'] 	= true;

							}
							$drone_count++;


						} else {

							if(fittingTools::applyShipSkills(abs($row['value']), "+", $type, $row['attributeName'], false, 1, $neg, $value['groupID'], (($j==10)?$value['volume']:$value['capacity']), $value['name'], $value["tech"], $j, $value['mass'])){
								//echo $value['name']." - ".abs($row['value']). " - G". $value['groupID'] ." - ".$type." -".$row['attributeName']." -".$neg." - I".$typeID['icon']." -".$typeID['capacity']." -".$typeID['typeName']." -".$tech["value"]." -".$j."<br/>";
								//echo $j." - ".$value['icon']." - ".$moduleArr[$value[itemid]]['iconloc']." - ".$value[itemid]."<br />";
								$moduleArr[$value[itemid]]['value'] .= $row['value'].",";
								$moduleArr[$value[itemid]]['attributeName'] .= $row['attributeName'].",";
								$moduleArr[$value[itemid]]['displayName'] .= $row['displayName'].",";
								$moduleArr[$value[itemid]]['stackable'] .= $row['stackable'].",";
								$moduleArr[$value[itemid]]['unit'] .= $row['unit'].",";

								$moduleArr[$value[itemid]]['id'] 		= $value['id'];
								$moduleArr[$value[itemid]]['name'] 		= $value['name'];
								$moduleArr[$value[itemid]]['groupID'] 	= $value['groupID'];
								$moduleArr[$value[itemid]]['techLevel'] = $value['tech'];
								$moduleArr[$value[itemid]]['metaLevel'] = $value['meta'];

								$moduleArr[$value[itemid]]['icon'] 		= $value['icon'];

								if($j == 10 || $j == 6) {
									$moduleArr[$value[itemid]]['iconloc'] 		= $item->getIcon(32);
									//echo "10 ".$moduleArr[$value[itemid]]['icon']."<br />";
								} else {
									//$moduleArr[$value[itemid]]['icon'] 		= $value['icon'];
									$moduleArr[$value[itemid]]['iconloc'] 		= $item->getIcon(64,false);
									//echo $moduleArr[$value[itemid]]['icon']."<br />";
								}

								$moduleArr[$value[itemid]]['capacity'] 	= (($j==10)?$value['volume']:$value['capacity']);
								$moduleArr[$value[itemid]]['volume'] 	= $value['volume'];
								$moduleArr[$value[itemid]]['mass'] 		= $value['mass'];
							}

						}

					}
				}
				if($j != 10) {
					self::$moduleCount++;
				}

				/////////////////////////////////////////




				if($j == 1) {
					$hig++;
				} else if($j == 2) {
					$mid++;
				} else if($j == 3) {
					$low++;
				} else if($j == 5) {
					$rig++;
				}

			}
		}
	}
	/*echo "<pre>";
	print_r(self::$droneArr);
	echo "</pre>";*/

	//echo $hig." ".$mid." ".$low." ".$rig;
	//fittingTools::curPageURL()
	$arr = self::$shipStats->getShipSlots();

	for($h = $hig; $h < $arr['hislots']; $h++) {
		self::$modSlots[1][] = array('id'=> 0,'name'=> 'Empty High Slot', 'iconloc'=> ((self::$simpleurl)?fittingTools::curPageURL():"").'mods/ship_tool_kb/images/equipment/icon00_hig.png', 'metaLevel' => 0, 'techLevel' => 0, 'capacity' => 0, 'volume' => 0, 'mass' => 0);
	}

	for($m = $mid; $m < $arr['medslots']; $m++) {
		self::$modSlots[2][] = array('id'=> 0,'name'=> 'Empty Mid Slot', 'iconloc'=> ((self::$simpleurl)?fittingTools::curPageURL():"").'mods/ship_tool_kb/images/equipment/icon00_mid.png', 'metaLevel' => 0, 'techLevel' => 0, 'capacity' => 0, 'volume' => 0, 'mass' => 0);
	}

	for($l = $low; $l < $arr['lowslots']; $l++) {
		self::$modSlots[3][] = array('id'=> 0,'name'=> 'Empty Low Slot', 'iconloc'=> ((self::$simpleurl)?fittingTools::curPageURL():"").'mods/ship_tool_kb/images/equipment/icon00_low.png', 'metaLevel' => 0, 'techLevel' => 0, 'capacity' => 0, 'volume' => 0, 'mass' => 0);
	}

	for($r = $rig; $r < $arr['rigslots']; $r++) {
		self::$modSlots[5][] = array('id'=> 0,'name'=> 'Empty Rig Slot', 'iconloc'=> ((self::$simpleurl)?fittingTools::curPageURL():"").'mods/ship_tool_kb/images/equipment/icon00_rig.png', 'metaLevel' => 0, 'techLevel' => 0, 'capacity' => 0, 'volume' => 0, 'mass' => 0);
	}

	if(!empty(self::$modSlots[7])) {
		foreach(self::$modSlots[7] as $i => $value) {
			fittingTools::subsystemaddon($value['name']);
		}
	}

	/*echo "<pre>";
	print_r(self::$modSlots);
	//print_r(self::$shipStats->getShipSlots());
	echo "</pre>";*/
}


function getModuleStats() {

	$moduleArr = Array();

}

function ignoreMod($name) {

	if(strstr(strtolower($name), "drone link")
	|| strstr(strtolower($name), "drone navigation")
	|| strstr(strtolower($name), "drone control")
	|| strstr(strtolower($name), "co-processor")
	|| strstr(strtolower($name), "cpu enhancer")
	|| strstr(strtolower($name), "micro aux")
	|| strstr(strtolower($name), "micro b66")
	|| strstr(strtolower($name), "micro b88")
	|| strstr(strtolower($name), "micro 'vigor'")
	|| strstr(strtolower($name), "micro k-exhau")
	|| strstr(strtolower($name), "micro b66")
	|| strstr(strtolower($name), "reactor control")
	|| strstr(strtolower($name), "reaction control")
	|| strstr(strtolower($name), "probe launcher")
	|| strstr(strtolower($name), "scanner probe")
	|| strstr(strtolower($name), "interdiction sphere launcher")
	|| strstr(strtolower($name), "warp disrupt probe")) {
		return false;
	}


	return true;

}

function dumpDrones($name) {

	if(strpos(strtolower($name), "armor maintenance bot")) {
		return false;
	} else if(strpos(strtolower($name), "shield maintenance bot")) {
		return false;
	} else if(strpos(strtolower($name), " ec-")) {
		return false;
	} else if(strpos(strtolower($name), " ev-")) {
		return false;
	} else if(strpos(strtolower($name), " sd-")) {
		return false;
	} else if(strpos(strtolower($name), " sw-")) {
		return false;
	} else if(strpos(strtolower($name), " tp-")) {
		return false;
	} else if(strpos(strtolower($name), " td-")) {
		return false;
	} else if(strpos(strtolower($name), "mining drone")) {
		return false;
	}
	return true;
}

function advancedModuleSettings($param_input) {

	if(strstr(strtolower($param_input), "microwarpdrive")
	|| strstr(strtolower($param_input), "digital booster")
	|| strstr(strtolower($param_input), "y-t8 ")
	|| strstr(strtolower($param_input), "catalyzed ")
	|| strstr(strtolower($param_input), "phased m")
	|| strstr(strtolower($param_input), "quad ")) {
		return "mwd";
	} else if(strstr(strtolower($param_input), "afterburner")
	|| strstr(strtolower($param_input), "analog booster")
	|| strstr(strtolower($param_input), "y-s8 ")
	|| strstr(strtolower($param_input), "cold-gas ")
	|| strstr(strtolower($param_input), "lif fueled ")
	|| strstr(strtolower($param_input), "monopropellant ")) {
		return "ab";
	}
	return "";
}


function negRules($param_input, $param_unit) {

	switch($param_input) {
		case 'true':
			return fittingTools::negConditions($param_unit);
			//return 1;
		break;
		default:
			return 0;
		break;
	}
}

function negConditions($param_input) {
	switch(strtolower($param_input)) {
		case "hp":
			return 0;
		break;
		default:
			return 1;
		break;
	}
}

function returnShipSkills() {


	//echo self::$shipStats->getShipDesc();



	$splitArray = Array("\r\n"," and ");
	$arr = Array(
	"es:",
	":",
	"per level.",
	"per level",
	"per skill level",
	"+",
	"-",
	" all");

	//$testSplit = explode("Skill Bonus",self::$shipStats->getShipDesc());
	$testSplit = preg_split("(Skill Bonus|Role Bonus|bonus per level)",self::$shipStats->getShipDesc());
	$finished = false;

	/*echo "<pre>";
	print_r($testSplit);
	echo "</pre>";*/

	//echo self::$shipStats->getShipDesc();
	$effects = "";
	for($a = 1; $a < count($testSplit); $a++) {

		$testSplit2 = explode("\n",$testSplit[$a]);
		//echo $testSplit[$a]."<br />";
	/*echo "<pre>";
	print_r($testSplit2);
	echo "</pre>";*/
		for($b = 0; $b < count($testSplit2); $b++) {
			$string = str_replace($arr,"",$testSplit2[$b]);
			if($string) {
				//echo parseString($string)."<br />";


				/*//testing
				$skillsetforparse = explode("*BRK*", fixString($string));

				foreach($skillsetforparse as $output) {
					echo $output."<br />";

				}*/

				$statStart = fittingTools::organiseEffect(fittingTools::fixString($string));
				$effects .= fittingTools::sortEffects($statStart);

				//echo $effects;


			}
		}
	}




	$baseEffects = explode("*brk*",$effects);

	$i = 0;
	$theEffects = self::$shipStats->getShipEffects();
	foreach($baseEffects as $baseEffects2) {

		$baseEffects3 = explode(",",$baseEffects2);

		//foreach($baseEffects3 as $value) {
		if($baseEffects2 != "") {
			$theEffects[$i] = Array("bonus" => $baseEffects3[0], "type" => $baseEffects3[1], "effect" => $baseEffects3[2]);
		}
		//}
		$i++;
	}


	self::$shipStats->setShipEffects($theEffects);
	/*echo "<pre>";
	print_r(self::$shipStats->getShipEffects());
	echo "</pre>";*/
}

function organiseEffect($arrayInput) {

	$newformatedString = "";
	$bonusAmount = "";
	$bounsType = "";
	$bonusName = "";

	$bonusType = new shipEffect();

	$skillsetforparse = explode("*BRK*", trim($arrayInput));

	foreach($skillsetforparse as $output) {

		//search for parts of the string
		if (preg_match('/^[0-9]/',$output)) {
			$breakNum = explode("%",$output);
			$bonusAmount = $breakNum[0];
		}
		if($output != "") {

			if(strstr($output,"Role Bonus")) {
				$bounsType = "=";
			} else if(strstr($output,"bonus to")) {
				$bounsType = "+";
			} else if(strstr($output,"reduction in") || (substr("-", 0) == "-" && strstr($output," need for"))) {
				$bounsType = "-";
			} else if(strstr($output,"Bonus")) {
				$bounsType = "+";
			} else {
				$bounsType = "+";
			}
			//echo "--- ".strtolower($output)."<br/>";
			$bonusName = $bonusType->findEffectName(strtolower($output));


		}

		$newformatedString .= $bonusAmount.",".$bounsType.",".$bonusName."*brk*";
	}


	return $newformatedString;
}

function fixString($str) {



	//breakStatement
	//get the number at the front of the image
	//get the percentage
	//get the type
	//after get the phrase and clean it



	//return preg_replace('/and [0-9.]{0,3}+/', "*BRK*".getNumberBack($str), $str);
	return preg_replace('/ and [0-9]+/', "*BRK*".fittingTools::getNumberBack($str), $str);
}

function getNumberBack($str) {

	$break = explode(" and ", $str);
	$i=0;
	foreach($break as $value) {

		//if (preg_match('/[0-9.]{0,3}/',$value)) {
		if (preg_match('/[0-9]{0,3}/',$value)) {

			$getprevalue = explode(".", $value);
			$getendvalue = explode("%", $getprevalue[0]);
			if(is_numeric($getendvalue[0])) {
				$num = $getendvalue[0];
			}

		}
		$i++;
	}

	return $num;
}

function sortEffects($input) {

	$effectString = "";
	$skillsetforparse = explode("*brk*", trim($input));
	foreach($skillsetforparse as $output) {

		$break = explode(",", trim($output));

		//echo $break[0] . $break[1] . $break[2];
		if($break[0] && $break[1] && $break[2]) {

			$effectString .= $output."*brk*";

		}

	}

	return $effectString;
}

function shipEffects() {
	if(self::$shipStats->getShipEffects()) {
		foreach(self::$shipStats->getShipEffects() as $i => $value) {
			//echo $value['effect']."<br />";
			fittingTools::applyShipSkills($value['bonus'], $value['type'], "%", $value['effect'], true, 5, 1, "", 0, "", 0, 0, 0);
		}
	}
}

function applyDroneSkills($bonus, $type, $mode, $effect, $shipEff, $skillBonus, $negEffect, $groupID, $capacity, $modName, $techLevel) {

	if(strtolower($effect) == "emdamage") {
		$arr = self::$shipStats->getDroneDamage();
		$arr[self::$moduleCount]['emDamage'] = $bonus;
		self::$shipStats->setDroneDamage($arr);
		return true;
	}
	if(strtolower($effect) == "explosivedamage") {
		$arr = self::$shipStats->getDroneDamage();
		$arr[self::$moduleCount]['exDamage'] = $bonus;
		self::$shipStats->setDroneDamage($arr);
		return true;
	}
	if(strtolower($effect) == "kineticdamage") {
		$arr = self::$shipStats->getDroneDamage();
		$arr[self::$moduleCount]['kiDamage'] = $bonus;
		self::$shipStats->setDroneDamage($arr);
		return true;
	}
	if(strtolower($effect) == "thermaldamage") {
		$arr = self::$shipStats->getDroneDamage();
		$arr[self::$moduleCount]['thDamage'] = $bonus;
		self::$shipStats->setDroneDamage($arr);
		return true;
	}


	if(strtolower($effect) == "speed") {
		$arr = self::$shipStats->getDroneDamage();
		$arr[self::$moduleCount]['name'] = $modName;
		$arr[self::$moduleCount]['rofDr'] = ($bonus/1000);
		$arr[self::$moduleCount]['techlevel'] = $techLevel;
		self::$shipStats->setDroneDamage($arr);
		return true;

	}

	if(strtolower($effect) == "damagemultiplier") {
		$arr = self::$shipStats->getDroneDamage();
		$arr[self::$moduleCount]['damageDr'] = $bonus;
		self::$shipStats->setDroneDamage($arr);
		return true;
	}
	return false;
}


function setTank($module_param) {

	if(strstr(strtolower($module_param), "shield booster")
	|| strstr(strtolower($module_param), "shield overload")
	|| strstr(strtolower($module_param), "clarity ward")
	|| strstr(strtolower($module_param), "converse ")
	|| strstr(strtolower($module_param), "saturation in")) {
		self::$shipStats->setTankType("shield");
	} else if(strstr(strtolower($module_param), "armor repair")
	|| strstr(strtolower($module_param), "vestment reconstructer")
	|| strstr(strtolower($module_param), "carapace restoration")
	|| strstr(strtolower($module_param), "armor regenerator")) {
		if(!strstr(strtolower($module_param), "remote")) {
			self::$shipStats->setTankType("armor");
		}
	} else if(strstr(strtolower($module_param), "hull repair")
	|| strstr(strtolower($module_param), "hull reconstructer")
	|| strstr(strtolower($module_param), "structural restoration")
	|| strstr(strtolower($module_param), "structural regenerator")) {
		if(!strstr(strtolower($module_param), "remote")) {
			self::$shipStats->setTankType("hull");
		}
	}

}

function applyShipSkills($bonus, $type, $mode, $effect, $shipEff, $skillBonus, $negEffect, $groupID, $capacity, $modName, $techLevel, $moduleLevel, $mass) {

	fittingTools::setTank($modName);

	//echo $modName." -> ".$bonus." -> ".$effect." I ".$groupID."<br />";

	//echo $icon."<br />";

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

	if((strtolower($modName) == "siege module i" || strtolower($modName) == "siege module ii") && (strtolower($effect) != "cpu" && strtolower($effect) != "power")) {
		return false;
	}

	if(strtolower($effect) == "shieldcapacity" || strtolower($effect) == "capacitybonus" || strtolower($effect) == "shieldcapacitybonus" || strtolower($effect) == "shieldcapacitymultiplier") {
		//echo $modName." <br/>";
		if($bonus < 1 && $bonus != 0) {
			$bonus = (1-$bonus)*100;
		} else if($bonus == 1) {
			$bonus = 0;
		}
		if($bonus != 0) {
			if($groupID == "137" || $groupID == "766") {
				if(!fittingTools::isReactor($modName)) {
					$bonus = ($bonus-1)*100;
					self::$shipStats->setShieldAmount(fittingTools::statOntoShip(self::$shipStats->getShieldAmount(), $bonus, $type, $mode, $negEffect));
				}
			} else {
				self::$shipStats->setShieldAmount(fittingTools::statOntoShip(self::$shipStats->getShieldAmount(), $bonus, $type, $mode, $negEffect));
			}
		}
		return true;
	}
	if(strtolower($effect) == "armorhp" || strtolower($effect) == "armorhpbonusadd" || strtolower($effect) == "armorhpbonus") {
		//self::$shipStats->setArmorAmount($row['value']);
		self::$shipStats->setArmorAmount(fittingTools::statOntoShip(self::$shipStats->getArmorAmount(), $bonus, $type, $mode, $negEffect));
		return true;
	}
	if(strtolower($effect) == "structurehpmultiplier") {
		//self::$shipStats->setHullAmount($row['value']);

		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}

		self::$shipStats->setHullAmount(fittingTools::statOntoShip(self::$shipStats->getHullAmount(), $bonus, "-", $mode, 1));
		self::$shipStats->structure++;
		return true;
	}

	if(strtolower($effect) == "scanradarstrength" && $bonus > 0 && $moduleLevel == 7) {
		self::$shipStats->setSensorType(fittingTools::getSensorTypeImg('radar'));
		self::$shipStats->setSensorAmount($bonus);
		return true;
	}
	if(strtolower($effect) == "scanladarstrength" && $bonus > 0 && $moduleLevel == 7) {
		self::$shipStats->setSensorType(fittingTools::getSensorTypeImg('ladar'));
		self::$shipStats->setSensorAmount($bonus);
		return true;
	}
	if(strtolower($effect) == "scanmagnetometricstrength" && $bonus > 0 && $moduleLevel == 7) {
		self::$shipStats->setSensorType(fittingTools::getSensorTypeImg('magnetometric'));
		self::$shipStats->setSensorAmount($bonus);
		return true;
	}
	if(strtolower($effect) == "scangravimetricstrength" && $bonus > 0 && $moduleLevel == 7) {
		self::$shipStats->setSensorType(fittingTools::getSensorTypeImg('gravimetric'));
		self::$shipStats->setSensorAmount($bonus);
		return true;
	}

	if(strtolower($effect) == "shieldemdamageresonance" || strtolower($effect) == "emdamageresistancebonus") {

		if($bonus < 1 && $bonus != 0) {
			$bonus = (1-$bonus)*100;
		}

		//echo $negEffect."<br />";
		if(!$negEffect && $groupID != "60") {

			if($groupID == "98" || $groupID == "328" || $groupID == "326" || $groupID == "773") {
				if(strstr(strtolower($modName), "hardener")) {
					$emBonus = $bonus;
					$order = 1;
				} else if(strstr(strtolower($modName), "reactive membrane")
				|| strstr(strtolower($modName), "reactive plating")
				|| strstr(strtolower($modName), "reflective membrane")
				|| strstr(strtolower($modName), "reflective plating")
				|| strstr(strtolower($modName), "thermic membrane")
				|| strstr(strtolower($modName), "thermic plating")
				|| strstr(strtolower($modName), "magnetic membrane")
				|| strstr(strtolower($modName), "magnetic plating")
				|| strstr(strtolower($modName), "regenerative membrane")
				|| strstr(strtolower($modName), "regenerative plating")) {
					$emBonus = fittingTools::statOntoShip($bonus, (5*5), "+", "%", 1);
					$order = 1;
				} else if($groupID == "773") {
					$emBonus = $bonus;
					$order = 1;
				} else {
					$emBonus = fittingTools::statOntoShip($bonus, (5*5), "+", "%", 1);

					if($groupID == "98") {
						$order = 5;
					} else if($groupID == "326") {
						$order = 2;
					}

				}
				//echo $modName." : ".$bonus." : ".self::$shipStats->getArmorEM()." : ".$emBonus." : ";
				//self::$shipStats->setArmorEM(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorEM(), $emBonus, $type, self::$emArmor));
				//echo self::$shipStats->getArmorEM()."<br />";

				if($bonus != 0) {
					$arr = self::$shipStats->getShipResists();
					$arr[self::$shieldResistPos]['name'] = $modName;
					$arr[self::$shieldResistPos]['section'] = "armor";
					$arr[self::$shieldResistPos]['resist'] = "em";
					$arr[self::$shieldResistPos]['amount'] = $emBonus;
					$arr[self::$shieldResistPos]['type'] = $type;
					//$arr[self::$shieldResistPos]['neg'] = self::$emArmor;
					$arr[self::$shieldResistPos]['order'] = $order;
					self::$shipStats->setShipResists($arr);
					self::$shieldResistPos++;
				}
				//self::$emArmor++;
			} else {
				//self::$shipStats->setShieldEM(fittingTools::getLevel5SkillsPlus(self::$shipStats->getShieldEM(), ($bonus*$skillBonus), $type, self::$emShield));
				//self::$emShield++;

				if($groupID == "77") {
					$order = 2;
					$emBonus = $bonus;
				} else if($groupID == "774") {
					$emBonus = $bonus;
					$order = 2;
				} else {
					$order = 1;
					$emBonus = fittingTools::statOntoShip($bonus, (5*5), "+", "%", 1);
				}

				if($bonus != 0) {
					$arr = self::$shipStats->getShipResists();
					$arr[self::$shieldResistPos]['name'] = $modName;
					$arr[self::$shieldResistPos]['section'] = "shield";
					$arr[self::$shieldResistPos]['resist'] = "em";
					$arr[self::$shieldResistPos]['amount'] = ($emBonus*$skillBonus);
					$arr[self::$shieldResistPos]['type'] = $type;
					//$arr[self::$shieldResistPos]['neg'] = self::$emArmor;
					$arr[self::$shieldResistPos]['order'] = $order;
					self::$shipStats->setShipResists($arr);
					self::$shieldResistPos++;
				}
			}

		} else {
			if($bonus != 0) {
				$arr = self::$shipStats->getShipResists();
				$arr[self::$shieldResistPos]['name'] = $modName;
				$arr[self::$shieldResistPos]['section'] = "shield";
				$arr[self::$shieldResistPos]['resist'] = "em";
				$arr[self::$shieldResistPos]['amount'] = ($bonus*$skillBonus);
				$arr[self::$shieldResistPos]['type'] = $type;
				//$arr[self::$shieldResistPos]['neg'] = self::$emArmor;
				$arr[self::$shieldResistPos]['order'] = 3;
				self::$shipStats->setShipResists($arr);
				self::$shieldResistPos++;
			}
			//self::$shipStats->setShieldEM(fittingTools::getLevel5SkillsPlus(self::$shipStats->getShieldEM(), ($bonus*$skillBonus), $type, 1));
		}
		return true;
	}


	if(strtolower($effect) == "passivearmoremdamageresonance" && $moduleLevel == 7) {
		if($bonus < 1 && $bonus != 0) {
			$bonus = (1-$bonus)*100;
		}
		if($bonus != 0) {
			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = $modName;
			$arr[self::$shieldResistPos]['section'] = "armor";
			$arr[self::$shieldResistPos]['resist'] = "em";
			$arr[self::$shieldResistPos]['amount'] = $bonus;
			$arr[self::$shieldResistPos]['type'] = $type;
			//$arr[self::$shieldResistPos]['neg'] = self::$emArmor;
			$arr[self::$shieldResistPos]['order'] = 4;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;
		}
		return true;
	}

	if(strtolower($effect) == "passivearmorthermaldamageresonance" && $moduleLevel == 7) {
		if($bonus < 1 && $bonus != 0) {
			$bonus = (1-$bonus)*100;
		}
		if($bonus != 0) {
			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = $modName;
			$arr[self::$shieldResistPos]['section'] = "armor";
			$arr[self::$shieldResistPos]['resist'] = "th";
			$arr[self::$shieldResistPos]['amount'] = $bonus;
			$arr[self::$shieldResistPos]['type'] = $type;
			//$arr[self::$shieldResistPos]['neg'] = self::$emArmor;
			$arr[self::$shieldResistPos]['order'] = 4;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;
		}
		return true;
	}

	if(strtolower($effect) == "passivearmorkineticdamageresonance" && $moduleLevel == 7) {
		if($bonus < 1 && $bonus != 0) {
			$bonus = (1-$bonus)*100;
		}
		if($bonus != 0) {
			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = $modName;
			$arr[self::$shieldResistPos]['section'] = "armor";
			$arr[self::$shieldResistPos]['resist'] = "ki";
			$arr[self::$shieldResistPos]['amount'] = $bonus;
			$arr[self::$shieldResistPos]['type'] = $type;
			//$arr[self::$shieldResistPos]['neg'] = self::$emArmor;
			$arr[self::$shieldResistPos]['order'] = 4;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;
		}
		return true;
	}

	if(strtolower($effect) == "passivearmorexplosivedamageresonance" && $moduleLevel == 7) {
		if($bonus < 1 && $bonus != 0) {
			$bonus = (1-$bonus)*100;
		}
		if($bonus != 0) {
			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = $modName;
			$arr[self::$shieldResistPos]['section'] = "armor";
			$arr[self::$shieldResistPos]['resist'] = "ex";
			$arr[self::$shieldResistPos]['amount'] = $bonus;
			$arr[self::$shieldResistPos]['type'] = $type;
			//$arr[self::$shieldResistPos]['neg'] = self::$emArmor;
			$arr[self::$shieldResistPos]['order'] = 4;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;
		}
		return true;
	}

	if(strtolower($effect) == "passiveshieldemdamageresonance" && $moduleLevel == 7) {
		if($bonus < 1 && $bonus != 0) {
			$bonus = (1-$bonus)*100;
		}
		if($bonus != 0) {
			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = $modName;
			$arr[self::$shieldResistPos]['section'] = "shield";
			$arr[self::$shieldResistPos]['resist'] = "em";
			$arr[self::$shieldResistPos]['amount'] = $bonus;
			$arr[self::$shieldResistPos]['type'] = $type;
			//$arr[self::$shieldResistPos]['neg'] = self::$emArmor;
			$arr[self::$shieldResistPos]['order'] = 4;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;
		}
		return true;
	}

	if(strtolower($effect) == "passiveshieldthermaldamageresonance" && $moduleLevel == 7) {
		if($bonus < 1 && $bonus != 0) {
			$bonus = (1-$bonus)*100;
		}
		if($bonus != 0) {
			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = $modName;
			$arr[self::$shieldResistPos]['section'] = "shield";
			$arr[self::$shieldResistPos]['resist'] = "th";
			$arr[self::$shieldResistPos]['amount'] = $bonus;
			$arr[self::$shieldResistPos]['type'] = $type;
			//$arr[self::$shieldResistPos]['neg'] = self::$emArmor;
			$arr[self::$shieldResistPos]['order'] = 4;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;
		}
		return true;
	}

	if(strtolower($effect) == "passiveshieldkineticdamageresonance" && $moduleLevel == 7) {
		if($bonus < 1 && $bonus != 0) {
			$bonus = (1-$bonus)*100;
		}
		if($bonus != 0) {
			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = $modName;
			$arr[self::$shieldResistPos]['section'] = "shield";
			$arr[self::$shieldResistPos]['resist'] = "ki";
			$arr[self::$shieldResistPos]['amount'] = $bonus;
			$arr[self::$shieldResistPos]['type'] = $type;
			//$arr[self::$shieldResistPos]['neg'] = self::$emArmor;
			$arr[self::$shieldResistPos]['order'] = 4;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;
		}
		return true;
	}

	if(strtolower($effect) == "passiveshieldexplosivedamageresonance" && $moduleLevel == 7) {
		if($bonus < 1 && $bonus != 0) {
			$bonus = (1-$bonus)*100;
		}
		if($bonus != 0) {
			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = $modName;
			$arr[self::$shieldResistPos]['section'] = "shield";
			$arr[self::$shieldResistPos]['resist'] = "ex";
			$arr[self::$shieldResistPos]['amount'] = $bonus;
			$arr[self::$shieldResistPos]['type'] = $type;
			//$arr[self::$shieldResistPos]['neg'] = self::$emArmor;
			$arr[self::$shieldResistPos]['order'] = 4;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;
		}
		return true;
	}


	if(strtolower($effect) == "shieldthermaldamageresonance" || strtolower($effect) == "thermaldamageresistancebonus") {
		if($bonus < 1 && $bonus != 0) {
			$bonus = (1-$bonus)*100;
		}
		if(!$negEffect && $groupID != "60") {

			if($groupID == "98" || $groupID == "328" || $groupID == "326" || $groupID == "773") {
				if(strstr(strtolower($modName), "hardener")) {
					$thBonus = $bonus;
					$order = 1;
				} else if(strstr(strtolower($modName), "reactive membrane")
				|| strstr(strtolower($modName), "reactive plating")
				|| strstr(strtolower($modName), "reflective membrane")
				|| strstr(strtolower($modName), "reflective plating")
				|| strstr(strtolower($modName), "thermic membrane")
				|| strstr(strtolower($modName), "thermic plating")
				|| strstr(strtolower($modName), "magnetic membrane")
				|| strstr(strtolower($modName), "magnetic plating")
				|| strstr(strtolower($modName), "regenerative membrane")
				|| strstr(strtolower($modName), "regenerative plating")) {
					$thBonus = fittingTools::statOntoShip($bonus, (5*5), "+", "%", 1);
					$order = 1;
				} else if($groupID == "773") {
					$thBonus = $bonus;
					$order = 1;
				} else {
					$thBonus = fittingTools::statOntoShip($bonus, (5*5), "+", "%", 1);
					if($groupID == "98") {
						$order = 5;
					} else {
						$order = 2;
					}
					//$order = 2;
				}

				if($bonus != 0) {
					$arr = self::$shipStats->getShipResists();
					$arr[self::$shieldResistPos]['name'] = $modName;
					$arr[self::$shieldResistPos]['section'] = "armor";
					$arr[self::$shieldResistPos]['resist'] = "th";
					$arr[self::$shieldResistPos]['amount'] = $thBonus;
					$arr[self::$shieldResistPos]['type'] = $type;
					//$arr[self::$shieldResistPos]['neg'] = self::$thArmor;
					$arr[self::$shieldResistPos]['order'] = $order;
					self::$shipStats->setShipResists($arr);
					self::$shieldResistPos++;
				}
				//self::$shipStats->setArmorTh(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorTh(), $thBonus, $type, self::$thArmor));
				//self::$thArmor++;
			} else {
				//self::$shipStats->setShieldTh(fittingTools::getLevel5SkillsPlus(self::$shipStats->getShieldTh(), ($bonus*$skillBonus), $type, self::$thShield));
				//self::$thShield++;

				if($groupID == "77") {
					$thBonus = $bonus;
					$order = 2;
				} else if($groupID == "774") {
					$thBonus = $bonus;
					$order = 2;
				} else {
					$order = 1;
					$thBonus = fittingTools::statOntoShip($bonus, (5*5), "+", "%", 1);
				}

				if($bonus != 0) {
					$arr = self::$shipStats->getShipResists();
					$arr[self::$shieldResistPos]['name'] = $modName;
					$arr[self::$shieldResistPos]['section'] = "shield";
					$arr[self::$shieldResistPos]['resist'] = "th";
					$arr[self::$shieldResistPos]['amount'] = ($thBonus*$skillBonus);
					$arr[self::$shieldResistPos]['type'] = $type;
					//$arr[self::$shieldResistPos]['neg'] = self::$thArmor;
					$arr[self::$shieldResistPos]['order'] = $order;
					self::$shipStats->setShipResists($arr);
					self::$shieldResistPos++;
				}
			}

		} else {
			if($bonus != 0) {
				$arr = self::$shipStats->getShipResists();
				$arr[self::$shieldResistPos]['name'] = $modName;
				$arr[self::$shieldResistPos]['section'] = "shield";
				$arr[self::$shieldResistPos]['resist'] = "th";
				$arr[self::$shieldResistPos]['amount'] = ($bonus*$skillBonus);
				$arr[self::$shieldResistPos]['type'] = $type;
				//$arr[self::$shieldResistPos]['neg'] = self::$thArmor;
				$arr[self::$shieldResistPos]['order'] = 3;
				self::$shipStats->setShipResists($arr);
				self::$shieldResistPos++;
			}
			//self::$shipStats->setShieldTh(fittingTools::getLevel5SkillsPlus(self::$shipStats->getShieldTh(), ($bonus*$skillBonus), $type, 1));
		}
		return true;
	}
	if(strtolower($effect) == "shieldkineticdamageresonance" || strtolower($effect) == "kineticdamageresistancebonus") {
		if($bonus < 1 && $bonus != 0) {
			$bonus = (1-$bonus)*100;
		}
		if(!$negEffect && $groupID != "60") {
			//
			if($groupID == "98" || $groupID == "328" || $groupID == "326" || $groupID == "773") {
				if(strstr(strtolower($modName), "hardener")) {
					$kiBonus = $bonus;
					$order = 1;
				} else if(strstr(strtolower($modName), "reactive membrane")
				|| strstr(strtolower($modName), "reactive plating")
				|| strstr(strtolower($modName), "reflective membrane")
				|| strstr(strtolower($modName), "reflective plating")
				|| strstr(strtolower($modName), "thermic membrane")
				|| strstr(strtolower($modName), "thermic plating")
				|| strstr(strtolower($modName), "magnetic membrane")
				|| strstr(strtolower($modName), "magnetic plating")
				|| strstr(strtolower($modName), "regenerative membrane")
				|| strstr(strtolower($modName), "regenerative plating")) {
					$kiBonus = fittingTools::statOntoShip($bonus, (5*5), "+", "%", 1);
					$order = 1;
				} else if($groupID == "773") {
					$kiBonus = $bonus;
					$order = 1;
				} else {
					$kiBonus = fittingTools::statOntoShip($bonus, (5*5), "+", "%", 1);
					//$order = 2;
					if($groupID == "98") {
						$order = 5;
					} else if($groupID == "326") {
						$order = 2;
					}
				}

				//echo self::$shipStats->getArmorKi()." ";
				//self::$shipStats->setArmorKi(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorKi(), $kiBonus, $type, self::$kiArmor));
				//echo $kiBonus." ".self::$shipStats->getArmorKi()."<br />";

				if($bonus != 0) {
					$arr = self::$shipStats->getShipResists();
					$arr[self::$shieldResistPos]['name'] = $modName;
					$arr[self::$shieldResistPos]['section'] = "armor";
					$arr[self::$shieldResistPos]['resist'] = "ki";
					$arr[self::$shieldResistPos]['amount'] = $kiBonus;
					$arr[self::$shieldResistPos]['type'] = $type;
					//$arr[self::$shieldResistPos]['neg'] = self::$kiArmor;
					$arr[self::$shieldResistPos]['order'] = $order;
					self::$shipStats->setShipResists($arr);
					self::$shieldResistPos++;
				}
				//self::$kiArmor++;
			} else {
				//self::$shipStats->setShieldKi(fittingTools::getLevel5SkillsPlus(self::$shipStats->getShieldKi(), ($bonus*$skillBonus), $type, self::$kiShield));
				//self::$kiShield++;

				if($groupID == "77") {
					$order = 2;
					$kiBonus = $bonus;
				} else if($groupID == "774") {
					$kiBonus = $bonus;
					$order = 2;
				} else {
					$order = 1;
					$kiBonus = fittingTools::statOntoShip($bonus, (5*5), "+", "%", 1);
				}

				if($bonus != 0) {
					$arr = self::$shipStats->getShipResists();
					$arr[self::$shieldResistPos]['name'] = $modName;
					$arr[self::$shieldResistPos]['section'] = "shield";
					$arr[self::$shieldResistPos]['resist'] = "ki";
					$arr[self::$shieldResistPos]['amount'] = ($kiBonus*$skillBonus);
					$arr[self::$shieldResistPos]['type'] = $type;
					//$arr[self::$shieldResistPos]['neg'] = self::$kiArmor;
					$arr[self::$shieldResistPos]['order'] = $order;
					self::$shipStats->setShipResists($arr);
					self::$shieldResistPos++;
				}
			}




		} else {
			//self::$shipStats->setShieldKi(fittingTools::getLevel5SkillsPlus(self::$shipStats->getShieldKi(), ($bonus*$skillBonus), $type, 1));
			if($bonus != 0) {
				$arr = self::$shipStats->getShipResists();
				$arr[self::$shieldResistPos]['name'] = $modName;
				$arr[self::$shieldResistPos]['section'] = "shield";
				$arr[self::$shieldResistPos]['resist'] = "ki";
				$arr[self::$shieldResistPos]['amount'] = ($bonus*$skillBonus);
				$arr[self::$shieldResistPos]['type'] = $type;
				//$arr[self::$shieldResistPos]['neg'] = self::$kiArmor;
				$arr[self::$shieldResistPos]['order'] = 3;
				self::$shipStats->setShipResists($arr);
				self::$shieldResistPos++;
			}
		}
		return true;
	}
	if(strtolower($effect) == "shieldexplosivedamageresonance" || strtolower($effect) == "explosivedamageresistancebonus") {
		if($bonus < 1 && $bonus != 0) {
			$bonus = (1-$bonus)*100;
		}
		if(!$negEffect && $groupID != "60") {

			if($groupID == "98" || $groupID == "328" || $groupID == "326" || $groupID == "773") {
				if(strstr(strtolower($modName), "hardener")) {
					$exBonus = $bonus;
					$order = 1;
				} else if(strstr(strtolower($modName), "reactive membrane")
				|| strstr(strtolower($modName), "reactive plating")
				|| strstr(strtolower($modName), "reflective membrane")
				|| strstr(strtolower($modName), "reflective plating")
				|| strstr(strtolower($modName), "thermic membrane")
				|| strstr(strtolower($modName), "thermic plating")
				|| strstr(strtolower($modName), "magnetic membrane")
				|| strstr(strtolower($modName), "magnetic plating")
				|| strstr(strtolower($modName), "regenerative membrane")
				|| strstr(strtolower($modName), "regenerative plating")) {
					$exBonus = fittingTools::statOntoShip($bonus, (5*5), "+", "%", 1);
					$order = 1;
				} else if($groupID == "773") {
					$exBonus = $bonus;
					$order = 1;
				} else {
					$exBonus = fittingTools::statOntoShip($bonus, (5*5), "+", "%", 1);
					//$order = 2;
					if($groupID == "98") {
						$order = 5;
					} else if($groupID == "326") {
						$order = 2;
					}
				}

				if($bonus != 0) {
					$arr = self::$shipStats->getShipResists();
					$arr[self::$shieldResistPos]['name'] = $modName;
					$arr[self::$shieldResistPos]['section'] = "armor";
					$arr[self::$shieldResistPos]['resist'] = "ex";
					$arr[self::$shieldResistPos]['amount'] = $exBonus;
					$arr[self::$shieldResistPos]['type'] = $type;
					//$arr[self::$shieldResistPos]['neg'] = self::$exArmor;
					$arr[self::$shieldResistPos]['order'] = $order;
					self::$shipStats->setShipResists($arr);
					self::$shieldResistPos++;
				}
				//self::$exArmor++;
			} else {
				//self::$shipStats->setShieldEx(fittingTools::getLevel5SkillsPlus(self::$shipStats->getShieldEx(), ($bonus*$skillBonus), $type, self::$exShield));
				//self::$exShield++;

				if($groupID == "77") {
					$order = 2;
					$exBonus = $bonus;
				} else if($groupID == "774") {
					$exBonus = $bonus;
					$order = 2;
				} else {
					$order = 1;
					$exBonus = fittingTools::statOntoShip($bonus, (5*5), "+", "%", 1);
				}

				if($bonus != 0) {
					$arr = self::$shipStats->getShipResists();
					$arr[self::$shieldResistPos]['name'] = $modName;
					$arr[self::$shieldResistPos]['section'] = "shield";
					$arr[self::$shieldResistPos]['resist'] = "ex";
					$arr[self::$shieldResistPos]['amount'] = ($exBonus*$skillBonus);
					$arr[self::$shieldResistPos]['type'] = $type;
					//$arr[self::$shieldResistPos]['neg'] = self::$exArmor;
					$arr[self::$shieldResistPos]['order'] = $order;
					self::$shipStats->setShipResists($arr);
					self::$shieldResistPos++;
				}
			}



		} else {
			//self::$shipStats->setShieldEx(fittingTools::getLevel5SkillsPlus(self::$shipStats->getShieldEx(), ($bonus*$skillBonus), $type, 1));
			if($bonus != 0) {
				$arr = self::$shipStats->getShipResists();
				$arr[self::$shieldResistPos]['name'] = $modName;
				$arr[self::$shieldResistPos]['section'] = "shield";
				$arr[self::$shieldResistPos]['resist'] = "ex";
				$arr[self::$shieldResistPos]['amount'] = ($bonus*$skillBonus);
				$arr[self::$shieldResistPos]['type'] = $type;
				//$arr[self::$shieldResistPos]['neg'] = self::$exArmor;
				$arr[self::$shieldResistPos]['order'] = 3;
				self::$shipStats->setShipResists($arr);
				self::$shieldResistPos++;
			}
		}
		return true;
	}
	if(strtolower($effect) == "shieldrechargerate") {
		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}
		self::$shipStats->setShieldRecharge($bonus/1000);
		return true;
	}

	if(strtolower($effect) == "armoremdamageresonance") {
		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}
		if(!$negEffect && $groupID != "60") {
			//self::$shipStats->setArmorEM(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorEM(), ($bonus*$skillBonus), $type, self::$emArmor));
			//self::$emArmor++;
			if($bonus != 0) {
				$arr = self::$shipStats->getShipResists();
				$arr[self::$shieldResistPos]['name'] = $modName;
				$arr[self::$shieldResistPos]['section'] = "armor";
				$arr[self::$shieldResistPos]['resist'] = "em";
				$arr[self::$shieldResistPos]['amount'] = ($bonus*$skillBonus);
				$arr[self::$shieldResistPos]['type'] = $type;
				//$arr[self::$shieldResistPos]['neg'] = self::$kiArmor;
				$arr[self::$shieldResistPos]['order'] = 2;
				self::$shipStats->setShipResists($arr);
				self::$shieldResistPos++;
			}
		} else {
			//self::$shipStats->setArmorEM(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorEM(), ($bonus*$skillBonus), $type, 1));
			if($bonus != 0) {
				$arr = self::$shipStats->getShipResists();
				$arr[self::$shieldResistPos]['name'] = $modName;
				$arr[self::$shieldResistPos]['section'] = "armor";
				$arr[self::$shieldResistPos]['resist'] = "em";
				$arr[self::$shieldResistPos]['amount'] = ($bonus*$skillBonus);
				$arr[self::$shieldResistPos]['type'] = $type;
				//$arr[self::$shieldResistPos]['neg'] = self::$kiArmor;
				$arr[self::$shieldResistPos]['order'] = 3;
				self::$shipStats->setShipResists($arr);
				self::$shieldResistPos++;
			}
		}
		return true;
	}
	if(strtolower($effect) == "armorthermaldamageresonance") {
		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}

		if(!$negEffect && $groupID != "60") {
			if($bonus != 0) {
				$arr = self::$shipStats->getShipResists();
				$arr[self::$shieldResistPos]['name'] = $modName;
				$arr[self::$shieldResistPos]['section'] = "armor";
				$arr[self::$shieldResistPos]['resist'] = "th";
				$arr[self::$shieldResistPos]['amount'] = ($bonus*$skillBonus);
				$arr[self::$shieldResistPos]['type'] = $type;
				//$arr[self::$shieldResistPos]['neg'] = self::$kiArmor;
				$arr[self::$shieldResistPos]['order'] = 2;
				self::$shipStats->setShipResists($arr);
				self::$shieldResistPos++;
			}
			//self::$shipStats->setArmorTh(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorTh(), ($bonus*$skillBonus), $type, self::$thArmor));
			//self::$thArmor++;
		} else {
			//self::$shipStats->setArmorTh(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorTh(), ($bonus*$skillBonus), $type, 1));
			if($bonus != 0) {
				$arr = self::$shipStats->getShipResists();
				$arr[self::$shieldResistPos]['name'] = $modName;
				$arr[self::$shieldResistPos]['section'] = "armor";
				$arr[self::$shieldResistPos]['resist'] = "th";
				$arr[self::$shieldResistPos]['amount'] = ($bonus*$skillBonus);
				$arr[self::$shieldResistPos]['type'] = $type;
				//$arr[self::$shieldResistPos]['neg'] = self::$kiArmor;
				$arr[self::$shieldResistPos]['order'] = 3;
				self::$shipStats->setShipResists($arr);
				self::$shieldResistPos++;
			}
		}
		return true;
	}
	if(strtolower($effect) == "armorkineticdamageresonance") {
		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}

		if(!$negEffect && $groupID != "60") {
			//self::$kiArmor++;
			//self::$shipStats->setArmorKi(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorKi(), ($bonus*$skillBonus), $type, self::$kiArmor));
			//self::$kiArmor++;

			if($bonus != 0) {
				$arr = self::$shipStats->getShipResists();
				$arr[self::$shieldResistPos]['name'] = $modName;
				$arr[self::$shieldResistPos]['section'] = "armor";
				$arr[self::$shieldResistPos]['resist'] = "ki";
				$arr[self::$shieldResistPos]['amount'] = ($bonus*$skillBonus);
				$arr[self::$shieldResistPos]['type'] = $type;
				//$arr[self::$shieldResistPos]['neg'] = self::$kiArmor;
				$arr[self::$shieldResistPos]['order'] = 2;
				self::$shipStats->setShipResists($arr);
				self::$shieldResistPos++;
			}
		} else {
			//self::$shipStats->setArmorKi(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorKi(), ($bonus*$skillBonus), $type, 1));

			if($bonus != 0) {
				$arr = self::$shipStats->getShipResists();
				$arr[self::$shieldResistPos]['name'] = $modName;
				$arr[self::$shieldResistPos]['section'] = "armor";
				$arr[self::$shieldResistPos]['resist'] = "ki";
				$arr[self::$shieldResistPos]['amount'] = ($bonus*$skillBonus);
				$arr[self::$shieldResistPos]['type'] = $type;
				//$arr[self::$shieldResistPos]['neg'] = self::$kiArmor;
				$arr[self::$shieldResistPos]['order'] = 3;
				self::$shipStats->setShipResists($arr);
				self::$shieldResistPos++;
			}
		}
		return true;
	}
	if(strtolower($effect) == "armorexplosivedamageresonance") {
		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}

		if(!$negEffect && $groupID != "60") {
			//self::$shipStats->setArmorEx(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorEx(), ($bonus*$skillBonus), $type, self::$exArmor));
			//self::$exArmor++;

			if($bonus != 0) {
				$arr = self::$shipStats->getShipResists();
				$arr[self::$shieldResistPos]['name'] = $modName;
				$arr[self::$shieldResistPos]['section'] = "armor";
				$arr[self::$shieldResistPos]['resist'] = "ex";
				$arr[self::$shieldResistPos]['amount'] = ($bonus*$skillBonus);
				$arr[self::$shieldResistPos]['type'] = $type;
				//$arr[self::$shieldResistPos]['neg'] = self::$kiArmor;
				$arr[self::$shieldResistPos]['order'] = 2;
				self::$shipStats->setShipResists($arr);
				self::$shieldResistPos++;
			}
		} else {
			//self::$shipStats->setArmorEx(fittingTools::getLevel5SkillsPlus(self::$shipStats->getArmorEx(), ($bonus*$skillBonus), $type, 1));
			if($bonus != 0) {
				$arr = self::$shipStats->getShipResists();
				$arr[self::$shieldResistPos]['name'] = $modName;
				$arr[self::$shieldResistPos]['section'] = "armor";
				$arr[self::$shieldResistPos]['resist'] = "ex";
				$arr[self::$shieldResistPos]['amount'] = ($bonus*$skillBonus);
				$arr[self::$shieldResistPos]['type'] = $type;
				//$arr[self::$shieldResistPos]['neg'] = self::$kiArmor;
				$arr[self::$shieldResistPos]['order'] = 3;
				self::$shipStats->setShipResists($arr);
				self::$shieldResistPos++;
			}
		}
		return true;
	}

	if(strtolower($effect) == "hullemdamageresonance") {
		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}
		//self::$shipStats->setHullEM(fittingTools::getLevel5SkillsPlus(self::$shipStats->getHullEM(), ($bonus*$skillBonus), $type, 1));
		if($bonus != 0) {
			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = $modName;
			$arr[self::$shieldResistPos]['section'] = "hull";
			$arr[self::$shieldResistPos]['resist'] = "em";
			$arr[self::$shieldResistPos]['amount'] = ($bonus*$skillBonus);
			$arr[self::$shieldResistPos]['type'] = $type;
			//$arr[self::$shieldResistPos]['neg'] = self::$kiArmor;
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;
		}
		return true;
	}
	if(strtolower($effect) == "hullthermaldamageresonance") {
		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}
		//self::$shipStats->setHullTh(fittingTools::getLevel5SkillsPlus(self::$shipStats->getHullTh(), ($bonus*$skillBonus), $type, 1));
		if($bonus != 0) {
			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = $modName;
			$arr[self::$shieldResistPos]['section'] = "hull";
			$arr[self::$shieldResistPos]['resist'] = "th";
			$arr[self::$shieldResistPos]['amount'] = ($bonus*$skillBonus);
			$arr[self::$shieldResistPos]['type'] = $type;
			//$arr[self::$shieldResistPos]['neg'] = self::$kiArmor;
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;
		}
		return true;
	}
	if(strtolower($effect) == "hullkineticdamageresonance") {
		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}
		//self::$shipStats->setHullKi(fittingTools::getLevel5SkillsPlus(self::$shipStats->getHullKi(), ($bonus*$skillBonus), $type, 1));
		if($bonus != 0) {
			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = $modName;
			$arr[self::$shieldResistPos]['section'] = "hull";
			$arr[self::$shieldResistPos]['resist'] = "ki";
			$arr[self::$shieldResistPos]['amount'] = ($bonus*$skillBonus);
			$arr[self::$shieldResistPos]['type'] = $type;
			//$arr[self::$shieldResistPos]['neg'] = self::$kiArmor;
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;
		}
		return true;
	}
	if(strtolower($effect) == "hullexplosivedamageresonance") {
		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}
		//self::$shipStats->setHullEx(fittingTools::getLevel5SkillsPlus(self::$shipStats->getHullEx(), ($bonus*$skillBonus), $type, 1));
		if($bonus != 0) {
			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = $modName;
			$arr[self::$shieldResistPos]['section'] = "hull";
			$arr[self::$shieldResistPos]['resist'] = "ex";
			$arr[self::$shieldResistPos]['amount'] = ($bonus*$skillBonus);
			$arr[self::$shieldResistPos]['type'] = $type;
			//$arr[self::$shieldResistPos]['neg'] = self::$kiArmor;
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;
		}
		return true;
	}

	//echo $modName." - ".self::$moduleCount." - ".$effect." - ".$bonus."<br />";
	if(strtolower($effect) == "powerengineeringoutputbonus") {
		//echo $modName." - ".$bonus."<br />";
		$arr = self::$loadPowerAdd;
		$arr[self::$moduleCount]['name'] = $modName;
		$arr[self::$moduleCount]['power'] = $bonus;
		$arr[self::$moduleCount]['type'] = "+";
		$arr[self::$moduleCount]['mode'] = "%";
		self::$loadPowerAdd = $arr;

		return true;
	}


	if(strtolower($effect) == "poweroutputmultiplier") {

		$arr = self::$loadPowerAdd;
		$arr[self::$moduleCount]['name'] = $modName;
		$arr[self::$moduleCount]['power'] = ($bonus-1)*100;
		$arr[self::$moduleCount]['type'] = "+";
		$arr[self::$moduleCount]['mode'] = "%";
		self::$loadPowerAdd = $arr;

		return true;

	}

	if(strtolower($effect) == "powerincrease") {

		$arr = self::$loadPowerAdd;
		$arr[self::$moduleCount]['name'] = $modName;
		$arr[self::$moduleCount]['power'] = $bonus;
		$arr[self::$moduleCount]['type'] = "+";
		$arr[self::$moduleCount]['mode'] = "+";
		self::$loadPowerAdd = $arr;

		return true;
	}


	if(strtolower($effect) == "cpumultiplier") {

		$arr = self::$loadCPUAdd;
		$arr[self::$moduleCount]['name'] = $modName;
		$arr[self::$moduleCount]['cpu'] = ($bonus-1)*100;
		$arr[self::$moduleCount]['type'] = "+";
		$arr[self::$moduleCount]['mode'] = "%";
		self::$loadCPUAdd = $arr;

		return true;

	}
	if(strtolower($effect) == "cpuoutputbonus2") {

		$arr = self::$loadCPUAdd;
		$arr[self::$moduleCount]['name'] = $modName;
		$arr[self::$moduleCount]['cpu'] = $bonus;
		$arr[self::$moduleCount]['type'] = "+";
		$arr[self::$moduleCount]['mode'] = "%";
		self::$loadCPUAdd = $arr;

		return true;

	}

	//echo $modName." - ".$effect." -- ".$bonus." -- ".$icon."<br />";
	if(strtolower($effect) == "cpuoutput") {
		//if($bonus != 0 && (self::$shipStats->getCpuAmount() < $bonus)) {
			self::$shipStats->setCpuAmount($bonus+self::$shipStats->getCpuAmount());
		//}
		return true;
	}

	if(strtolower($effect) == "drawback" && $groupID == "778") {
		$arr = self::$loadCPUAdd;
		$arr[self::$moduleCount]['name'] = $modName;
		$arr[self::$moduleCount]['cpu'] = $bonus/2;
		$arr[self::$moduleCount]['type'] = "-";
		$arr[self::$moduleCount]['mode'] = "%";
		$arr[self::$moduleCount]['stack'] = 1;
		self::$loadCPUAdd = $arr;
		return true;
	}

	if(strtolower($effect) == "poweroutput") {
		if($bonus != 0) {
			self::$shipStats->setPrgAmount($bonus);
		}
		return true;
	}

	if(strtolower($effect) == "turrethardpointmodifier") {
		if($bonus != 0) {
			self::$shipStats->setTurAmount($bonus);
			self::$shipStats->setTurUsed($bonus);
		}
		return true;
	}

	if(strtolower($effect) == "launcherhardpointmodifier") {
		if($bonus != 0) {
			self::$shipStats->setMisAmount($bonus);
			self::$shipStats->setMisUsed($bonus);
		}
		return true;
	}

	if(strtolower($effect) == "cpu") {
		//echo $modName." --- ".$groupID." --- ".$mass."<br />";

		$arr = self::$shipStats->getCpuUsed();
		$arr[self::$moduleCount]['name'] = $modName;
		$arr[self::$moduleCount]['cpu'] = $bonus;

		if($groupID == "330" && $modName == "Covert Ops Cloaking Device II") {
			$arr[self::$moduleCount]['effect'] = "covert_cloak";
		} else if($groupID == "55" && $mass == "2000") {//p
			$arr[self::$moduleCount]['effect'] = "heavy_cpu";
		} else if($groupID == "53" && $mass == "2000") {;//l
			$arr[self::$moduleCount]['effect'] = "heavy_cpu";
		} else if($groupID == "74" && $mass == "2000") {//h
			$arr[self::$moduleCount]['effect'] = "heavy_cpu";
		} else if($groupID == "203" || $groupID == "285" || $groupID == "766" || $groupID == "767" || $groupID == "768") {
			$arr[self::$moduleCount]['effect'] = "cpu_use";
		} else if($groupID == "769" || $groupID == "43") {//h
			$arr[self::$moduleCount]['effect'] = "cpu_use";
		} else if($groupID == "316") {
			$arr[self::$moduleCount]['effect'] = "war_bonus";
		} else if($groupID == "41") {
			$arr[self::$moduleCount]['effect'] = "shield_transCPU";
		} else if($groupID == "55" || $groupID == "74"
		|| $groupID == "510"|| $groupID == "507"|| $groupID == "508"|| $groupID == "509" || $groupID == "511" || $groupID == "771" || $groupID == "506" || $groupID == "524" || $groupID == "53"
		|| $groupID == "72" || $groupID == "74" || ($groupID == "862" && $modName == "Bomb Launcher I")) {
			$arr[self::$moduleCount]['effect'] = "weapon";
		} else if($groupID == "407") {
			$arr[self::$moduleCount]['effect'] = "capital_cpu";
		} else {
			$arr[self::$moduleCount]['effect'] = "base";
		}
		self::$shipStats->setCpuUsed($arr);

		return true;
	}

	if(strtolower($effect) == "power") {
		//echo strtolower($modName)." --- ".$groupID." -- ".$mass."<br />";
		$arr = self::$shipStats->getPrgUsed();
		$arr[self::$moduleCount]['name'] = $modName;
		$arr[self::$moduleCount]['power'] = $bonus;

		if($groupID == "38" || $groupID == "39") {
			$arr[self::$moduleCount]['effect'] = "shield";
		} else if($groupID == "508" && strpos(strtolower($modName), "torpedo") > -1) {
			$arr[self::$moduleCount]['effect'] = "seige_power";
		} else if($groupID == "55" && $mass == "2000") {//p
			$arr[self::$moduleCount]['effect'] = "heavy_power";
		} else if($groupID == "53" && $mass == "2000") {//l
			$arr[self::$moduleCount]['effect'] = "heavy_power";
		} else if($groupID == "74" && $mass == "2000") {//h
			$arr[self::$moduleCount]['effect'] = "heavy_power";
		} else if($groupID == "67") {
			$arr[self::$moduleCount]['effect'] = "cap_transPower";
		} else if($groupID == "55" || $groupID == "74"
		|| $groupID == "510"|| $groupID == "507"|| $groupID == "508"|| $groupID == "509" || $groupID == "511" || $groupID == "771" || $groupID == "506" || $groupID == "524" || $groupID == "53"
		|| $groupID == "72" || $groupID == "74" || ($groupID == "862" && $modName == "Bomb Launcher I")) {
			$arr[self::$moduleCount]['effect'] = "weapon";
		} else {
			$arr[self::$moduleCount]['effect'] = "base";
		}

		self::$shipStats->setPrgUsed($arr);

		return true;
	}

	if(strtolower($effect) == "maxvelocity" && $moduleLevel != 10) {
		//echo $modName."<br />";
		if($groupID == "12_06") {
		} else {
			self::$shipStats->setShipSpeed(fittingTools::statOntoShip(self::$shipStats->getShipSpeed(), ($bonus*$skillBonus), $type, $mode, 1));
		}
		return true;
	}

	if(strtolower($effect) == "implantbonusvelocity" || strtolower($effect) == "velocitybonus" && $moduleLevel != 10) {
		//self::$shipStats->setShipSpeed($row['value']);

		if($negEffect) {
			self::$shipStats->setShipSpeed(fittingTools::statOntoShip(self::$shipStats->getShipSpeed(), $bonus, "+", $mode, self::$speedV));
			self::$speedV++;
		} else {
			self::$shipStats->setShipSpeed(fittingTools::statOntoShip(self::$shipStats->getShipSpeed(), $bonus, "+", $mode, 0));
		}
		return true;
	}

	if(strtolower($effect) == "maxvelocitybonus" && $moduleLevel != 10) {

		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}
		if($groupID == "765") {
			self::$shipStats->setShipSpeed(fittingTools::statOntoShip(self::$shipStats->getShipSpeed(), $bonus, "-", $mode, self::$speedV));
			//self::$speedB++;

			self::$speedV++;
		} else if($groupID == "330") {
		} else {
			self::$shipStats->setShipSpeed(fittingTools::statOntoShip(self::$shipStats->getShipSpeed(), $bonus, "+", $mode, 0));
		}
		return true;
	}



	if(strtolower($effect) == "signatureradius") {
		self::$shipStats->setSigRadius($bonus);
		return true;
	}

	if(strtolower($effect) == "scanresolution") {
		self::$shipStats->setScan($bonus);
		return true;
	}


	if(strtolower($effect) == "maxtargetrange") {
		self::$shipStats->setDistance($bonus);
		return true;
	}

	if(strtolower($effect) == "maxtargetrangebonus") {
		if($groupID == "212" || $groupID == "210") {
			$arr = self::$shipStats->getSensorBooster();
			$arr[self::$moduleCount]['range'] = $bonus;
			$arr[self::$moduleCount]['negra'] = self::$range;
			$arr[self::$moduleCount]['type'] = "+";
			$arr[self::$moduleCount]['order'] = $slotOrder;
			self::$shipStats->setSensorBooster($arr);
			self::$range++;
		} else if($groupID == "208") {
		} else if($groupID == "315") {
			self::$shipStats->setDistance(fittingTools::statOntoShip(self::$shipStats->getDistance(), $bonus, "-", $mode, self::$warpStab));
			self::$warpStab++;
		} else {
			self::$shipStats->setDistance(fittingTools::statOntoShip(self::$shipStats->getDistance(), $bonus, "+", $mode, self::$range));
			self::$range++;
		}
		return true;
	}

	if(strtolower($effect) == "maxtargetrangemultiplier") {
		//echo $moduleLevel;
		if($groupID == "786") {

			$bonus = ($bonus-1)*100;
			$arr = self::$shipStats->getSensorBooster();
			$arr[self::$moduleCount]['range'] = $bonus;
			$arr[self::$moduleCount]['negra'] = self::$range;
			$arr[self::$moduleCount]['order'] = $slotOrder;
			self::$shipStats->setSensorBooster($arr);
			self::$range++;
		}
		return true;
	}

	if(strtolower($effect) == "scanresolutionbonus") {
		//echo $modName." -- ".self::$shipStats->getScan()." -- ".$groupID."<br />";
		if($groupID == "212" || $groupID == "210") {
			$arr = self::$shipStats->getSensorBooster();
			$arr[self::$moduleCount]['scan'] = $bonus;
			$arr[self::$moduleCount]['negsc'] = self::$scan;
			$arr[self::$moduleCount]['type'] = "+";
			$arr[self::$moduleCount]['order'] = $slotOrder;
			self::$shipStats->setSensorBooster($arr);
			self::$scan++;
			self::$sensorbooster[] = self::$moduleCount;
		} else if($groupID == "208") {
		} else {
			self::$shipStats->setScan(fittingTools::statOntoShip(self::$shipStats->getScan(), $bonus, "+", $mode, self::$scan));
			self::$scan++;
		}
		return true;
	}

	if(strtolower($effect) == "maxtargetrangebonusbonus") {
		if($groupID == "910") {

			$arr = self::$shipStats->getSensorBooster();
			$arr[self::$sensorbooster[0]]['scan'] = 0;
			$arr[self::$sensorbooster[0]]['range'] = $arr[self::$sensorbooster[0]]['range']*2;
			self::$shipStats->setSensorBooster($arr);

			unset(self::$sensorbooster[0]);
			self::$sensorbooster = array_values(self::$sensorbooster);
			return true;
		}

	}

	if(strtolower($effect) == "scanresolutionbonusbonus") {
		//echo "here<br/>";
		if($groupID == "910") {
			$arr = self::$shipStats->getSensorBooster();
			$arr[self::$sensorbooster[0]]['scan'] = $arr[self::$sensorbooster[0]]['scan']*2;
			$arr[self::$sensorbooster[0]]['range'] = 0;
			self::$shipStats->setSensorBooster($arr);

			unset(self::$sensorbooster[0]);
			self::$sensorbooster = array_values(self::$sensorbooster);
			return true;
		}

	}



	if(strtolower($effect) == "maxlockedtargetsbonus") {
		self::$shipStats->setTarget(fittingTools::statOntoShip(self::$shipStats->getTarget(), $bonus, "+", $mode, 0));
		return true;
	}

	if(strtolower($effect) == "maxlockedtargets") {
		self::$shipStats->setTarget($row['value']);
		return true;
	}

	if(strtolower($effect) == "maxactivedronebonus") {
		self::$droneAdd++;
		return true;
	}


	if(strtolower($effect) == "capacitorcapacity" && $moduleLevel == 7) {
		if($bonus != 0) {
			self::$shipStats->setCapAmount(fittingTools::statOntoShip(self::$shipStats->getCapAmount(), $bonus, "+", $mode, 0));
		}
		return true;
	}

	if(strtolower($effect) == "caprecharge") {
		if($bonus != 0) {
			self::$shipStats->setCapRecharge(fittingTools::statOntoShip(self::$shipStats->getCapRecharge(), ($bonus*$skillBonus), "-", $mode, 0));
		}
		return true;
	}

	if(strtolower($effect) == "rechargerate" && $moduleLevel == 7) {
		if($bonus != 0) {
			self::$shipStats->setCapRecharge(fittingTools::statOntoShip(self::$shipStats->getCapRecharge(), $bonus, "+", $mode, 0));
		}
		return true;
	}

	if(strtolower($effect) == "scanresolutionmultiplier") {

		if($groupID == "786") {
			$bonus = ($bonus-1)*100;
			self::$shipStats->setScan(fittingTools::statOntoShip(self::$shipStats->getScan(), $bonus, "+", $mode, self::$shieldHpRed));
			self::$shieldHpRed++;
		} else if($groupID == "315") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			self::$shipStats->setScan(fittingTools::statOntoShip(self::$shipStats->getScan(), $bonus, "-", $mode, self::$warpStabScan));
			self::$warpStabScan++;

		} else {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}

			self::$shipStats->setScan(fittingTools::statOntoShip(self::$shipStats->getScan(), $bonus, "-", $mode, self::$warpStabScan));
			self::$warpStabScan++;
		}
		return true;
	}


	if(strtolower($effect) == "capacitorcapacitymultiplier" || strtolower($effect) == "capacitorcapacitybonus") {
		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}
		if($groupID == "769" || $groupID == "766") {
			$bonus = ($bonus-1)*100;
			self::$shipStats->setCapAmount(fittingTools::statOntoShip(self::$shipStats->getCapAmount(), $bonus, "+", $mode, 1));
		} else if($groupID == "781") {
			self::$shipStats->setCapAmount(fittingTools::statOntoShip(self::$shipStats->getCapAmount(), $bonus, "+", $mode, 1));
		} else {
			if($bonus != 1) {
				self::$shipStats->setCapAmount(fittingTools::statOntoShip(self::$shipStats->getCapAmount(), $bonus, "-", $mode, 1));
			}
		}
		return true;
	}

	if(strtolower($effect) == "signatureradiusadd") {
		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}
		//echo $modName." ".self::$shipStats->getSigRadius()."<br />";
		self::$shipStats->setSigRadius(fittingTools::statOntoShip(self::$shipStats->getSigRadius(), $bonus, "+", $mode, 0));
		//echo $bonus." ".self::$shipStats->getSigRadius()." ".$mode."<br />";
		return true;
	}

	if(strtolower($effect) == "signatureradiusbonus") {
		if($groupID == "46") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}

			if(self::$shipStats->getMwdT3Sig()) {
				$bonus = fittingTools::statOntoShip($bonus, self::$shipStats->getMwdT3Sig(), "-", "%", 1);
			}

			self::$shipStats->setMwdSig($bonus);
		} else if($groupID == "379") {

		} else if($groupID == "52") {

			$arr = self::$shipStats->getSigRadiusBoost();
			$arr[self::$moduleCount]['sigAdd'] = $bonus;
			self::$srBooster[] = self::$moduleCount;
			self::$shipStats->setSigRadiusBoost($arr);
		} else {
			//echo $modName." ".self::$shipStats->getSigRadius()."<br />";
			self::$shipStats->setSigRadius(fittingTools::statOntoShip(self::$shipStats->getSigRadius(), $bonus, "+", $mode, self::$sigRadius));
			//self::$interstab++;
			//echo $bonus." ".self::$shipStats->getSigRadius()." ".$mode."<br />";
			self::$sigRadius++;
		}
		return true;
	}

	if(strtolower($effect) == "signatureradiusbonusbonus") {

		if($groupID == "908") {

			$arr = self::$shipStats->getSigRadiusBoost();
			$arr[self::$srBooster[0]]['sigAdd'] = "0";
			self::$shipStats->setSigRadiusBoost($arr);

			unset(self::$srBooster[0]);
			self::$srBooster = array_values(self::$srBooster);
		}
		return true;
	}

	if(strtolower($effect) == "speedboostfactor") {
		if($groupID == "46") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			if(fittingTools::advancedModuleSettings($modName) == "mwd") {
				self::$shipStats->setMwdThrust($bonus);
			} else if(fittingTools::advancedModuleSettings($modName) == "ab") {
				self::$shipStats->setABThrust($bonus);
			}

		}
		return true;
	}


	if(strtolower($effect) == "massaddition") {
		if($groupID == "46") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			if(fittingTools::advancedModuleSettings($modName) == "mwd") {
				self::$shipStats->setMwdMass($bonus);
			} else if(fittingTools::advancedModuleSettings($modName) == "ab") {
				self::$shipStats->setABMass($bonus);
			}

		} else {

			self::$shipStats->setMass(fittingTools::statOntoShip(self::$shipStats->getMass(), $bonus, "+", $mode, 0));
		}
		return true;
	}

	if(strtolower($effect) == "speedfactor") {
		if($groupID == "46") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			if(fittingTools::advancedModuleSettings($modName) == "mwd") {
				self::$shipStats->setMwdBoost($bonus);
			} else if(fittingTools::advancedModuleSettings($modName) == "ab") {
				if(self::$shipStats->getAbT3Boost()) {
					$bonus = fittingTools::statOntoShip($bonus, self::$shipStats->getAbT3Boost(), "+", "%", 0);
				}

				self::$shipStats->setABBoost($bonus);
			}

		}
		return true;
	}

	if(strtolower($effect) == "signatureradiusmwd") {
		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}
		self::$shipStats->setMwdSigRed($bonus);
		//echo self::$shipStats->getMwdSigRed();
		return true;
	}



	if(strtolower($effect) == "capacitorrechargeratemultiplier" || strtolower($effect) == "caprechargebonus") {

		if(!fittingTools::isReactor($modName)) {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			} else if($bonus == 1) {
				$bonus = 0;
			}
			//echo $modName." -- ".$groupID." -- ".$bonus."<br />";
			if($bonus != 0) {
				if($groupID == "57") {
					self::$shipStats->setCapRecharge(fittingTools::statOntoShip(self::$shipStats->getCapRecharge(), ($bonus-1)*100, "+", $mode, 0));
				} else {
					self::$shipStats->setCapRecharge(fittingTools::statOntoShip(self::$shipStats->getCapRecharge(), $bonus, "-", $mode, 0));
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
				self::$shipStats->setCapRecharge(fittingTools::statOntoShip(self::$shipStats->getCapRecharge(), $bonus, "-", $mode, 0));
			}
		} else if($groupID == "39") {
			$bonus = ($bonus-1)*100;
			self::$shipStats->setCapRecharge(fittingTools::statOntoShip(self::$shipStats->getCapRecharge(), $bonus, "+", $mode, 0));
		}
		return true;
	}



	if(strtolower($effect) == "scanladarstrengthpercent") {

		if($bonus > 0) {
			self::$shipStats->setSensorAmount(fittingTools::statOntoShip(self::$shipStats->getSensorAmount(), $bonus, "+", $mode, self::$scanStrength));
			self::$scanStrength++;
		}
		return true;
	}

	if(strtolower($effect) == "scangravimetricstrengthpercent") {

		if($bonus > 0) {
			self::$shipStats->setSensorAmount(fittingTools::statOntoShip(self::$shipStats->getSensorAmount(), $bonus, "+", $mode, self::$scanStrength));
			self::$scanStrength++;
		}
		return true;
	}

	if(strtolower($effect) == "scanmagnetometricstrengthpercent") {
		if($bonus > 0) {
			self::$shipStats->setSensorAmount(fittingTools::statOntoShip(self::$shipStats->getSensorAmount(), $bonus, "+", $mode, self::$scanStrength));
			self::$scanStrength++;
		}
		return true;
	}

	if(strtolower($effect) == "scanradarstrengthpercent") {
		if($bonus > 0) {
			self::$shipStats->setSensorAmount(fittingTools::statOntoShip(self::$shipStats->getSensorAmount(), $bonus, "+", $mode, self::$scanStrength));
			self::$scanStrength++;
		}
		return true;
	}


	if(strtolower($effect) == "durationskillbonus") {

		if($groupID == "774") {
			$arr = self::$shieldDur;
			$arr[self::$moduleCount]['dur'] = $bonus;
			$arr[self::$moduleCount]['type'] = "-";
			$arr[self::$moduleCount]['neg'] = self::$shieldAmp;
			self::$shieldDur = $arr;
			self::$shieldAmp++;
		} else {
			$arr = self::$armorDur;
			$arr[self::$moduleCount]['dur'] = $bonus;
			$arr[self::$moduleCount]['type'] = "-";
			$arr[self::$moduleCount]['neg'] = 1;
			self::$armorDur = $arr;
		}


		return true;

	}


	if(strtolower($effect) == "drawback") {
		if($groupID == "773") {
			//return "speedBoost";
			self::$shipStats->setShipSpeed(fittingTools::statOntoShip(self::$shipStats->getShipSpeed(), (($bonus/100)*(10*5)), "-", $mode, self::$speedV));
			self::$speedV++;
		} else if($groupID == "774") {
			//return "sigradius";
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			//echo $modName." ".self::$shipStats->getSigRadius()."<br />";
			self::$shipStats->setSigRadius(fittingTools::statOntoShip(self::$shipStats->getSigRadius(), (($bonus/100)*(10*5)), "+", $mode, self::$sigRadius));
			self::$sigRadius++;
		} else if($groupID == "782") {
			//return "armorhp";

			self::$shipStats->setArmorAmount(fittingTools::statOntoShip(self::$shipStats->getArmorAmount(), (($bonus/100)*(10*5)), "-", $mode, 1));
		} else if($groupID == "786") {
			//return "shieldhp";
			self::$shipStats->setShieldAmount(fittingTools::statOntoShip(self::$shipStats->getShieldAmount(), (($bonus/100)*(10*5)), "-", $mode, 1));
		} else {
			//return $input;
		}
		return true;
	}


	if(strtolower($effect) == "duration") {
		if($groupID == "76") {
			$arr = self::$shipStats->getCapInj();
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['duration'] = ($bonus/1000);
			self::$shipStats->setCapInj($arr);
			self::$boosterPos[] = self::$moduleCount;
		} else if($groupID == "41" && strstr(strtolower($modName), "transporter")) {
			$arr = self::$shipStats->getTransCap();
			$arr[self::$moduleCount]['type'] = "shieldTrans";
			$arr[self::$moduleCount]['duration'] = ($bonus/1000);
			self::$shipStats->setTransCap($arr);
		} else if($groupID == "325" && strstr(strtolower($modName), "remote") || strstr(strtolower($modName), "regenerative projector")) {
			$arr = self::$shipStats->getTransCap();
			$arr[self::$moduleCount]['type'] = "armorTrans";
			$arr[self::$moduleCount]['duration'] = ($bonus/1000);
			self::$shipStats->setTransCap($arr);
		} else if($groupID == "67" && strstr(strtolower($modName), "transfer")
		|| strstr(strtolower($modName), "power projector")
		|| strstr(strtolower($modName), "energy succor")
		|| strstr(strtolower($modName), "energy transmitter")
		|| strstr(strtolower($modName), "power conduit")) {
			$arr = self::$shipStats->getTransCap();
			$arr[self::$moduleCount]['type'] = "energyTrans";
			$arr[self::$moduleCount]['duration'] = ($bonus/1000);
			self::$shipStats->setTransCap($arr);
		} else if($groupID == "40") {
			$arr = self::$shipStats->getTankBoost();
			$arr[self::$moduleCount]['dur'] = ($bonus/1000);
			self::$shipStats->setTankBoost($arr);

			$arr = self::$shipStats->getCapGJ();
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['duration'] = ($bonus/1000);
			self::$shipStats->setCapGJ($arr);
		} else if($groupID == "1156") {
			$arr = self::$shipStats->getTankBoost();
			$arr[self::$moduleCount]['dur'] = ($bonus/1000);
			self::$shipStats->setTankBoost($arr);
		} else if($groupID == "1199") {
			$arr = self::$shipStats->getTankBoost();
			$arr[self::$moduleCount]['dur'] = ($bonus/1000);
			self::$shipStats->setTankBoost($arr);

			$arr = self::$shipStats->getCapGJ();
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['duration'] = ($bonus/1000);
		} else if($groupID == "62") {
			$arr = self::$shipStats->getTankBoost();
			$arr[self::$moduleCount]['dur'] = ($bonus/1000);
			self::$shipStats->setTankBoost($arr);

			$arr = self::$shipStats->getCapGJ();
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['duration'] = ($bonus/1000);
			self::$shipStats->setCapGJ($arr);
		} else if($groupID == "72") {
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['rof'] = ($bonus/1000);
			$arr[self::$moduleCount]['type'] = "Large";
			$arr[self::$moduleCount]['techlevel'] = $techLevel;
			$arr[self::$moduleCount]['damage'] = 1;
			self::$shipStats->setDamageGun($arr);

			$arr = self::$shipStats->getCapGJ();
			$arr[self::$moduleCount]['capacity'] = 1;
			$arr[self::$moduleCount]['duration'] = ($bonus/1000);
			self::$shipStats->setCapGJ($arr);
		} else {
			$arr = self::$shipStats->getCapGJ();
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['duration'] = ($bonus/1000);
			self::$shipStats->setCapGJ($arr);
		}
		return true;
	}

//echo $modName." -> ".$effect." -> ".$bonus."<br />";
	if(strtolower($effect) == "chargegroup2") {
		if($groupID == "1156") {
			$arr = self::$shipStats->getTankBoost();
			if(strstr(strtolower($modName), "x-large")) {
				$arr[self::$moduleCount]['amount'] = 5;
			} else if(strstr(strtolower($modName), "large")) {
				$arr[self::$moduleCount]['amount'] = 7;
			} else if(strstr(strtolower($modName), "medium")) {
				$arr[self::$moduleCount]['amount'] = 9;
			} else if(strstr(strtolower($modName), "small")) {
				$arr[self::$moduleCount]['amount'] = 11;
			}
			self::$shipStats->setTankBoost($arr);
		} else if($groupID == "1199") {
			$arr = self::$shipStats->getTankBoost();
			if(strstr(strtolower($modName), "x-large")) {
				$arr[self::$moduleCount]['amount'] = 5;
			} else if(strstr(strtolower($modName), "large")) {
				$arr[self::$moduleCount]['amount'] = 7;
			} else if(strstr(strtolower($modName), "medium")) {
				$arr[self::$moduleCount]['amount'] = 9;
			} else if(strstr(strtolower($modName), "small")) {
				$arr[self::$moduleCount]['amount'] = 11;
			}
			self::$shipStats->setTankBoost($arr);
		}
	}

	if(strtolower($effect) == "capacitorneed") {
		if($groupID == "41" && strstr(strtolower($modName), "transporter")) {
			$arr = self::$shipStats->getTransCap();
			$arr[self::$moduleCount]['capNeeded'] = $bonus;
			self::$shipStats->setTransCap($arr);
		} else if($groupID == "325" && strstr(strtolower($modName), "remote") || strstr(strtolower($modName), "regenerative projector")) {
			$arr = self::$shipStats->getTransCap();
			$arr[self::$moduleCount]['capNeeded'] = $bonus;
			self::$shipStats->setTransCap($arr);
		} else if($groupID == "67" && strstr(strtolower($modName), "transfer")
		|| strstr(strtolower($modName), "power projector")
		|| strstr(strtolower($modName), "energy succor")
		|| strstr(strtolower($modName), "energy transmitter")
		|| strstr(strtolower($modName), "power conduit")) {
			$arr = self::$shipStats->getTransCap();
			$arr[self::$moduleCount]['capNeeded'] = $bonus;
			self::$shipStats->setTransCap($arr);
		}else if($groupID == "74" || $groupID == "53") {
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['capNeed'] = $bonus;
			self::$shipStats->setDamageGun($arr);
		} else if($groupID == "72") {
			$arr = self::$shipStats->getCapGJ();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['capacity'] = 1;
			$arr[self::$moduleCount]['capNeeded'] = $bonus;
			self::$shipStats->setCapGJ($arr);
		} else {
			$arr = self::$shipStats->getCapGJ();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['capNeeded'] = $bonus;
			self::$shipStats->setCapGJ($arr);
		}
		return true;
	}
	if(strtolower($effect) == "modulereactivationdelay") {

		if($groupID == "70_09") {
			$arr = self::$shipStats->getCapGJ();
			$arr[self::$moduleCount]['react'] = $bonus;
			self::$shipStats->setCapGJ($arr);
			return true;
		}

	}

	if(strtolower($effect) == "powertransferamount" && $groupID != "67") {
		$arr = self::$shipStats->getCapGJ();
		$arr[self::$moduleCount]['capAdd'] = $bonus;
		self::$shipStats->setCapGJ($arr);
		return true;
	}


	if(strtolower($effect) == "capacitorbonus") {

		if($groupID == "61") {
			if($bonus != 0) {
				self::$shipStats->setCapAmount(fittingTools::statOntoShip(self::$shipStats->getCapAmount(), $bonus, "+", $mode, 1));
			}
		} else  {
			$arr = self::$shipStats->getCapInj();
			$arr[self::$boosterPos[0]]['amount'] = $bonus;
			$arr[self::$boosterPos[0]]['vol'] = $capacity;
			self::$shipStats->setCapInj($arr);

			unset(self::$boosterPos[0]);
			self::$boosterPos = array_values(self::$boosterPos);
		}
		return true;
	}

	if(strtolower($effect) == "durationbonus") {

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
		return true;
	}


	if(strtolower($effect) == "capneedbonus") {

		$arr = self::$shipStats->getCapGJ();

		if($groupID == "909") {

			foreach($arr as $i => $value) {
				if($value['name'] == "Warp Disruption Field Generator I" && $value['capNeededBonus'] == null) {
					$arr[$i]['capNeededBonus'] = $bonus;
				}
			}

		} else if($groupID == "86") {
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$gunPosCap[0]]['capNeededBonus'] = $bonus;
			self::$shipStats->setDamageGun($arr);

			unset(self::$gunPosCap[0]);
			self::$gunPosCap = array_values(self::$gunPosCap);
		} else if($groupID == "773") {
			$arr = self::$shipStats->getTransCapEff();
			$arr[self::$moduleCount]['type'] = "armorTrans";
			$arr[self::$moduleCount]['amount'] = $bonus;
			$arr[self::$moduleCount]['neg'] = self::$armorRR;
			self::$shipStats->setTransCapEff($arr);
			self::$armorRR++;
		}
		return true;
	}




	if(strtolower($effect) == "speed") {

		if($groupID == "55" && (strpos($modName, "125") > -1 || strpos($modName, "150") > -1 || strpos($modName, "200") > -1 || strpos($modName, "250") > -1 || strpos($modName, "280") > -1)) {
			self::$gunPos[] = self::$moduleCount;
			self::$gunPosCap[] = self::$moduleCount;
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['rofP'] = ($bonus/1000);
			$arr[self::$moduleCount]['type'] = "Small";
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['techlevel'] = $techLevel;
			self::$shipStats->setDamageGun($arr);
		} else if($groupID == "55" && (strpos($modName, "Dual 180") > -1 || strpos($modName, "220") > -1 || strpos($modName, "425") > -1 || strpos($modName, "650") > -1 || strpos($modName, "720") > -1)) {
			self::$gunPos[] = self::$moduleCount;
			self::$gunPosCap[] = self::$moduleCount;
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['rofP'] = ($bonus/1000);
			$arr[self::$moduleCount]['type'] = "Medium";
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['techlevel'] = $techLevel;
			self::$shipStats->setDamageGun($arr);
		} else if($groupID == "55" && (strpos($modName, "Dual 425") > -1 || strpos($modName, "Dual 650") > -1 || strpos($modName, "800") > -1 || strpos($modName, "1200") > -1 || strpos($modName, "1400") > -1)) {
			self::$gunPos[] = self::$moduleCount;
			self::$gunPosCap[] = self::$moduleCount;
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['rofP'] = ($bonus/1000);
			$arr[self::$moduleCount]['type'] = "Large";
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['techlevel'] = $techLevel;
			self::$shipStats->setDamageGun($arr);
		} else if($groupID == "55" && (strpos($modName, "2500") > -1 || strpos($modName, "3500") > -1)) {
			self::$gunPos[] = self::$moduleCount;
			self::$gunPosCap[] = self::$moduleCount;
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['rofH'] = ($bonus/1000);
			$arr[self::$moduleCount]['type'] = "X-Large";
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['techlevel'] = $techLevel;
			self::$shipStats->setDamageGun($arr);
		}

		if($groupID == "74" && $mass == "500") {
			self::$gunPos[] = self::$moduleCount;
			self::$gunPosCap[] = self::$moduleCount;
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['rofH'] = ($bonus/1000);
			$arr[self::$moduleCount]['type'] = "Small";
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['techlevel'] = $techLevel;
			self::$shipStats->setDamageGun($arr);
		} else if($groupID == "74" && $mass == "1000") {
			self::$gunPos[] = self::$moduleCount;
			self::$gunPosCap[] = self::$moduleCount;
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['rofH'] = ($bonus/1000);
			$arr[self::$moduleCount]['type'] = "Medium";
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['techlevel'] = $techLevel;
			self::$shipStats->setDamageGun($arr);
		} else if($groupID == "74" && $mass == "2000") {
			self::$gunPos[] = self::$moduleCount;
			self::$gunPosCap[] = self::$moduleCount;
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['rofH'] = ($bonus/1000);
			$arr[self::$moduleCount]['type'] = "Large";
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['techlevel'] = $techLevel;
			self::$shipStats->setDamageGun($arr);
		} else if($groupID == "74" && $mass == "40000") {
			self::$gunPos[] = self::$moduleCount;
			self::$gunPosCap[] = self::$moduleCount;
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['rofH'] = ($bonus/1000);
			$arr[self::$moduleCount]['type'] = "X-Large";
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['techlevel'] = $techLevel;
			self::$shipStats->setDamageGun($arr);
		}


		if($groupID == "53" && $mass == "500") {
			self::$gunPos[] = self::$moduleCount;
			self::$gunPosCap[] = self::$moduleCount;
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['rofL'] = ($bonus/1000);
			$arr[self::$moduleCount]['type'] = "Small";
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['techlevel'] = $techLevel;
			self::$shipStats->setDamageGun($arr);
		} else if($groupID == "53" && $mass == "1000") {
			self::$gunPos[] = self::$moduleCount;
			self::$gunPosCap[] = self::$moduleCount;
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['rofL'] = ($bonus/1000);
			$arr[self::$moduleCount]['type'] = "Medium";
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['techlevel'] = $techLevel;
			self::$shipStats->setDamageGun($arr);
		} else if($groupID == "53" && $mass == "2000") {
			self::$gunPos[] = self::$moduleCount;
			self::$gunPosCap[] = self::$moduleCount;
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['rofL'] = ($bonus/1000);
			$arr[self::$moduleCount]['type'] = "Large";
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['techlevel'] = $techLevel;
			self::$shipStats->setDamageGun($arr);
		} else if($groupID == "53" && $mass == "40000") {
			self::$gunPos[] = self::$moduleCount;
			self::$gunPosCap[] = self::$moduleCount;
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['rofL'] = ($bonus/1000);
			$arr[self::$moduleCount]['type'] = "X-Large";
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['techlevel'] = $techLevel;
			self::$shipStats->setDamageGun($arr);
		}

		if($groupID == "507" || $groupID == "511" || $groupID == "509") {
			self::$gunPos[] = self::$moduleCount;
			self::$gunPosCap[] = self::$moduleCount;
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['rofM'] = ($bonus/1000);
			$arr[self::$moduleCount]['type'] = "Small";
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['techlevel'] = $techLevel;
			self::$shipStats->setDamageGun($arr);
		} else if($groupID == "510"  || $groupID == "771") {
			self::$gunPos[] = self::$moduleCount;
			self::$gunPosCap[] = self::$moduleCount;
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['rofM'] = ($bonus/1000);
			$arr[self::$moduleCount]['type'] = "Medium";
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['techlevel'] = $techLevel;
			self::$shipStats->setDamageGun($arr);
		} else if($groupID == "506" || $groupID == "508") {
			self::$gunPos[] = self::$moduleCount;
			self::$gunPosCap[] = self::$moduleCount;
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['rofM'] = ($bonus/1000);
			$arr[self::$moduleCount]['type'] = "Large";
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['techlevel'] = $techLevel;
			self::$shipStats->setDamageGun($arr);
		} else if($groupID == "524") {
			self::$gunPos[] = self::$moduleCount;
			self::$gunPosCap[] = self::$moduleCount;
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['rofM'] = ($bonus/1000);
			$arr[self::$moduleCount]['type'] = "X-Large";
			$arr[self::$moduleCount]['capacity'] = $capacity;
			$arr[self::$moduleCount]['techlevel'] = $techLevel;
			self::$shipStats->setDamageGun($arr);
		}
		return true;
	}



	if(strtolower($effect) == "damagemultiplier") {

		if($groupID == "55") {
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['damageP'] = $bonus;
			self::$shipStats->setDamageGun($arr);
		}

		if($groupID == "74") {
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['damageH'] = $bonus;
			self::$shipStats->setDamageGun($arr);
		}


		if($groupID == "53") {
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['damageL'] = $bonus;
			self::$shipStats->setDamageGun($arr);
		}

		if($groupID == "776") {
			$arr = self::$shipStats->getDamageModules();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['neg'] = self::$hybridDam++;
			$arr[self::$moduleCount]['damageH'] = ($bonus-1)*100;
			self::$shipStats->setDamageModules($arr);
		} else if($groupID == "775") {
			$arr = self::$shipStats->getDamageModules();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['neg'] = self::$lazerDam++;
			$arr[self::$moduleCount]['damageL'] = ($bonus-1)*100;
			self::$shipStats->setDamageModules($arr);
		} else if($groupID == "777") {
			$arr = self::$shipStats->getDamageModules();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['neg'] = self::$projectileDam++;
			$arr[self::$moduleCount]['damageP'] = ($bonus-1)*100;
			self::$shipStats->setDamageModules($arr);
		} else if($groupID == "779") {
			$arr = self::$shipStats->getDamageModules();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['neg'] = self::$missileDam++;
			$arr[self::$moduleCount]['damageM'] = ($bonus-1)*100;
			self::$shipStats->setDamageModules($arr);
		}

		if($groupID == "205") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			$arr = self::$shipStats->getDamageModules();
			$dex = "L";
			$arr[self::$moduleCount]['neg'] = self::$lazerDam++;
			self::$lazerRof++;
			$arr[self::$moduleCount]['damage'.$dex] = ($bonus-1)*100;
			self::$shipStats->setDamageModules($arr);
		}
		if($groupID == "302") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			$arr = self::$shipStats->getDamageModules();
			$dex = "H";
			$arr[self::$moduleCount]['neg'] = self::$hybridDam++;
			self::$hybridRof++;
			$arr[self::$moduleCount]['damage'.$dex] = ($bonus-1)*100;
			self::$shipStats->setDamageModules($arr);
		}
		if($groupID == "59") {
			if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}
			$arr = self::$shipStats->getDamageModules();
			$dex = "P";
			$arr[self::$moduleCount]['neg'] = self::$projectileDam++;
			self::$projectileRof++;
			$arr[self::$moduleCount]['damage'.$dex] = ($bonus-1)*100;
			self::$shipStats->setDamageModules($arr);
		}


		return true;
	}


	if($groupID == "72") {
		if(strtolower($effect) == "emdamage") {
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['emDamage'] = $bonus;
			self::$shipStats->setDamageGun($arr);
			return true;
		}
		if(strtolower($effect) == "explosivedamage") {
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['exDamage'] = $bonus;
			self::$shipStats->setDamageGun($arr);
			return true;
		}
		if(strtolower($effect) == "kineticdamage") {
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['kiDamage'] = $bonus;
			self::$shipStats->setDamageGun($arr);
			return true;
		}
		if(strtolower($effect) == "thermaldamage") {
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$moduleCount]['thDamage'] = $bonus;
			self::$shipStats->setDamageGun($arr);
			//return true;
		}
	} else {
		if(strtolower($effect) == "emdamage" ) {
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$gunPos[0]]['emDamage'] = $bonus;
			self::$shipStats->setDamageGun($arr);
			self::$gunDamageCounter++;
			return true;
		}
		if(strtolower($effect) == "explosivedamage") {
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$gunPos[0]]['exDamage'] = $bonus;
			self::$shipStats->setDamageGun($arr);
			self::$gunDamageCounter++;
			return true;
		}
		if(strtolower($effect) == "kineticdamage") {
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$gunPos[0]]['kiDamage'] = $bonus;
			self::$shipStats->setDamageGun($arr);
			self::$gunDamageCounter++;
			return true;
		}
		if(strtolower($effect) == "thermaldamage") {
			$arr = self::$shipStats->getDamageGun();
			$arr[self::$gunPos[0]]['thDamage'] = $bonus;
			self::$shipStats->setDamageGun($arr);
			self::$gunDamageCounter++;
			//return true;
		}

		if(self::$gunDamageCounter == 4) {
			if(is_array(self::$gunPos)) {
				$arr[self::$gunPos[0]]['ammoCap'] = $capacity;
				self::$shipStats->setDamageGun($arr);
				unset(self::$gunPos[0]);
				self::$gunPos = array_values(self::$gunPos);
				self::$gunDamageCounter = 0;
			}
			return true;
		}
	}


	if(strtolower($effect) == "speedmultiplier") {
		if($bonus < 1) {
				$bonus = (1-$bonus)*100;
			}

		if($groupID == "776") {
			$arr = self::$shipStats->getDamageModules();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['neg'] = self::$hybridRof++;
			$arr[self::$moduleCount]['rofH'] = $bonus;
			self::$shipStats->setDamageModules($arr);
		} else if($groupID == "775") {
			$arr = self::$shipStats->getDamageModules();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['neg'] = self::$lazerRof++;
			$arr[self::$moduleCount]['rofL'] = $bonus;
			self::$shipStats->setDamageModules($arr);
		} else if($groupID == "777") {
			$arr = self::$shipStats->getDamageModules();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['neg'] = self::$projectileRof++;
			$arr[self::$moduleCount]['rofP'] = $bonus;
			self::$shipStats->setDamageModules($arr);
		} else if($groupID == "779") {
			$arr = self::$shipStats->getDamageModules();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['neg'] = self::$missileRof++;
			$arr[self::$moduleCount]['rofM'] = $bonus;
			self::$shipStats->setDamageModules($arr);
		} else {

			if(strstr(strtolower($modName), "ballistic")
			|| strstr(strtolower($modName), "bolt array")) {
				$dex = "M";
			} else if(strstr(strtolower($modName), "magnetic field")
			|| strstr(strtolower($modName), "Gauss field")
			|| strstr(strtolower($modName), "insulated")
			|| strstr(strtolower($modName), "linear flux")
			|| strstr(strtolower($modName), "magnetic vortex")) {
				$dex = "H";
			} else if(strstr(strtolower($modName), "gyrostabilizer")
			 || strstr(strtolower($modName), "stabilization")
			 || strstr(strtolower($modName), "stabilized")
			 || strstr(strtolower($modName), "counterbalanced")
			 || strstr(strtolower($modName), "inertial suspensor")) {
				$dex = "P";
			} else if(strstr(strtolower($modName), "coolant")
			 || strstr(strtolower($modName), "heat sink")
			 || strstr(strtolower($modName), "thermal radiator")
			 || strstr(strtolower($modName), "heat exhaust")) {
				$dex = "L";
			}

			$arr = self::$shipStats->getDamageModules();
			$arr[self::$moduleCount]['name'] = $modName;
			$arr[self::$moduleCount]['rof'.$dex] = $bonus;
			self::$shipStats->setDamageModules($arr);
		}
		return true;
	}

	if(strtolower($effect) == "missiledamagemultiplierbonus") {

		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}

		$arr = self::$shipStats->getDamageModules();
		$arr[self::$moduleCount]['neg'] = self::$missileDam++;
		self::$missileRof++;
		$arr[self::$moduleCount]['damageM'] = ($bonus-1)*100;
		self::$shipStats->setDamageModules($arr);
		return true;
	}



	if(strtolower($effect) == "damagemultiplierbonus") {

		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}

		$arr = self::$shipStats->getDroneDamageMod();
		$arr[self::$moduleCount]['name'] = $modName;
		$arr[self::$moduleCount]['neg'] = self::$droneDam;
		$arr[self::$moduleCount]['rofdr'] = 0;
		$arr[self::$moduleCount]['damagedr'] = $bonus;

		if(strstr(strtolower($modName),"sentry")) {
			$arr[self::$moduleCount]['type'] = 3;
		} else {
			$arr[self::$moduleCount]['type'] = 0;
		}

		self::$droneDam++;
		self::$shipStats->setDroneDamageMod($arr);
		return true;
	}


	if(strtolower($effect) == "dronedamagebonus") {
		//echo "here";
		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}

		$arr = self::$shipStats->getDroneDamageMod();
		$arr[self::$moduleCount]['name'] = $modName;
		$arr[self::$moduleCount]['neg'] = self::$droneDam;
		$arr[self::$moduleCount]['rofdr'] = 0;
		$arr[self::$moduleCount]['damagedr'] = $bonus;
		$arr[self::$moduleCount]['type'] = 0;


		self::$droneDam++;
		self::$shipStats->setDroneDamageMod($arr);
		return true;
	}

	//echo $modName." - ".$effect." -- ".$bonus." -- ".$groupID."<br />";

	if(strtolower($effect) == "shieldrechargeratemultiplier" || strtolower($effect) == "rechargeratebonus") {
		//self::$shipStats->setShieldRecharge($row['value']/1000);

		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}

		if($groupID == "766") {
			if(!fittingTools::isReactor($modName)) {
				self::$shipStats->setShieldRecharge(fittingTools::statOntoShip(self::$shipStats->getShieldRecharge(), $bonus, "-", $mode, 1));
			}
		} else if($groupID == "774" || $groupID == "36" || $groupID == "770" || $groupID == "57") {
			self::$shipStats->setShieldRecharge(fittingTools::statOntoShip(self::$shipStats->getShieldRecharge(), $bonus, "-", $mode, 1));
		}
		return true;
	}

	if(strtolower($effect) == "shieldbonus") {
		//self::$shipStats->setShieldRecharge($row['value']/1000);

		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}

		if($groupID == "40") {
			$arr = self::$shipStats->getTankBoost();
			$arr[self::$moduleCount]['boost'] = $bonus;
			$arr[self::$moduleCount]['type'] = "shield";
			self::$shipStats->setTankBoost($arr);
		}

		if($groupID == "1156") {
			$arr = self::$shipStats->getTankBoost();
			$arr[self::$moduleCount]['boost'] = $bonus;
			$arr[self::$moduleCount]['type'] = "shield";
			$arr[self::$moduleCount]['icon'] = "105_4";
			self::$shipStats->setTankBoost($arr);
		}

		return true;
	}



	if(strtolower($effect) == "armordamageamount") {
		//self::$shipStats->setShieldRecharge($row['value']/1000);

		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}

		if($groupID == "62") {
			$arr = self::$shipStats->getTankBoost();
			$arr[self::$moduleCount]['boost'] = $bonus;
			$arr[self::$moduleCount]['type'] = "armor";
			self::$shipStats->setTankBoost($arr);
		}
		if($groupID == "1199") {
			$arr = self::$shipStats->getTankBoost();
			$arr[self::$moduleCount]['boost'] = $bonus;
			$arr[self::$moduleCount]['type'] = "armor";
			self::$shipStats->setTankBoost($arr);
		}
		return true;
	}



	if(strtolower($effect) == "structuredamageamount") {
		//self::$shipStats->setShieldRecharge($row['value']/1000);

		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}

		if($groupID == "63") {
			$arr = self::$shipStats->getTankBoost();
			$arr[self::$moduleCount]['boost'] = $bonus;
			$arr[self::$moduleCount]['type'] = "hull";
			self::$shipStats->setTankBoost($arr);
		}
		return true;
	}


	if(strtolower($effect) == "shieldboostmultiplier") {
		//self::$shipStats->setShieldRecharge($row['value']/1000);

		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}

		if($groupID == "338") {
			$arr = self::$shipStats->getTankAmpShield();
			$arr[self::$moduleCount]['boost'] = $bonus;
			$arr[self::$moduleCount]['type'] = "+";
			$arr[self::$moduleCount]['neg'] = self::$shieldAmp;
			self::$shipStats->setTankAmpShield($arr);
			self::$shieldAmp++;
		}/* else if($groupID == "01_01") {
			$arr = self::$shipStats->getTankAmpShield();
			$arr[self::$moduleCount]['boost'] = $bonus;
			$arr[self::$moduleCount]['type'] = "-";
			$arr[self::$moduleCount]['neg'] = self::$shieldAmp;
			self::$shipStats->setTankAmpShield($arr);
			self::$shieldAmp++;
		}*/
		return true;
	}


	if(strtolower($effect) == "repairbonus") {
		//self::$shipStats->setShieldRecharge($row['value']/1000);

		if($bonus < 1) {
			$bonus = (1-$bonus)*100;
		}

		$arr = self::$shipStats->getTankAmpArmor();
		$arr[self::$moduleCount]['boost'] = $bonus;
		$arr[self::$moduleCount]['type'] = "+";
		$arr[self::$moduleCount]['neg'] = self::$armorAmp;
		self::$shipStats->setTankAmpArmor($arr);
		self::$armorAmp++;
		return true;
	}

	if(strtolower($effect) == "upgradecost") {
		self::$shipStats->setCalUsed($bonus+self::$shipStats->getCalUsed());
		return true;
	}

	return false;
}


function isReactor($modName) {
	if(strstr(strtolower($modName),"reactor control")) {
		return true;
	} else if(strstr(strtolower($modName),"reaction control")) {
		return true;
	} else {
		return false;
	}
	return false;
}

function displayShipStats($param_ship, $param_shipimgx, $param_shipimgy) {
	//global $shipStats;
	global $smarty;

	$simpleurlheader = fittingTools::curPageURL();

	if(self::$shipStats->getShieldAmount() > 999999) {
		self::$shipStats->setShieldAmount(round((self::$shipStats->getShieldAmount()/1000000),1)." m");
	} else {
		self::$shipStats->setShieldAmount(round(self::$shipStats->getShieldAmount()));
	}
	if(self::$shipStats->getArmorAmount() > 999999) {
		self::$shipStats->setArmorAmount(round((self::$shipStats->getArmorAmount()/1000000),1)." m");
	} else {
		self::$shipStats->setArmorAmount(round(self::$shipStats->getArmorAmount()));
	}
	if(self::$shipStats->getHullAmount() > 999999) {
		self::$shipStats->setHullAmount(round((self::$shipStats->getHullAmount()/1000000),1)." m");
	} else {
		self::$shipStats->setHullAmount(round(self::$shipStats->getHullAmount()));
	}

	if(self::$shipStats->getEffectiveShield() > 999999) {
		self::$shipStats->setEffectiveShield(round((self::$shipStats->getEffectiveShield()/1000000),1)." m");
	} else {
		self::$shipStats->setEffectiveShield(round(self::$shipStats->getEffectiveShield()));
	}
	if(self::$shipStats->getEffectiveArmor() > 999999) {
		self::$shipStats->setEffectiveArmor(round((self::$shipStats->getEffectiveArmor()/1000000),1)." m");
	} else {
		self::$shipStats->setEffectiveArmor(round(self::$shipStats->getEffectiveArmor()));
	}
	if(self::$shipStats->getEffectiveHull() > 999999) {
		self::$shipStats->setEffectiveHull(round((self::$shipStats->getEffectiveHull()/1000000),1)." m");
	} else {
		self::$shipStats->setEffectiveHull(round(self::$shipStats->getEffectiveHull()));
	}


	if(self::$shipStats->getCapStable()) {
		$capSize = "capSizeGreen";
		$back = "shipcapright";
		$capAmountM =  round(self::$shipStats->getCapStatus());
		$perc = "";
	} else {
		$capSize = "capSizeDarkRed";
		$back = "shipcaprightRed";
		$capAmountM = self::$shipStats->getCapStatus();
		$perc = "";
	}
	$backdrop = "";

	if(config::get('ship_display_back')) {
		$smarty->assign('ship_display_back', config::get('ship_display_back'));
	} else {
		$smarty->assign('ship_display_back', "#222222");
	}

	//$smarty->assign('simpleurl', self::$simpleurl);
	if(self::$simpleurl) {
		$smarty->assign('simpleurlheader', $simpleurlheader);
	} else {
		$smarty->assign('simpleurlheader', config::get('cfg_kbhost'));
	}

	//$smarty->assign('backdrop', $backdrop);
	//$smarty->assign('left', $left);
	//$smarty->assign('top', $top);
	$smarty->assign('getShipIcon', self::$shipStats->getShipIcon());
	$smarty->assign('backdropImgType', $backdropImgType);
	$smarty->assign('modSlotsh', self::$modSlots[1]);
	$smarty->assign('modSlotsm', self::$modSlots[2]);
	$smarty->assign('modSlotsl', self::$modSlots[3]);
	$smarty->assign('modSlotsr', self::$modSlots[5]);
	$smarty->assign('modSlotss', self::$modSlots[7]);

	$smarty->assign('getPilotNameURL', self::$shipStats->getPilotNameURL());
	$smarty->assign('getPilotPort', self::$shipStats->getPilotPort());
	$smarty->assign('getPilotName', fittingTools::ShortenText(self::$shipStats->getPilotName(),20));
	$smarty->assign('getPilotCorpURL', self::$shipStats->getPilotCorpURL());
	$smarty->assign('getPilotCorpShort', fittingTools::ShortenText(self::$shipStats->getPilotCorp(),20));
	$smarty->assign('getPilotCorp', self::$shipStats->getPilotCorp());
	$smarty->assign('getPilotAllianceURL', self::$shipStats->getPilotAllianceURL());
	$smarty->assign('getPilotAlliance', fittingTools::ShortenText(self::$shipStats->getPilotAlliance(),20));
	$smarty->assign('getPilotDate', self::$shipStats->getPilotDate());
	$smarty->assign('getPilotShipURL', self::$shipStats->getPilotShipURL());
	$smarty->assign('getPilotShip', self::$shipStats->getPilotShip());
	$smarty->assign('getPilotShipClass', self::$shipStats->getPilotShipClass());
	$smarty->assign('getPilotLocURL', self::$shipStats->getPilotLocURL());
	$smarty->assign('getPilotLoc', self::$shipStats->getPilotLoc());
	$smarty->assign('getPilotLocReg', self::$shipStats->getPilotLocReg());
	$smarty->assign('getPilotLocSec', fittingTools::getSystemColour(self::$shipStats->getPilotLocSec()));

	$smarty->assign('totcal', self::$shipStats->getCalAmount());
	$smarty->assign('usedcal', self::$shipStats->getCalUsed());
	if(self::$shipStats->getCalAmount() == 0) {
		$percal = 0;
	} else {
		$percal = ((self::$shipStats->getCalUsed()/self::$shipStats->getCalAmount()));
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


	$smarty->assign('totcpu', round(self::$shipStats->getCpuAmount(),2));
	if(self::$shipStats->getCpuAmount() < self::$loadCPU) {
		if(self::$shipStats->getCpuAmount() == 0) {
			$percpu = 0;
		} else {
			$percpu = ((self::$shipStats->getCpuAmount()/self::$shipStats->getCpuAmount()));
		}
		$smarty->assign('usedcpu', "<span style='color:#b00000;'>".round(self::$loadCPU,2)."</span>");

	} else {
		$smarty->assign('usedcpu', round(self::$loadCPU,2));
		if(self::$shipStats->getCpuAmount() == 0) {
			$percpu = 0;
		} else {
			$percpu = ((self::$loadCPU/self::$shipStats->getCpuAmount()));
		}
	}


	//$percpu = ((self::$loadCPU/self::$shipStats->getCpuAmount()));
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



	$smarty->assign('totprg', round(self::$shipStats->getPrgAmount(),2));

	if(self::$shipStats->getPrgAmount() < self::$loadPower) {
		if(self::$shipStats->getPrgAmount() == 0) {
			$perprg = 0;
		} else {
			$perprg = ((self::$shipStats->getPrgAmount()/self::$shipStats->getPrgAmount()));
		}
		$smarty->assign('usedprg', "<span style='color:#b00000;'>".round(self::$loadPower,2)."</span>");

	} else {
		$smarty->assign('usedprg', round(self::$loadPower,2));

		if(self::$shipStats->getPrgAmount() == 0) {
			$perprg = 0;
		} else {
			$perprg = ((self::$loadPower/self::$shipStats->getPrgAmount()));
		}
	}

	//$perprg = ((self::$loadPower/self::$shipStats->getPrgAmount()));
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


	$smarty->assign('getCorpPort', self::$shipStats->getCorpPort());
	$smarty->assign('getAlliPort', self::$shipStats->getAlliPort());

	$smarty->assign('getEffectiveHp', number_format(self::$shipStats->getEffectiveHp()));
	$smarty->assign('getPilotDam', number_format(self::$shipStats->getPilotDam()));
	$smarty->assign('getPilotCos', self::$shipStats->getPilotCos());

	$smarty->assign('getShieldAmount', self::$shipStats->getShieldAmount());
	$smarty->assign('getArmorAmount', self::$shipStats->getArmorAmount());
	$smarty->assign('getHullAmount', self::$shipStats->getHullAmount());
	$smarty->assign('getEffectiveShield', self::$shipStats->getEffectiveShield());
	$smarty->assign('getEffectiveArmor', self::$shipStats->getEffectiveArmor());
	$smarty->assign('getEffectiveHull', self::$shipStats->getEffectiveHull());
	$smarty->assign('getTankType', self::$shipStats->getTankType());
	$smarty->assign('getSensorType', self::$shipStats->getSensorType());
	$smarty->assign('getTankAmount', round(self::$shipStats->getTankAmount()));
	$smarty->assign('getDamage', round(self::$shipStats->getDamage(), 1));
	$smarty->assign('getDroneDamage', round(self::$shipStats->getDroneDPS(), 1));
	$smarty->assign('getMissileDamage', round(self::$shipStats->getMissileDPS(), 1));
	$smarty->assign('getTurretDamage', round(self::$shipStats->getTurretDPS(), 1));
	$smarty->assign('getVolley', round(self::$shipStats->getVolley(), 1));
	$smarty->assign('getSensorAmount', round(self::$shipStats->getSensorAmount()));

	$smarty->assign('getTurUsed', round(self::$shipStats->getTurUsed()));
	$smarty->assign('getMisUsed', round(self::$shipStats->getMisUsed()));


	$smarty->assign('getShieldEMPS', fittingTools::returnPixelSize(self::$shipStats->getShieldEM(), 36));
	$smarty->assign('getShieldThPS', fittingTools::returnPixelSize(self::$shipStats->getShieldTh(), 36));
	$smarty->assign('getShieldKiPS', fittingTools::returnPixelSize(self::$shipStats->getShieldKi(), 36));
	$smarty->assign('getShieldExPS', fittingTools::returnPixelSize(self::$shipStats->getShieldEx(), 36));
	$smarty->assign('getShieldEM', round(self::$shipStats->getShieldEM()));
	$smarty->assign('getShieldTh', round(self::$shipStats->getShieldTh()));
	$smarty->assign('getShieldKi', round(self::$shipStats->getShieldKi()));
	$smarty->assign('getShieldEx', round(self::$shipStats->getShieldEx()));
	$smarty->assign('getShieldRecharge', fittingTools::toMinutesAndHours(round(self::$shipStats->getShieldRecharge())));


	$smarty->assign('getArmorEMPS', fittingTools::returnPixelSize(self::$shipStats->getArmorEM(), 36));
	$smarty->assign('getArmorThPS', fittingTools::returnPixelSize(self::$shipStats->getArmorTh(), 36));
	$smarty->assign('getArmorKiPS', fittingTools::returnPixelSize(self::$shipStats->getArmorKi(), 36));
	$smarty->assign('getArmorExPS', fittingTools::returnPixelSize(self::$shipStats->getArmorEx(), 36));
	$smarty->assign('getArmorEM', round(self::$shipStats->getArmorEM()));
	$smarty->assign('getArmorTh', round(self::$shipStats->getArmorTh()));
	$smarty->assign('getArmorKi', round(self::$shipStats->getArmorKi()));
	$smarty->assign('getArmorEx', round(self::$shipStats->getArmorEx()));

	$smarty->assign('getHullEMPS', fittingTools::returnPixelSize(self::$shipStats->getHullEM(), 36));
	$smarty->assign('getHullThPS', fittingTools::returnPixelSize(self::$shipStats->getHullTh(), 36));
	$smarty->assign('getHullKiPS', fittingTools::returnPixelSize(self::$shipStats->getHullKi(), 36));
	$smarty->assign('getHullExPS', fittingTools::returnPixelSize(self::$shipStats->getHullEx(), 36));
	$smarty->assign('getHullEM', round(self::$shipStats->getHullEM()));
	$smarty->assign('getHullTh', round(self::$shipStats->getHullTh()));
	$smarty->assign('getHullKi', round(self::$shipStats->getHullKi()));
	$smarty->assign('getHullEx', round(self::$shipStats->getHullEx()));


	$smarty->assign('getShipSpeed', round(self::$shipStats->getShipSpeed()));
	$smarty->assign('getMass', self::$shipStats->getMass());
	$smarty->assign('getWarpSpeed', self::$shipStats->getWarpSpeed());
	$smarty->assign('mwdActive', self::$mwdActive);
	$smarty->assign('mwdActiveAct', is_numeric(self::$mwdActive));
	$smarty->assign('abActive', self::$abActive);
	$smarty->assign('abActiveAct', is_numeric(self::$abActive));

	$smarty->assign('getSigRadius', round(self::$shipStats->getSigRadius()));
	$smarty->assign('mwdSigature', self::$mwdSigature);
	$smarty->assign('mwdSigatureAct', is_numeric(self::$mwdSigature));
	$smarty->assign('getScan', round(self::$shipStats->getScan()));
	$smarty->assign('getDistance', round(self::$shipStats->getDistance()/1000, 2));
	$smarty->assign('getTarget', round(self::$shipStats->getTarget()));




	$smarty->assign('back', $back);
	$smarty->assign('capSize', $capSize);
	$smarty->assign('getCapStatus', fittingTools::returnPixelSize(self::$shipStats->getCapStatus(), 245));
	$smarty->assign('capAmountMperc', $capAmountM.$perc);
	$smarty->assign('getCapStable', self::$shipStats->getCapStable());
	$smarty->assign('getCapAmount', round(self::$shipStats->getCapAmount()));
	$smarty->assign('getCapRecharge', fittingTools::toMinutesAndHours(round((self::$shipStats->getCapRecharge()/1000))));
	$smarty->assign('totalCapUse', round(fittingTools::totalCapUse(), 1));
	$smarty->assign('totalCapInjected', round((self::$shipStats->getCapRechargeRate()+fittingTools::totalCapInjected()), 1));

	$smarty->assign('modSlotsd', self::$modSlots[6]);
	$smarty->assign('modSlotsa', self::$modSlots[10]);
	$smarty->assign('displayOutput', fittingTools::displayOutput());

	return $smarty->fetch("../../../mods/ship_tool_kb/ship_display_tool.tpl");
}


function getSystemColour($system) {

	switch ($system)
	{
	   case "0.0":
		  return "<span style='color:#bb0000'>".$system."</span>";
		  break;
	   case "0.1":
		  return "<span style='color:#bb2000'>".$system."</span>";
		  break;
	   case "0.2":
		  return "<span style='color:#bb4000'>".$system."</span>";
		  break;
	   case "0.3":
		  return "<span style='color:#bb5000'>".$system."</span>";
		  break;
	   case "0.4":
		  return "<span style='color:#bb6000'>".$system."</span>";
		  break;
	   case "0.5":
		  return "<span style='color:#60bb00'>".$system."</span>";
		  break;
	   case "0.6":
		  return "<span style='color:#70cc00'>".$system."</span>";
		  break;
	   case "0.7":
		  return "<span style='color:#70dd00'>".$system."</span>";
		  break;
	   case "0.8":
		  return "<span style='color:#70ff00'>".$system."</span>";
		  break;
	   case "0.9":
		  return "<span style='color:#80ff40'>".$system."</span>";
		  break;
	   case "1.0":
		  return "<span style='color:#80ff80'>".$system."</span>";
		  break;
	   default:
		  return "<span style='color:#80ffff'>".$system."</span>";
		  break;
	}

}

function getTechLevel($tech, $meta, $name) {

	switch($tech) {
		case "1":
			if (($meta == 6)) // is a storyline item?
			{
				return "storyline";
			}
			elseif (($meta > 6) && ($meta < 10)) // is a faction item?
			{
				return "faction";
			}
		 	elseif (($meta > 10) && strstr($name,"Modified")) // or it's an officer?
			{
				return "officer";
			}
			elseif (($meta > 10) && (strstr($name,"-Type"))) // or it's just a deadspace item.
			{
				return "deadspace";
			}
			elseif ((
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

function ShortenText($text,$chars) {
  $count = strlen($text);
  $text = substr($text,0,$chars);
  if($count > $chars) {
		$text = $text." ...";
	}
	return $text;
}


function getSensorTypeImg($sensor_param) {

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

function returnPixelSize($amount_param, $pixil_param) {
	return ($pixil_param/100)*$amount_param;
}






function effectHP($hp, $em, $th, $ki, $ex) {
	return $hp / (((1-($em/100))+(1-($th/100))+(1-($ki/100))+(1-($ex/100)))/4);
}


function peakShieldRecharge($shieldCap, $shieldRec) {
	if($shieldRec == 0) {
		return 0;
	} else {
		return (($shieldCap/$shieldRec)*2.5);
	}
}

function tankAbleDPS($peakRegen, $em, $th, $ki, $ex) {
	return 4*$peakRegen/((1-($em/100))+(1-($th/100))+(1-($ki/100))+(1-($ex/100)));
}

function getReppedArmorPerCycle($reppedAmount, $reppedCycle) {
	return ($reppedAmount/$reppedCycle);
}

function isCapStable($capPS,$capUse) {

	if($capPS >= $capUse) {
		return true;
	} else {
		return false;
	}

}


function capUsage($capAmount, $capUsage, $capRechagePS, $capRecharge) {
	if($capRechagePS == 0) {
		return 0;
	} else {
		return ($capAmount/($capUsage-$capRechagePS));
	}

}

function capUsagePer($capAmount, $capUsage, $capRechagePS, $capRecharge) {
	if($capRechagePS == 0) {
		return 0;
	} else {
		//return 100-($capAmount/($capRechagePS-$capUsage))/($capAmount/100);
		//echo $capRecharge." ".($capAmount/$capUsage);
		$output = ($capRecharge/1000)-($capAmount/$capUsage);
		if($output > 100) {
			return 100;
		} else {
			return $output;
		}
	}
}



function toMinutesAndHours($seconds)
{
    $hoursmin = "";
    $hours = intval(intval($seconds) / 3600);
    if($hours > 0)
    {
        $hoursmin .= $hours."h ";
    }
    $minutes = bcmod((intval($seconds) / 60),60);
    if($hours > 0 || $minutes > 0)
    {
        $hoursmin .= $minutes."m ";
    }
    $seconds = bcmod(intval($seconds),60);
    $hoursmin .= $seconds."s";

    return $hoursmin;
}





function statOntoShip($stat_param, $numChange_param, $type_param, $mode_param, $negEffect) {

	if(!$negEffect) {
		$negEffect = 1;
	}

	if ($type_param=="+" && $mode_param=="%") {
		return ($stat_param+($stat_param*((fittingTools::stackingPenalties($negEffect)*$numChange_param)/100)));
	} else if ($type_param == "-" && $mode_param == "%") {
		return ($stat_param-($stat_param*((fittingTools::stackingPenalties($negEffect)*$numChange_param)/100)));
	} else if ($type_param == "+" && $mode_param == "+") {
		return ($stat_param + (fittingTools::stackingPenalties($negEffect)*$numChange_param));
	} else if ($type_param == "-" && $mode_param == "-") {
		return ($stat_param - (fittingTools::stackingPenalties($negEffect)*$numChange_param));
	}
	return 0;
}


function stackingPenalties($modNum) {
	return pow(0.5,pow((($modNum-1)/2.22292081),2));
}


function getLevel5SkillsPlus($skills_param,$base_param,$type_param,$negEffect) {

	if ($type_param=="+") {
		return (((1-($skills_param/100))*(fittingTools::stackingPenalties($negEffect)*$base_param))+$skills_param);
	} else {
		return (((1-($skills_param/100))*(fittingTools::stackingPenalties($negEffect)*$base_param))-$skills_param);
	}
}


function capInjector($capBooster, $storage, $size, $duration) {

	if($capBooster == 0 || $storage == 0 || $size == 0 || $duration == 0) {
		return 0;
	} else {
		return ($capBooster/((floor($storage/$size)*$duration)+10))*floor($storage/$size);
	}

}

function displayOutput() {

	$currentversion = "3.0";

	$title = "EvE Ship Display Tool (v$currentversion) developed by Spark\'s (Chris Sheppard)";
	$body = "Special thanks to Hans Glockenspiel (In-Game name) and kazhkaz (Region name coder) for helping out.<br><br><br>The Stats may not be 100% correct but maybe corrected so please contact me, my aim is to make sure that these stats are correct.<br><br>Any issues with the Display tool Please send Spark\'s in Game EvE Mail or go to the eve-dev forum to post: <a href=\'http://eve-id.net/forum/viewtopic.php?f=505&t=17295\' target=\'_blank\'>Here</a>. Please provide as much information as you can regarding the error. A link to the killmail would be great aswell.<br><br><a href=\'".fittingTools::curPageURL()."mods/ship_tool_kb/images/ShipInfo.jpg\' target=\'_blank\'>Click here for ship display</a><br><br>Change log:<br>3.0: Asorted fixes across all ships. Complete overhaul of the stat system. Clean up of the code.<br>2.9: Fixed Stealth bomber Powergrid issues. Fixed Acillary Shield bosters. Made ship images feed from Eve-online.<br>2.8: Fixed inferno modules and sorted new covert ops ships background positions<br>2.7: Some more fixes, especially to background colours. Now editable to match your background<br>2.6: Assorted fixes to CPU and power grid <br>2.5: New look simular to in game, better performance, CPU, Powergrid, Calibration, Final blow, Top damage, API verification, Turret and Missile added.<br>2.1: Fixed noob ships. Fixed display none base root sites. Again Chimeria Fixed. Classified Systems fixed. Damage 0 fixed. Images now use built in EDK4 OO Item to get image data. Simple URL with Ship mod Fixed.<br>2.0: No longer needs module images, gets them from Killboard. Works with EDK4. Added new ships to list<br>1.7: Fixing MWD and Bubble scripts<br>1.6: Cap injector fix<br>1.5: Minor Fixes<br>1.4: Fixed slot issue<br>1.3: Improved load performance again. Rework of code functionality. Page load much better than before. Changed the layout. Fixed ship positions. Fixed DPS on some ships. Added Super cap/Carrier/Dread drone counts with Drone Control link fix. Tweaks to the Tech III ships<br>1.2: Improved load performance<br>1.1: MWD stats fixed again<br>1.0: Add system colours. Fixed minior bugs<br>0.99: Added MWD icon. Added Region name on the kill display. Added new tags<br>0.98: Support for the new incusion ships<br>0.95: Fixed ship ID issue and realigned images<br>0.93: Fixed issue with Marauders<br>0.92: Fixed Citadel cruise launchers bug<br>0.9: Added admin support - Now you can select your panel background<br>0.75: Fixed % Bug on ships involved<br>0.73: Added Structure tank support<br>0.72: Fixed Drones displaying more than 5. Fixed portraits. Fixed some of the ship images<br>0.71: Added Chimeria and Hel images<br>0.7: Better support against errors. Added Missing mod slots. Minor fixes<br>0.6: better support for structures<br>0.55: Fixed Tech III propulsion and engineering sub systems. Minor Rework to Smartbombs and to Cap Batteries. Fixed Tech III ship images<br>0.51: Added support for Smart bombs. Fixed Info Screen<br>0.5: Displays Stats, icons and ship image with pilot stats<br><br><a href=\'http://www.elementstudio.co.uk\' target=\'_blank\'>Element Studio production</a></div>";
	$display .= "<html><head><link rel=\'stylesheet\' type=\'text/css\' href=\'".fittingTools::curPageURL()."mods/ship_tool_kb/style/style.css\' /><link rel=\'stylesheet\' type=\'text/css\' href=\'".fittingTools::curPageURL()."themes/default/default.css\' /><title>Ship Display Tool</title></head><body><div id=\'frame\'><div id=\'topImg\'></div><div id=\'titleBar\'>$title</div><div id=\'bodyBar\'>$body</div></body></html>";


	//$jscommand = "newwindow2=window.open('','','height=500,width=300,toolbar=no,scrollbars=yes');	var tmp = newwindow2.document;	tmp.write('<html><head><title>Ship Display Tool</title></head><body>".$display."</body></html>');tmp.close();";
	$jscommand = "newwindow2=window.open('','','height=401,width=501,toolbar=no,scrollbars=yes');
	var tmp = newwindow2.document;
	tmp.write('".$display."');
	tmp.close();";

	//return "" . $jscommand . "";
	return "javascript:" . htmlentities($jscommand, ENT_QUOTES) . " void(0);";
}


function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";

 $dirpop = explode("/",$_SERVER["REQUEST_URI"]);
 array_pop($dirpop);
if(self::$simpleurl) {
	array_pop($dirpop);
	array_pop($dirpop);
	array_pop($dirpop);
}

 $dir = implode("/",$dirpop);

 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$dir."/";
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$dir."/";
 }
 //$pageURL = config::get('cfg_kbhost');
 return $pageURL;
}

































function subsystemaddon($modname_param) {

	switch($modname_param) {
		case "Legion Defensive - Adaptive Augmenter":
			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = "Tech III";
			$arr[self::$shieldResistPos]['section'] = "armor";
			$arr[self::$shieldResistPos]['resist'] = "em";
			$arr[self::$shieldResistPos]['amount'] = (5*5);
			$arr[self::$shieldResistPos]['type'] = "+";
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;

			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = "Tech III";
			$arr[self::$shieldResistPos]['section'] = "armor";
			$arr[self::$shieldResistPos]['resist'] = "th";
			$arr[self::$shieldResistPos]['amount'] = (5*5);
			$arr[self::$shieldResistPos]['type'] = "+";
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;

			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = "Tech III";
			$arr[self::$shieldResistPos]['section'] = "armor";
			$arr[self::$shieldResistPos]['resist'] = "ki";
			$arr[self::$shieldResistPos]['amount'] = (5*5);
			$arr[self::$shieldResistPos]['type'] = "+";
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;

			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = "Tech III";
			$arr[self::$shieldResistPos]['section'] = "armor";
			$arr[self::$shieldResistPos]['resist'] = "ex";
			$arr[self::$shieldResistPos]['amount'] = (5*5);
			$arr[self::$shieldResistPos]['type'] = "+";
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;
		break;
		case "Legion Defensive - Augmented Plating":
			self::$shipStats->setArmorAmount(fittingTools::statOntoShip(self::$shipStats->getArmorAmount(), (10*5), "+", "%", 1));
		break;
		case "Legion Defensive - Nanobot Injector":
			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "armorBoost";
			$arr2[self::$moduleCount]['bonus'] = 10;//(10*5);
			$arr2[self::$moduleCount]['type'] = "+";
			self::$shipStats->setShipEffects($arr2);
		break;
		case "Legion Defensive - Warfare Processor":
			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "war_bonus";
			$arr2[self::$moduleCount]['bonus'] = 99;//(10*5);
			$arr2[self::$moduleCount]['type'] = "-";
			self::$shipStats->setShipEffects($arr2);
		break;

		case "Tengu Defensive - Adaptive Shielding":

			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = "Tech III";
			$arr[self::$shieldResistPos]['section'] = "shield";
			$arr[self::$shieldResistPos]['resist'] = "em";
			$arr[self::$shieldResistPos]['amount'] = (5*5);
			$arr[self::$shieldResistPos]['type'] = "+";
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;

			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = "Tech III";
			$arr[self::$shieldResistPos]['section'] = "shield";
			$arr[self::$shieldResistPos]['resist'] = "th";
			$arr[self::$shieldResistPos]['amount'] = (5*5);
			$arr[self::$shieldResistPos]['type'] = "+";
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;

			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = "Tech III";
			$arr[self::$shieldResistPos]['section'] = "shield";
			$arr[self::$shieldResistPos]['resist'] = "ki";
			$arr[self::$shieldResistPos]['amount'] = (5*5);
			$arr[self::$shieldResistPos]['type'] = "+";
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;

			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = "Tech III";
			$arr[self::$shieldResistPos]['section'] = "shield";
			$arr[self::$shieldResistPos]['resist'] = "ex";
			$arr[self::$shieldResistPos]['amount'] = (5*5);
			$arr[self::$shieldResistPos]['type'] = "+";
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;

		break;
		case "Tengu Defensive - Amplification Node":
			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "shieldBoost";
			$arr2[self::$moduleCount]['bonus'] = 10;//(10*5);
			$arr2[self::$moduleCount]['type'] = "+";
			self::$shipStats->setShipEffects($arr2);
		break;
		case "Tengu Defensive - Supplemental Screening":
			self::$shipStats->setShieldAmount(fittingTools::statOntoShip(self::$shipStats->getShieldAmount(), (10*5), "+", "%", 1));
		break;
		case "Tengu Defensive - Warfare Processor":
			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "war_bonus";
			$arr2[self::$moduleCount]['bonus'] = 99;//(10*5);
			$arr2[self::$moduleCount]['type'] = "-";
			self::$shipStats->setShipEffects($arr2);
		break;

		case "Loki Defensive - Adaptive Shielding":

			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = "Tech III";
			$arr[self::$shieldResistPos]['section'] = "shield";
			$arr[self::$shieldResistPos]['resist'] = "em";
			$arr[self::$shieldResistPos]['amount'] = (5*5);
			$arr[self::$shieldResistPos]['type'] = "+";
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;

			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = "Tech III";
			$arr[self::$shieldResistPos]['section'] = "shield";
			$arr[self::$shieldResistPos]['resist'] = "th";
			$arr[self::$shieldResistPos]['amount'] = (5*5);
			$arr[self::$shieldResistPos]['type'] = "+";
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;

			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = "Tech III";
			$arr[self::$shieldResistPos]['section'] = "shield";
			$arr[self::$shieldResistPos]['resist'] = "ki";
			$arr[self::$shieldResistPos]['amount'] = (5*5);
			$arr[self::$shieldResistPos]['type'] = "+";
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;

			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = "Tech III";
			$arr[self::$shieldResistPos]['section'] = "shield";
			$arr[self::$shieldResistPos]['resist'] = "ex";
			$arr[self::$shieldResistPos]['amount'] = (5*5);
			$arr[self::$shieldResistPos]['type'] = "+";
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;


		break;
		case "Loki Defensive - Adaptive Augmenter":
			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = "Tech III";
			$arr[self::$shieldResistPos]['section'] = "armor";
			$arr[self::$shieldResistPos]['resist'] = "em";
			$arr[self::$shieldResistPos]['amount'] = (5*5);
			$arr[self::$shieldResistPos]['type'] = "+";
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;

			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = "Tech III";
			$arr[self::$shieldResistPos]['section'] = "armor";
			$arr[self::$shieldResistPos]['resist'] = "th";
			$arr[self::$shieldResistPos]['amount'] = (5*5);
			$arr[self::$shieldResistPos]['type'] = "+";
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;

			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = "Tech III";
			$arr[self::$shieldResistPos]['section'] = "armor";
			$arr[self::$shieldResistPos]['resist'] = "ki";
			$arr[self::$shieldResistPos]['amount'] = (5*5);
			$arr[self::$shieldResistPos]['type'] = "+";
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;

			$arr = self::$shipStats->getShipResists();
			$arr[self::$shieldResistPos]['name'] = "Tech III";
			$arr[self::$shieldResistPos]['section'] = "armor";
			$arr[self::$shieldResistPos]['resist'] = "ex";
			$arr[self::$shieldResistPos]['amount'] = (5*5);
			$arr[self::$shieldResistPos]['type'] = "+";
			$arr[self::$shieldResistPos]['order'] = 3;
			self::$shipStats->setShipResists($arr);
			self::$shieldResistPos++;
		break;
		case "Loki Defensive - Amplification Node":
			self::$shipStats->setSigRadius(fittingTools::statOntoShip(self::$shipStats->getSigRadius(), (5*5), "+", "%", 1));
		break;
		case "Loki Defensive - Warfare Processor":
			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "war_bonus";
			$arr2[self::$moduleCount]['bonus'] = 99;//(10*5);
			$arr2[self::$moduleCount]['type'] = "-";
			self::$shipStats->setShipEffects($arr2);
		break;

		case "Proteus Defensive - Adaptive Augmenter":
				//$atttpye = "armorResists";$type = true;$tanktype = "armor";

				$arr = self::$shipStats->getShipResists();
				$arr[self::$shieldResistPos]['name'] = "Tech III";
				$arr[self::$shieldResistPos]['section'] = "armor";
				$arr[self::$shieldResistPos]['resist'] = "em";
				$arr[self::$shieldResistPos]['amount'] = (5*5);
				$arr[self::$shieldResistPos]['type'] = "+";
				$arr[self::$shieldResistPos]['order'] = 3;
				self::$shipStats->setShipResists($arr);
				self::$shieldResistPos++;

				$arr = self::$shipStats->getShipResists();
				$arr[self::$shieldResistPos]['name'] = "Tech III";
				$arr[self::$shieldResistPos]['section'] = "armor";
				$arr[self::$shieldResistPos]['resist'] = "th";
				$arr[self::$shieldResistPos]['amount'] = (5*5);
				$arr[self::$shieldResistPos]['type'] = "+";
				$arr[self::$shieldResistPos]['order'] = 3;
				self::$shipStats->setShipResists($arr);
				self::$shieldResistPos++;

				$arr = self::$shipStats->getShipResists();
				$arr[self::$shieldResistPos]['name'] = "Tech III";
				$arr[self::$shieldResistPos]['section'] = "armor";
				$arr[self::$shieldResistPos]['resist'] = "ki";
				$arr[self::$shieldResistPos]['amount'] = (5*5);
				$arr[self::$shieldResistPos]['type'] = "+";
				$arr[self::$shieldResistPos]['order'] = 3;
				self::$shipStats->setShipResists($arr);
				self::$shieldResistPos++;

				$arr = self::$shipStats->getShipResists();
				$arr[self::$shieldResistPos]['name'] = "Tech III";
				$arr[self::$shieldResistPos]['section'] = "armor";
				$arr[self::$shieldResistPos]['resist'] = "ex";
				$arr[self::$shieldResistPos]['amount'] = (5*5);
				$arr[self::$shieldResistPos]['type'] = "+";
				$arr[self::$shieldResistPos]['order'] = 3;
				self::$shipStats->setShipResists($arr);
				self::$shieldResistPos++;

		break;
		case "Proteus Defensive - Nanobot Injector":
			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "armorboost";
			$arr2[self::$moduleCount]['bonus'] = (10*5);
			$arr2[self::$moduleCount]['type'] = "+";
			self::$shipStats->setShipEffects($arr2);
		break;
		case "Proteus Defensive - Augmented Plating":
			self::$shipStats->setArmorAmount(fittingTools::statOntoShip(self::$shipStats->getArmorAmount(), (10*5), "+", "%", 1));
		break;
		case "Proteus Defensive - Warfare Processor":
			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "war_bonus";
			$arr2[self::$moduleCount]['bonus'] = 99;//(10*5);
			$arr2[self::$moduleCount]['type'] = "-";
			self::$shipStats->setShipEffects($arr2);
		break;
	}

if(strtolower($effect) == "scanradarstrength" && $bonus > 0 && $moduleLevel == 7) {
		self::$shipStats->setSensorType(fittingTools::getSensorTypeImg('radar'));
		self::$shipStats->setSensorAmount($bonus);
	}
	if(strtolower($effect) == "scanladarstrength" && $bonus > 0 && $moduleLevel == 7) {
		self::$shipStats->setSensorType(fittingTools::getSensorTypeImg('ladar'));
		self::$shipStats->setSensorAmount($bonus);
	}
	if(strtolower($effect) == "scanmagnetometricstrength" && $bonus > 0 && $moduleLevel == 7) {
		self::$shipStats->setSensorType(fittingTools::getSensorTypeImg('magnetometric'));
		self::$shipStats->setSensorAmount($bonus);
	}
	if(strtolower($effect) == "scangravimetricstrength" && $bonus > 0 && $moduleLevel == 7) {
		self::$shipStats->setSensorType(fittingTools::getSensorTypeImg('gravimetric'));
		self::$shipStats->setSensorAmount($bonus);
	}


	switch($modname_param) {
		case "Legion Electronics - Dissolution Sequencer":
			self::$shipStats->setSensorAmount(fittingTools::statOntoShip(self::$shipStats->getSensorAmount(), (15*5), "+", "%", 1));
			self::$shipStats->setDistance(fittingTools::statOntoShip(self::$shipStats->getDistance(), (5*5), "+", "%", 1));
		break;
		case "Legion Electronics - Tactical Targeting Network":
			self::$shipStats->setScan(fittingTools::statOntoShip(self::$shipStats->getScan(), (15*5), "+", "%", 1));
		break;
		case "Legion Electronics - Emergent Locus Analyzer":
		break;
		case "Legion Electronics - Energy Parasitic Complex":
		break;

		case "Tengu Electronics - Dissolution Sequencer":
			self::$shipStats->setSensorAmount(fittingTools::statOntoShip(self::$shipStats->getSensorAmount(), (15*5), "+", "%", 1));
			self::$shipStats->setDistance(fittingTools::statOntoShip(self::$shipStats->getDistance(), (5*5), "+", "%", 1));
		break;
		case "Tengu Electronics - Obfuscation Manifold":
		break;
		case "Tengu Electronics - CPU Efficiency Gate":
		break;
		case "Tengu Electronics - Emergent Locus Analyzer":
		break;

		case "Proteus Electronics - Dissolution Sequencer":
			self::$shipStats->setSensorAmount(fittingTools::statOntoShip(self::$shipStats->getSensorAmount(), (15*5), "+", "%", 1));
			self::$shipStats->setDistance(fittingTools::statOntoShip(self::$shipStats->getDistance(), (5*5), "+", "%", 1));
		break;
		case "Proteus Electronics - Friction Extension Processor":
		break;
		case "Proteus Electronics - CPU Efficiency Gate":
		break;
		case "Proteus Electronics - Emergent Locus Analyzer":
		break;

		case "Loki Electronics - Tactical Targeting Network":
			self::$shipStats->setScan(fittingTools::statOntoShip(self::$shipStats->getScan(), (15*5), "+", "%", 1));
		break;
		case "Loki Electronics - Dissolution Sequencer":
			self::$shipStats->setSensorAmount(fittingTools::statOntoShip(self::$shipStats->getSensorAmount(), (15*5), "+", "%", 1));
			self::$shipStats->setDistance(fittingTools::statOntoShip(self::$shipStats->getDistance(), (5*5), "+", "%", 1));
		break;
		case "Loki Electronics - Immobility Drivers":
		break;
		case "Loki Electronics - Emergent Locus Analyzer":
		break;

	}

	switch($modname_param) {
		case "Legion Offensive - Drone Synthesis Projector":
			self::$moduleCount++;
			$arr = self::$shipStats->getShipEffects();
			$arr[self::$moduleCount]['effect'] = "turretCap";
			$arr[self::$moduleCount]['bonus'] = 10;
			$arr[self::$moduleCount]['type'] = "-";
			self::$shipStats->setShipEffects($arr);



			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "damagedr";
			$arr2[self::$moduleCount]['bonus'] = 10;
			$arr2[self::$moduleCount]['type'] = "+";
			self::$shipStats->setShipEffects($arr2);
		break;
		case "Legion Offensive - Assault Optimization":
			self::$moduleCount++;
			$arr = self::$shipStats->getShipEffects();
			$arr[self::$moduleCount]['effect'] = "damageM";
			$arr[self::$moduleCount]['bonus'] = (5*5);
			$arr[self::$moduleCount]['type'] = "+";
			self::$shipStats->setShipEffects($arr);



			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "rofM";
			$arr2[self::$moduleCount]['bonus'] = 5;
			$arr2[self::$moduleCount]['type'] = "-";
			self::$shipStats->setShipEffects($arr2);
		break;
		case "Legion Offensive - Liquid Crystal Magnifiers":
			self::$moduleCount++;
			$arr = self::$shipStats->getShipEffects();
			$arr[self::$moduleCount]['effect'] = "turretCap";
			$arr[self::$moduleCount]['bonus'] = 10;
			$arr[self::$moduleCount]['type'] = "-";
			self::$shipStats->setShipEffects($arr);



			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "damageL";
			$arr2[self::$moduleCount]['bonus'] = 10;
			$arr2[self::$moduleCount]['type'] = "+";
			self::$shipStats->setShipEffects($arr2);
		break;
		case "Legion Offensive - Covert Reconfiguration":
			self::$moduleCount++;
			$arr = self::$shipStats->getShipEffects();
			$arr[self::$moduleCount]['effect'] = "turretCap";
			$arr[self::$moduleCount]['bonus'] = 10;
			$arr[self::$moduleCount]['type'] = "-";
			self::$shipStats->setShipEffects($arr);

			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "covert_cloak";
			$arr2[self::$moduleCount]['bonus'] = 100;//(10*5);
			$arr2[self::$moduleCount]['type'] = "-";
			self::$shipStats->setShipEffects($arr2);
		break;

		case "Tengu Offensive - Accelerated Ejection Bay":


			self::$moduleCount++;
			$arr = self::$shipStats->getShipEffects();
			$arr[self::$moduleCount]['effect'] = "damageki";
			$arr[self::$moduleCount]['bonus'] = 5;
			$arr[self::$moduleCount]['type'] = "+";
			self::$shipStats->setShipEffects($arr);



			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "rofM";
			$arr2[self::$moduleCount]['bonus'] = 7.5;
			$arr2[self::$moduleCount]['type'] = "-";
			self::$shipStats->setShipEffects($arr2);


		break;
		case "Tengu Offensive - Rifling Launcher Pattern":
			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "rofM";
			$arr2[self::$moduleCount]['bonus'] = 5;
			$arr2[self::$moduleCount]['type'] = "-";
			self::$shipStats->setShipEffects($arr2);
		break;
		case "Tengu Offensive - Magnetic Infusion Basin":
			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "damageH";
			$arr2[self::$moduleCount]['bonus'] = 5;
			$arr2[self::$moduleCount]['type'] = "+";
			self::$shipStats->setShipEffects($arr2);
		break;
		case "Tengu Offensive - Covert Reconfiguration":
			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "rofM";
			$arr2[self::$moduleCount]['bonus'] = 5;
			$arr2[self::$moduleCount]['type'] = "-";
			self::$shipStats->setShipEffects($arr2);

			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "covert_cloak";
			$arr2[self::$moduleCount]['bonus'] = 100;//(10*5);
			$arr2[self::$moduleCount]['type'] = "-";
			self::$shipStats->setShipEffects($arr2);
		break;

		case "Proteus Offensive - Dissonic Encoding Platform":
			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "damageH";
			$arr2[self::$moduleCount]['bonus'] = 10;
			$arr2[self::$moduleCount]['type'] = "+";
			self::$shipStats->setShipEffects($arr2);
		break;
		case "Proteus Offensive - Hybrid Propulsion Armature":
			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "damageH";
			$arr2[self::$moduleCount]['bonus'] = 10;
			$arr2[self::$moduleCount]['type'] = "+";
			self::$shipStats->setShipEffects($arr2);
		break;
		case "Proteus Offensive - Drone Synthesis Projector":
			self::$moduleCount++;
			$arr = self::$shipStats->getShipEffects();
			$arr[self::$moduleCount]['effect'] = "damageH";
			$arr[self::$moduleCount]['bonus'] = 5;
			$arr[self::$moduleCount]['type'] = "+";
			self::$shipStats->setShipEffects($arr);



			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "damagedr";
			$arr2[self::$moduleCount]['bonus'] = 10;
			$arr2[self::$moduleCount]['type'] = "+";
			self::$shipStats->setShipEffects($arr2);
		break;
		case "Proteus Offensive - Covert Reconfiguration":
			self::$moduleCount++;
			$arr = self::$shipStats->getShipEffects();
			$arr[self::$moduleCount]['effect'] = "damageH";
			$arr[self::$moduleCount]['bonus'] = 5;
			$arr[self::$moduleCount]['type'] = "+";
			self::$shipStats->setShipEffects($arr);

			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "covert_cloak";
			$arr2[self::$moduleCount]['bonus'] = 100;//(10*5);
			$arr2[self::$moduleCount]['type'] = "-";
			self::$shipStats->setShipEffects($arr2);
		break;

		case "Loki Offensive - Turret Concurrence Registry":
			self::$moduleCount++;
			$arr = self::$shipStats->getShipEffects();
			$arr[self::$moduleCount]['effect'] = "damageP";
			$arr[self::$moduleCount]['bonus'] = 10;
			$arr[self::$moduleCount]['type'] = "+";
			self::$shipStats->setShipEffects($arr);
		break;
		case "Loki Offensive - Projectile Scoping Array":
			self::$moduleCount++;
			$arr = self::$shipStats->getShipEffects();
			$arr[self::$moduleCount]['effect'] = "rofP";
			$arr[self::$moduleCount]['bonus'] = 7.5;
			$arr[self::$moduleCount]['type'] = "-";
			self::$shipStats->setShipEffects($arr);
		break;
		case "Loki Offensive - Hardpoint Efficiency Configuration":
			self::$moduleCount++;
			$arr = self::$shipStats->getShipEffects();
			$arr[self::$moduleCount]['effect'] = "rofP";
			$arr[self::$moduleCount]['bonus'] = 7.5;
			$arr[self::$moduleCount]['type'] = "-";
			self::$shipStats->setShipEffects($arr);

			self::$moduleCount++;
			$arr = self::$shipStats->getShipEffects();
			$arr[self::$moduleCount]['effect'] = "rofM";
			$arr[self::$moduleCount]['bonus'] = 7.5;
			$arr[self::$moduleCount]['type'] = "-";
			self::$shipStats->setShipEffects($arr);
		break;
		case "Loki Offensive - Covert Reconfiguration":
			self::$moduleCount++;
			$arr = self::$shipStats->getShipEffects();
			$arr[self::$moduleCount]['effect'] = "rofP";
			$arr[self::$moduleCount]['bonus'] = 5;
			$arr[self::$moduleCount]['type'] = "-";
			self::$shipStats->setShipEffects($arr);

			self::$moduleCount++;
			$arr2 = self::$shipStats->getShipEffects();
			$arr2[self::$moduleCount]['effect'] = "covert_cloak";
			$arr2[self::$moduleCount]['bonus'] = 100;//(10*5);
			$arr2[self::$moduleCount]['type'] = "-";
			self::$shipStats->setShipEffects($arr2);
		break;

	}


	switch($modname_param) {
		case "Legion Propulsion - Chassis Optimization":
			self::$shipStats->setShipSpeed(fittingTools::statOntoShip(self::$shipStats->getShipSpeed(), (5*5), "+", "%", 1));
		break;
		case "Legion Propulsion - Fuel Catalyst":
			if(self::$shipStats->getABBoost()) {
				self::$shipStats->setAbT3Boost(fittingTools::statOntoShip(self::$shipStats->getAbT3Boost(), (10*5), "+", "+", 1));
				$bonus = fittingTools::statOntoShip(self::$shipStats->getABBoost(), self::$shipStats->getAbT3Boost(), "+", "%", 0);
				self::$shipStats->setABBoost($bonus);
			} else {
				self::$shipStats->setAbT3Boost(fittingTools::statOntoShip(self::$shipStats->getAbT3Boost(), (10*5), "+", "+", 1));
			}
		break;
		case "Legion Propulsion - Wake Limiter":

			if(self::$shipStats->getMwdSig()) {
				self::$shipStats->setMwdT3Sig(fittingTools::statOntoShip(self::$shipStats->getMwdT3Sig(), (5*5), "+", "+", 1));
				$bonus = fittingTools::statOntoShip(self::$shipStats->getMwdSig(), self::$shipStats->getMwdT3Sig(), "-", "%", 1);
				self::$shipStats->setMwdSig($bonus);
			} else {
				self::$shipStats->setMwdT3Sig(fittingTools::statOntoShip(self::$shipStats->getMwdT3Sig(), (5*5), "+", "+", 1));
			}

		break;
		case "Legion Propulsion - Interdiction Nullifier":
		break;


		case "Tengu Propulsion - Fuel Catalyst":
			if(self::$shipStats->getABBoost()) {
				self::$shipStats->setAbT3Boost(fittingTools::statOntoShip(self::$shipStats->getAbT3Boost(), (10*5), "+", "+", 1));
				$bonus = fittingTools::statOntoShip(self::$shipStats->getABBoost(), self::$shipStats->getAbT3Boost(), "+", "%", 0);
				self::$shipStats->setABBoost($bonus);
			} else {
				self::$shipStats->setAbT3Boost(fittingTools::statOntoShip(self::$shipStats->getAbT3Boost(), (10*5), "+", "+", 1));
			}
		break;
		case "Tengu Propulsion - Intercalated Nanofibers":
		break;
		case "Tengu Propulsion - Gravitational Capacitor":
		break;
		case "Tengu Propulsion - Interdiction Nullifier":
		break;

		case "Proteus Propulsion - Wake Limiter":
			if(self::$shipStats->getMwdSig()) {
				self::$shipStats->setMwdT3Sig(fittingTools::statOntoShip(self::$shipStats->getMwdT3Sig(), (5*5), "+", "+", 1));
				$bonus = fittingTools::statOntoShip(self::$shipStats->getMwdSig(), self::$shipStats->getMwdT3Sig(), "-", "%", 1);
				self::$shipStats->setMwdSig($bonus);
			} else {
				self::$shipStats->setMwdT3Sig(fittingTools::statOntoShip(self::$shipStats->getMwdT3Sig(), (5*5), "+", "+", 1));
			}
		break;
		case "Proteus Propulsion - Localized Injectors":
			self::$shipStats->setSpeedT3Cap(fittingTools::statOntoShip(self::$shipStats->getSpeedT3Cap(), (15*5), "+", "+", 1));
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
			self::$shipStats->setShipSpeed(fittingTools::statOntoShip(self::$shipStats->getShipSpeed(), (5*5), "+", "%", 1));
		break;
		case "Loki Propulsion - Fuel Catalyst":

			if(self::$shipStats->getABBoost()) {
				self::$shipStats->setAbT3Boost(fittingTools::statOntoShip(self::$shipStats->getAbT3Boost(), (10*5), "+", "+", 1));
				$bonus = fittingTools::statOntoShip(self::$shipStats->getABBoost(), self::$shipStats->getAbT3Boost(), "+", "%", 0);
				self::$shipStats->setABBoost($bonus);
			} else {
				self::$shipStats->setAbT3Boost(fittingTools::statOntoShip(self::$shipStats->getAbT3Boost(), (10*5), "+", "+", 1));
			}

		break;
	}



	switch($modname_param) {
		case "Tengu Engineering - Augmented Capacitor Reservoir":
			self::$shipStats->setCapAmount(fittingTools::statOntoShip(self::$shipStats->getCapAmount(), (5*5), "+", "%", 1));
		break;
		case "Tengu Engineering - Capacitor Regeneration Matrix":
			self::$shipStats->setCapRecharge(fittingTools::statOntoShip(self::$shipStats->getCapRecharge(), (5*5), "-", "%", 1));
		break;
		case "Tengu Engineering - Supplemental Coolant Injector":
		break;
		case "Tengu Engineering - Power Core Multiplier":
		break;

		case "Proteus Engineering - Augmented Capacitor Reservoir":
		break;
		case "Proteus Engineering - Capacitor Regeneration Matrix":
			self::$shipStats->setCapRecharge(fittingTools::statOntoShip(self::$shipStats->getCapRecharge(), (5*5), "-", "%", 1));
		break;
		case "Proteus Engineering - Supplemental Coolant Injector":
		break;
		case "Proteus Engineering - Power Core Multiplier":
		break;

		case "Loki Engineering - Power Core Multiplier":
		break;
		case "Loki Engineering - Augmented Capacitor Reservoir":
			self::$shipStats->setCapAmount(fittingTools::statOntoShip(self::$shipStats->getCapAmount(), (5*5), "+", "%", 1));
		break;
		case "Loki Engineering - Capacitor Regeneration Matrix":
			self::$shipStats->setCapRecharge(fittingTools::statOntoShip(self::$shipStats->getCapRecharge(), (5*5), "-", "%", 1));
		break;
		case "Loki Engineering - Supplemental Coolant Injector":
		break;

		case "Legion Engineering - Power Core Multiplier":
		break;
		case "Legion Engineering - Augmented Capacitor Reservoir":
			self::$shipStats->setCapAmount(fittingTools::statOntoShip(self::$shipStats->getCapAmount(), (5*5), "+", "%", 1));
		break;
		case "Legion Engineering - Capacitor Regeneration Matrix":
			self::$shipStats->setCapRecharge(fittingTools::statOntoShip(self::$shipStats->getCapRecharge(), (5*5), "-", "%", 1));
		break;
		case "Legion Engineering - Supplemental Coolant Injector":
		break;
	}

}
















}







//echo $fitter;
?>
