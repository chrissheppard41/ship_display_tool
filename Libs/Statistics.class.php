<?php
/**
 *  The Statisitics class
 *  Handles the stats of the ship
 *
 *	@author Chris Sheppard (Spark's)
 *  @version 1.0
 *  Code in this document belongs to:
 *  @copyright Chris Sheppard
 *  EvE online own all module/ships/other details rights
 *  Runs within EvE Dev killboard
 *
  */
class Statisitics
{
/**
 * modShipResists method
 * The bonus' for the ship and creates rules for them
 *
 * @param $_arr (array)
 * @param $_shieldResistPos (int)
 * @param $_section (string)
 * @param $_modName (string)
 * @param $_resist (string)
 * @param $_bonus (int)
 * @param $_type (string)
 * @param $_order (int)
 * @return (array)
 */
	public static function modShipResists($_arr, &$_shieldResistPos, $_section, $_modName, $_resist, $_bonus, $_type, $_order) {
		$_arr[$_shieldResistPos]['name']	= $_modName;
		$_arr[$_shieldResistPos]['section']	= $_section;
		$_arr[$_shieldResistPos]['resist'] 	= $_resist;
		$_arr[$_shieldResistPos]['amount'] 	= $_bonus;
		$_arr[$_shieldResistPos]['type'] 	= $_type;
		//$_arr[$_shieldResistPos]['neg'] 	= self::$emArmor;
		$_arr[$_shieldResistPos]['order'] 	= $_order;
		$_shieldResistPos++;

		return $_arr;
	}

/**
 * modShipEnergy method
 * The modification of the energy type used within a ships mods
 *
 * @param $_arr (array)
 * @param $_moduleCount (int)
 * @param $_eng (int)
 * @param $_name (int)
 * @param $_amount (int)
 * @param $_type (int)
 * @param $_mode (int)
 * @return (array)
 */
	public static function modShipEnergy($_arr, $_moduleCount, $_eng, $_name, $_amount, $_type, $_mode, $_stack = -1) {
		$_arr[$_moduleCount]['name'] = $_name;
		$_arr[$_moduleCount][$_eng]	= $_amount;
		$_arr[$_moduleCount]['type'] = $_type;
		$_arr[$_moduleCount]['mode'] = $_mode;
		if($_stack != -1)
			$_arr[$_moduleCount]['stack'] = $_stack;

		return $_arr;
	}

/**
 * modScanning method
 * The modification of the energy type used within a ships mods
 *
 * @param $_arr (array)
 * @param $_moduleCount (int)
 * @param $_eng (int)
 * @param $_name (int)
 * @param $_amount (int)
 * @param $_type (int)
 * @param $_mode (int)
 * @return (array)
 */
	public static function modScanning($_arr, $_moduleCount, $_bonusType, $_bonus, $_neg, &$_negCount, $_type, $_order) {
		$_arr[$_moduleCount][$_bonusType]	= $_bonus;
		$_arr[$_moduleCount][$_neg]			= $_negCount;
		$_arr[$_moduleCount]['type'] 		= $_type;
		$_arr[$_moduleCount]['order'] 		= $_order;
		$_negCount++;

		return $_arr;
	}

/**
 * modOrdering method
 * The modification of the scanning equipment
 *
 * @param $_arr (array)
 * @param $_sensorbooster (array)
 * @param $_scan (bool)
 * @param $_range (bool)
 * @return (array)
 */
	public static function modOrdering($_arr, &$_sensorbooster, $_scan, $_range) {
		$_arr[$_sensorbooster[0]]['scan'] = ($_scan)?$_arr[$_sensorbooster[0]]['range']*2:0;
		$_arr[$_sensorbooster[0]]['range'] = ($_range)?$_arr[$_sensorbooster[0]]['range']*2:0;

		unset($_sensorbooster[0]);
		$_sensorbooster = array_values($_sensorbooster);

		return $_arr;
	}

/**
 * modShieldDur method
 * The modification of the shield duration
 *
 * @param $_arr (array)
 * @param $_moduleCount (int)
 * @param $_bonus (int)
 * @param $_type (int)
 * @param $_neg (int)
 * @return (array)
 */
	public static function modShieldDur($_arr, $_moduleCount, $_bonus, $_type, &$_neg, $inc_count = false) {
		$_arr[$_moduleCount]['dur'] = $_bonus;
		$_arr[$_moduleCount]['type'] = $_type;
		$_arr[$_moduleCount]['neg'] = $_neg;
		if($inc_count)
			$_neg++;

		return $_arr;
	}

/**
 * modDuration method
 * The modification of the cap duration
 *
 * @param $_arr (array)
 * @param $_moduleCount (int)
 * @param $_indname (int)
 * @param $_indval (int)
 * @param $_duration (int)
 * @return (array)
 */
	public static function modDuration($_arr, $_moduleCount, $_indname, $_indval, $_duration) {
		$_arr[$_moduleCount][$_indname] = $_indval;
		$_arr[$_moduleCount]['duration'] = $_duration;

		return $_arr;
	}

/**
 * modDuration method
 * The modification of the cap duration
 *
 * @param $_arr (array)
 * @param $_moduleCount (int)
 * @param $_indname (int)
 * @param $_indval (int)
 * @param $_duration (int)
 * @return (array)
 */
	public static function modCapneed($_arr, $_moduleCount, $_modName, $_capacity, $_capNeeded) {
		if($_modName != null) $_arr[$_moduleCount]['name'] = $_modName;
		if($_capacity != null) $_arr[$_moduleCount]['capacity'] = $_capacity;
		$_arr[$_moduleCount]['capNeeded'] = $_capNeeded;

		return $_arr;
	}

/**
 * modDurationBoost method
 * The modification of the boost duration
 *
 * @param $_arr (array)
 * @param $_moduleCount (int)
 * @param $_duration (int)
 * @return (array)
 */
	public static function modDurationBoost($_arr, $_moduleCount, $_duration) {
		$_arr[$_moduleCount]['dur'] = $_duration;
		return $_arr;
	}

/**
 * modSpeed method
 * The modification of the speed of guns
 *
 * @param $_arr (array)
 * @param $_moduleCount (int)
 * @param $_gunPos (array)
 * @param $_gunPosCap (array)
 * @param $_gunRoF (string)
 * @param $_amount (int)
 * @param $_type (string)
 * @param $_capacity (int)
 * @param $_techlevel (int)
 * @return (array)
 */
	public static function modSpeed($_arr, $_moduleCount, &$_gunPos, &$_gunPosCap, $_name, $_gunRoF, $_amount, $_type, $_capacity, $_techlevel) {
		$_gunPos[] 		= $_moduleCount;
		$_gunPosCap[] 	= $_moduleCount;

		$_arr[$_moduleCount]['name'] = $_name;
		$_arr[$_moduleCount][$_gunRoF] = $_amount;
		$_arr[$_moduleCount]['type'] = $_type;
		$_arr[$_moduleCount]['capacity'] = $_capacity;
		$_arr[$_moduleCount]['techlevel'] = $_techlevel;

		return $_arr;
	}

/**
 * modDamage method
 * The modification of the damage of guns
 *
 * @param $_arr (array)
 * @param $_moduleCount (int)
 * @param $_gunDam (string)
 * @param $_amount (int)
 * @return (array)
 */
	public static function modDamage($_arr, $_moduleCount, $_gunDam, $_amount) {
		$_arr[$_moduleCount][$_gunDam] = $_amount;

		return $_arr;
	}


/**
 * modDamageMods method
 * The modification of the damage of damage mods
 *
 * @param $_arr (array)
 * @param $_moduleCount (int)
 * @param $_modName (string)
 * @param $_neg (int)
 * @param $_damageType (string)
 * @param $_damage (int)
 * @param $_rofneg (int)
 * @return (array)
 */
	public static function modDamageMods($_arr, $_moduleCount, $_modName, &$_neg, $_damageType, $_damage, &$_rofneg = null) {
		$_arr[$_moduleCount]['name'] = $_modName;
		$_arr[$_moduleCount]['neg'] = $_neg++;
		$_arr[$_moduleCount][$_damageType] = $_damage;

		if($_rofneg != null)
			$_rofneg++;
		return $_arr;
	}


/**
 * modDroneDamageMods method
 * The modification of the damage of damage mods
 *
 * @param $_arr (array)
 * @param $_moduleCount (int)
 * @param $_modName (string)
 * @param $_neg (int)
 * @param $_rofdr (int)
 * @param $_damagedr (int)
 * @param $_type (int)
 * @return (array)
 */
	public static function modDroneDamageMods($_arr, $_moduleCount, $_modName, &$_neg, $_rofdr, $_damagedr, $_type) {
		$_arr[$_moduleCount]['name'] = $_modName;
		$_arr[$_moduleCount]['neg'] = $_neg;
		$_arr[$_moduleCount]['rofdr'] = $_rofdr;
		$_arr[$_moduleCount]['damagedr'] = $_damagedr;
		$_arr[$_moduleCount]['type'] = $_type;


		$_neg++;
		return $_arr;
	}

/**
 * modTankBoost method
 * The modification of the ships tank boost
 *
 * @param $_arr (array)
 * @param $_moduleCount (int)
 * @param $_boost (int)
 * @param $_type (string)
 * @param $_neg (int)
 * @return (array)
 */
	public static function modTankBoost($_arr, $_moduleCount, $_boost, $_type, &$_neg = null) {
		$_arr[$_moduleCount]['boost'] = $_boost;
		$_arr[$_moduleCount]['type'] = $_type;


		if($_neg != null) {
			$_arr[$_moduleCount]['neg'] = $_neg;
			$_neg++;
		}
		return $_arr;
	}

/**
 * modShipeffects method
 * The modification of the ships tank boost
 *
 * @param $_arr (array)
 * @param $_moduleCount (int)
 * @param $_boost (int)
 * @param $_type (string)
 * @param $_neg (int)
 * @return (array)
 */
	public static function modShipeffects($_arr, &$_moduleCount, $_effect, $_bonus, $_type) {
		$_moduleCount++;
		$_arr[$_moduleCount]['effect'] = $_effect;
		$_arr[$_moduleCount]['bonus'] = $_bonus;
		$_arr[$_moduleCount]['type'] = $_type;

		return $_arr;
	}

/**
 * slots method
 * Converts the new slot ints to the old style
 *
 * @param $slot (int)
 * @return (int)
 */
	public static function slots($_slot, $_name, $_cat) {
		if($_slot == 0) {
			switch($_name) {
				case "Drone Bay":
					return 6;
				break;
				case "Medium power slot 1":
					return 11;
				break;
				case "High power slot 1":
					return 10;
				break;
			}
		} else {
			if($_name != "Cargo"
			&& $_name != "Fleet Hangar") {
				if($_cat == 8) {
					return 11;
				}
				switch($_slot) {
					case "11":
						return 3;
					break;
					case "19":
						return 2;
					break;
					case "27":
						return 1;
					break;
					case "92":
						return 5;
					break;
					case "125":
						return 0;
					break;
				}
			}
		}
		return 100;
	}
}