<?php
// Original by TEKAI
// Ammo addition and little modifications by Wes Lave
// Changes made, instead of echo, changed to variable. Spark's
//require_once('common/includes/class.kill.php');


//$kll_id = intval($_GET['kll_id']);






class fitting
{
	var $kill_id = "";

	function fitting($kll_id) {

		$this->kill_id = $kll_id;

	}


	function displayFitting() {
$eftFit = "";

/*$kill = new Kill($this->kill_id);
$ship = $kill->getVictimShip();
$pilotname = $kill->getVictimName();
//$shipclass = $ship->getClass();
$shipname = $ship->getName();
//$system = $kill->getSystem();
$killtitle .= $pilotname."'s ".$shipname;*/

$fitting_array[1] = array();    // high slots
$fitting_array[2] = array();    // med slots
$fitting_array[3] = array();    // low slots
$fitting_array[5] = array();    // rig slots
$fitting_array[6] = array();    // drone bay
$fitting_array[7] = array();    // subsystems
$fitting_array[10] = array();    // ammo
$ammo_array[1] = array();	// high ammo
$ammo_array[2] = array();	// mid ammo



/*select kb3_items_destroyed.*, kb3_invtypes.typeID, kb3_invtypes.typeName, kb3_invtypes.capacity, kb3_invtypes.mass, kb3_invtypes.volume, kb3_invtypes.icon, kb3_item_types.itt_slot from (kb3_items_destroyed left join kb3_invtypes on kb3_items_destroyed.itd_itm_id = kb3_invtypes.typeID) left join kb3_item_types on itt_id = groupID where kb3_items_destroyed.itd_kll_id =*/


$qry = new DBQuery();
$qry->execute("select kb3_items_destroyed.*,
kb3_invtypes.typeID, kb3_invtypes.groupID, kb3_invtypes.typeName, kb3_invtypes.capacity, kb3_invtypes.mass, kb3_invtypes.volume, kb3_invtypes.icon,
kb3_item_types.itt_slot
from
(kb3_items_destroyed left join kb3_invtypes on kb3_items_destroyed.itd_itm_id = kb3_invtypes.typeID)
left join kb3_item_types on itt_id = groupID where kb3_items_destroyed.itd_kll_id = '".$this->kill_id."'
union all
select kb3_items_dropped.*,
kb3_invtypes.typeID, kb3_invtypes.groupID, kb3_invtypes.typeName, kb3_invtypes.capacity, kb3_invtypes.mass, kb3_invtypes.volume, kb3_invtypes.icon,
kb3_item_types.itt_slot
from
(kb3_items_dropped left join kb3_invtypes on kb3_items_dropped.itd_itm_id = kb3_invtypes.typeID)
left join kb3_item_types on itt_id = groupID where kb3_items_dropped.itd_kll_id = '".$this->kill_id."'
ORDER BY groupID");

/*

[0] => Array
    (
        [Name] => E500 Prototype Energy Vampire
		[groupID] => 68
		[chargeSize] =>
		[itemid] => 16501
	)

*/

/*"id" => $row['typeID'],
"capacity" => $row['capacity'],
"mass" => $row['mass'],
"volume" => $row['volume'],
"icon" => $row['icon'],
"id" => $row['itt_slot']*/



while($row = $qry->getRow()) {
$qryA = new DBQuery();
$qryA->execute("select kb3_dgmtypeattributes.value
from kb3_dgmtypeattributes
inner join kb3_dgmattributetypes on kb3_dgmtypeattributes.attributeID = kb3_dgmattributetypes.attributeID
where typeID = '".$row['itd_itm_id']."' and attributeName = 'techLevel'");
$tech = $qryA->getRow();

$qryA = new DBQuery();
$qryA->execute("select kb3_dgmtypeattributes.value
from kb3_dgmtypeattributes
inner join kb3_dgmattributetypes on kb3_dgmtypeattributes.attributeID = kb3_dgmattributetypes.attributeID
where typeID = '".$row['itd_itm_id']."' and attributeName = 'metaLevel'");
$meta = $qryA->getRow();



	if($row['itt_slot'] == 0) {

		if($row['itd_itl_id'] == 6) {

			for($i = 0; $i < $row['itd_quantity']; $i++) {

				$fitting_array[6][] = Array(
					"name" => $row['typeName'],
					"groupID" => $row['groupID'],
					"itemid" => $row['itd_itm_id'],
					"id" => $row['typeID'],
					"capacity" => $row['capacity'],
					"mass" => $row['mass'],
					"volume" => $row['volume'],
					"icon" => $row['icon'],
					"slot" => $row['itt_slot'],
					"meta" => $meta['value'],
					"tech" => $tech['value']
				);

			}

		} else if($row['groupID'] == 87 // Capacitor Boosters
			|| $row['groupID'] == 910 // Sensor Boosters
			|| $row['groupID'] == 909 // Tracking Disruptors
			|| $row['groupID'] == 907 // Tracking Computers
			|| $row['groupID'] == 911 // Tracking Damp
		) {
			if($row['itd_itl_id'] == 2 || $row['itd_itl_id'] == 0) {
				$qry2 = new DBQuery();
$qry2->execute("select kb3_dgmtypeattributes.value
from kb3_dgmtypeattributes
inner join kb3_dgmattributetypes on kb3_dgmtypeattributes.attributeID = kb3_dgmattributetypes.attributeID
where typeID = '".$row['itd_itm_id']."' and kb3_dgmattributetypes.attributeName = 'launcherGroup'");
$usedgroupID = $qry2->getRow();

				$ammo_array[2][] = Array(
					"name" => $row['typeName'],
					"groupID" => $row['groupID'],
					"usedgroupID" => $usedgroupID['value'],
					"itemid" => $row['itd_itm_id'],
					"id" => $row['typeID'],
					"capacity" => $row['capacity'],
					"mass" => $row['mass'],
					"volume" => $row['volume'],
					"icon" => $row['icon'],
					"slot" => $row['itt_slot'],
					"meta" => $meta['value'],
					"tech" => $tech['value']
				);
			}
		} else {
			if($row['itd_itl_id'] != 4) {
				$qry2 = new DBQuery();
$qry2->execute("select kb3_dgmtypeattributes.value
from kb3_dgmtypeattributes
inner join kb3_dgmattributetypes on kb3_dgmtypeattributes.attributeID = kb3_dgmattributetypes.attributeID
where typeID = '".$row['itd_itm_id']."' and kb3_dgmattributetypes.attributeName = 'launcherGroup'");
$usedgroupID = $qry2->getRow();

				$ammo_array[1][] = Array(
					"name" => $row['typeName'],
					"groupID" => $row['groupID'],
					"usedgroupID" => $usedgroupID['value'],
					"itemid" => $row['itd_itm_id'],
					"id" => $row['typeID'],
					"capacity" => $row['capacity'],
					"mass" => $row['mass'],
					"volume" => $row['volume'],
					"icon" => $row['icon'],
					"slot" => $row['itt_slot'],
					"meta" => $meta['value'],
					"tech" => $tech['value']
				);
			}
		}


	} else {

		if($row['itd_itl_id'] != 4) {
		for($i = 0; $i < $row['itd_quantity']; $i++) {

		if($row['groupID'] == 87 // Capacitor Boosters
			|| $row['groupID'] == 910 // Sensor Boosters
			|| $row['groupID'] == 909 // Tracking Disruptors
			|| $row['groupID'] == 907 // Tracking Computers
			|| $row['groupID'] == 911 // Tracking Damp
		) {
			if($row['itd_itl_id'] == 2) {
				$qry2 = new DBQuery();
$qry2->execute("select kb3_dgmtypeattributes.value
from kb3_dgmtypeattributes
inner join kb3_dgmattributetypes on kb3_dgmtypeattributes.attributeID = kb3_dgmattributetypes.attributeID
where typeID = '".$row['itd_itm_id']."' and kb3_dgmattributetypes.attributeName = 'launcherGroup'");
$usedgroupID = $qry2->getRow();

				$ammo_array[2][] = Array(
					"name" => $row['typeName'],
					"groupID" => $row['groupID'],
					"usedgroupID" => $usedgroupID['value'],
					"itemid" => $row['itd_itm_id'],
					"id" => $row['typeID'],
					"capacity" => $row['capacity'],
					"mass" => $row['mass'],
					"volume" => $row['volume'],
					"icon" => $row['icon'],
					"slot" => $row['itt_slot'],
					"meta" => $meta['value'],
					"tech" => $tech['value']
				);
			}
		} else {
			if($row['groupID'] == 908) {
				$fitting_array[10][] = Array(
					"name" => $row['typeName'],
					"groupID" => $row['groupID'],
					"usedgroupID" => $usedgroupID['value'],
					"itemid" => $row['itd_itm_id'],
					"id" => $row['typeID'],
					"capacity" => $row['capacity'],
					"mass" => $row['mass'],
					"volume" => $row['volume'],
					"icon" => $row['icon'],
					"slot" => $row['itt_slot'],
					"meta" => $meta['value'],
					"tech" => $tech['value']
				);

			} else {

				$fitting_array[$row[itt_slot]][] = Array(
					"name" => $row['typeName'],
					"groupID" => $row['groupID'],
					"chargeSize" => "",
					"itemid" => $row['itd_itm_id'],
					"id" => $row['typeID'],
					"capacity" => $row['capacity'],
					"mass" => $row['mass'],
					"volume" => $row['volume'],
					"icon" => $row['icon'],
					"slot" => $row['itt_slot'],
					"meta" => $meta['value'],
					"tech" => $tech['value']
				);
			}
			}

		}
		}

	}

}




$length = count($ammo_array[1]);
$temp = array();
if(is_array($fitting_array[1]))
{

	$hiammo = array();
	foreach ($fitting_array[1] as $highfit)
	{
		$group = $highfit["groupID"];
		$itemID = $highfit["itemid"];
		$size = $highfit["chargeSize"];

		if($group == 483 // Modulated Deep Core Miner II, Modulated Strip Miner II and Modulated Deep Core Strip Miner II
			|| $group == 53 // Laser Turrets
			|| $group == 55 // Projectile Turrets
			|| $group == 74 // Hybrid Turrets
			|| ($group >= 506 && $group <= 511) // Some Missile Lauchers
			|| $group == 481 // Probe Launchers
			|| $group == 899 // Warp Disruption Field Generator I
			|| $group == 771 // Heavy Assault Missile Launchers
			|| $group == 589 // Interdiction Sphere Lauchers
			|| $group == 524 // Citadel Torpedo Launchers
		)
		{
			$found = 0;
			if ($group == 511)
			{ $group = 509; } // Assault Missile Lauchers uses same ammo as Standard Missile Lauchers
			if(is_array($ammo_array[1]))
			{
				$i = 0;
				while (!($found) && $i<$length)
				{
					$temp = array_shift($ammo_array[1]);

					if (($temp["usedgroupID"] == $group) && ($temp["size"] == $size))
					{
						$fitting_array[10][]=Array(
						'name' => $temp["name"],
						'itemid' => $temp["itemid"],
						"id" => $temp['id'],
						"capacity" => $temp['capacity'],
						"mass" => $temp['mass'],
						"volume" => $temp['volume'],
						"icon" => $temp['icon'],
						"slot" => $temp['itt_slot'],
						"meta" => $temp['meta'],
						"tech" => $temp['tech']
						);
						$found = 1;
					}
					array_push($ammo_array[1],$temp);
					$i++;
				}
			}
			if (!($found))
			{
				$hiammo[]=0;
			}
		} else
		{
			$hiammo[]=0;
		}
	}
}


$length = count($ammo_array[2]);
if(is_array($fitting_array[2]))
{
	$midammo = array();
	foreach ($fitting_array[2] as $midfit)
	{
		$group = $midfit["groupID"];
		$itemID = $highfit["itemid"];
		if($group == 76 // Capacitor Boosters
			|| $group == 208 // Remote Sensor Dampeners
			|| $group == 212 // Sensor Boosters
			|| $group == 291 // Tracking Disruptors
			|| $group == 213 // Tracking Computers
			|| $group == 209 // Tracking Links
			|| $group == 290 // Remote Sensor Boosters
		)
		{
			$found = 0;
			if(is_array($ammo_array[2]))
			{
				$i = 0;
				while (!($found) && $i<$length)
				{
					$temp = array_shift($ammo_array[2]);
					if ($temp["usedgroupID"] == $group)
					{
						$fitting_array[10][]=Array(
						'name' => $temp["name"],
						'itemid' => $temp["itemid"],
						"id" => $temp['id'],
						"capacity" => $temp['capacity'],
						"mass" => $temp['mass'],
						"volume" => $temp['volume'],
						"icon" => $temp['icon'],
						"slot" => $temp['itt_slot'],
						"meta" => $temp['meta'],
						"tech" => $temp['tech']
						);
						$found = 1;
					}
					array_push($ammo_array[2],$temp);
					$i++;
				}
			}
			if (!($found))
			{
				$midammo[]=0;
			}
		}
		else
		{
			$midammo[]=0;
		}
	}
}



if(!(empty($fitting_array[6])))
{
	foreach ($fitting_array[6] as $array_rowd)
	{
		$sort_by_named["name"][] = $array_rowd["name"];
	}
	array_multisort($sort_by_named["name"],SORT_ASC,$fitting_array[6]);
}

$slots = array(3 => "[empty low slot]",
	2 => "[empty mid slot]",
	1 => "[empty high slot]",
	5 => "[empty rig slot]",
	7 => "",
	6 => "",
	10 => "",
	11 => "");

	/*echo "<pre>";
	print_r($ammo_array);
	echo "</pre>";
	echo "<pre>";
	print_r($fitting_array);
	echo "</pre>";*/
	return $fitting_array;
}



};


//$fitter = new fitting;
?>