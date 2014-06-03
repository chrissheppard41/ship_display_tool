<?php
/**
 *  The Shipstats class
 *  Holds all the ship stats to be displayed
 *
 *	@author Chris Sheppard (Spark's)
 *  @version 1.0
 *  Code in this document belongs to:
 *  @copyright Chris Sheppard
 *  EvE online own all module/ships/other details rights
 *  Runs within EvE Dev killboard
 *
  */
class Shipstats
{
	private $sAm,
			$sEmr,
			$sThr,
			$sKir,
			$sExr,
			$sRe,
			$aAm,
			$aEmr,
			$aThr,
			$aKir,
			$aExr,
			$hAm,
			$hEmr,
			$hThr,
			$hKir,
			$hExr,
			$cAm,
			$cRe,
			$oSp,
			$oSe,
			$oTy,
			$oTa,
			$oDi,
			$oSc,
			$oSi,
			$oMa,
			$rSize,
			$shipSlots,


			$effectiveShield,
			$effectiveArmor,
			$effectiveHull,
			$effectiveHp,

			$tankAmount,
			$tankType,

			$capRechargeRate,
			$capStatus,
			$capStable,

			$pilotName,
			$pilotCorp,
			$pilotAlliance,
			$pilotShip,
			$pilotLoc,
			$pilotLocReg,
			$pilotLocSec,
			$pilotDate,
			$pilotDam,
			$pilotCos,
			$pilotShipClass,

			$pilotPort,
			$pilotNameURL,
			$pilotCorpURL,
			$pilotAllianceURL,
			$pilotShipURL,
			$pilotLocURL,

			$shipIcon,

			$shipDesc,
			$shipEffects,

			$isMWD,
			$mwdBoost,
			$mwdSig,
			$mwdThrust,
			$mwdMass,
			$mwdSigRed,

			$isAB,
			$abBoost,
			$abThrust,
			$abMass,

			$abT3Boost,
			$mwdT3Boost,
			$mwdT3Sig,
			$mwdT3Cap,

			$capInj,

			$capGJ,

			$damageMod,
			$damageGun,
			$damage,
			$volley,
			$damageModules,
			$droneDamage,
			$droneDamageMod,

			$transCap,
			$transCapEff,

			$senBoost,

			$sigRadiusBoost,

			$tankTypeofship,
			$tankBoost,
			$tankAmpShield,
			$tankAmpArmor,

			$shipResists,


			$droneDPS,
			$missileDPS,
			$turretDPS,

			$warpSpeed,

			$corpPort,
			$alliPort,

			$cal_amount,
			$cal_used,

			$cpu_amount,
			$cpu_used,

			$prg_amount,
			$prg_used,

			$tur_amount,
			$tur_used,

			$mis_amount,
			$mis_used;

	public 	$speedV 		= 1,
			$speedB 		= 1,
			$sigRadius 		= 1,
			$shieldHpRed 	= 1,
			$structure 		= 1,
			$emArmor 		= 1,
			$thArmor 		= 1,
			$kiArmor 		= 1,
			$exArmor 		= 1,

			$emShield 		= 1,
			$thShield 		= 1,
			$kiShield 		= 1,
			$exShield 		= 1,

			$emHull 		= 1,
			$thHull 		= 1,
			$kiHull 		= 1,
			$exHull 		= 1,

			$scanStrength 	= 1,

			$missileDam 	= 1,
			$hybridDam 		= 1,
			$projectileDam 	= 1,
			$lazerDam 		= 1,

			$missileRof 	= 1,
			$hybridRof 		= 1,
			$projectileRof 	= 1,
			$lazerRof 		= 1,

			$mwdSigature 	= "No MWD",
			$mwdActive 		= "No MWD",
			$abActive 		= "No AB",

			$moduleCount 	= 0,
			$boosterPos,
			$gunPos,
			$gunPosCap,
			$gunDamageCounter = 0,

			$sensorbooster,
			$scan 			= 1,
			$range 			= 1,

