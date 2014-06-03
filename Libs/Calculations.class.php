<?php
/**
 *  The Calculations class
 *  Contains all calculations within the system
 *
 *	@author Chris Sheppard (Spark's)
 *  @version 1.0
 *  Code in this document belongs to:
 *  @copyright Chris Sheppard
 *  EvE online own all module/ships/other details rights
 *  Runs within EvE Dev killboard
 *
  */
class Calculations
{

/**
 * returnPixelSize method
 * Used to display the resist pixil level
 *
 * @param $amount_param (string)
 * @param $pixil_param (string)
 * @return (int)
 */
	public static function returnPixelSize($amount_param, $pixil_param) {
		return ($pixil_param/100)*$amount_param;
	}

/**
 * effectHP method
 * Returns the effective hp based on whether it's armour, hull or shield
 *
 * @param $hp (int)
 * @param $em (int)
 * @param $th (int)
 * @param $ki (int)
 * @param $ex (int)
 * @return (int)
 */
	public static function effectHP($hp, $em, $th, $ki, $ex) {
		return $hp / (((1-($em/100))+(1-($th/100))+(1-($ki/100))+(1-($ex/100)))/4);
	}

/**
 * peakShieldRecharge method
 * Returns the peak shield amount
 *
 * @param $shieldCap (string)
 * @param $shieldRec (string)
 * @return (int)
 */
	public static function peakShieldRecharge($shieldCap, $shieldRec) {
		if($shieldRec == 0) {
			return 0;
		} else {
			return (($shieldCap/$shieldRec)*2.5);
		}
	}

/**
 * tankAbleDPS method
 * returns the breaking point in the shield for how much dps a ship shields can take
 *
 * @param $peakRegen (int)
 * @param $em (int)
 * @param $th (int)
 * @param $ki (int)
 * @param $ex (int)
 * @return (int)
 */
	public static function tankAbleDPS($peakRegen, $em, $th, $ki, $ex) {
		return 4*$peakRegen/((1-($em/100))+(1-($th/100))+(1-($ki/100))+(1-($ex/100)));
	}

/**
 * isCapStable method
 * Returns wheather or not the ship is cap stable
 *
 * @param $capPS (int)
 * @param $capUse (int)
 * @return (bool)
 */
	public static function isCapStable($capPS,$capUse) {
		if($capPS >= $capUse) {
			return true;
		}
		return false;
	}

/**
 * capUsage method
 * Returns the cap usage
 *
 * @param $capAmount (int)
 * @param $capUsage (int)
 * @param $capRechagePS (int)
 * @param $capRecharge (int)
 * @return (int)
 */
	public static function capUsage($capAmount, $capUsage, $capRechagePS, $capRecharge) {
		if($capRechagePS == 0) {
			return 0;
		}
		return ($capAmount/($capUsage-$capRechagePS));
	}

/**
 * toMinutesAndHours method
 * Converts seconds into minutes and hours
 *
 * @param $seconds (int)
 * @return (int)
 */
	public static function toMinutesAndHours($seconds) {
		$hoursmin = "";
		$hours = intval(intval($seconds) / 3600);
		if($hours > 0) {
			$hoursmin .= $hours."h ";
		}

		$minutes = (intval($seconds)/60)%60;
		if($hours > 0 || $minutes > 0) {
			$hoursmin .= $minutes."m ";
		}

		$seconds = intval($seconds)%60;
		$hoursmin .= $seconds."s";

		return $hoursmin;
	}

/**
 * statOntoShip method
 * Calculates the stats for the module/ship
 *
 * @param $stat_param (int)
 * @param $numChange_param (int)
 * @param $type_param (int)
 * @param $mode_param (string)
 * @param $negEffect (int)
 * @return (int)
 */
	public static function statOntoShip($stat_param, $numChange_param, $type_param, $mode_param, $negEffect) {

		if(!$negEffect) {
			$negEffect = 1;
		}

		if ($type_param == "+" && $mode_param == "%") {
			return ($stat_param+($stat_param*((self::stackingPenalties($negEffect)*$numChange_param)/100)));
		} else if ($type_param == "-" && $mode_param == "%") {
			return ($stat_param - ($stat_param*((self::stackingPenalties($negEffect)*$numChange_param)/100)));
		} else if ($type_param == "+" && $mode_param == "+") {
			return ($stat_param + (self::stackingPenalties($negEffect)*$numChange_param));
		} else if ($type_param == "-" && $mode_param == "-") {
			return ($stat_param - (self::stackingPenalties($negEffect)*$numChange_param));
		}
		return 0;
	}
/**
 * stackingPenalties method
 * Calculates the stacking penalty
 *
 * @param $modNum (int)
 * @return (int)
 */
	private function stackingPenalties($modNum) {
		return pow(0.5,pow((($modNum-1)/2.22292081),2));
	}

/**
 * getLevel5SkillsPlus method
 * Returns the module/ship plus skill level
 *
 * @param $skills_param (int)
 * @param $base_param (int)
 * @param $type_param (string)
 * @param $negEffect (int)
 * @return (int)
 */
	public static function getLevel5SkillsPlus($skills_param,$base_param,$type_param,$negEffect) {

		if ($type_param=="+") {
			return (((1-($skills_param/100))*(self::stackingPenalties($negEffect)*$base_param))+$skills_param);
		} else {
			return (((1-($skills_param/100))*(self::stackingPenalties($negEffect)*$base_param))-$skills_param);
		}
	}

/**
 * capInjector method
 * Calculates the cap injector
 *
 * @param $capBooster (int)
 * @param $storage (int)
 * @param $size (string)
 * @param $duration (int)
 * @return (int)
 */
	public static function capInjector($capBooster, $storage, $size, $duration) {
		if($capBooster == 0 || $storage == 0 || $size == 0 || $duration == 0) {
			return 0;
		} else {
			return ($capBooster/((floor($storage/$size)*$duration)+10))*floor($storage/$size);
		}
	}

/**
 * getShipSpeed method
 * Calculates the ship speed
 *
 * @param $shipSpeed (int)
 * @param $boost (int)
 * @param $thrust (int)
 * @param $mass (int)
 * @return
 */
	public static function getShipSpeed($shipSpeed, $boost, $thrust, $mass) {
		return $shipSpeed*(1+(($boost/100)*(1+5*0.05)*($thrust/$mass)));
	}

/**
 * propCeptorBonus method
 * Based on the ship skill (interceptors for example) apply the role bonus
 *
 * @param $cap (int)
 * @param $off (int)
 * @return
 */
	public static function propCeptorBonus($cap, $off) {
		return $cap-(($cap/100)*$off);
	}

/**
 * calculateMass method
 * Calculates the mass of the ship
 *
 * @param $param_mass (int)
 * @return
 */
	public function calculateMass($param_mass) {
		//1.482e+07
		$break = explode("e+",$param_mass);
		$exp = 1;
		for($e=0; $e < $break[1]; $e++) {
			$exp = $exp*10;
		}

		//echo $break[0]." ".($break[1]*10)." ".$exp;
		return ($break[0]*$exp);
	}
}
