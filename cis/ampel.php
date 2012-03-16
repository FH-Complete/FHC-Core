<?php
require_once('../config/cis.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/ampel.class.php');
require_once('../include/datum.class.php');

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
	if($rot>0 || $gelb>0)
	{
		echo '[';
		if($rot>0)
			echo '<a href="private/tools/ampelverwaltung.php" target="content" title="Red Alert"><img src="../skin/images/ampel_rot.gif" style="vertical-align: bottom;"> '.$rot.'</a>';
		if($rot>0 && $gelb>0)
			echo ' | ';
		if($gelb>0)
			echo '<a href="private/tools/ampelverwaltung.php" target="content" title="Yellow Alert"><img src="../skin/images/ampel_gelb.png"  style="vertical-align: bottom;"> '.$gelb.'</a>';
		echo ' ]';
	}
}
else
{
	echo "<script>window.setTimeout('loadampel()',5000);</script>";
	//echo microtime();
}
?>