			$warpStab 		= 1,
			$warpStabScan 	= 1,
			$interstab 		= 1,
			$sheildHPPD 	= 1,

			$srBooster 		= array(),

			$armorKiArr,
			$shieldAmp 		= 1,
			$armorDur 		= array(),
			$shieldDur 		= array(),
			$armorAmp 		= 1,
			$shieldAmpCap 	= 1,

			$shieldResistPos = 0,

			$droneDam 		= 1,
			$droneMax 		= 5,
			$droneAdd 		= 0,
			$droneArr 		= array(),
			$armorRR 		= 1,

			$capMultiEff 	= 1,

			$simpleurl,

			$extid,

			$loadCPU 		= 0,
			$loadPower 		= 0,

			$loadCPUAdd 	= array(),
			$loadPowerAdd 	= array();

	function __construct() {
		$this->sAm = 0;
		$this->sEmr = 0;
		$this->sThr = 0;
		$this->sKir = 0;
		$this->sExr = 0;
		$this->sRe = 0;
		$this->aAm = 0;
		$this->aEmr = 0;
		$this->aThr = 0;
		$this->aKir = 0;
		$this->aExr = 0;
		$this->hAm = 0;
		$this->hEmr = 0;
		$this->hThr = 0;
		$this->hKir = 0;
		$this->hExr = 0;
		$this->cAm = 0;
		$this->cRe = 0;
		$this->oSp = 0;
		$this->oSe = 0;
		$this->oTy = "";
		$this->oTa = 0;
		$this->oDi = 0;
		$this->oSc = 0;
		$this->oSi = 0;
		$this->oMa = 0;
		$this->rSize = 0;

		$this->shipSlots = array();

		$this->effectiveShield = 0;
		$this->effectiveArmor = 0;
		$this->effectiveHull = 0;
		$this->effectiveHp = 0;

		$this->tankAmount = 0;
		$this->tankHull = "";

		$this->capRechargeRate = 0;
		$this->capStatus = 0;
		$this->capStable = 0;

		$this->pilotName = "";
		$this->pilotCorp = "";
		$this->pilotAlliance = "";
		$this->pilotShip = "";
		$this->pilotLoc = "";
		$this->pilotLocReg = "";
		$this->pilotLocSec = "";
		$this->pilotDate = "";
		$this->pilotDam = "";
		$this->pilotCos = "";
		$this->pilotShipClass = "";

		$this->pilotPort = "";
		$this->pilotNameURL = "";
		$this->pilotCorpURL = "";
		$this->pilotAllianceURL = "";
		$this->pilotShipURL = "";
		$this->pilotLocURL = "";

		$this->corpPort = "";
		$this->alliPort = "";

		$this->shipIcon = "";

		$this->shipDesc = "";
		$this->shipEffects = array();


		$this->isMWD = "";
		$this->mwdBoost = 0;
		$this->mwdSig = 0;
		$this->mwdThrust = 0;
		$this->mwdMass = 0;
		$this->mwdSigRed = 0;

		$this->isAB = "";
		$this->abBoost = 0;
		$this->abThrust = 0;
		$this->abMass = 0;

		$this->abT3Boost = 0;
		$this->mwdT3Boost = 0;
		$this->mwdT3Sig = 0;
		$this->mwdT3Cap = 0;

		$this->capInj = array();

		$this->capGJ = array();

		$this->damageMod = array();
		$this->damageGun = array();
		$this->damage = array();
		$this->volley = array();
		$this->damageModules = array();
		$this->droneDamage = array();
		$this->droneDamageMod = array();

		$this->transCap = array();
		$this->transCapEff = array();

		$this->senBoost = array();

		$this->sigRadiusBoost = array();

		$this->tankofship = "pass";
		$this->tankBoost = array();
		$this->tankAmpShield = array();
		$this->tankAmpArmor = array();

		$this->shipResists = array();

		$this->droneDPS = 0;
		$this->missileDPS = 0;
		$this->turretDPS = 0;
		$this->warpSpeed = 0;


		$this->cal_amount = 0;
		$this->cal_used = 0;



		$this->cpu_amount = 0;
		$this->cpu_used = array();

		$this->prg_amount = 0;
		$this->prg_used = array();

		$this->tur_amount = 0;
		$this->tur_used = 0;

		$this->mis_amount = 0;
		$this->mis_used = 0;

	}

