<?php
/**
 *  The Misc class
 *  Holds all misc methods
 *
 *	@author Chris Sheppard (Spark's)
 *  @version 1.0
 *  Code in this document belongs to:
 *  @copyright Chris Sheppard
 *  EvE online own all module/ships/other details rights
 *  Runs within EvE Dev killboard
 *
  */
class Misc
{
	public static $simpleurl;
/**
 * displayOutput method
 * The info popup
 *
 * @param
 * @return (string)
 */
	public static function displayOutput() {
		$title = "EvE Ship Display Tool (v".FittingTools::$currentversion.") developed by Spark\'s (Chris Sheppard)";
		$body = "<p>Special thanks to Hans Glockenspiel (In-Game name) for helping out. Salvoxia for being a long term supporter and everyone else who contributed.</p>
		<p>The Stats may not be 100% correct but maybe corrected so please contact me, my aim is to make sure that these stats are correct.
		Any issues with the Display tool Please send Spark\'s in Game EvE Mail or go to the eve-dev forum to post: <a href=\'http://eve-id.net/forum/viewtopic.php?f=505&t=17295\' target=\'_blank\'>Here</a>.
		Please provide as much information as you can regarding the error.
		A link to the killmail would be great aswell.</p>
		<p><a href=\'".self::curPageURL()."mods/ship_display_tool/images/ShipInfo.jpg\' target=\'_blank\'>Click here for ship display</a></p>
		<p><h3>Change log:</h3>
		3.5: Major code improvements. Performance boost. Fix for the new version of the killboard. Assorted fixes scattered across all ships<br />
		3.0: Asorted fixes across all ships. Complete overhaul of the stat system. Clean up of the code.<br />
		2.9: Fixed Stealth bomber Powergrid issues. Fixed Acillary Shield bosters. Made ship images feed from Eve-online.<br />
		2.8: Fixed inferno modules and sorted new covert ops ships background positions<br />
		2.7: Some more fixes, especially to background colours. Now editable to match your background<br />
		2.6: Assorted fixes to CPU and power grid<br />
		2.5: New look simular to in game, better performance, CPU, Powergrid, Calibration, Final blow, Top damage, API verification, Turret and Missile added.<br />
		2.1: Fixed noob ships. Fixed display none base root sites. Again Chimeria Fixed. Classified Systems fixed. Damage 0 fixed. Images now use built in EDK4 OO Item to get image data. Simple URL with Ship mod Fixed.<br />
		2.0: No longer needs module images, gets them from Killboard. Works with EDK4. Added new ships to list<br />
		1.7: Fixing MWD and Bubble scripts<br />1.6: Cap injector fix<br />
		1.5: Minor Fixes<br />
		1.4: Fixed slot issue<br />
		1.3: Improved load performance again. Rework of code functionality. Page load much better than before. Changed the layout. Fixed ship positions. Fixed DPS on some ships. Added Super cap/Carrier/Dread drone counts with Drone Control link fix. Tweaks to the Tech III ships<br />
		1.2: Improved load performance<br />
		1.1: MWD stats fixed again<br />
		1.0: Add system colours. Fixed minior bugs<br />0.99: Added MWD icon. Added Region name on the kill display. Added new tags<br />
		0.98: Support for the new incusion ships<br />
		0.95: Fixed ship ID issue and realigned images<br />
		0.93: Fixed issue with Marauders<br />
		0.92: Fixed Citadel cruise launchers bug<br />
		0.9: Added admin support - Now you can select your panel background<br />
		0.75: Fixed % Bug on ships involved<br />
		0.73: Added Structure tank support<br />
		0.72: Fixed Drones displaying more than 5. Fixed portraits. Fixed some of the ship images<br />
		0.71: Added Chimeria and Hel images<br />
		0.7: Better support against errors. Added Missing mod slots. Minor fixes<br />
		0.6: better support for structures<br />
		0.55: Fixed Tech III propulsion and engineering sub systems. Minor Rework to Smartbombs and to Cap Batteries. Fixed Tech III ship images<br />
		0.51: Added support for Smart bombs. Fixed Info Screen<br />
		0.5: Displays Stats, icons and ship image with pilot stats
		</p>
		<p><a href=\'http://www.elementstudio.co.uk\' target=\'_blank\'>Element Studio production</a></p>
		";
		$display .= "<html>
		<head>
			<link rel=\'stylesheet\' type=\'text/css\' href=\'".self::curPageURL()."mods/ship_display_tool/style/style.css\' />
			<link rel=\'stylesheet\' type=\'text/css\' href=\'".self::curPageURL()."themes/default/default.css\' />
			<title>Ship Display Tool</title>
		</head>
		<body>
			<div id=\'frame\'>
				<div id=\'topImg\'>
					<img src=\'".self::curPageURL()."mods/ship_display_tool/images/backdrop.jpg\' alt=\'Banner\' />
				</div>
				<h1 id=\'titleBar\'>$title</h1>
				$body
			</div>
		</body>
		</html>";

		$display = str_replace(array("\r","\n"), "", $display);
		//$jscommand = "newwindow2=window.open('','','height=500,width=300,toolbar=no,scrollbars=yes');	var tmp = newwindow2.document;	tmp.write('<html><head><title>Ship Display Tool</title></head><body>".$display."</body></html>');tmp.close();";
		$jscommand = "newwindow2=window.open('','','height=501,width=526,toolbar=no,scrollbars=yes');
		var tmp = newwindow2.document;
		tmp.write('".$display."');
		tmp.close();";

		//return "" . $jscommand . "";
		return "javascript:" . htmlentities($jscommand, ENT_QUOTES) . " void(0);";
	}

/**
 * curPageURL method
 * Gets the current host URL for links within the system
 * @param
 * @return (string)
 */
	public static function curPageURL() {
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") $pageURL .= "s";
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

		return $pageURL;
	}

/**
 * getSystemColour method
 * Depending on what system the pilot was killed in, display a colour red being 0.0 light blue being 1.0
 *
 * @param $system (string)
 * @return (string)
 */
	public static function getSystemColour($system) {

		switch ($system) {
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

/**
 * ShortenText method
 * Used to shorten a long input string
 *
 * @param $text (string)
 * @param $chars (int)
 * @return (string)
 */
	public static function ShortenText($_text,$_chars) {
		$count = strlen($_text);
		$_text = substr($_text, 0, $_chars);
		if($count > $_chars) {
			$_text = $_text." ...";
		}
		return $_text;
	}

/**
 * awesome <pre> wrapper method
 *
 * @param $data (mixed)
 * @param $doDie (bool)
 * @return (bool) || (void)
 */
	public static function pre($data, $doDie = true) {
		echo "<pre>";

		if (is_array($data)) {
			print_r($data);
		} else {
			var_dump($data);
		}

		echo "</pre>";

		if ($doDie) {
			die();
		}

		return true;
	}
}