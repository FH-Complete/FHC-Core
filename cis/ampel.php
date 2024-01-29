<?php
require_once('../config/cis.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/ampel.class.php');
require_once('../include/datum.class.php');
require_once('../include/phrasen.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);
?>
<script src="../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script>
	$(document).ready(function () {
		$('#ampel_div').html('');
	});

	function hide_ampel_div() {
		document.getElementById("ampel_div").style.display = "none";
	}
</script>

<?php
if (is_user_logged_in())
{
	$user = get_uid();

	$ampel = new ampel();
	$ampel->loadUserAmpel($user);
	$rot = 0;
	$gelb = 0;
	$gruen = 0;
	$verpflichtend = false;
	$cnt_verpflichtend = 0;
	$cnt_abgelaufen = 0;
	$cnt_notConf_notOverdue = 0;    //counts mandatory, not confirmed && not overdued ampeln (for popup)

	$datum = new datum();
	$now = $datum->mktime_fromdate(date('Y-m-d'));
	foreach ($ampel->result as $row)
	{
		$deadline = $datum->mktime_fromdate($row->deadline);
		$vorlaufzeit = $row->vorlaufzeit;
		$verfallszeit = $row->verfallszeit;
		$bestaetigt = $ampel->isBestaetigt($user, $row->ampel_id);
		$verpflichtend = $row->verpflichtend;
		$abgelaufen = false;

		$datum_liegt_vor_vorlaufzeit = false;
		$datum_liegt_nach_verfallszeit = false;

		if (!is_null($vorlaufzeit))
		{
			$datum_liegt_vor_vorlaufzeit = $now < strtotime('-'.$vorlaufzeit.' day', $deadline);
		}

		if (!is_null($verfallszeit))
		{
			$datum_liegt_nach_verfallszeit = $now > strtotime('+'.$verfallszeit.' day', $deadline);
		}

		//count mandatory
		if ($verpflichtend == 't')
		{
			$cnt_verpflichtend++;
		}

		//count overdue
		if ($datum_liegt_nach_verfallszeit)
		{
			$cnt_abgelaufen++;
		}

		//set status
		if ($bestaetigt)
		{
			$gruen++;
		}
		else
		{
			if ($now >= $deadline && !$datum_liegt_nach_verfallszeit && !$bestaetigt)
			{
				$rot++;
			}
			else
			{
				if (!$datum_liegt_nach_verfallszeit && !$datum_liegt_vor_vorlaufzeit)
				{
					$gelb++;
				}
			}
		}

		//count mandatory ampeln that are not confirmed and not overdue (for popup)
		if ($verpflichtend == 't' && !$bestaetigt && !$datum_liegt_nach_verfallszeit && !$datum_liegt_vor_vorlaufzeit)
		{
			$cnt_notConf_notOverdue++;
		}
	}

	//if at least ONE mandatory notification, which is not overdue -> trigger notification-POPUP
	if ($cnt_notConf_notOverdue > 0)
	{
		echo '	<script>
					$(document).ready(function()
					{
						function resizeIframe(obj)
						{
							obj.style.height = obj.contentWindow.document.body.scrollHeight + \'px\';
						}

						var html_content = \'<iframe src="'.APP_ROOT.'cis/private/tools/ampelverwaltung.php?verpflichtend=true" name="ampel" frameborder="0" width="100%" height="100% onload="resizeIframe(this) id="ampel_frame"></iframe><button id="close_button" class="btn btn-default" onclick="hide_ampel_div()">'.$p->t('tools/ampelClose').'</button>\';
						$("#ampel_div").html(html_content);
					});
					</script>';

		echo '	<style type="text/css">
					#ampel_div
					{
						position:absolute;
						top: 20%;
						left: 10%;
						right: 10%;
						width: 70%;
						height: 50%;
						scrolling: no;
						z-index: 1003;
						background-color: #fefefe;
						margin: auto;
						text-align: center;
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
						top: 0px;
						font-size: 150%;
						height: 50px;
						width: 100%;
						background-color: white;
						border: none;
						/*border-top: 4px solid black;*/
						/*border-bottom: 4px solid black;*/
					}
					</style>';
	}

	//show & color header ampel-link
	if ($rot > 0)
	{
		echo '<a href="private/tools/ampelverwaltung.php" target="content" title="'.$p->t("tools/ampelsystem").'"><span style="color: red;">'.$p->t("tools/ampelsystem").'</span></a>&nbsp;&nbsp;<span style="color: #A5AFB6">|</span>&nbsp;&nbsp;';
	}
	elseif ($gelb > 0)
	{
		echo '<a href="private/tools/ampelverwaltung.php" target="content" title="'.$p->t("tools/ampelsystem").'"><span style="color: orange;">'.$p->t("tools/ampelsystem").'</span></a>&nbsp;&nbsp;<span style="color: #A5AFB6">|</span>&nbsp;&nbsp;';
	}
	elseif ($rot == 0 || $rot <= $cnt_abgelaufen && $gelb == 0)
	{
		echo '<a href="private/tools/ampelverwaltung.php" target="content" title="'.$p->t("tools/ampelsystem").'"><span style="color: #A5AFB6">'.$p->t("tools/ampelsystem").'</span></a>&nbsp;&nbsp;<span style="color: #A5AFB6">|</span>&nbsp;&nbsp;';
	}
}
else
{
	echo "<script>window.setTimeout('loadampel()',1000);</script>";
}
?>
