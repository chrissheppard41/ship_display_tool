<link rel='stylesheet' href='{$simpleurlheader}/mods/ship_display_tool/style/style.css' type='text/css' media='all' />

<div class='controllerFitter'>
<div id='fitcontainer'>

	<ul id='infoBar'>

		<li class="infosect">
			<ul id='victimBodycontainer'>
				<li class='liheader kb-table-header'><a href='{$getPilotNameURL}'>{$getPilotName}</a> lost a <a href='{$getPilotShipURL}'>{$getPilotShip}</a> ({$getPilotShipClass}) in <a href='{$getPilotLocURL}'>{$getPilotLoc}</a> - {$getPilotLocReg} ({$getPilotLocSec})</li>
				<li id="shipcontainer" class="kb-table-row-even">
					<div class="shipview">
						<!--div id='{$backdrop}' style='left:{$left}px;top:{$top}px;'></div-->
						<!--img src='{$simpleurlheader}img/ships/256_256/{$getShipIcon}.png' alt='' id='shipImg'/-->
						<img src='http://image.eveonline.com/Render/{$getShipIcon}_512.png' alt='' id='shipImg'/>


						<canvas id="shipCover" width="430" height="426"></canvas>
						<canvas id="shipcpu" width="72" height="152"></canvas>
						<canvas id="shipprg" width="152" height="72"></canvas>
						<canvas id="shipcal" width="65" height="102"></canvas>
						<script>
							//window.onload = function(){
								var canvas = document.getElementById("shipCover");
								var context = canvas.getContext("2d");
								var centerX = canvas.width / 2;
								var centerY = canvas.height / 2;
								var radius = 275;

								context.beginPath();
								context.arc(centerX, centerY, radius, 0, 2 * Math.PI, false);
								context.fillStyle = "rgba(0,0,0,0)";
								context.fill();
								//context.globalAlpha = 0;
								context.lineWidth = 150;
								context.strokeStyle = '{$ship_display_back}';
								context.stroke();


								var canvas2 = document.getElementById("shipcpu");
								var context2 = canvas2.getContext("2d");
								context2.moveTo({$percpuxs}, {$percpuys});
								context2.quadraticCurveTo({$percpux1}, {$percpuy1}, {$percpuxe}, {$percpuye});
								context2.lineWidth = 9;
								context2.strokeStyle = "#356160"; // line color
								context2.stroke();


								var canvas3 = document.getElementById("shipprg");
								var context3 = canvas3.getContext("2d");
								context3.moveTo({$perprgxs}, {$perprgys});
								context3.quadraticCurveTo({$perprgx1}, {$perprgy1}, {$perprgxe}, {$perprgye});
								context3.lineWidth = 9;
								context3.strokeStyle = "#67160a"; // line color
								context3.stroke();


								var canvas4 = document.getElementById("shipcal");
								var context4 = canvas4.getContext("2d");
								context4.moveTo({$percalxs}, {$percalys});
								context4.quadraticCurveTo({$percalx1}, {$percaly1}, {$percalxe}, {$percalye});
								context4.lineWidth = 9;
								context4.strokeStyle = "#4a5356";
								context4.stroke();


							//};
							function getStyle(className) {
								var classes = document.styleSheets[0].rules || document.styleSheets[0].cssRules
								for(var x=0;x<classes.length;x++) {
									if(classes[x].selectorText==className) {
											return classes[x].style.backgroundColor;
											break;
									}
								}
							}

						</script>
						<div id='fitting_view'></div>

						<ul>
							<li id='d_turret'></li>
							<li id='d_missile'></li>
							<li id='d_turcount_{$getTurUsed}'></li>
							<li id='d_miscount_{$getMisUsed}'></li>
							<li>mis</li>
						{if $modSlotsh}
							{foreach $modSlotsh as $value}
								<li id='h{$value@key}'><img src='{$value.iconloc}' alt="{$value.name}" title="{$value.name}" style='width:40px;height:40px;' /></li>
							{/foreach}
						{/if}
						{if $modSlotsm}
							{foreach $modSlotsm as $value}
								<li id='m{$value@key}'><img src='{$value.iconloc}' alt="{$value.name}" title="{$value.name}" style='width:40px;height:40px;' /></li>
							{/foreach}
						{/if}
						{if $modSlotsl}
							{foreach $modSlotsl as $value}
								<li id='l{$value@key}'><img src='{$value.iconloc}' alt="{$value.name}" title="{$value.name}" style='width:40px;height:40px;' /></li>
							{/foreach}
						{/if}
						{if $modSlotsr}
							{foreach $modSlotsr as $value}
								<li id='r{$value@key}'><img src='{$value.iconloc}' alt="{$value.name}" title="{$value.name}" style='width:40px;height:40px;' /></li>
							{/foreach}
						{/if}
						{if $modSlotss}
							{foreach $modSlotss as $value}
								<li id='s{$value@key}'><img src='{$value.iconloc}' alt="{$value.name}" title="{$value.name}" style='width:40px;height:40px;' /></li>
							{/foreach}
						{/if}
						</ul>

						<ul id="fitting_cal">
							<li>Calibration</li>
							<li>{$usedcal} / {$totcal}</li>
						</ul>

						<ul id="fitting_grid">
							<li>CPU</li>
							<li>{$usedcpu} / {$totcpu}</li>
							<li class="space_grid"></li>
							<li>Power grid</li>
							<li>{$usedprg} / {$totprg}</li>
						</ul>

						<ul id="km_posting">
							<li>API: <span>{if (bool)$extid}Yes{else}No{/if}</span></li>
							<li>Source: <span>{if $type == "API"}API{else if $type == "IP"}Manual{else if $type == "URL"}Fetch{else if $type == "CREST"}CREST{/if}</span></li>
							<li>Damage: <span>{$getPilotDam}</span></li>
							<li>Cost: <span>{$getPilotCos} isk</span></li>
						</ul>

					</div>

					<div id="dronebar">
						<ul class="containers">
							<li class="liheader kb-table-header"></li>

							<li class="libody kb-table-row-even">
								<ul>
									{if $modSlotsd}
										{foreach $modSlotsd as $value}
											<li><img src='{$value.iconloc}' alt="{$value.name}" title="{$value.name}" style='width:32px;height:32px;' /></li>
										{/foreach}
									{/if}
								</ul>
							</li>
						</ul>
					</div>

					<div id="cargobar">
						<ul class="containers">
							<li class="liheader kb-table-header"></li>

							<li class="libody kb-table-row-even">
								<ul>
									{if $modSlotsa}
										{foreach $modSlotsa as $value}
											<li><img src='{$value.iconloc}' alt="{$value.name}" title="{$value.name}" style='width:32px;height:32px;' /></li>
										{/foreach}
									{/if}
								</ul>
							</li>
						</ul>
					</div>

				</li>
				<li class="infosect">
					<ul id='victimBodycontainer'>
						<li class='libody kb-table-row-even'>
							<ul id="victimcol">
								<li class="port"><a href='{$getPilotNameURL}'><img src='{$getPilotPort}' alt='' /></a></li>
								<li class="portmini"><a href='{$getPilotCorpURL}'><img src='{$getCorpPort}' alt='{$getPilotCorp}' title='{$getPilotCorp}' /></a></li>
								<li class="portmini"><a href='{$getPilotAllianceURL}'><img src='{$getAlliPort}' alt='{$getPilotAlliance}' title='{$getPilotAlliance}' /></a></li>
							</ul>
							<ul id="victimcol1">
								<li>Name: <span class="r_wid"><a href='{$getPilotNameURL}'>{$getPilotName}</a></span></li>
								<li>Corp: <span class="r_wid"><a href='{$getPilotCorpURL}'>{$getPilotCorp}</a></span></li>
								<li>Alliance: <span class="r_wid"><a href='{$getPilotAllianceURL}'>{$getPilotAlliance}</a></span></li>
								<li>Date: <span class="r_wid">{$getPilotDate}</span></li>
							</ul>
							<ul id="victimcol2">
								<li>Ship: <span class="r_wid"><a href='{$getPilotShipURL}'>{$getPilotShip}</a><br />({$getPilotShipClass})</span></li>
								<li>Location: <span class="r_wid"><a href='{$getPilotLocURL}'>{$getPilotLoc}</a><br /> {$getPilotLocReg} ({$getPilotLocSec})</span></li>
							</ul>
						</li>
					</ul>
				</li>
			</ul>
		</li>
	</ul>



	<div id='statbar'>

		<ul class="containers">
			{if $getCapStable}
				<li class="liheader kb-table-header"><span class="arrow">Capacitor</span> <span class="right">Stable ({$capAmountMperc}%)</span></li>

				<li class="libody kb-table-row-even">
					<ul id="capContainer">
						{if $capAmountMperc > 0 && $capAmountMperc <= 10}
							<li class="cap_left" id="cap10"></li>
						{else if $capAmountMperc > 10 && $capAmountMperc <= 20}
							<li class="cap_left" id="cap20"></li>
						{else if $capAmountMperc > 20 && $capAmountMperc <= 30}
							<li class="cap_left" id="cap30"></li>
						{else if $capAmountMperc > 30 && $capAmountMperc <= 40}
							<li class="cap_left" id="cap40"></li>
						{else if $capAmountMperc > 40 && $capAmountMperc <= 50}
							<li class="cap_left" id="cap50"></li>
						{else if $capAmountMperc > 50 && $capAmountMperc <= 60}
							<li class="cap_left" id="cap60"></li>
						{else if $capAmountMperc > 60 && $capAmountMperc <= 70}
							<li class="cap_left" id="cap70"></li>
						{else if $capAmountMperc > 70 && $capAmountMperc <= 80}
							<li class="cap_left" id="cap80"></li>
						{else if $capAmountMperc > 80 && $capAmountMperc <= 90}
							<li class="cap_left" id="cap90"></li>
						{else}
							<li class="cap_left" id="cap100"></li>
						{/if}
						<li class="cap_right">{$getCapAmount} GJ / {$getCapRecharge}</li>
						<li class="cap_right">-{$totalCapUse} GJ/s +{$totalCapInjected} GJ/s</li>
					</ul>
				</li>
			{else}
				<li class="liheader kb-table-header"><span class="arrow">Capacitor</span> <span class="right">{$capAmountMperc}</span></li>

				<li class="libody kb-table-row-even">
					<ul id="capContainer">
						<li class="cap_left" id="cap0"></li>
						<li class="cap_right">{$getCapAmount} GJ / {$getCapRecharge}</li>
						<li class="cap_right">-{$totalCapUse} GJ/s +{$totalCapInjected} GJ/s</li>
					</ul>
				</li>
			{/if}
		</ul>

		<ul class="containers">
			<li class="liheader kb-table-header"><span class="arrow">Offense</span> <span class="right">{$getDamage} dps / {$getVolley} vol</span></li>

			<li class="libody kb-table-row-even">
				<ul class="tier3">
					<li id="turret" class="fixheight">{$getTurretDamage}</li>
					<li id="drone" class="fixheight">{$getDroneDamage}</li>
					<li id="missile" class="fixheight">{$getMissileDamage}</li>
				</ul>
			</li>
		</ul>

		<ul class="containers">
			<li class="liheader kb-table-header"><span class="arrow">Defense</span> <span class="right">{$getEffectiveHp} hp</span></li>

			<li class="libody kb-table-row-even">
				<ul class="tier5 header">
					<li class="title fixheight" id="{$getTankType}">{$getTankAmount}</li>
					<li id="headerEM"></li>
					<li id="headerTH"></li>
					<li id="headerKI"></li>
					<li id="headerEX"></li>
				</ul>
				<ul class="tier5 shield">
					<li class="title"><p>{$getShieldAmount}</p><p class="full">{$getShieldRecharge}</p></li>
					<li id="shieldEM"><span class="res_back" style="width:{$getShieldEMPS}px;"></span><span class="res_push">{$getShieldEM}</span></li>
					<li id="shieldTH"><span class="res_back" style="width:{$getShieldThPS}px;"></span><span class="res_push">{$getShieldTh}</span></li>
					<li id="shieldKI"><span class="res_back" style="width:{$getShieldKiPS}px;"></span><span class="res_push">{$getShieldKi}</span></li>
					<li id="shieldEX"><span class="res_back" style="width:{$getShieldExPS}px;"></span><span class="res_push">{$getShieldEx}</span></li>
				</ul>
				<ul class="tier5 armour">
					<li class="title fixheight">{$getArmorAmount}</li>
					<li id="armourEM"><span class="res_back" style="width:{$getArmorEMPS}px;"></span><span class="res_push">{$getArmorEM}</span></li>
					<li id="armourTH"><span class="res_back" style="width:{$getArmorThPS}px;"></span><span class="res_push">{$getArmorTh}</span></li>
					<li id="armourKI"><span class="res_back" style="width:{$getArmorKiPS}px;"></span><span class="res_push">{$getArmorKi}</span></li>
					<li id="armourEX"><span class="res_back" style="width:{$getArmorExPS}px;"></span><span class="res_push">{$getArmorEx}</span></li>
				</ul>
				<ul class="tier5 hull">
					<li class="title fixheight">{$getHullAmount}</li>
					<li id="hullEM"><span class="res_back" style="width:{$getHullEMPS}px;"></span><span class="res_push">{$getHullEM}</span></li>
					<li id="hullTH"><span class="res_back" style="width:{$getHullThPS}px;"></span><span class="res_push">{$getHullTh}</span></li>
					<li id="hullKI"><span class="res_back" style="width:{$getHullKiPS}px;"></span><span class="res_push">{$getHullKi}</span></li>
					<li id="hullEX"><span class="res_back" style="width:{$getHullExPS}px;"></span><span class="res_push">{$getHullEx}</span></li>
				</ul>
			</li>
		</ul>

		<ul class="containers">
			<li class="liheader kb-table-header"><span class="arrow">Targeting</span> <span class="right">{$getDistance} km</span></li>

			<li class="libody kb-table-row-even">
				<ul class="tier2">
					<li id="{$getSensorType}" class="fixheight">{$getSensorAmount} points</li>
					<li id="scanres" class="fixheight">{$getScan} mm</li>
					<li id="sigrad"><span class="full_nopad">{$getSigRadius} m</span>
						{if $mwdSigatureAct}
							<span class="full_nopad">{$mwdSigature} m</span>
						{/if}
					</li>
					<li id="targets" class="fixheight">{$getTarget}x</li>
				</ul>
			</li>
		</ul>

		<ul class="containers">
			<li class="liheader kb-table-header"><span class="arrow">Navigation</span> <span class="right">{$getShipSpeed} m/s</span></li>

			<li class="libody kb-table-row-even">
				<ul class="tier2" id="tier232">
					<li id="propmwd" class="fixheight">
						{$mwdActive}
						{if $mwdActiveAct}
							 m/s
						{/if}</li>
					<li id="propab" class="fixheight">
						{$abActive}
						{if $abActiveAct}
							 m/s
						{/if}
					</li>
				</ul>
			</li>
		</ul>

		<ul class="containers">
			<li class="infosect">
				<ul id='topDamBodycontianer'>
					<li class='liheader kb-table-header'>Top Damage</li>

					<li class='libody kb-table-row-even'>
						<a href='{$topgetPilotURL}'><img src='{$topgetPilotIcon}' alt='{$topgetPilotName}' title='{$topgetPilotName}' /></a>
						<a href='{$topgetCorpURL}'><img src='{$topgetCorpIcon}' alt='{$topgetCorpName}' title='{$topgetCorpName}' style="width:32px; height:32px; border:0px" /></a>
						<a href='{$topgetShipURL}'><img src='{$topgetShipIcon}' alt='{$topgetShipName}' title='{$topgetShipName}' style="width:32px; height:32px; border:0px" /></a>
						{if $topgetShipID != 0}
							<a href='{$topgetAllianceURL}'>
								<img src='{$topgetAllianceIcon}' alt='{$topgetAllianceName}' title='{$topgetAllianceName}' style="width:32px; height:32px; border:0px" />
							</a>
						{else}
							<img src='{$topgetAllianceIcon}' alt='{$topgetAllianceName}' title='{$topgetAllianceName}' style="width:32px; height:32px; border:0px" />
						{/if}


						{if $topgetWeaponID != 0}
							<a href='{$topgetWeaponURL}'>{$topgetWeaponIcon}</a>
						{else}
							{$topgetWeaponIcon}
						{/if}
					</li>
				</ul>
			</li>
			<li class="infosect">
				<ul id='finalBodycontianer'>
					<li class='liheader kb-table-header'>Final Blow</li>

					<li class='libody kb-table-row-even'>
						<a href='{$fingetPilotURL}'><img src='{$fingetPilotIcon}' alt='{$fingetPilotName}' title='{$fingetPilotName}' /></a>
						<a href='{$fingetCorpURL}'><img src='{$fingetCorpIcon}' alt='{$fingetCorpName}' title='{$fingetCorpName}' style="width:32px; height:32px; border:0px" /></a>
						<a href='{$fingetShipURL}'><img src='{$fingetShipIcon}' alt='{$fingetShipName}' title='{$fingetShipName}' style="width:32px; height:32px; border:0px" /></a>
						{if $fingetShipID != 0}
							<a href='{$fingetAllianceURL}'>
								<img src='{$fingetAllianceIcon}' alt='{$fingetAllianceName}' title='{$fingetAllianceName}' style="width:32px; height:32px; border:0px" />
							</a>
						{else}
							<img src='{$fingetAllianceIcon}' alt='{$fingetAllianceName}' title='{$fingetAllianceName}' style="width:32px; height:32px; border:0px" />
						{/if}


						{if $fingetWeaponID != 0}
							<a href='{$fingetWeaponURL}'>{$fingetWeaponIcon}</a>
						{else}
							{$fingetWeaponIcon}
						{/if}
					</li>
				</ul>
			</li>
		</ul>
	</div>

	<div id='infoIcon'>
		<a href='#' onclick='{$displayOutput}'><img src='{$simpleurlheader}/mods/ship_display_tool/images/flashImage/icon.png' alt='Info' title='Info' /></a>
	</div>
</div>
</div>