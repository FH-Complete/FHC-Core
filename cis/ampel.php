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
		//echo '<a href="private/tools/ampelverwaltung.php" target="content" title="'.$p->t("tools/ampelsystem").' '.$rot.' rote"><img src="../skin/images/doppelampel_grau.gif" alt="Ampel"></a>';
		echo '<a href="private/tools/ampelverwaltung.php" target="content" title="'.$p->t("tools/ampelsystem").'"><span style="color: #A5AFB6">'.$p->t("tools/ampelsystem").'</span></a>';
		
		
//	if($rot>0 || $gelb>0)
//	{
//		echo '<td width="26px" height="26px" align="center"><a href="private/tools/ampelverwaltung.php" target="content" title="Red Alert"><img src="../skin/images/glocke_aktiv.gif" alt="glocke"></a></td>';
//	}

		
	if($rot>0 || $gelb>0)
	{
		echo '';
		if($rot>0 && $gelb==0)
					//echo '<td width="26px" height="26px" align="center" style="background-image:url(../skin/images/glocke_aktiv.gif); background-repeat:no-repeat"><a href="private/tools/ampelverwaltung.php" target="content" title="Red Alert"><strong>'.$rot.'</strong></a></td>';
			//echo '<a href="private/tools/ampelverwaltung.php" target="content" title="'.$p->t("tools/ampelsystem").'"><img src="../skin/images/doppelampel_rot.gif" alt=""></a>';
			echo '<a href="private/tools/ampelverwaltung.php" target="content" title="'.$p->t("tools/ampelsystem").'"><span style="color: red; text-decoration: blink;">'.$p->t("tools/ampelsystem").'</span></a>';
		//if($rot>0 && $gelb>0)
			//echo ' <td></td>';
		//	echo '<a href="private/tools/ampelverwaltung.php" target="content" title="'.$p->t("tools/ampelsystem").'"><img src="../skin/images/doppelampel_rot_gelb.gif" alt=""></a>';
		if($gelb>0 && $rot==0)
					//echo '<td width="26px" height="26px" align="center" style="background-image:url(../skin/images/glocke_aktiv.gif); background-repeat:no-repeat"><a href="private/tools/ampelverwaltung.php" target="content" title="Yellow Alert"><strong>'.$gelb.'</strong></a></td>';
			//echo '<a href="private/tools/ampelverwaltung.php" target="content" title="'.$p->t("tools/ampelsystem").'"><img src="../skin/images/doppelampel_gelb.gif" alt=""></a>';
			echo '<a href="private/tools/ampelverwaltung.php" target="content" title="'.$p->t("tools/ampelsystem").'"><span style="color: #0086CC; text-decoration: blink;">'.$p->t("tools/ampelsystem").'</span></a>';
		echo ' ';
	}
}
else
{
	echo "<script>window.setTimeout('loadampel()',5000);</script>";
	//echo microtime();
}
?>