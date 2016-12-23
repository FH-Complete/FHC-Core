<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * @brief bietet die Moeglichkeit zur Anzeige und
 * Aenderung der Zeitwuensche
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/zeitwunsch.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/sprache.class.php');

$sprache = getSprache(); 
$lang = new sprache(); 
$lang->load($sprache);
$p = new phrasen($sprache); 

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

$uid = get_uid();

if(!check_lektor($uid))
	die($p->t('global/keineBerechtigungFuerDieseSeite'));

$PHP_SELF = $_SERVER['PHP_SELF'];

if(isset($_GET['type']))
	$type=$_GET['type'];

$datum_obj = new datum();

//Stundentabelleholen
if(! $result_stunde=$db->db_query('SELECT * FROM lehre.tbl_stunde ORDER BY stunde'))
	die($db->db_last_error());
$num_rows_stunde=$db->db_num_rows($result_stunde);

// Zeitwuensche speichern
if (isset($type) && $type=='save')
{
	$zw = new zeitwunsch();
	
	for ($t=1;$t<7;$t++)
	{
		for ($i=0;$i<$num_rows_stunde;$i++)
		{
			$var='wunsch'.$t.'_'.$i;
			if(!isset($_POST[$var]))
				continue;
			$gewicht=$_POST[$var];
			$stunde=$i+1;
			
			$zw->mitarbeiter_uid = $uid;
			$zw->stunde = $stunde;
			$zw->tag = $t;
			$zw->gewicht = $gewicht;
			$zw->updateamum = date('Y-m-d H:i:s');
			$zw->updatevon = $uid;
			
			if (!$zw->exists($uid, $stunde, $t))
			{
				$zw->new = true;
				$zw->insertamum = date('Y-m-d H:i:s');
				$zw->insertvon = $uid;
			}
			else 
				$zw->new = false;
				
			if(!$zw->save())
				echo $zw->errormsg;
		}
	}
}

$zw = new zeitwunsch();
if(!$zw->loadPerson($uid))
	die($zw->errormsg);

$wunsch = $zw->zeitwunsch;


// Personendaten
$person = new benutzer();
if(!$person->load($uid))
	die($person->errormsg);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title><?php echo $p->t('zeitwunsch/zeitwunsch');?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
		<link href="../../../skin/flexcrollstyles.css" rel="stylesheet" type="text/css" />
		<script src="../../../include/js/flexcroll.js" type="text/javascript" ></script>
		<script type="text/javascript">
		// Pruefen ob nur die erlaubten Werte verwendet wurden
		function checkvalues()
		{
			var elem = document.getElementsByTagName('input');
			var error=false;
			
			for (var i = 0;i<elem.length;i++)
			{
				if(elem[i].name.match("^wunsch"))
				{
					if(!elem[i].value.match("^\-?[1-2]\d{0,0}$"))
						error=true;
				}
			}
			
			if(error)
			{
				alert('<?php echo $p->t('zeitwunsch/falscheWerteEingetragen');?>');
				return false;
			}
			else
				return true;
		}
		</script>
	</head>

	<body>
	<div class="flexcroll" style="outline: none;">
	<table>
	  <tr>	
	    <td>
	    <h1><?php echo $p->t('zeitwunsch/zeitwunsch');?></h1>
