<?php



class shipEffect
{

	function shipEffect() {	}

function findEffectName($input) {

	if(strstr($input,"energy turret capacitor use and small energy turret damage")) {
		return "damageL*brk*5,+,turretCap";
	}

	if(strstr($input,"reduction in the capacitor need of large hybrid turrets")) {
		return "*brk*10,-,turretCap";
	}
	if(strstr($input,"reduction in the capacitor need of large energy turrets")) {
		return "*brk*10,-,turretCap";
	}

	if(strstr($input,"energy turret damage")) {
		return "damageL";
	}

	if(strstr($input,"bonus to laser damage")) {
		return "damageL";
	}

	if(strstr($input,"energy turret rate of fire")) {
		return "rofL";
	}

	if(strstr($input,"armor resistance")) {
		return "armoremdamageresonance*brk*5,+,armorthermaldamageresonance*brk*5,+,armorkineticdamageresonance*brk*5,+,armorexplosivedamageresonance";
	}

	if(strstr($input,"laser capacitor need")) {
		return "turretCap";
	}

	if(strstr($input,"energy turret capacitor")) {
		return "turretCap";
	}

	if(strstr($input,"shield, armor and hull hitpoints")) {
		return "armoremdamageresonance*brk*10,+,armorthermaldamageresonance*brk*10,+,armorkineticdamageresonance*brk*10,+,armorexplosivedamageresonance*brk*emdamageresonance*brk*10,+,thermaldamageresonance*brk*10,+,kineticdamageresonance*brk*10,+,explosivedamageresonance*brk*shieldemdamageresonance*brk*10,+,shieldthermaldamageresonance*brk*10,+,shieldkineticdamageresonance*brk*10,+,shieldexplosivedamageresonance";
	}

	if(strstr($input,"rocket damage")) {
		return "damageM";
	}

	if(strstr($input,"cap recharge rate")) {
		return "capRecharge";
	}

	if(strstr($input,"capacitor recharge time")) {
		return "capRecharge";
	}

	if(strstr($input,"capacitor recharge rate")) {
		return "capRecharge";
	}


	if(strstr($input,"capacitor capacity")) {
		return "capAmount";
	}

	if(strstr($input,"hybrid turret damage")) {
		return "damageH";
	}

	if(strstr($input,"hybrid turret rate of fire")) {
		return "rofH";
	}

	if(strstr($input,"drone hitpoints, damage")) {
		return "damagedr";
	}

	if(strstr($input,"scout drone thermal damage")) {
		return "damagedr";
	}

	if(strstr($input,"microwarpdrive signature radius penalty")) {
		return "signatureradiusMWD";
	}

	if(strstr($input,"role bonus 80% reduction in propulsion jamming systems activation cost")) {
		return "*brk*80,=,propJamming";
	}

	if(strstr($input,"100% bonus to large hybrid weapon damage")) {
		return "*brk*100,=,damageH";
	}

	if(strstr($input,"100% bonus to large projectile weapon damage")) {
		return "*brk*100,=,damageP";
	}

	if(strstr($input,"role bonus 100% bonus to large energy weapon damage")) {
		return "*brk*100,=,damageL";
	}

	if(strstr($input,"armor repairer effectiveness")) {
		return "armorBoost";
	}

	if(strstr($input,"armor repair amount")) {
		return "armorBoost";
	}

	if(strstr($input,"armor repairer repair amount")) {
		return "armorBoost";
	}

	if(strstr($input,"repair amount of armor repair")) {
		return "armorBoost";
	}

	if(strstr($input,"armor repairer boost amount")) {
		return "armorBoost";
	}

	if(strstr($input,"armor repairer duration")) {
		return "armorRepCycle";
	}

	if(strstr($input,"armor hitpoints")) {
		return "armorhp";
	}

	if(strstr($input,"armor hp")) {
		return "armorhp";
	}

	if(strstr($input,"ecm target jammer strength and multiplies the cloaked velocity by 125%")) {
		return "";
	}

	if(strstr($input,"max velocity")) {
		return "maxvelocity";
	}

	if(strstr($input,"bonus to large energy turret tracking and multiplies the cloaked velocity by 125%")) {
		return "";
	}
	if(strstr($input,"shield transport and energy transfer array capacitor use")) {
		return "shieldEnergyTrans";
	}

	if(strstr($input,"energy transfer array and remote armor repair system capacitor use")) {
		return "armorEnergyTrans";
	}

	if(strstr($input,"remote armor repair system capacitor use")) {
		return "armorTrans";
	}

	if(strstr($input,"shield transport capacitor use")) {
		return "shieldTrans";
	}

	if(strstr($input,"projectile turret rate of fire and large projectile turret damage")) {
		return "damageP*brk*5,+,rofP";
	}

	if(strstr($input,"siege and cruise missile launcher firing speed")) {
		return "rofM";
	}

	if(strstr($input,"projectile turret damage")) {
		return "damageP";
	}

	if(strstr($input,"projectile turret firing speed")) {
		return "rofP";
	}

	if(strstr($input,"heavy assault missile damage")) {
		return "damageM";
	}

	if(strstr($input,"projectile turret rate of fire")) {
		return "rofP";
	}

	if(strstr($input,"projectile weapon rate of fire")) {
		return "rofP";
	}

	if(strstr($input,"bomb em damage")) {
		return "";
	}

	if(strstr($input,"cruise missile and torpedo rate of fire")) {
		return "rofM";
	}

	if(strstr($input,"em damage")) {
		return "damageem";
	}

	if(strstr($input,"explosive missile damage")) {
		return "damageex";
	}

	if(strstr($input,"em missile damage")) {
		return "damageem";
	}

	if(strstr($input,"explosive, kinetic and thermal missile damage")) {
		return "*brk*5,+,damageex*brk*5,+,damageki*brk*5,+,damageth";
	}

	if(strstr($input,"em, explosive, and thermal missile damage")) {
		return "*brk*5,+,damageem*brk*5,+,damageex*brk*5,+,damageth";
	}

	if(strstr($input,"drone hitpoints and damage")) {
		return "damagedr";
	}

	if(strstr($input,"drone damage")) {
		return "damagedr";
	}

	if(strstr($input,"drone hit points and damage")) {
		return "damagedr";
	}

	if(strstr($input,"bomb explosive damage")) {
		return "";
	}

	if(strstr($input,"bomb thermal damage")) {
		return "";
	}

	if(strstr($input,"torpedo thermal damage")) {
		return "damageth";
	}

	if(strstr($input,"projectile damage bonus")) {
		return "damageP";
	}

	if(strstr($input,"projectile damage")) {
		return "damageP";
	}

	if(strstr($input,"projectile weapons damage and rate of fire")) {
		return "damageP*brk*5,+,rofP";
	}

	if(strstr($input,"remote sensor dampener capacitor need")) {
		return "sentran";
	}

	if(strstr($input,"warp disruptor capacitor need")) {
		return "disCap";
	}

	if(strstr($input,"signature radius")) {
		return "signatureradius";
	}

	if(strstr($input,"explosive damage")) {
		return "damageex";
	}

	if(strstr($input,"shield resistance")) {
		return "shieldemdamageresonance*brk*5,+,shieldthermaldamageresonance*brk*5,+,shieldkineticdamageresonance*brk*5,+,shieldexplosivedamageresonance";
	}

	if(strstr($input,"capacitor use of shield transporters")) {
		return "shieldTran";
	}

	if(strstr($input,"bonus to light missile and rocket kinetic damage")) {
		return "damageM";
	}

	if(strstr($input,"missile velocity")) {
		return "";
	}

	if(strstr($input,"torpedo velocity")) {
		return "";
	}

	if(strstr($input,"explosion velocity")) {
		return "";
	}

	if(strstr($input,"cruise missile and torpedo damage")) {
		return "*brk*100,+,damageM*brk*";
	}

	if(strstr($input,"hybrid weapon damage")) {
		return "damageH";
	}

	if(strstr($input,"velocity factor of stasis webifiers")) {
		return "";
	}

	if(strstr($input,"velocity")) {
		return "maxvelocity";
	}

	if(strstr($input,"shield booster boost amount")) {
		return "shieldBoost";
	}

	if(strstr($input,"shield boosting")) {
		return "shieldBoost";
	}

	if(strstr($input,"shield boost amount")) {
		return "shieldBoost";
	}

	if(strstr($input,"bonus to shield booster")) {
		return "shieldBoost";
	}

	if(strstr($input,"bonus shield hp")) {
		return "shieldcapacity";
	}

	if(strstr($input,"shield hp")) {
		return "shieldcapacity";
	}

	if(strstr($input,"missile kinetic damage")) {
		return "damageki";
	}

	if(strstr($input,"kinetic missile damage")) {
		return "damageki";
	}



	if(strstr($input,"sphere launcher rate of fire")) {
		return "";
	}

	if(strstr($input,"launcher rate of fire")) {
		return "rofM";
	}

	if(strstr($input,"shield capacity")) {
		return "shieldcapacity";
	}

	if(strstr($input,"25% rate of fire for turrets")) {
		return "*brk*25,+,rofT";
	}

	if(strstr($input,"microwarpdrive capacitor bonus")) {
		return "capAmount";
	}

	if(strstr($input,"bomb kinetic damage")) {
		return "";
	}

	if(strstr($input,"kinetic damage")) {
		return "damageki";
	}

	if(strstr($input,"rocket and light missile thermal damage")) {
		return "damageth";
	}


	if(strstr($input,"capacitor need of remote armor repair system")) {
		return "armorTrans";
	}

	if(strstr($input,"hybrid damage")) {
		return "damageH";
	}

	if(strstr($input,"reduction in siege missile launcher powergrid needs")) {
		return "*brk*99.65,-,seige_power";
	}

	if(strstr($input,"reduction in cloak cpu use")) {
		return "*brk*99.5,-,covert_cloak";
	}

	if(strstr($input,"reduced cpu need for cloaking device")) {
		return "*brk*100,-,covert_cloak";
	}

	if(strstr($input,"bonus to cpu need of covert ops cloaks")) {
		return "*brk*99.25,-,covert_cloak";
	}

	//echo $input."<br />";
	if(strstr($input,"reduction in warfare link module cpu need")) {
		return "*brk*99,-,war_bonus";
	}

	if(strstr($input,"reduction in the powergrid need of large")) {
		return "*brk*95,-,heavy_power";
	}


	if(strstr($input,"reduction in the cpu need of large")) {
		return "*brk*50,-,heavy_cpu";
	}


	if(strstr($input,"reduction in cpu need for gang link modules")) {
		return "*brk*99,-,war_bonus";
	}


	if(strstr($input,"reduction in cpu need for gang link modules")) {
		return "*brk*99,-,war_bonus";
	}

	if(strstr($input,"50% cpu need for shield transporters")) {
		return "shield_transCPU";
	}
	if(strstr($input,"50% power need for energy transfer arrays")) {
		return "cap_transPower";
	}

	if(strstr($input," reduction in cpu need for warfare link modules")) {
		return "capital_cpu";
	}



	if(strstr($input,"reduction in torpedo launcher powergrid needs")) {
		return "*brk*99.65,-,seige_power";
	}

}


};


?>