<?php
//require_once('common/includes/class.http.php');
require_once('common/includes/class.httprequest.php');
require_once('common/admin/admin_menu.php');

$version = "2.8";

/*$html .= "
<script src='http://code.jquery.com/jquery.min.js' type='text/javascript'></script>
<script type='text/javascript'>
$(document).ready(function(){
	$.getJSON('http://www.elementstudio.co.uk/downloads/v.json', function(data) {

	alert('here');

	})
.success(function() { alert('second success'); })
.error(function() { alert('error'); })
.complete(function() { alert('complete'); });
});
</script>";*/

$page = new Page('Ship Display tool - Settings');

$html .= "Ship Display Tool Admin page.<br />Created by Spark's.<br />Enjoy.";


$backgroundimg = config::get('ship_display_back');
if($backgroundimg == "") {
	$backgroundimg = "#222222";
}
$html .= "<br />
<form name=\"add\" action=\"?a=settings_ship_tool_kb&amp;step=add\" method=\"post\"><br /><br />
	<div style='float:left; width:100%;'>Select your mod background colour in hash, Example: #ffffff: <input type='text' name='sel_back' value='".$backgroundimg."' /></div>
	<div style='float:left; width:100%;'><input type=\"submit\" value=\"save\" /></div>
</form>
";




$html .= "<br /><br />Remember to report bugs to this post: <a href='http://eve-id.net/forum/viewtopic.php?f=505&t=17295'>http://eve-id.net/forum/viewtopic.php?f=505&t=17295</a>.<br /><br />Thanks";





if ($_POST) {
  $tool_back = $_POST["sel_back"];


  config::set('ship_display_back', $tool_back);

  Header("Location: ?a=settings_ship_tool_kb");
}



$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