<!--Auskommentiert von Kindl, da sich der Hilfetext nicht vom Anleitungtext auf der Seite unterscheidet	
				<td class="ContentHeader" align="right">
				<A onclick="window.open('zeitwunsch_help.php','Hilfe', 'height=320,width=480,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');" class="hilfe" target="_blank">
				<font class="ContentHeader">
				<?php echo $p->t('zeitwunsch/help')?>&nbsp;
				</font>
				</A>
			</td>-->
		<?php
			echo "<h2>".$p->t('zeitwunsch/zeitwunschVon')." $person->titelpre $person->vorname $person->nachname $person->titelpost<br/></h2>";
			echo $p->t('zeitwunsch/tragenSieInDiesesNormwochenraster')."<br/><br/>";
			echo '<FORM name="zeitwunsch" method="post" action="zeitwunsch.php?type=save" onsubmit="return checkvalues()">
  				<TABLE>
    			<TR>';
    		
		  	echo '<th>'.$p->t('global/stunde').'<br>'.$p->t('global/beginn').'<br>'.$p->t('global/ende').'</th>';
			for ($i=0;$i<$num_rows_stunde; $i++)
			{
				$beginn=$db->db_result($result_stunde,$i,'"beginn"');
				$beginn=substr($beginn,0,5);
				$ende=$db->db_result($result_stunde,$i,'"ende"');
				$ende=substr($ende,0,5);
				$stunde=$db->db_result($result_stunde,$i,'"stunde"');
				echo "<th><div align=\"center\">$stunde<br>$beginn<br>$ende</div></th>";
			}
			
    		echo '</TR>';
			
			for ($j=1; $j<7; $j++)
			{
				echo '<TR><TD>'.$tagbez[$lang->index][$j].'</TD>';
			  	for ($i=0;$i<$num_rows_stunde;$i++)
				{
					if (isset($wunsch[$j][$i+1]))
						$index=$wunsch[$j][$i+1];
					else
						$index=1;
					//$id='bgcolor';
					//$id.=$index+3;
					$bgcolor=$cfgStdBgcolor[$index+3];
					echo '<TD style="padding-left: 5px; padding-right:5px;" align="center"  bgcolor="'.$bgcolor.'"><INPUT align="right" type="text" name="wunsch'.$j.'_'.$i.'" size="1" maxlength="2" value="'.$index.'"></TD>';
				}
				echo '</TR>';
			}
			
			echo '
			</TABLE><br>
			<INPUT type="hidden" name="uid" value="'.$uid.'">
			<INPUT type="submit" name="Abschicken" value="'.$p->t('global/speichern').'">
			';
			
			if($zw->updateamum!='')
			{
				echo '<font size="x-small">'.$p->t('zeitwunsch/letzteAenderung').': '.$datum_obj->formatDatum($zw->updateamum,'d.m.Y H:i:s').' '.$p->t('zeitwunsch/von').' '.$zw->updatevon.'</font>';
			}
			?>
			
			</FORM>
			<hr><?php 
			$href = "<a href='zeitsperre_resturlaub.php' class='Item'>";
			echo $p->t('zeitwunsch/formularZumEintragenDerZeitsperren', array($href));
			?>
			</a>
			<h3><?php echo $p->t('zeitwunsch/erklärung');?>:</h3>
			<P><?php echo $p->t('zeitwunsch/kontrollierenSieIhreZeitwuensche');?>!<BR><BR>
			</P>
			<TABLE align=center>
			  <TR>
			    <TH><B><?php echo $p->t('zeitwunsch/wert');?></B></TH>
			    <TH>
			      <DIV align="center"><B><?php echo $p->t('zeitwunsch/bedeutung');?></B></DIV>
			    </TH>
			  </TR>
			  <TR>
			    <TD>
			      <DIV align="right">2</DIV>
			    </TD>
			    <TD>&nbsp;&nbsp;<?php echo $p->t('zeitwunsch/hierMoechteIchUnterrichten');?></TD>
			  </TR>
			  <TR>
			    <TD>
			      <DIV align="right">1</DIV>
			    </TD>
			    <TD>&nbsp;&nbsp;<?php echo $p->t('zeitwunsch/hierKannIchUnterrichten');?></TD>
			  </TR>
			  <!--<TR>
			    <TD>
			      <DIV align="right">0</DIV>
			    </TD>
			    <TD>keine Bedeutung</TD>
			  </TR>-->
			  <TR>
			    <TD>
			      <DIV align="right">-1</DIV>
			    </TD>
			    <TD>&nbsp;&nbsp;<?php echo $p->t('zeitwunsch/nurInNotfaellen');?></TD>
			  </TR>
			  <TR>
			    <TD>
			      <DIV align="right">-2</DIV>
			    </TD>
			    <TD>&nbsp;&nbsp;<?php echo $p->t('zeitwunsch/hierAufGarKeinenFall');?></TD>
			  </TR>
			</TABLE>
			<h2><?php echo $p->t('zeitwunsch/folgendePunkteSindZuBeachten');?>:</h2>
			<OL>
			  <LI><?php echo $p->t('zeitwunsch/verwendenSieDenWertNur');?></LI>
			  <LI><?php echo $p->t('zeitwunsch/sperrenSieNurTermine');?></LI>
			  <LI><?php echo $p->t('zeitwunsch/esSolltenFuerJedeStunde');?></LI>
			</OL>
			<P><?php echo $p->t('lvplan/fehlerUndFeedback');?> <A class="Item" href="mailto:<?php echo MAIL_LVPLAN;?>"><?php echo $p->t('lvplan/lvKoordinationsstelle');?></A>.</P>
			</td>
		</tr>
	</table>
	</div>
	</body>
</html>