	public function getShieldAmount() {
		return $this->sAm;
	}

	public function setShieldAmount($input) {
		$this->sAm = $input;
	}

	public function getArmorAmount() {
		return $this->aAm;
	}

	public function setArmorAmount($input) {
		$this->aAm = $input;
	}

	public function getHullAmount() {
		return $this->hAm;
	}

	public function setHullAmount($input) {
		$this->hAm = $input;
	}

	public function getSensorAmount() {
		return $this->oSe;
	}

	public function setSensorAmount($input) {
		$this->oSe = $input;
	}

	public function getSensorType() {
		return $this->oTy;
	}

	public function setSensorType($input) {
		$this->oTy = $input;
	}


	public function getShieldEM() {
		return $this->sEmr;
	}

	public function setShieldEM($input) {
		$this->sEmr = $input;
	}

	public function getShieldTh() {
		return $this->sThr;
	}

	public function setShieldTh($input) {
		$this->sThr = $input;
	}

	public function getShieldKi() {
		return $this->sKir;
	}

	public function setShieldKi($input) {
		$this->sKir = $input;
	}

	public function getShieldEx() {
		return $this->sExr;
	}

	public function setShieldEx($input) {
		$this->sExr = $input;
	}

	public function getShieldRecharge() {
		return $this->sRe;
	}

	public function setShieldRecharge($input) {
		$this->sRe = $input;
	}



	public function getArmorEM() {
		return $this->aEmr;
	}

	public function setArmorEM($input) {
		$this->aEmr = $input;
	}

	public function getArmorTh() {
		return $this->aThr;
	}

	public function setArmorTh($input) {
		$this->aThr = $input;
	}

	public function getArmorKi() {
		return $this->aKir;
	}

	public function setArmorKi($input) {
		$this->aKir = $input;
	}

	public function getArmorEx() {
		return $this->aExr;
	}

	public function setArmorEx($input) {
		$this->aExr = $input;
	}



	public function getHullEM() {
		return $this->hEmr;
	}

	public function setHullEM($input) {
		$this->hEmr = $input;
	}

	public function getHullTh() {
		return $this->hThr;
	}

	public function setHullTh($input) {
		$this->hThr = $input;
	}

	public function getHullKi() {
		return $this->hKir;
	}

	public function setHullKi($input) {
		$this->hKir = $input;
	}

	public function getHullEx() {
		return $this->hExr;
	}

	public function setHullEx($input) {
		$this->hExr = $input;
	}




	public function getEffectiveShield() {
		return $this->effectiveShield;
	}

	public function setEffectiveShield($input) {
		$this->effectiveShield = $input;
	}

	public function getEffectiveArmor() {
		return $this->effectiveArmor;
	}

	public function setEffectiveArmor($input) {
		$this->effectiveArmor = $input;
	}

	public function getEffectiveHull() {
		return $this->effectiveHull;
	}

	public function setEffectiveHull($input) {
		$this->effectiveHull = $input;
	}

	public function getEffectiveHp() {
		return $this->effectiveHp;
	}

	public function setEffectiveHp($input) {
		$this->effectiveHp = $input;
	}



	public function getTankAmount() {
		return $this->tankAmount;
	}

	public function setTankAmount($input) {
		$this->tankAmount = $input;
	}

	public function getTankType() {
		return $this->tankType;
	}

	public function setTankType($input) {
		$this->tankType = $input;
	}


	public function getShipSpeed() {
		return $this->oSp;
	}

	public function setShipSpeed($input) {
		$this->oSp = $input;
	}

	public function getSigRadius() {
		return $this->oSi;
	}

