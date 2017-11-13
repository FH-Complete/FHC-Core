<?php
require_once('../config/cis.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/ampel.class.php');
require_once('../include/datum.class.php');
require_once('../include/phrasen.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);
?>
<script src="../vendor/jquery/jqueryV1/jquery-1.12.4.min.js"></script>
<script>
$(document).ready(function()
{
	$('#ampel_div').html('');
});
function hide_ampel_div()
{
	document.getElementById("ampel_div").style.display="none";
}
</script>

<?php
if(is_user_logged_in())
{
	$user = get_uid();
	
	$ampel = new ampel();
	$ampel->loadUserAmpel($user);
	$rot=0;
	$gelb=0;
	$verpflichtend = false;
	$datum = new datum();
	foreach($ampel->result as $row)
	{
		$ts_deadline = $datum->mktime_fromdate($row->deadline);
		$vlz = "-".$row->vorlaufzeit." day";
		$ts_vorlaufzeit = strtotime($vlz, $ts_deadline);
		$ts_now = $datum->mktime_fromdate(date('Y-m-d'));
		if($ts_deadline < $ts_now)
		{
			$rot++;
			if ($row->verpflichtend == 't')
				$verpflichtend = true;
		}
		else
		{
			if($ts_vorlaufzeit<=$ts_now && $ts_now<=$ts_deadline)
			{
				$gelb++;
				if ($row->verpflichtend == 't')
					$verpflichtend = true;
			}
		}
	}
	if($rot==0 && $gelb==0)
		echo '<a href="private/tools/ampelverwaltung.php" target="content" title="'.$p->t("tools/ampelsystem").'"><span style="color: #A5AFB6">'.$p->t("tools/ampelsystem").'</span></a>&nbsp;&nbsp;<span style="color: #A5AFB6">|</span>&nbsp;&nbsp;';
		
	if($rot>0 || $gelb>0)
	{
		// Wenn es eine verpflichtende Ampel gibt, das Pupup im CIS anzeigen
		if ($verpflichtend == true)
		{
			echo '	<script>
					$(document).ready(function()
					{
						var html_content = \'<iframe src="'.APP_ROOT.'cis/private/tools/ampelverwaltung.php" name="ampel" frameborder="0" width="95%" height="95%"></iframe><button id="close_button" onclick="hide_ampel_div()">Close</button>\';
						$("#ampel_div").html(html_content);
					});
					</script>';
			
			echo '	<style type="text/css">
					#ampel_div
					{
						position:absolute;
						top: 20%;
						left: 15%;
						width: 70%;
						height: 60%;
						z-index: 1003;
						background-color: #fefefe;
						margin: auto;
						text-align: center;
						padding-top: 20px;
						border: 3px solid black;
						-webkit-box-shadow: 0px 0px 0px 2000px rgba(0,0,0,0.47);
						-moz-box-shadow: 0px 0px 0px 2000px rgba(0,0,0,0.47);
						box-shadow: 0px 0px 0px 2000px rgba(0,0,0,0.47);
						-webkit-animation-name: animatetop;
						-webkit-animation-duration: 0.4s;
						animation-name: animatetop;
						animation-duration: 0.4s
					}
					#close_button
					{
						position: relative;
						top: 5px;
						font-size: 150%;
						height: 50px;
						width: 100%;
					}
					</style>';
		}
		if($rot>0)
			echo '<a href="private/tools/ampelverwaltung.php" target="content" title="'.$p->t("tools/ampelsystem").'"><span style="color: red;">'.$p->t("tools/ampelsystem").'</span></a>&nbsp;&nbsp;<span style="color: #A5AFB6">|</span>&nbsp;&nbsp;';
		elseif($gelb>0)
			echo '<a href="private/tools/ampelverwaltung.php" target="content" title="'.$p->t("tools/ampelsystem").'"><span style="color: orange;">'.$p->t("tools/ampelsystem").'</span></a>&nbsp;&nbsp;<span style="color: #A5AFB6">|</span>&nbsp;&nbsp;';
	}
}
else
{
	echo "<script>window.setTimeout('loadampel()',5000);</script>";
	//echo microtime();
}
?>