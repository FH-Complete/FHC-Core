<?php
/* Copyright (C) 2008 Technikum-Wien
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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/**
 * Inventur
 *
 * Formular zur Unterstuetzung der Inventur
 * - Zuerst wird ein Ort oder eine Person ausgewaehlt fuer die die Inventur durchgefuehrt werden soll
 * - dann werden alle Betriebsmittel eingescannt. Diese werden automatisch der Person/Ort zugeteilt und das Inventurdatum wird gesetzt
 * - Uber den Punkt "Uebersicht" erhaelt man eine Liste mit den Betriebsmitteln die zwar zum Ort/Person zugeteilt sind, aber noch nicht gescannt wurden
 * - Diese koennen dann in den Dummy Raum verschoben oder auf ausgeschieden gesetzt werden
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/ort.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/organisationseinheit.class.php');
require_once('../../include/betriebsmittel.class.php');
require_once('../../include/betriebsmittelperson.class.php');
require_once('../../include/betriebsmitteltyp.class.php');
require_once('../../include/betriebsmittelstatus.class.php');
require_once('../../include/betriebsmittel_betriebsmittelstatus.class.php');
require_once('../../include/datum.class.php');

if (!$uid = get_uid())
	die('Keine UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

$oBenutzerberechtigung = new benutzerberechtigung();
$oBenutzerberechtigung->errormsg='';
$oBenutzerberechtigung->berechtigungen=array();
if (!$oBenutzerberechtigung->getBerechtigungen($uid))
	die('Sie haben keine Berechtigung !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

$errormsg=array();
$recht=false;
$schreib_recht=false;
$datum_obj = new datum();


$ort_kurzbz=trim((isset($_REQUEST['ort_kurzbz']) ? $_REQUEST['ort_kurzbz']:''));
$person_id=trim(isset($_REQUEST['person_id']) ? $_REQUEST['person_id']:'');
$work=trim(isset($_REQUEST['work']) ? $_REQUEST['work']:'');
$personen_namen='';

if($oBenutzerberechtigung->isBerechtigt('wawi/inventar', null, 'suid') )
	$schreib_recht=true;

// Pruefen ob Schreibrechte (Anzeigen der Aenderungsmoeglichkeit)
if($oBenutzerberechtigung->isBerechtigt('wawi/inventar:begrenzt',null,'su')	)
	$schreib_recht=true;
if (!$schreib_recht)
	die('Sie haben keine Berechtigung f&uuml;r diese Seite !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

if(is_numeric($person_id))
{
	$person_obj = new person();
	if($person_obj->load($person_id))
		$personen_namen = $person_obj->titelpre.' '.$person_obj->vorname.' '.$person_obj->nachname.' '.$person_obj->titelpost;
}
$ajax=trim(isset($_REQUEST['ajax']) ?$_REQUEST['ajax']:false);
$work=trim(isset($_REQUEST['work']) ?$_REQUEST['work']:false);

// Statusaenderung
if ($ajax!='')
{
	if(strtolower($ajax)=='inventarisieren')
	{
		if(isset($_REQUEST['inventarnummer']) && $_REQUEST['inventarnummer']!='')
		{
			$inventarnummer = $_REQUEST['inventarnummer'];
			$ort_kurzbz = $_REQUEST['ort_kurzbz'];
			$person_id = $_REQUEST['person_id'];
			$errormsg='';

			$betriebsmittel_obj = new betriebsmittel();
			if($betriebsmittel_obj->load_inventarnummer($inventarnummer))
			{
				$value['beschreibung']=$betriebsmittel_obj->beschreibung;
				$value['verwendung']=$betriebsmittel_obj->verwendung;
				$value['ort_old']=$betriebsmittel_obj->ort_kurzbz;
				$value['inventarnummer']=$inventarnummer;
				$value['betriebsmittel_id']=$betriebsmittel_obj->betriebsmittel_id;

				//Inventarisierung speichern und ggf den Ort anpassen
				if($ort_kurzbz!='' && $ort_kurzbz!=$betriebsmittel_obj->ort_kurzbz)
				{
					$betriebsmittel_obj->ort_kurzbz = $ort_kurzbz;
				}
				$betriebsmittel_obj->inventuramum = date('Y-m-d H:i:s');
				$betriebsmittel_obj->inventurvon = $uid;
				if(!$betriebsmittel_obj->save(false))
					$errormsg = $betriebsmittel_obj->errormsg;

				if($person_id!='')
				{
					$bmp = new betriebsmittelperson();
					$zuordnen=true;

					//Wenn das Betriebsmittel an eine andere Person ausgegeben ist, dann zurueckgeben
					if($bmp->load_betriebsmittelpersonen($betriebsmittel_obj->betriebsmittel_id))
					{
						if($bmp->person_id!=$person_id)
						{
							if($bmp->retouram=='')
							{
								$bmp->retouram=date('Y-m-d');
								if(!$bmp->save(false))
									$errormsg = $bmp->errormsg;
							}
						}
						else
							$zuordnen=false;
					}

					if($zuordnen)
					{
						//Neue Person zuordnen
						$bmp = new betriebsmittelperson();
						$bmp->person_id = $person_id;
						$bmp->betriebsmittel_id = $betriebsmittel_obj->betriebsmittel_id;
						$bmp->ausgegebenam = date('Y-m-d');
						$bmp->updateamum = date('Y-m-d H:i:s');
						$bmp->updatevon = $uid;
						$bmp->insertamum = date('Y-m-d H:i:s');
						$bmp->insertvon = $uid;
						if(!$bmp->save(true))
							$errormsg = $bmp->errormsg;
					}
				}
				$value['person_id']=$person_id;
				$value['errormsg']=$errormsg;

				echo json_encode($value);
			}
			else
			{
				echo 'ERROR LOADING:'.$inventarnummer;
			}
		}
	}
	exit;
}
if(isset($_POST['updateliste']))
{
	if(isset($_POST['work']) && $_POST['work']=='dummy')
	{
		//Eintraege in den Dummy Raum verschieben
		$ids = $_POST['bmid'];
		foreach($ids as $id)
		{
			$bm_obj = new betriebsmittel();
			if($bm_obj->load($id))
			{
				$bm_obj->ort_kurzbz='DUMMY';
				if(!$bm_obj->save(false))
					echo 'Fehler beim Speichern von ID:'.$id;
			}
			else
			{
				echo 'Fehler beim Laden von ID:'.$id;
			}
		}

		$work='uebersicht';
	}
	if(isset($_POST['work']) && $_POST['work']=='ausscheiden')
	{
		//Eintraege auf ausgeschieden setzen
		$ids = $_POST['bmid'];
		foreach($ids as $id)
		{
			$bm_obj = new betriebsmittel_betriebsmittelstatus();

			$bm_obj->betriebsmittel_id = $id;
			$bm_obj->betriebsmittelstatus_kurzbz = 'ausgeschieden';
			$bm_obj->datum = date('Y-m-d');
			$bm_obj->insertamum = date('Y-m-d H:i:s');
			$bm_obj->insertvon = $uid;
			$bm_obj->updateamum = date('Y-m-d H:i:s');
			$bm_obj->updatevon = $uid;
			$bm_obj->new = true;
			if(!$bm_obj->save())
				echo 'Fehler beim Speichern von ID:'.$id;
		}

		$work='uebersicht';
	}
	else
	{
		// Verschiebung in einen anderen Raum
		$ids = $_POST['bmid'];
		foreach($ids as $id)
		{
			$bm_obj = new betriebsmittel();
			if($bm_obj->load($id))
			{
				$bm_obj->ort_kurzbz=$_POST['work'];
				if(!$bm_obj->save(false))
					echo 'Fehler beim Speichern von ID:'.$id;
			}
			else
			{
				echo 'Fehler beim Laden von ID:'.$id;
			}
		}

		$work='uebersicht';
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Inventar - Inventur</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../skin/jquery.css" type="text/css">
		<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
		<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
		<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css"/>
		<script type="text/javascript">
		var ajxFile = "<?php echo $_SERVER["PHP_SELF"];  ?>";
		var erfasst = new Array();
		$(document).ready(function() {
			if(document.getElementById('inventarnummer'))
				document.getElementById('inventarnummer').focus();

			$('#inventarnummer').keypress(function(event){
				if(event.keyCode=='13')
					inventarnummerchange();
			});
		})

		function inventarnummerchange()
		{
			var item=document.getElementById('inventarnummer');
			if (item.value.length>=10)
			{
				setTimeout('loadInventar()',500);
			}
		}

		function loadInventar()
		{
			var inventarnummer = document.getElementById('inventarnummer').value;
			erfasst.push(inventarnummer);

			$.ajax
			(
				{
					type: "POST",
					dataType: "json",
					url: ajxFile,
					data: "ajax=inventarisieren&person_id="+encodeURIComponent('<?php echo $person_id;?>')+"&ort_kurzbz="+encodeURIComponent('<?php echo $ort_kurzbz ?>')+"&inventarnummer=" + encodeURIComponent(inventarnummer),
					success: function(phpData)
					{
						var div = document.getElementById('inventarliste');
						var li = document.createElement("li");

						li.innerHTML = '<a href="inventar.php?betriebsmittel_id='+phpData.betriebsmittel_id+'" target="_blank">'
						+phpData.inventarnummer+'<\/a>'
						+' - '+phpData.beschreibung+' - '+phpData.verwendung;

						div.appendChild(li);
						document.getElementById('inventarnummer').value='';
						document.getElementById('inventarnummer').focus();
						return;
					},
					error: function(XMLHttpRequest, textStatus, errorThrown)
					{
						var div = document.getElementById('inventarliste');
						var p = document.createElement("p");
						var txt = document.createTextNode("Error:"+inventarnummer);
						ErrorSound();
						p.appendChild(txt);
						div.appendChild(p);
						document.getElementById('inventarnummer').value='';
						document.getElementById('inventarnummer').focus();
					}
				}
			);
		}

		function formatItem(row)
		{
		    return row[0] + " <i>" + row[1] + "<\/i> ";
		}
		function ErrorSound()
		{
		    var audioElement = document.getElementById('sound1');
		    audioElement.play();
		}
		</script>
	</head>
	<body>

<audio src="<?php echo APP_ROOT;?>skin/sounds/inventar_error.ogg" id="sound1"/>
  Your browser does not support the audio tag.
</audio>
	<h1 title="Anwender:<?php echo $uid ?>">&nbsp;Inventar - Inventur&nbsp;</h1>
    <form name="sendform" action="<?php echo $_SERVER["PHP_SELF"];  ?>" method="post" enctype="application/x-www-form-urlencoded">
	<div>
		<table class="navbar">
			<tr>
				<td><label for="ort_kurzbz">Ort</label>&nbsp;
						<input id="ort_kurzbz" name="ort_kurzbz" size="10" maxlength="40" value="<?php echo $ort_kurzbz;?>" />&nbsp;
						<script type="text/javascript">

						function selectItem(li) {
						   return false;
						}

						$(document).ready(function() {
							$('#ort_kurzbz').autocomplete({
								source: "inventar_autocomplete.php?work=inventar_ort",
								minLength:2,
								response: function(event, ui)
								{
									//Value und Label fuer die Anzeige setzen
									for(i in ui.content)
									{
										ui.content[i].value=ui.content[i].ort_kurzbz;
										ui.content[i].label=ui.content[i].ort_kurzbz+" "+ui.content[i].bezeichnung;
									}
								},
								select: function(event, ui)
								{
									ui.item.value=ui.item.ort_kurzbz;
								}
							});
							/*  $('#ort_kurzbz').autocomplete('inventar_autocomplete.php', {
								minChars:2,
								matchSubset:1,matchContains:1,
								width:300,
								cacheLength:0,
								onItemSelect:selectItem,
								formatItem:formatItem,
								extraParams:{'work':'inventar_ort'
											,'betriebsmitteltyp':$("#betriebsmitteltyp").val()
											,'betriebsmittelstatus_kurzbz':$("#betriebsmittelstatus_kurzbz").val() }
							  }); */
					  });
						</script>
				</td>

				<td>&nbsp;<label for="person_id">Mitarbeiter</label>&nbsp;
					<input id="person_id" name="person_id" size="13" maxlength="14" value="<?php echo $person_id; ?>">
						<script type="text/javascript">

						$(document).ready(function()
						{
							$('#person_id').autocomplete({
								source: "inventar_autocomplete.php?work=person",
								minLength:4,
								response: function(event, ui)
								{
									//Value und Label fuer die Anzeige setzen
									for(i in ui.content)
									{
										ui.content[i].value=ui.content[i].person_id;
										ui.content[i].label=ui.content[i].person_id+' '+ui.content[i].anrede+' '+ui.content[i].titelpre+' '+ui.content[i].vorname+' '+ui.content[i].nachname+' '+ui.content[i].funktion;
									}
								},
								select: function(event, ui)
								{
									ui.item.value=ui.item.person_id;
								}
							});
							/*  $('#person_id').autocomplete('inventar_autocomplete.php',
							  {
								minChars:4,
								matchSubset:1,matchContains:1,
								width:400,
								formatItem:formatItem,
								extraParams:{'work':'person' }
							  }); */
					  });
					</script>
					<?php
						echo $personen_namen;
					?>
				</td>
				<td  class="ac_submit">&nbsp;<a href="javascript:document.sendform.work.value='inventarisieren';document.sendform.submit();"><img src="../../skin/images/application_go.png" alt="suchen" />&nbsp;Inventur starten</a></td>
				<td  class="ac_submit">&nbsp;<a href="javascript:document.sendform.work.value='uebersicht';document.sendform.submit();"><img src="../../skin/images/application_go.png" alt="suchen" />&nbsp;&Uuml;bersicht - keine Inventur</a></td>
			</tr>
		</table>
	</div>
	<input type="hidden" name="work" value="" />
	</form>