	public function setSigRadius($input) {
		$this->oSi = $input;
	}

	public function getScan() {
		return $this->oSc;
	}

	public function setScan($input) {
		$this->oSc = $input;
	}

	public function getTarget() {
		return $this->oTa;
	}

	public function setTarget($input) {
		$this->oTa = $input;
	}

	public function getDistance() {
		return $this->oDi;
	}

	public function setDistance($input) {
		$this->oDi = $input;
	}

	public function getMass() {
		return $this->oMa;
	}

	public function setMass($input) {
		$this->oMa = $input;
	}


	public function getCapAmount() {
		return $this->cAm;
	}

	public function setCapAmount($input) {
		$this->cAm = $input;
	}

	public function getCapRecharge() {
		return $this->cRe;
	}

	public function setCapRecharge($input) {
		$this->cRe = $input;
	}


	public function getCapRechargeRate() {
		return $this->capRechargeRate;
	}

	public function setCapRechargeRate($input) {
		$this->capRechargeRate = $input;
	}

	public function getCapStatus() {
		return $this->capStatus;
	}

	public function setCapStatus($input) {
		$this->capStatus = $input;
	}

	public function getCapStable() {
		return $this->capStable;
	}

	public function setCapStable($input) {
		$this->capStable = $input;
	}



	public function getRSize() {
		return $this->rSize;
	}

	public function setRSize($input) {
		$this->rSize = $input;
	}


	public function getShipSlots() {
		return $this->shipSlots;
	}

	public function setShipSlots($input) {
		$this->shipSlots = $input;
	}



	public function getPilotName() {
		return $this->pilotName;
	}

	public function setPilotName($input) {
		$this->pilotName = $input;
	}

	public function getPilotCorp() {
		return $this->pilotCorp;
	}

	public function setPilotCorp($input) {
		$this->pilotCorp = $input;
	}

	public function getPilotAlliance() {
		return $this->pilotAlliance;
	}

	public function setPilotAlliance($input) {
		$this->pilotAlliance = $input;
	}

	public function getPilotShip() {
		return $this->pilotShip;
	}

	public function setPilotShip($input) {
		$this->pilotShip = $input;
	}

	public function getPilotLoc() {
		return $this->pilotLoc;
	}

	public function getPilotLocReg() {
		return $this->pilotLocReg;
	}

	public function setPilotLoc($input) {
		$this->pilotLoc = $input;
	}

	public function setPilotLocReg($input) {
		$this->pilotLocReg = $input;
	}

	public function getPilotLocSec() {
		return $this->pilotLocSec;
	}

	public function setPilotLocSec($input) {
		$this->pilotLocSec = $input;
	}

	public function getPilotDate() {
		return $this->pilotDate;
	}

	public function setPilotDate($input) {
		$this->pilotDate = $input;
	}

	public function getPilotDam() {
		return $this->pilotDam;
	}

	public function setPilotDam($input) {
		$this->pilotDam = $input;
	}

	public function getPilotCos() {
		return $this->pilotCos;
	}

	public function setPilotCos($input) {
		$this->pilotCos = $input;
	}

	public function getPilotShipClass() {
		return $this->pilotShipClass;
	}

	public function setPilotShipClass($input) {
		$this->pilotShipClass = $input;
	}




	public function getPilotPort() {
		return $this->pilotPort;
	}

	public function setPilotPort($input) {
		$this->pilotPort = $input;
	}

	public function getCorpPort() {
		return $this->corpPort;
	}

	public function setCorpPort($input) {
		$this->corpPort = $input;
	}

	public function getAlliPort() {
		return $this->alliPort;
	}

	public function setAlliPort($input) {
		$this->alliPort = $input;
	}

	public function getPilotNameURL() {
		return $this->pilotNameURL;
	}

	public function setPilotNameURL($input) {
		$this->pilotNameURL = $input;
	}

	public function getPilotCorpURL() {
		return $this->pilotCorpURL;
	}

