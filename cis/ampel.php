<?php
require_once('../config/cis.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/ampel.class.php');
require_once('../include/datum.class.php');
require_once('../include/phrasen.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);

if(is_user_logged_in())
{
	$user = get_uid();
	
	$ampel = new ampel();
	$ampel->loadUserAmpel($user);
	$rot=0;
	$gelb=0;
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
		}
		else
		{
			if($ts_vorlaufzeit<=$ts_now && $ts_now<=$ts_deadline)
			{
				$gelb++;
			}
		}
	}
	if($rot==0 && $gelb==0)
		echo '<a href="private/tools/ampelverwaltung.php" target="content" title="'.$p->t("tools/ampelsystem").'"><span style="color: #A5AFB6">'.$p->t("tools/ampelsystem").'</span></a>&nbsp;&nbsp;<span style="color: #A5AFB6">|</span>&nbsp;&nbsp;';
		
	if($rot>0 || $gelb>0)
	{
		echo '';
		if($rot>0 && $gelb==0)
			echo '<a href="private/tools/ampelverwaltung.php" target="content" title="'.$p->t("tools/ampelsystem").'"><span style="color: red;">'.$p->t("tools/ampelsystem").'</span></a>&nbsp;&nbsp;<span style="color: #A5AFB6">|</span>&nbsp;&nbsp;';
		if($gelb>0 && $rot==0)
			echo '<a href="private/tools/ampelverwaltung.php" target="content" title="'.$p->t("tools/ampelsystem").'"><span style="color: orange;">'.$p->t("tools/ampelsystem").'</span></a>&nbsp;&nbsp;<span style="color: #A5AFB6">|</span>&nbsp;&nbsp;';
		echo ' ';
	}
}
else
{
	echo "<script>window.setTimeout('loadampel()',5000);</script>";
	//echo microtime();
}
?>