<?php
// ----------------------------------------
// Inventardaten - lesen
// ----------------------------------------
if($ort_kurzbz!='')
{
	$ort_obj = new ort();
	if(!$ort_obj->load($ort_kurzbz))
		die('Der eingetragene Ort ist ungueltig');
}

if($person_id!='')
{
	$person_obj = new person();
	if(!$person_obj->load($person_id))
		die('Die eingetragene Person ist ungueltig');
}
$oBetriebsmittel = new betriebsmittel();
if($work=='inventarisieren')
{
	if($ort_kurzbz!='' || $person_id!='')
	{
		echo '
		<span style="font-size:small">Inventur f&uuml;r '.$ort_kurzbz.' '.$personen_namen.'</span>
		<hr />
		<label for="inventarnummer">Inventarnummer: </label>&nbsp;
		<input id="inventarnummer" name="inventarnummer" type="text" size="10" maxlength="30">&nbsp;
		<hr />
		<div id="inventarliste">
		</div>';

	}
	else
	{
		echo 'Ort oder Person muss angegeben werden';
	}
}
elseif($work=='uebersicht')
{
	echo '<hr>Die folgenden Betriebsmittel wurden in den letzten 20 Wochen nicht inventarisiert und sind zugeordnet:<br /><br />';

	$qry = "SELECT * FROM wawi.tbl_betriebsmittel LEFT JOIN wawi.tbl_bestellung USING(bestellung_id)
			WHERE
				(inventuramum is null OR inventuramum < now()-'20 weeks'::interval)";
	if($ort_kurzbz!='')
		$qry.="	AND ort_kurzbz='".addslashes($ort_kurzbz)."'";
	if($person_id!='')
	{
		//Letzte zugeteilte Person filtern
		$qry.="
		AND EXISTS (
			SELECT person_id
			FROM wawi.tbl_betriebsmittelperson
			WHERE
				retouram IS NULL
				AND betriebsmittel_id=tbl_betriebsmittel.betriebsmittel_id
				AND person_id='".addslashes($person_id)."'
		)";
	}
	//$qry.=" AND wawi.get_status_betriebsmittel(betriebsmittel_id) IN ('Aenderung','Inventar Extern','Inventur','Reparatur','vorhanden','keineZuordnung')";
	$qry.=" AND (SELECT betriebsmittelstatus_kurzbz
        FROM wawi.tbl_betriebsmittel_betriebsmittelstatus
        WHERE betriebsmittel_id=tbl_betriebsmittel.betriebsmittel_id
        ORDER BY datum desc,insertamum desc, betriebsmittelbetriebsmittelstatus_id desc
        LIMIT 1) IN ('Aenderung','Inventar Extern','Inventur','Reparatur','vorhanden','keineZuordnung')
        AND betriebsmitteltyp NOT IN('Zutrittskarte','Schluessel')";

	$db = new basis_db();
	if($result = $db->db_query($qry))
	{
		echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
		echo 'Anzahl:'.$db->db_num_rows($result);
		echo '<table>';
		echo '<tr class="liste">
				<th></th>
				<th>Inv.Nr</th>
				<th>Beschreibung</th>
				<th>Verwendung</th>
				<th>Typ</th>
				<th>Bestellnr</th>
				<th colspan="2">Inventur</th>
			 </tr>';
		$i=0;
		while($row = $db->db_fetch_object($result))
		{
			$i++;
			echo '<tr class="liste'.($i%2).'">';
			echo '<td><input type="checkbox" checked="checked" name="bmid[]" value="'.$row->betriebsmittel_id.'"/></td>';
			echo '<td><a href="inventar.php?betriebsmittel_id='.$row->betriebsmittel_id.'" target="_blank">'.$row->inventarnummer.'</a></td>';
			echo '<td>',$row->beschreibung,'</td>';
			echo '<td>',$row->verwendung,'</td>';
			echo '<td>',$row->betriebsmitteltyp,'</td>';
			echo '<td>',$row->bestell_nr,'</td>';
			echo '<td>',$row->inventuramum,'</td>';
			echo '<td>',$row->inventurvon,'</td>';
			echo '</tr>';
		}
		echo '</table><br />';
		echo '<SELECT name="work">
				<OPTION value="dummy">Verschieben in DUMMY Raum</OPTION>
				<OPTION value="ausscheiden">Status&auml;nderung - ausgeschieden</OPTION>';

		$ort = new ort();
		$ort->getAll();
		foreach($ort->result as $row_ort)
		{
			echo '<option value="'.$row_ort->ort_kurzbz.'">'.$row_ort->ort_kurzbz.'</option>';
		}
		echo '
			</SELECT>';
		echo '<input type="hidden" name="ort_kurzbz" value="'.$ort_kurzbz.'" />';
		echo '<input type="hidden" name="person_id" value="'.$person_id.'" />';
		echo '<input type="submit" name="updateliste" value="Durchf&uuml;hren" />';
		echo '</form>';
	}
}
?>
</body>
</html>