	public function setPilotCorpURL($input) {
		$this->pilotCorpURL = $input;
	}

	public function getPilotAllianceURL() {
		return $this->pilotAllianceURL;
	}

	public function setPilotAllianceURL($input) {
		$this->pilotAllianceURL = $input;
	}

	public function getPilotShipURL() {
		return $this->pilotShipURL;
	}

	public function setPilotShipURL($input) {
		$this->pilotShipURL = $input;
	}

	public function getPilotLocURL() {
		return $this->pilotLocURL;
	}

	public function setPilotLocURL($input) {
		$this->pilotLocURL = $input;
	}



	public function getShipIcon() {
		return $this->shipIcon;
	}

	public function setShipIcon($input) {
		$this->shipIcon = $input;
	}



	public function getShipDesc() {
		return $this->shipDesc;
	}

	public function setShipDesc($input) {
		$this->shipDesc = $input;
	}

	public function getShipEffects() {
		return $this->shipEffects;
	}

	public function setShipEffects($input) {
		$this->shipEffects = $input;
	}




	public function getIsMWD() {
		return $this->isMWD;
	}

	public function setIsMWD($input) {
		$this->isMWD = $input;
	}

	public function getMwdBoost() {
		return $this->mwdBoost;
	}

	public function setMwdBoost($input) {
		$this->mwdBoost = $input;
	}

	public function getMwdSig() {
		return $this->mwdSig;
	}

	public function setMwdSig($input) {
		$this->mwdSig = $input;
	}

	public function getMwdThrust() {
		return $this->mwdThrust;
	}

	public function setMwdThrust($input) {
		$this->mwdThrust = $input;
	}

	public function getMwdMass() {
		return $this->mwdMass;
	}

	public function setMwdMass($input) {
		$this->mwdMass = $input;
	}

	public function getMwdSigRed() {
		return $this->mwdSigRed;
	}

	public function setMwdSigRed($input) {
		$this->mwdSigRed = $input;
	}


	public function getIsAB() {
		return $this->isAB;
	}

	public function setIsAB($input) {
		$this->isAB = $input;
	}

	public function getABBoost() {
		return $this->abBoost;
	}

	public function setABBoost($input) {
		$this->abBoost = $input;
	}

	public function getABThrust() {
		return $this->abThrust;
	}

	public function setABThrust($input) {
		$this->abThrust = $input;
	}

	public function getABMass() {
		return $this->abMass;
	}

	public function setABMass($input) {
		$this->abMass = $input;
	}


	public function getAbT3Boost() {
		return $this->abT3Boost;
	}

	public function setAbT3Boost($input) {
		$this->abT3Boost = $input;
	}

	public function getMwdT3Boost() {
		return $this->mwdT3Boost;
	}

	public function setMwdT3Boost($input) {
		$this->mwdT3Boost = $input;
	}

	public function getMwdT3Sig() {
		return $this->mwdT3Sig;
	}

	public function setMwdT3Sig($input) {
		$this->mwdT3Sig = $input;
	}

	public function getSpeedT3Cap() {
		return $this->mwdT3Cap;
	}

	public function setSpeedT3Cap($input) {
		$this->mwdT3Cap = $input;
	}


	public function getCapInj() {
		return $this->capInj;
	}

	public function setCapInj($input) {
		$this->capInj = $input;
	}

	public function getCapGJ() {
		return $this->capGJ;
	}

	public function setCapGJ($input) {
		$this->capGJ = $input;
	}



	public function getDamageMod() {
		return $this->damageMod;
	}

	public function setDamageMod($input) {
		$this->damageMod = $input;
	}

	public function getDamageGun() {
		return $this->damageGun;
	}

	public function setDamageGun($input) {
		$this->damageGun = $input;
	}

	public function getDamage() {
		return $this->damage;
	}

	public function setDamage($input) {
		$this->damage = $input;
	}

	public function getVolley() {
		return $this->volley;
	}

	public function setVolley($input) {
		$this->volley = $input;
	}

