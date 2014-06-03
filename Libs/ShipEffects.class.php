<?php
/**
 *  The ShipEffect class
 *  Takes the ship bonus' and organises the effect they will have ont he ship/modules
 *
 *	@author Chris Sheppard (Spark's)
 *  @version 1.0
 *  Code in this document belongs to:
 *  @copyright Chris Sheppard
 *  EvE online own all module/ships/other details rights
 *  Runs within EvE Dev killboard
 *
  */
class ShipEffect
{

/**
 * findEffectName method
 * Takes an input string and does checks to see what bonus is appropreate
 *
 * @param $_input (string)
 * @param $_bonus (int)
 * @return (int)
 */
	public static function findEffectName($_input, $_bonus) {

		//echo $_input." | ".$_bonus."<br />";

		if(strstr($_input,"ecm target jammer strength and multiplies the cloaked velocity by 125%")
		|| strstr($_input,"bonus to large energy turret tracking and multiplies the cloaked velocity by 125%")
		|| strstr($_input,"bomb em damage")
		|| strstr($_input,"bomb explosive damage")
		|| strstr($_input,"bomb thermal damage")
		|| strstr($_input,"missile velocity")
		|| strstr($_input,"torpedo velocity")
		|| strstr($_input,"explosion velocity")
		|| strstr($_input,"velocity factor of stasis webifiers")
		|| strstr($_input,"sphere launcher rate of fire")
		|| strstr($_input,"bomb kinetic damage")
		|| strstr($_input,"explosion velocity")
		|| strstr($_input,"tractor beam")) {
			return array();
			//return "";
		}

		if(strstr($_input,"energy turret capacitor use and small energy turret damage")
		|| strstr($_input,"energy turret damage")
		|| strstr($_input,"bonus to laser damage")
		|| strstr($_input,"role bonus 100% bonus to large energy weapon damage")) {
			return array(
				array(
					'effect' => "damageL",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"reduction in the capacitor need of large hybrid turrets")
		|| strstr($_input,"reduction in the capacitor need of large energy turrets")
		|| strstr($_input,"laser capacitor need")
		|| strstr($_input,"energy turret capacitor")) {
			return array(
				array(
					'effect' => "turretCap",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"energy turret rate of fire")) {
			return array(
				array(
					'effect' => "rofL",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"armor resistance")) {
			return array(
				array(
					'effect' => "armoremdamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "armorthermaldamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "armorkineticdamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "armorexplosivedamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"shield, armor and hull hitpoints")) {
			return array(
				array(
					'effect' => "armoremdamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "armorthermaldamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "armorkineticdamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "armorexplosivedamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "shieldemdamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "shieldthermaldamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "shieldkineticdamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "shieldexplosivedamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "emdamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "thermaldamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "kineticdamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "explosivedamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"rocket damage")
		|| strstr($_input,"heavy assault missile damage")
		|| strstr($_input,"bonus to light missile and rocket kinetic damage")
		|| strstr($_input,"cruise missile and torpedo damage")) {
			return array(
				array(
					'effect' => "damageM",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"cap recharge rate")
		|| strstr($_input,"capacitor recharge time")
		|| strstr($_input,"capacitor recharge rate")) {
			return array(
				array(
					'effect' => "capRecharge",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"capacitor capacity")
		|| strstr($_input,"microwarpdrive capacitor bonus")) {
			return array(
				array(
					'effect' => "capAmount",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"hybrid turret damage")
		|| strstr($_input,"100% bonus to large hybrid weapon damage")
		|| strstr($_input,"hybrid weapon damage")
		|| strstr($_input,"hybrid damage")) {
			return array(
				array(
					'effect' => "damageH",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"hybrid turret rate of fire")) {
			return array(
				array(
					'effect' => "rofH",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"drone hitpoints, damage")
		|| strstr($_input,"scout drone thermal damage")
		|| strstr($_input,"drone hitpoints and damage")
		|| strstr($_input,"drone damage")
		|| strstr($_input,"drone hit points and damage")) {
			return array(
				array(
					'effect' => "damagedr",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"microwarpdrive signature radius penalty")) {
			return array(
				array(
					'effect' => "signatureradiusMWD",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"role bonus 80% reduction in propulsion jamming systems activation cost")) {
			return array(
				array(
					'effect' => "propJamming",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"100% bonus to large projectile weapon damage")
		|| strstr($_input,"projectile turret rate of fire and large projectile turret damage")
		|| strstr($_input,"projectile turret damage")
		|| strstr($_input,"projectile damage bonus")
		|| strstr($_input,"projectile damage")
		|| strstr($_input,"projectile weapons damage and rate of fire")) {
			return array(
				array(
					'effect' => "damageP",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"armor repairer effectiveness")
		|| strstr($_input,"armor repair amount")
		|| strstr($_input,"armor repairer amount")
		|| strstr($_input,"armor repairer repair amount")
		|| strstr($_input,"repair amount of armor repair")
		|| strstr($_input,"armor repairer boost amount")) {
			return array(
				array(
					'effect' => "armorBoost",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"armor repairer duration")) {
			return array(
				array(
					'effect' => "armorRepCycle",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"armor hitpoints")
		|| strstr($_input,"armor hp")) {
			return array(
				array(
					'effect' => "armorhp",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"max velocity")
		|| strstr($_input,"velocity")) {
			return array(
				array(
					'effect' => "maxvelocity",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"remote capacitor transmitter activation")) {
			return array(
				array(
					'effect' => "energyTrans",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"remote armor repairer activation")) {
			return array(
				array(
					'effect' => "armorTrans",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"remote shield booster activation")) {
			return array(
				array(
					'effect' => "shieldTrans",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"siege and cruise missile launcher firing speed")
		|| strstr($_input,"cruise missile and torpedo rate of fire")
		|| strstr($_input,"launcher rate of fire")) {
			return array(
				array(
					'effect' => "rofM",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"projectile turret firing speed")
		|| strstr($_input,"projectile turret rate of fire")
		|| strstr($_input,"projectile weapon rate of fire")) {
			return array(
				array(
					'effect' => "rofP",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"em damage")
		|| strstr($_input,"em missile damage")) {
			return array(
				array(
					'effect' => "damageem",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"explosive missile damage")
		|| strstr($_input,"explosive damage")) {
			return array(
				array(
					'effect' => "damageex",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}
		if(strstr($_input,"explosive, kinetic and thermal missile damage")
		|| strstr($_input,"em, explosive, and thermal missile damage")) {
			return array(
				array(
					'effect' => "damageem",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "damageth",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "damageki",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "damageex",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"torpedo thermal damage")
		|| strstr($_input,"rocket and light missile thermal damage")) {
			return array(
				array(
					'effect' => "damageth",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"remote sensor dampener capacitor need")) {
			return array(
				array(
					'effect' => "sentran",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"warp disruptor capacitor need")) {
			return array(
				array(
					'effect' => "disCap",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"signature radius")) {
			return array(
				array(
					'effect' => "signatureradius",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"shield resistance")) {
			return array(
				array(
					'effect' => "shieldemdamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "shieldthermaldamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "shieldkineticdamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				),
				array(
					'effect' => "shieldexplosivedamageresonance",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"shield boosting")
		|| strstr($_input,"shield boost amount")
		|| strstr($_input,"bonus to shield booster")) {
			return array(
				array(
					'effect' => "shieldBoost",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"shield hp")
		|| strstr($_input,"shield capacity")) {
			return array(
				array(
					'effect' => "shieldcapacity",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"missile kinetic damage")
		|| strstr($_input,"kinetic missile damage")
		|| strstr($_input,"kinetic damage")) {
			return array(
				array(
					'effect' => "damageki",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"rate of fire for turrets")) {
			return array(
				array(
					'effect' => "rofT",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"reduction in siege missile launcher powergrid needs")
		|| strstr($_input,"reduction in torpedo launcher powergrid needs")) {
			return array(
				array(
					'effect' => "seige_power",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"reduction in cloak cpu use")
		|| strstr($_input,"reduced cpu need for cloaking device")
		|| strstr($_input,"bonus to cpu need of covert ops cloaks")) {
			return array(
				array(
					'effect' => "covert_cloak",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"reduction in warfare link module cpu need")
		|| strstr($_input,"reduction in cpu need for gang link modules")
		|| strstr($_input,"reduction in cpu need for gang link modules")) {
			return array(
				array(
					'effect' => "war_bonus",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"reduction in the powergrid need of large")) {
			return array(
				array(
					'effect' => "heavy_power",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"reduction in the cpu need of large")) {
			return array(
				array(
					'effect' => "heavy_cpu",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"remote shield booster cpu")) {
			return array(
				array(
					'effect' => "shield_transCPU",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}
		if(strstr($_input,"remote armor repairer powergrid")) {
			return array(
				array(
					'effect' => "armor_transPower",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}
		if(strstr($_input,"remote capacitor transmitter powergrid")) {
			return array(
				array(
					'effect' => "cap_transPower",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		if(strstr($_input,"reduction in cpu need for warfare link modules")) {
			return array(
				array(
					'effect' => "capital_cpu",
					'bonus' => $_bonus,
					'type' => self::bonuseffect($_input, $_bonus)
				)
			);
		}

		return array();
	}

/**
 * bonuseffect method
 * From the input string tries and figures out if the effect adds or takes away from a ship/module stat
 *
 * @param $_input (string)
 * @return (int)
 */
	public static function bonuseffect($_input, $_bonus) {

		if($_bonus == 100) return "=";

		if(strstr($_input,"reduction")) {
			return "-";
		}

		return "+";
	}

};