	public function getDamageModules() {
		return $this->damageModules;
	}

	public function setDamageModules($input) {
		$this->damageModules = $input;
	}

	public function getDroneDamage() {
		return $this->droneDamage;
	}

	public function setDroneDamage($input) {
		$this->droneDamage = $input;
	}

	public function getDroneDamageMod() {
		return $this->droneDamageMod;
	}

	public function setDroneDamageMod($input) {
		$this->droneDamageMod = $input;
	}


	public function getTransCap() {
		return $this->transCap;
	}

	public function setTransCap($input) {
		$this->transCap = $input;
	}

	public function getTransCapEff() {
		return $this->transCapEff;
	}

	public function setTransCapEff($input) {
		$this->transCapEff = $input;
	}

	public function getSensorBooster() {
		return $this->senBoost;
	}

	public function setSensorBooster($input) {
		$this->senBoost = $input;
	}


	public function getSigRadiusBoost() {
		return $this->sigRadiusBoost;
	}

	public function setSigRadiusBoost($input) {
		$this->sigRadiusBoost = $input;
	}

	public function getTankofShip() {
		return $this->tankTypeofship;
	}

	public function setTankofShip($input) {
		$this->tankTypeofship = $input;
	}


	public function getTankBoost() {
		return $this->tankBoost;
	}

	public function setTankBoost($input) {
		$this->tankBoost = $input;
	}


	public function getTankAmpShield() {
		return $this->tankAmpShield;
	}

	public function setTankAmpShield($input) {
		$this->tankAmpShield = $input;
	}

	public function getTankAmpArmor() {
		return $this->tankAmpArmor;
	}

	public function setTankAmpArmor($input) {
		$this->tankAmpArmor = $input;
	}




	public function getShipResists() {
		return $this->shipResists;
	}

	public function setShipResists($input) {
		$this->shipResists = $input;
	}





	public function getDroneDPS() {
		return $this->droneDPS;
	}

	public function setDroneDPS($input) {
		$this->droneDPS = $input;
	}

	public function getMissileDPS() {
		return $this->missileDPS;
	}

	public function setMissileDPS($input) {
		$this->missileDPS = $input;
	}

	public function getTurretDPS() {
		return $this->turretDPS;
	}

	public function setTurretDPS($input) {
		$this->turretDPS = $input;
	}

	public function getWarpSpeed() {
		return $this->warpSpeed;
	}

	public function setWarpSpeed($input) {
		$this->warpSpeed = $input;
	}




	public function getCalAmount() {
		return $this->cal_amount;
	}

	public function setCalAmount($input) {
		$this->cal_amount = $input;
	}

	public function getCalUsed() {
		return $this->cal_used;
	}

	public function setCalUsed($input) {
		$this->cal_used = $input;
	}





	public function getCpuAmount() {
		return $this->cpu_amount;
	}

	public function setCpuAmount($input) {
		$this->cpu_amount = $input;
	}

	public function getCpuUsed() {
		return $this->cpu_used;
	}

	public function setCpuUsed($input) {
		$this->cpu_used = $input;
	}




	public function getPrgAmount() {
		return $this->prg_amount;
	}

	public function setPrgAmount($input) {
		$this->prg_amount = $input;
	}

	public function getPrgUsed() {
		return $this->prg_used;
	}

	public function setPrgUsed($input) {
		$this->prg_used = $input;
	}




	public function getTurAmount() {
		return $this->tur_amount;
	}

	public function setTurAmount($input) {
		$this->tur_amount = $input;
	}

	public function getTurUsed() {
		return $this->tur_used;
	}

	public function setTurUsed($input) {
		$this->tur_used = $input;
	}



	public function getMisAmount() {
		return $this->mis_amount;
	}

	public function setMisAmount($input) {
		$this->mis_amount = $input;
	}

	public function getMisUsed() {
		return $this->mis_used;
	}

	public function setMisUsed($input) {
		$this->mis_used = $input;
	}

}