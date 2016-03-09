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
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
require_once('../../../../config/cis.config.inc.php');
require_once('../../../../config/global.config.inc.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/studiengang.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/lehreinheit.class.php');
require_once('../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/uebung.class.php');
require_once('../../../../include/beispiel.class.php');
require_once('../../../../include/studentnote.class.php');
require_once('../../../../include/datum.class.php');
require_once('../../../../include/legesamtnote.class.php');
require_once('../../../../include/lvgesamtnote.class.php');
require_once('../../../../include/zeugnisnote.class.php');
require_once('../../../../include/pruefung.class.php');
require_once('../../../../include/person.class.php');
require_once('../../../../include/benutzer.class.php');
require_once('../../../../include/mitarbeiter.class.php');
require_once('../../../../include/moodle19_course.class.php');
require_once('../../../../include/moodle24_course.class.php');
require_once('../../../../include/mail.class.php');
require_once('../../../../include/phrasen.class.php');
require_once('../../../../include/note.class.php');
require_once('../../../../include/notenschluessel.class.php');
require_once('../../../../include/studienplan.class.php');

$summe_stud=0;
$summe_t2=0;
$summe_komm=0;
$summe_ng=0;

$sprache = getSprache();
$p = new phrasen($sprache);

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

$debg=(isset($_REQUEST['debug'])?$_REQUEST['debug']:'');

$user = get_uid();
if(!check_lektor($user))
	die($p->t('global/keineBerechtigungFuerDieseSeite'));
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(isset($_GET['lvid']) && is_numeric($_GET['lvid'])) //Lehrveranstaltung_id
	$lvid = $_GET['lvid'];
else
	die($p->t('global/fehlerBeiDerParameteruebergabe'));

if(isset($_GET['lehreinheit_id']) && is_numeric($_GET['lehreinheit_id'])) //Lehreinheit_id
	$lehreinheit_id = $_GET['lehreinheit_id'];
else
	$lehreinheit_id = '';
//Laden der Lehrveranstaltung
$lv_obj = new lehrveranstaltung();
if(!$lv_obj->load($lvid))
	die($lv_obj->errormsg);

//Studiengang laden
$stg_obj = new studiengang($lv_obj->studiengang_kz);

$datum_obj = new datum();

if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
	$stsem = '';

if($stsem!='' && !check_stsem($stsem))
	die($p->t('anwesenheitsliste/studiensemesterIstUngueltig'));

$datum_obj = new datum();

$uebung_id = (isset($_GET['uebung_id'])?$_GET['uebung_id']:'');
$uid = (isset($_GET['uid'])?$_GET['uid']:'');

$noten_obj = new note();
$noten_obj->getAll();

echo '<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<title>Gesamtnote</title>
    <link href="../../../../skin/jquery.css" rel="stylesheet"  type="text/css"/>
    <link href="../../../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
    <script src="../../../../include/js/jquery1.9.min.js" type="text/javascript"></script>

	<style type="text/css">
	.td_datum
	{
		width:70px;
		text-align: left;
	}
	.td_note{
		width:50px;
		text-align:center;
	}
	.gradetable
	{
		background:#DCE4EF;
		border: 1px solid #FFF;
		font-size: 8pt;
		padding: 4px;
	}

	</style>
	<script language="JavaScript" type="text/javascript">
	var notenrequests=0;
	var notenrequests_arr=Array();
	var noten_array=Array();
';

$noten_array=array();
foreach($noten_obj->result as $row)
{
	echo "	noten_array['".$row->note."']='".addslashes($row->bezeichnung)."';\n";
	$noten_array[$row->note]['bezeichnung']=$row->bezeichnung;
	$noten_array[$row->note]['positiv']=$row->positiv;
	$noten_array[$row->note]['aktiv']=$row->aktiv;
	$noten_array[$row->note]['lehre']=$row->lehre;
}

?>
	function getOffset(pos)
	{
		var x,y;
		if (self.pageYOffset) // all except Explorer
		{
			x = self.pageXOffset;
			y = self.pageYOffset;
		}
		else if (document.documentElement && document.documentElement.scrollTop)
			// Explorer 6 Strict
		{
			x = document.documentElement.scrollLeft;
			y = document.documentElement.scrollTop;
		}
		else if(window.scrollX)
		{
			x = window.scrollX;
			y = window.scrollY;
		}
		else if (document.body) // all other Explorers
		{
			x = document.body.scrollLeft;
			y = document.body.scrollTop;
		}
		if(pos=='x')
			return x;
		else
			return y;
	}

    // ******************************************
    // * Note eines Studenten Speichern
    // ******************************************
	function saveLVNote(uid)
	{
		note = document.getElementById(uid).note.value;
		if(document.getElementById(uid).note)
			note_label = document.getElementById(uid).note.label;
		else
			note_label='';
		if(note=='')
		{
			alert('Bitte wählen Sie eine Note aus');
			return false;
		}
		if(document.getElementById(uid).punkte)
			punkte = document.getElementById(uid).punkte.value;
		else
			punkte='';

		note_orig = document.getElementById(uid).note_orig.value;

		//Request erzeugen und die Note speichern
	    stud_uid = uid;

		var jetzt = new Date();
		var ts = jetzt.getTime();

		var url= '<?php echo "lvgesamtnoteeintragen.php?lvid=".urlencode($lvid)."&stsem=".urlencode($stsem); ?>';
	    url += '&submit=1&student_uid='+encodeURIComponent(uid)+"&note="+encodeURIComponent(note)+"&punkte="+encodeURIComponent(punkte)+"&"+ts;

		$.ajax({
			type:"GET",
			url: url,
			success:function(result)
			{
				document.getElementById(uid).note_orig.value=noten_array[note];
		     	uid = stud_uid;
				var note = document.getElementById(uid).note.value;
	            var resp = result;
	            if (resp == "neu" || resp == "update" || resp == "update_f")
	            {
	            	notentd = document.getElementById("note_"+uid);
	            	while (notentd.childNodes.length>0)
	            	{
						notentd.removeChild(notentd.lastChild);
	            	}
					if(punkte!='')
						notentext = noten_array[note]+' ('+punkte+')';
					else
						notentext = noten_array[note];
	            	notenode = document.createTextNode(notentext);
                    notentd.appendChild(notenode);
					notenstatus = document.getElementById("status_"+uid);
					if (resp == "update_f")
                    	notenstatus.innerHTML = "<img src='../../../../skin/images/changed.png'>";
                }
                else
         		{
             		alert(resp);
             		document.getElementById(uid).note.value="";
         		}
  			},
  			error:function(result)
  			{
  				alert('Speichern der Note fehlgeschlagen');
  			}
  		});
	}

	// *************************************************
	// * Formular zum Eintragen einer Pruefung erstellen
	// *************************************************
	function pruefungAnlegen(uid,datum,note,lehreinheit_id,punkte,typ)
	{
		if(typeof(typ)=='undefined')
			typ = 'Termin2';
		var str = "<form name='nachpruefung_form'><table style='width:95%'><tr><td colspan='2' align='right'><a href='#' onclick='closeDiv();'>X</a></td></tr>";

		var anlegendiv = document.getElementById("nachpruefung_div");
		var y = getOffset('y');
		y = y+50;
		anlegendiv.style.top = y+"px";
		var x = getOffset('x');
		x = x+300;

		anlegendiv.style.left = x+"px";

		str += "<tr><td colspan='2'><b><?php echo $p->t('benotungstool/pruefungAnlegenFuer');?> "+uid+":</b></td></tr>";
		str += "<tr><td><?php echo $p->t('global/datum');?>:</td>";
		str += "<td><input type='hidden' name='uid' value='"+uid+"'><input type='hidden' name='le_id' value='"+lehreinheit_id+"'>";
		str += "<input type='hidden' name='typ' value='"+typ+"'>";
		str += "<input type='text' id='pruefungsdatum' name='datum' size='10' value='"+datum+"'> [DD.MM.YYYY]</td></tr>";

		<?php
		if(defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE)
		{
			echo 'str += "<tr><td>'.$p->t('benotungstool/punkte').':</td>";';
			echo 'str += "<td><input type=\'text\' id=\'pruefungspunkte\' name=\'punkte\' size=\'10\' value=\'"+punkte+"\' oninput=\'PruefungPunkteEingabe()\'></td></tr>";';
		}
		?>

		str += "<tr><td><?php echo $p->t('benotungstool/note');?>:</td>";

		str +='<?php
			echo '<td><select name="note" id="pruefungnoteselect">';
			echo '<option value="">-- keine Auswahl --</option>';
			foreach($noten_obj->result as $row_note)
			{
				if($row_note->lehre && $row_note->aktiv)
					echo '<option value="'.$row_note->note.'">'.$row_note->bezeichnung.'</option>';
			}
			echo '</select></td>';
		?>';
		str += "</tr><tr><td colspan='2' align='center'><input id='pruefungsnotensave' type='button' name='speichern' value='<?php echo $p->t('global/speichern');?>' onclick='pruefungSpeichern();'></td></tr>";
		str += "</table></form>";
		anlegendiv.innerHTML = str;
		anlegendiv.style.visibility = "visible";
		$('#pruefungsdatum').datepicker();
		$('#pruefungnoteselect').val(note);
	}

	// **********************************************
	// * Speichern der Pruefung
	// **********************************************
	function pruefungSpeichern()
	{
		var note = document.nachpruefung_form.note.value;
		var typ=document.nachpruefung_form.typ.value;
		if(document.nachpruefung_form.punkte)
			var punkte = document.nachpruefung_form.punkte.value;
		else
			var punkte='';
		var datum = document.nachpruefung_form.datum.value;
		var datum_test = datum.split(".");
		if (datum_test[0].length != 2 || datum_test[1].length != 2 || datum_test[2].length!=4 || isNaN(datum_test[2]) || datum_test[1]>12 || datum_test[1]<1 || datum_test[0]>31 || datum_test[0]<1)
			alert("Invalid Date Format: DD.MM.YYYY");
		else
		{
			var anlegendiv = document.getElementById("nachpruefung_div");

			var note = document.nachpruefung_form.note.value;
			if (note == "" || isNaN(note))
			{
				document.nachpruefung_form.note.value = "9";
				note = "9";
			}
			var uid = document.nachpruefung_form.uid.value;
			var lehreinheit_id = document.nachpruefung_form.le_id.value;

		    var jetzt = new Date();
			var ts = jetzt.getTime();
		    var url= '<?php echo "nachpruefungeintragen.php?lvid=$lvid&stsem=$stsem"; ?>';
		    url += '&submit=1&student_uid='+uid+'&note='+note+'&datum='+datum+'&lehreinheit_id_pr='+lehreinheit_id+'&punkte='+punkte+'&typ='+typ+'&'+ts;

			$.ajax({
				type:"GET",
				url: url,
				success:function(result)
				{
					var anlegendiv = document.getElementById("nachpruefung_div");
					var datum = 	document.nachpruefung_form.datum.value;
					var note = document.nachpruefung_form.note.value;
					var typ = document.nachpruefung_form.typ.value;
					if(document.nachpruefung_form.punkte)
						var punkte = document.nachpruefung_form.punkte.value;
					else
						var punkte='';
					var uid = document.nachpruefung_form.uid.value;
					var lehreinheit_id = document.nachpruefung_form.le_id.value;
		            var resp = result;

		            if (resp == "neu" || resp == "update" || resp == "update_f" || resp == "update_pr")
		            {
		            	if (resp != "update_pr")
		            	{
			                notentd = document.getElementById("note_"+uid);
			            	while (notentd.childNodes.length>0)
			            	{
								notentd.removeChild(notentd.lastChild);
			            	}
							if(punkte!='')
								var notentext = noten_array[note]+' ('+punkte+')';
							else
								var notentext = noten_array[note];
			            	notenode = document.createTextNode(notentext);
		                    notentd.appendChild(notenode);
						}
						notenstatus = document.getElementById("status_"+uid);
						if (resp == "update_f")
	                    	notenstatus.innerHTML = "<img src='../../../../skin/images/changed.png'>";
	                    document.getElementById("lvnoteneingabe_"+uid).style.visibility = "hidden";

	   			 		anlegendiv.innerHTML = "";
						anlegendiv.style.visibility = "hidden";

						document.getElementById("span_"+typ+"_"+uid).innerHTML = "<table><tr><td class='td_datum'>"+datum+"</td><td class='td_note'>"+noten_array[note]+"<td><input type='button' name='anlegen' value='<?php echo $p->t('global/aendern'); ?>' onclick='pruefungAnlegen(\""+uid+"\",\""+datum+"\",\""+note+"\",\""+lehreinheit_id+"\",\""+punkte+"\",\""+typ+"\")'></td></tr></table>";
					}
	  			},
	  			error:function(result)
	  			{
	  				alert('Request fehlgeschlagen');
	  			}
	  		});
	    }
	}

 	function closeDiv()
 	{
 		var anlegendiv = document.getElementById("nachpruefung_div");
 		anlegendiv.innerHTML = "";
 		anlegendiv.style.visibility = "hidden";
 	}

 	function OnFreigabeSubmit()
	{
		if(document.getElementById('textbox-freigabe-passwort').value.length==0)
		{
			alert('Bitte geben Sie zuerst Ihr Passwort ein!');
			return false;
		}
		return true;
	}

	/**
	 * Wird bei der Punkteeingabe aufgerufen und laedt
	 * die dazupassende Noten anhand des Notenschluessels
	 */
	function PunkteEingabe(idx)
	{
		var punkte = $('#textbox-punkte-'+idx).val();
		punkte = punkte.replace(',','.');
		// Request absetzen und Note zu den Punkten holen
		if(punkte!='')
		{
			if(typeof(notenrequests_arr[idx])=='undefined')
				notenrequests_arr[idx]=0;
			notenrequests_arr[idx]=notenrequests_arr[idx]+1;
			$('#button-note-save-'+idx).prop('disabled',true);
			$.ajax({
				type:"POST",
				url:"lvgesamtnote_worker.php",
				data: { lehrveranstaltung_id: '<?php echo $lvid; ?>',
						punkte: punkte,
						work: 'getGradeFromPoints',
						studiensemester_kurzbz: '<?php echo $stsem;?>'
					},
				success:function(result)
				{
				    note=result;
					notenrequests_arr[idx]=notenrequests_arr[idx]-1;

					var notendropdown = $('#dropdown-note-'+idx);
					notendropdown.val(note);
					notendropdown.prop('disabled',true);

					if(notenrequests_arr[idx]==0)
					{
						$('#button-note-save-'+idx).prop('disabled',false);
					}
	  			},
	  			error:function(result)
	  			{
					notenrequests_arr[idx]=notenrequests_arr[idx]-1;
					alert('Notenermittlung fehlgeschlagen');
	  			}
	  		});
		}
		else
		{
			var notendropdown = $('#dropdown-note-'+idx);
			notendropdown.prop('disabled',false);
		}
	}

	/**
	 * Wird bei der Punkteeingabe aufgerufen und laedt
	 * die dazupassende Noten anhand des Notenschluessels
	 */
	function PruefungPunkteEingabe()
	{
		var punkte = $('#pruefungspunkte').val();
		punkte = punkte.replace(',','.');

		// Request absetzen und Note zu den Punkten holen
		if(punkte!='')
		{
			notenrequests = notenrequests+1;
			$('#pruefungsnotensave').prop('disabled',true);
			$.ajax({
				type:"POST",
				url:"lvgesamtnote_worker.php",
				data: { lehrveranstaltung_id: '<?php echo $lvid; ?>',
						punkte: punkte,
						work: 'getGradeFromPoints',
						studiensemester_kurzbz: '<?php echo $stsem;?>'
					},
				success:function(result)
				{
					notenrequests = notenrequests-1;
				    note=result;

					var notendropdown = $('#pruefungnoteselect');
					notendropdown.val(note);
					notendropdown.prop('disabled',true);

					if(notenrequests==0)
						$('#pruefungsnotensave').prop('disabled',false);
	  			},
	  			error:function(result)
	  			{
					notenrequests = notenrequests-1;
					if(notenrequests==0)
						$('#pruefungsnotensave').prop('disabled',false);
	  				alert('Noten ermittlung fehlgeschlagen');
	  			}
	  		});
		}
		else
		{
			var notendropdown = $('#pruefungnoteselect');
			notendropdown.prop('disabled',false);
		}
	}

	// ****
	// * Oeffnet ein Fenster fuer den Import von Noten aus dem Excel
	// ****
	function GradeImport()
	{
		var str = "<form name='gradeimport_form'><center><table style='width:95%'><tr><td colspan='2' align='right'><a href='#' onclick='closeDiv();'>X</a></td></tr>";

		var anlegendiv = document.getElementById("nachpruefung_div");
		var y = getOffset('y');
		y = y+50;
		anlegendiv.style.top = y+"px";

		str += '<tr><td><?php echo $p->t('benotungstool/importAnweisung');?>:</td>';
		str += '<td></td><tr><td><textarea id="noteimporttextarea" name="notenimport"></textarea></td></tr>';
		str += "<tr><td><input type='button' name='speichern' value='<?php echo $p->t('global/speichern');?>' onclick='saveGradeBulk();'>";
		str += "</td><td></td></tr></table></center></form>";
		anlegendiv.innerHTML = str;
		anlegendiv.style.visibility = "visible";
		$('#noteimporttextarea').focus();
	}

	// Speichert die Noten ueber den Import
	function saveGradeBulk()
	{
		data = $('#noteimporttextarea').val();
		closeDiv();

		//Reihen ermitteln
		var rows = data.split("\n");
		var i=0;
		var params='';

		var gradedata = {};

		for(row in rows)
		{
			zeile = rows[row].split("	");

			if(zeile[0]!='' && zeile[1]!='')
			{
				gradedata['matrikelnr_'+i]=zeile[0];
				<?php
				if(CIS_GESAMTNOTE_PUNKTE)
					echo "gradedata['punkte_'+i]= zeile[1];";
				else
					echo "gradedata['note_'+i]= zeile[1];";
				?>

				i++;
			}
		}

		if(i>0)
		{

		    var jetzt = new Date();
			var ts = jetzt.getTime();
		    var url= '<?php echo "lvgesamtnoteeintragen.php?lvid=".urlencode($lvid)."&stsem=".urlencode($stsem); ?>';
		    url += '&submit=1&'+ts;
		    $.ajax({
				type:"POST",
				url: url,
				data: gradedata,
				success:function(result)
				{
				    var resp = result;
		            if (resp!='')
		            {
						alert(resp);
	                }
	                window.location.reload();
	  			},
	  			error:function(result)
	  			{
	  				alert('Request fehlgeschlagen');
	  			}
	  		});

		}
		else
		{
			alert('Zum Importieren der Noten markieren sie die Spalten Kennzeichen und Note im Excel-File und kopieren sie diese in die Zwischenablage. Drücken sie danach diesen Knopf erneut, um die Noten zu importieren');
		}
	}

//-->
</script>
</head>

<body>

<?php
//wenn eine Uebung oder LE-Gesamtnote existiert die Note aus dem Uebungstool uebernehmen
//sonst aus dem Moodle
$qry = "SELECT
			1
		FROM
			lehre.tbl_lehrveranstaltung
			JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id)
			JOIN campus.tbl_uebung USING(lehreinheit_id)
		WHERE
			studiensemester_kurzbz=".$db->db_add_param($stsem)." AND
			lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER)."
		UNION
		SELECT
			1
		FROM
			campus.tbl_legesamtnote
		WHERE
			lehreinheit_id in (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
								WHERE studiensemester_kurzbz=".$db->db_add_param($stsem)." AND
								lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER).")

		";
if($result = $db->db_query($qry))
{
	if($db->db_num_rows($result)>0)
		$grade_from_moodle=false;
	else
	{
		if(MOODLE)
			$grade_from_moodle=true;
		else
			$grade_from_moodle=false;
	}
}
else
	die($p->t('global/fehleraufgetreten'));

//Kopfzeile
echo '
<table width="100%"><tr><td>
<h1>'.$p->t('benotungstool/gesamtnote').'</h1>
<h2>'.$lv_obj->bezeichnung_arr[$sprache].'</h2>
</td><td align="right">';

//Studiensemester laden
$stsem_obj = new studiensemester();
if($stsem=='')
	$stsem = $stsem_obj->getaktorNext();
$stsem_obj->getAll();

//Studiensemester DropDown
$stsem_content = $p->t('global/studiensemester').": <SELECT name='stsem' onChange=\"self.location=this.options[this.selectedIndex].value\">";
foreach($stsem_obj->studiensemester as $studiensemester)
{
	$selected = ($stsem == $studiensemester->studiensemester_kurzbz?'selected':'');
	$stsem_content.= "<OPTION value='lvgesamtnoteverwalten.php?lvid=$lvid&stsem=$studiensemester->studiensemester_kurzbz' $selected>$studiensemester->studiensemester_kurzbz</OPTION>\n";
}
$stsem_content.= "</SELECT>\n";


if(!$rechte->isBerechtigt('admin',0) &&
   !$rechte->isBerechtigt('admin',$lv_obj->studiengang_kz) &&
   !$rechte->isBerechtigt('lehre',$lv_obj->studiengang_kz))
{
	$qry = "SELECT lehreinheit_id FROM lehre.tbl_lehrveranstaltung JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id)
			JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
			WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER)." AND
			tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stsem)." AND tbl_lehreinheitmitarbeiter.mitarbeiter_uid=".$db->db_add_param($user).';';
	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)==0)
			die($p->t('global/keineBerechtigungFuerDieseSeite'));
	}
	else
	{
		die($p->t('global/fehleraufgetreten'));
	}
}
echo $stsem_content;
echo '</td></tr>';

echo '<tr>';
echo "<td>";
echo '<a href="'.$p->t('dms_link/dokuwikiGesamtnote').'" class="Item" target="_blank">'.$p->t('global/anleitung').'</a>';
if(defined('CIS_ANWESENHEITSLISTE_NOTENLISTE_ANZEIGEN') && CIS_ANWESENHEITSLISTE_NOTENLISTE_ANZEIGEN)
	echo "<br><a class='Item' href='../notenliste.xls.php?stg=$stg_obj->studiengang_kz&lvid=$lvid&stsem=$stsem'>".$p->t('benotungstool/notenlisteImport')."</a>";

// eingetragene lv-gesamtnoten freigeben
if (isset($_REQUEST["freigabe"]) and ($_REQUEST["freigabe"] == 1))
{
	//Passwort pruefen
	if(checkldapuser($user, $_REQUEST['passwort']))
	{
		$jetzt = date("Y-m-d H:i:s");
		$neuenoten = 0;
		$studlist = "<table border='1'><tr><td><b>".$p->t('global/personenkz')."</b></td><td><b>".$p->t('global/nachname')."</b></td><td><b>".$p->t('global/vorname')."</b></td>";
		if(defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE)
		{
			$studlist.="<td><b>".$p->t('benotungstool/punkte')."</b></td>\n";
		}
		$studlist.="<td><b>".$p->t('benotungstool/note')."</b></td>\n";
		$studlist.="<td><b>".$p->t('benotungstool/bearbeitetvon')."</b></td></tr>\n";

		// studentenquery
		$qry_stud = "SELECT
						DISTINCT uid, vorname, nachname, matrikelnr
					FROM
						campus.vw_student_lehrveranstaltung
						JOIN campus.vw_student USING(uid)
					WHERE
						studiensemester_kurzbz = ".$db->db_add_param($stsem)."
						AND lehrveranstaltung_id = ".$db->db_add_param($lvid, FHC_INTEGER)."
					ORDER BY nachname, vorname ";
        if($result_stud = $db->db_query($qry_stud))
		{
			$i=1;
			while($row_stud = $db->db_fetch_object($result_stud))
			{
				$lvgesamtnote = new lvgesamtnote();
    			if ($lvgesamtnote->load($lvid,$row_stud->uid,$stsem))
    			{
					if ($lvgesamtnote->benotungsdatum > $lvgesamtnote->freigabedatum)
					{
    					$lvgesamtnote->freigabedatum = $jetzt;
    					$lvgesamtnote->freigabevon_uid = $user;
    					$lvgesamtnote->save($new=null);
    					$studlist .= "<tr><td>".trim($row_stud->matrikelnr)."</td>";
						$studlist .= "<td>".trim($row_stud->nachname)."</td>";
						$studlist .= "<td>".trim($row_stud->vorname)."</td>";
						if(defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE)
						{
							$studlist.="<td>".($lvgesamtnote->punkte!=''?trim(number_format($lvgesamtnote->punkte,2)):'')."</td>\n";
						}
						$studlist.="<td>".$noten_array[trim($lvgesamtnote->note)]['bezeichnung']."</td>";
						$studlist.="<td>".$lvgesamtnote->mitarbeiter_uid.($lvgesamtnote->updatevon!=''?" (".$lvgesamtnote->updatevon.")":'')."</td></tr>\n";
    					$neuenoten++;
    				}
    			}
			}
		}

		$studlist .= "</table>";

		//mail an assistentin und den user selber verschicken
		if ($neuenoten > 0)
		{
			$lv = new lehrveranstaltung($lvid);
			$sg = new studiengang($lv->studiengang_kz);
			$lektor_adresse = $user."@".DOMAIN;
			$adressen = $sg->email.", ".$user."@".DOMAIN;

			$studienplan = new studienplan();
			$studienplan->getStudienplanLehrveranstaltung($lvid, $stsem);
			$studienplan_bezeichnung='';
			foreach($studienplan->result as $row)
				$studienplan_bezeichnung.=$row->bezeichnung.' ';

			$mit = new mitarbeiter();
			$mit->load($user);

			$freigeber = "<b>".mb_strtoupper($user)."</b>";
			$mail = new mail($adressen, 'vilesci@'.DOMAIN, 'Notenfreigabe '.$lv->bezeichnung." ".$lv->orgform_kurzbz.' - '.$studienplan_bezeichnung,'');
			$htmlcontent="<html>
				<body>
					<b>".$sg->kuerzel.' '.$lv->semester.'.Semester '.$lv->bezeichnung." ".$lv->orgform_kurzbz." - ".$stsem."</b>
					(".$lv->semester.". Sem.)
					<br><br>".$p->t('global/benutzer')." ".$freigeber." (".$mit->kurzbz.") ".$p->t('benotungstool/hatDieLvNotenFuerFolgendeStudenten').":
					<br><br>\n".$studlist."
					<br>Anzahl der Noten:".$neuenoten."
					<br>".$p->t('abgabetool/mailVerschicktAn').": ".$adressen."
				</body></html>";
			$mail->setHTMLContent($htmlcontent);
			$mail->setReplyTo($lektor_adresse);
			$mail->send();
		}
	}
	else
	{
		echo '<span><font class="error">'.$p->t('gesamtnote/passwortFalsch').'</font></span>';
	}
}

if(defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE)
	echo '<br><a href="#" onclick="window.open(\'notenschluessel.php?lehrveranstaltung_id='.$lvid.'&stsem='.$stsem.'\',\'Grades\', \'height=200,width=350,left=50,top=50,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes\'); return false;">'.$p->t('gesamtnote/notenschluesselanzeigen').'</a>';

echo '</td></tr></table><br>';
echo '<table width="100%" height="10px"><tr><td>';


// alle Pruefungen für die LV holen
$studpruef_arr = array();
$pr_all = new Pruefung();
if ($pr_all->getPruefungenLV($lvid,"Termin2",$stsem))
{
	if ($pr_all->result)
	{
		foreach ($pr_all->result as $pruefung)
		{
			$studpruef_arr[$pruefung->student_uid][$pruefung->lehreinheit_id]["note"] = $pruefung->note;
			$studpruef_arr[$pruefung->student_uid][$pruefung->lehreinheit_id]["punkte"] = $pruefung->punkte;
			$studpruef_arr[$pruefung->student_uid][$pruefung->lehreinheit_id]["datum"] = $datum_obj->formatDatum($pruefung->datum,'d.m.Y');
		}
	}
}
$summe_t2=count($studpruef_arr);

$studpruef_arr_t3 = array();
$pr_all = new Pruefung();
if ($pr_all->getPruefungenLV($lvid,"Termin3",$stsem))
{
	if ($pr_all->result)
	{
		foreach ($pr_all->result as $pruefung)
		{
			$studpruef_arr_t3[$pruefung->student_uid][$pruefung->lehreinheit_id]["note"] = $pruefung->note;
			$studpruef_arr_t3[$pruefung->student_uid][$pruefung->lehreinheit_id]["punkte"] = $pruefung->punkte;
			$studpruef_arr_t3[$pruefung->student_uid][$pruefung->lehreinheit_id]["datum"] = $datum_obj->formatDatum($pruefung->datum,'d.m.Y');
		}
	}
}
$summe_t3=count($studpruef_arr_t3);

$studpruef_komm = array();
$pr_komm = new Pruefung();
if ($pr_komm->getPruefungenLV($lvid,"kommPruef",$stsem))
{
	if ($pr_komm->result)
	{
		foreach ($pr_komm->result as $kpruefung)
		{
			$studpruef_komm[$kpruefung->student_uid][$kpruefung->lehreinheit_id]["note"] = $kpruefung->note;
			$studpruef_komm[$kpruefung->student_uid][$kpruefung->lehreinheit_id]["punkte"] = $kpruefung->punkte;
			$studpruef_komm[$kpruefung->student_uid][$kpruefung->lehreinheit_id]["datum"] = $datum_obj->formatDatum($kpruefung->datum,'d.m.Y');
		}
	}
}
$summe_komm=count($studpruef_komm);

//Studentenliste

echo '<table class="gradetable">';
echo "
		<tr>
			<th></th>
			<th>".$p->t('global/uid')."</th>
			<th>".$p->t('global/nachname')."</th>
			<th>".$p->t('global/vorname')."</th>
			<th>".($grade_from_moodle?''.$p->t('benotungstool/moodleNote').'':''.$p->t('benotungstool/leNoten').' (LE-ID)')."</th>
			<th>".$p->t('benotungstool/punkte').' / '.$p->t('benotungstool/note')."</th>
			<th rowspan=2>".$p->t('benotungstool/lvNote')."<br>
				<input type='button' onclick='GradeImport()' value='".$p->t('benotungstool/importieren')."'>
			</th>
			<th align='right' rowspan=2>
				<form name='freigabeform' action='".$_SERVER['PHP_SELF']."?lvid=$lvid&lehreinheit_id=$lehreinheit_id&stsem=$stsem' method='POST' onsubmit='return OnFreigabeSubmit()'>
				<input type='hidden' name='freigabe' value='1'>
				<span style='white-space:nowrap;'>".$p->t('global/passwort').": <input type='password' size='8' id='textbox-freigabe-passwort' name='passwort'></span>
				<br><input type='submit' name='frei' value='Freigabe'>
				</form>
			</th>
			<th>".$p->t('benotungstool/zeugnisnote')."</th>
			<th colspan='2'>".$p->t('benotungstool/nachpruefung')."</th>";
			if(defined('CIS_GESAMTNOTE_PRUEFUNG_TERMIN3') && CIS_GESAMTNOTE_PRUEFUNG_TERMIN3)
			{
				echo "<th colspan='2' nowrap>".$p->t('benotungstool/nachpruefung2')."</th>";
			}
			echo "
			<th colspan='2'>".$p->t('benotungstool/kommissionellePruefung')."</th>
		</tr>
			<tr>
				<th colspan='9'>&nbsp;</th>
				<th colspan='2'>
					<table>
					<tr>
						<td class='td_datum'>".$p->t('global/datum')."</td>
						<td class='td_note'>".$p->t('benotungstool/note')."</td>
						<td></td>
					</tr>
					</table>
				</th>";
				if(defined('CIS_GESAMTNOTE_PRUEFUNG_TERMIN3') && CIS_GESAMTNOTE_PRUEFUNG_TERMIN3)
				{
					echo "<th colspan='2'>
						<table>
						<tr>
							<td class='td_datum'>".$p->t('global/datum')."</td>
							<td class='td_note'>".$p->t('benotungstool/note')."</td>
							<td></td>
						</tr>
						</table>
					</th>";
				}
				echo "
				<th colspan='2'>
					<table>
					<tr>
						<td class='td_datum'>".$p->t('global/datum')."</td>
						<td class='td_note'>".$p->t('benotungstool/note')."</td>
						<td></td>
					</tr>
					</table>
				</th>
			</tr>
			";


		if($grade_from_moodle)
		{
			flush();
			ob_flush();

			$moodle24 = new moodle24_course();
			$moodle24->loadNoten($lvid, $stsem);

			$moodle24_course_bezeichnung=array();

			if(count($moodle24->result)>0)
			{
				// Bezeichnungen der Moodlekurse laden
				foreach($moodle24->result as $obj)
				{
					if(!isset($moodle24_course_bezeichnung[$obj->mdl_course_id]))
					{
						$moodle24course = new moodle24_course();
						$moodle24course->load($obj->mdl_course_id);

						$moodle24_course_bezeichnung[$obj->mdl_course_id]=$moodle24course->mdl_shortname;
					}

					if(!isset($moodle24_course_gewicht[$obj->mdl_course_id]))
					{
						$mdl_obj = new moodle24_course();
						$mdl_lehreinheiten=$mdl_obj->getLeFromCourse($obj->mdl_course_id);

						foreach($mdl_lehreinheiten as $row_mdl_lehreinheit)
						{
							if($row_mdl_lehreinheit!='')
							{
								$lehreinheit_gewicht_obj = new lehreinheit();
								$lehreinheit_gewicht_obj->load($row_mdl_lehreinheit);

								if($lehreinheit_gewicht_obj->gewicht!='')
								{
									$moodle24_course_gewicht[$obj->mdl_course_id]=$lehreinheit_gewicht_obj->gewicht;
									break;
								}
							}
						}
					}
				}
			}
		}

		// studentenquery
		$qry_stud = "SELECT
						DISTINCT uid, vorname, nachname, matrikelnr
					FROM
						campus.vw_student_lehrveranstaltung
						JOIN campus.vw_student USING(uid)
					WHERE
						studiensemester_kurzbz = ".$db->db_add_param($stsem)."
						AND lehrveranstaltung_id = ".$db->db_add_param($lvid)."
					ORDER BY nachname, vorname ";
      	$mdldaten=null;
	    if($result_stud = $db->db_query($qry_stud))
		{
			$i=1;
			$errorshown=false;
			$summe_stud=$db->db_num_rows($result_stud);
			while($row_stud = $db->db_fetch_object($result_stud))
			{

				echo "<tr class='liste".($i%2)."'>
					<td><a href='mailto:$row_stud->uid@".DOMAIN."'><img src='../../../../skin/images/button_mail.gif'></a></td>";
				echo "
					<td>$row_stud->uid</td>
					<td>$row_stud->nachname</td>
					<td>$row_stud->vorname</td>";

				$note_les_str = '';
				$le_anz = 0;
				$note_le = 0;
				$note_le_gewichtet=0;
				$gewichtsumme=0;
				$note=0;
				if($grade_from_moodle)
				{
					//Moodle 1.9

					// Alle Moodlekursdaten zu Lehreinheit und Semester lesen wenn noch nicht belegt.
					if (is_null($mdldaten) && defined('CONN_STRING_MOODLE'))
					{
						//Noten aus Moodle
						if (!isset($moodle_course))
							$moodle_course = new moodle19_course();

						if (!$mdldaten = $moodle_course->loadNoten($lvid, $stsem, '', true,$debg))
							$mdldaten='';
					}
					// Verarbeitet die Kursdaten
					if (!is_null($mdldaten) && is_array($mdldaten))
					{
							reset($mdldaten);
							$title="";
							$mdl_shortname='';
	    					for ($imdldaten=0;$imdldaten<count($mdldaten) ;$imdldaten++)
							{

								$mdldata=$mdldaten[$imdldaten]->result;
								$kursArr=(isset($mdldata[2])?$mdldata[2]:array());
								$kursasObj=(isset($mdldata[3])?$mdldata[3]:array());
								$kursname=(isset($mdldata[7])?$mdldata[7]:'');
								$shortname=(isset($mdldata[8])?$mdldata[8]:'');

								$note=0;
								$userGef=false;

								reset($kursArr);
		    					for ($iKurs=0;$iKurs<count($kursArr) ;$iKurs++)
								{
									if (isset($kursArr[$iKurs]) && isset($kursArr[$iKurs][2]) && isset($kursArr[$iKurs][6]) && strtolower(trim($row_stud->uid))==strtolower(trim($kursArr[$iKurs][2])) )
									{
									    $note=trim($kursArr[$iKurs][6]);
										$userGef=true;

									   	if (is_numeric($note)  || $debg)
									   	{
										   	if (is_numeric($note))
										   	{
										   		$note_le += $note;
												$note_le_gewichtet +=$note*$gewichtung;
												$gewichtsumme+=$gewichtung;
					    			   			$le_anz += 1;
											}
						    				if ($note == 5)
						    					$leneg = " style='color:red; font-weight:bold'";
				    						else
		    									$leneg = " style='font-weight:bold'";

										   $mdl_shortname=$mdldaten[$imdldaten]->mdl_shortname;
			  							   $title="\r\nMoodle 1.9 KursID: ".$mdldaten[$imdldaten]->mdl_course_id ."\r\n\r\n".$kursname.', '.$mdl_shortname."\r\n";
									       foreach ($kursasObj[$iKurs] as $key => $value)
										   {
												$title.=$key."=>".$value."\r\n";
											}


											$note_les_str .= "<span ".$leneg.">".$note."</span> <span  title='".$title."' style='font-size:10px'>(".$mdl_shortname.")</span> ";
										}
									}	// ende If Richtiger User

									if ($userGef)
									{
										$iKurs=count($kursArr)+1; // diesen USER for beenden - user wurde gefunden
									}

								} // ende Kursschleife
							} // MoodleKurse abarbeiten

				#echo "<p><h1> $title Anzahl Noten gef. $le_anz $note_le </h1></p>";
					}
					else
					{
						if(defined('CONN_STRING_MOODLE'))
						{
							//den Error nur einmal anzeigen und nicht fuer jeden Studenten
							$moodle_course->errormsg=trim($moodle_course->errormsg);
							if(!$errorshown && !empty($moodle_course->errormsg) )
							{
								//echo '<br><b>'.$moodle_course->errormsg.'</b><br>';
								$errorshown=true;
							}
						}
					}

					// Moodle 2.4
					if(isset($moodle24) && count($moodle24->result)>0)
					{
						foreach($moodle24->result as $moodle24_noten)
						{
							if($moodle24_noten->uid==$row_stud->uid)
							{
								$gewichtung=1;
								$note_le+=$moodle24_noten->note;
								if(isset($moodle24_course_gewicht[$moodle24_noten->mdl_course_id]))
									$gewichtung=$moodle24_course_gewicht[$moodle24_noten->mdl_course_id];

								if($gewichtung=='')
									$gewichtung=1;
								$note_le_gewichtet+=$moodle24_noten->note*$gewichtung;
								$gewichtsumme+=$gewichtung;
								$le_anz+=1;
								//if ($moodle24_noten->note == 5)
								//	$leneg = " style='color:red; font-weight:bold'";
								//else
									$leneg = ' style="font-weight: bold;"';
								$title="Moodle KursID: ".$moodle24_noten->mdl_course_id.
								"\nKursbezeichnung: ".$moodle24_course_bezeichnung[$moodle24_noten->mdl_course_id].
								"\nUser: ".$moodle24_noten->uid.
								"\nNote: ".$moodle24_noten->note;
								if(defined('CIS_GESAMTNOTE_GEWICHTUNG') && CIS_GESAMTNOTE_GEWICHTUNG)
									$title.="\nGewichtung: ".$gewichtung;
								$note_les_str .= "<br><span".$leneg.">".$moodle24_noten->note."</span><span  title='".$title."' style='font-size:10px'> (".$moodle24_course_bezeichnung[$moodle24_noten->mdl_course_id].")</span> ";

							}
						}
					}
				}
				else
				{
					//Noten aus Uebungstool
					$le = new lehreinheit();
					$le->load_lehreinheiten($lvid, $stsem);
					foreach($le->lehreinheiten as $l)
					{
						$legesamtnote = new legesamtnote($l->lehreinheit_id);

		    			if (!$legesamtnote->load($row_stud->uid,$l->lehreinheit_id))
						{
		    				//$note_les_str .= "- (".$l->lehreinheit_id.")";
		    			}
		    			else
		    			{
		    				$note_le += $legesamtnote->note;

							$gewicht = $l->gewicht;
							if($l->gewicht=='')
								$gewicht = 1;
							$note_le_gewichtet+=$legesamtnote->note*$gewicht;
							$gewichtsumme+=$gewicht;

							$le_anz += 1;
		    				if ($legesamtnote->note == 5)
		    					$leneg = " style='color:red; font-weight:bold'";
		    				else
		    					$leneg = "";
							if(defined('CIS_GESAMTNOTE_GEWICHTUNG') && CIS_GESAMTNOTE_GEWICHTUNG)
								$title='Gewichtung: '.$l->gewicht;
							else
								$title='';
		    				$note_les_str .= '<span title="'.$title.'"><span'.$leneg.'>'.$legesamtnote->note.'</span> ('.$l->lehreinheit_id.') </span>';
		    			}
		    		}
				}

    			if ($lvgesamtnote = new lvgesamtnote($lvid,$row_stud->uid,$stsem))
    			{
    				$note_lv = $lvgesamtnote->note;
					$punkte_lv = $lvgesamtnote->punkte;
    			}
    			else
				{
    				$note_lv = null;
					$punkte_lv = null;
				}

				$punkte_vorschlag='';
				if (!is_null($note_lv))
					$note_vorschlag = $note_lv;
				else if ($le_anz > 0)
				{
					if(CIS_GESAMTNOTE_PUNKTE)
					{
						if(defined('CIS_GESAMTNOTE_GEWICHTUNG') && CIS_GESAMTNOTE_GEWICHTUNG)
						{
							// Lehreinheitsgewichtung
							$punkte_vorschlag = round($note_le_gewichtet/$gewichtsumme,2);
							$notenschluessel = new notenschluessel();
							$note_vorschlag = $notenschluessel->getNote($punkte_vorschlag, $lvid, $stsem);
						}
						else
						{
							$punkte_vorschlag = round($note_le/$le_anz,2);
							$notenschluessel = new notenschluessel();
							$note_vorschlag = $notenschluessel->getNote($punkte_vorschlag, $lvid, $stsem);
						}
					}
					else
					{
						if(defined('CIS_GESAMTNOTE_GEWICHTUNG') && CIS_GESAMTNOTE_GEWICHTUNG)
						{
							$note_vorschlag = round($note_le_gewichtet/$gewichtsumme);
						}
						else
						{
							$note_vorschlag = round($note_le/$le_anz);
						}

					}
				}
				else
					$note_vorschlag = null;
				if ($zeugnisnote = new zeugnisnote($lvid, $row_stud->uid, $stsem))
					$znote = $zeugnisnote->note;
				else
					$znote = null;


				echo "<td style='white-space: nowrap;'>".$note_les_str."&nbsp;</td>";

				if (key_exists($row_stud->uid,$studpruef_arr))
					$hide = "style='display:none;visibility:hidden;'";
				else
					$hide = "style='display:block;visibility:visible;'";

				if(!defined('CIS_GESAMTNOTE_UEBERSCHREIBEN') || CIS_GESAMTNOTE_UEBERSCHREIBEN || (!CIS_GESAMTNOTE_UEBERSCHREIBEN && is_null($znote)))
				{
					echo "<td valign='bottom' nowrap>
						<form name='$row_stud->uid' id='$row_stud->uid' method='POST' action='".$_SERVER['PHP_SELF']."?lvid=$lvid&lehreinheit_id=$lehreinheit_id&stsem=$stsem'>
							<span id='lvnoteneingabe_".$row_stud->uid."' ".$hide.">
							<input type='hidden' name='student_uid' value='$row_stud->uid'>";

					// Punkte
					if(CIS_GESAMTNOTE_PUNKTE)
					{
						//$punkte_lv = $punkte_vorschlag;
						echo '<input type="text" name="punkte" id="textbox-punkte-'.$i.'" value="'.$punkte_vorschlag.'" size="3" oninput="PunkteEingabe('.$i.')"/>';
					}

					// Noten DropDown
					if($punkte_vorschlag!='' && CIS_GESAMTNOTE_PUNKTE)
						$disabled='disabled="disabled"';
					else
						$disabled='';
					echo '<select name="note" id="dropdown-note-'.$i.'" '.$disabled.'>';
					echo '<option value="">-- keine Auswahl --</option>';
					foreach($noten_obj->result as $row_note)
					{
						if($row_note->note == $note_vorschlag)
							$selected='selected';
						else
							$selected='';

						if($row_note->lehre && $row_note->aktiv)
							echo '<option value="'.$row_note->note.'" '.$selected.'>'.$row_note->bezeichnung.'</option>';
					}
					echo '</select>';
					echo "
								<input type='hidden' name='note_orig' value='$note_lv'>
								<input type='button' id='button-note-save-".$i."' value='->' onclick=\"saveLVNote('".$row_stud->uid."');\">
							</span>
						</form></td>";
				}
				else
				{
					echo '<td></td>';
				}

				if(isset($noten_array[$note_lv]) && $noten_array[$note_lv]['positiv']==false)
					$negmarkier = " style='color:red; font-weight:bold;'";
				else
					$negmarkier = "";

				// LV Note
				echo '<td align="center" id="note_'.$row_stud->uid.'"><span '.$negmarkier.'>';
				if(isset($noten_array[$note_lv]))
					echo $noten_array[$note_lv]['bezeichnung'];
				if($punkte_lv!='')
					echo ' ('.number_format($punkte_lv,2).')';
				echo '</span></td>';

				//status
				echo "<td align='center' id='status_$row_stud->uid'>";
				if (!$lvgesamtnote->freigabedatum)
					echo "<img src='../../../../skin/images/offen.png'>";
				else if	($lvgesamtnote->benotungsdatum > $lvgesamtnote->freigabedatum)
					echo "<img src='../../../../skin/images/changed.png'>";
				else
					echo "<img src='../../../../skin/images/ok.png'>";

				echo "</td>";
				if (($znote) && ($note_lv != $znote))
					$stylestr = " style='color:red; border-color:red; border-style:solid; border-width:1px;'";
				else
					$stylestr ="";

				// Zeugnisnote
				echo "<td".$stylestr." align='center'>".(isset($noten_array[$znote])?$noten_array[$znote]['bezeichnung']:'')."</td>";

				if(isset($noten_array[$znote]) && $noten_array[$znote]['positiv']==false)
					$summe_ng++;

				// Pruefung 2. Termin
				if (key_exists($row_stud->uid, $studpruef_arr))
				{
					echo "<td colspan='2'>";
					echo "<span id='span_Termin2_".$row_stud->uid."'>";
					echo "<table>";
					$le_id_arr = array();
					$le_id_arr = array_keys($studpruef_arr[$row_stud->uid]);
					foreach ($le_id_arr as $le_id_stud)
					{
						$pr_note = $studpruef_arr[$row_stud->uid][$le_id_stud]["note"];
						$pr_punkte = $studpruef_arr[$row_stud->uid][$le_id_stud]["punkte"];
						$pr_datum = $studpruef_arr[$row_stud->uid][$le_id_stud]["datum"];
						$pr_le_id = $le_id_stud;

						if($pr_punkte!='')
							$pr_notenbezeichnung = $noten_array[$pr_note]['bezeichnung'].' ('.number_format($pr_punkte,2).')';
						else
							$pr_notenbezeichnung = $noten_array[$pr_note]['bezeichnung'];

						echo '<tr>
								<td class="td_datum">'.$pr_datum.'</td>
								<td class="td_note">'.$pr_notenbezeichnung.'</td>
								<td><input type="button" name="anlegen" value="'.$p->t('global/aendern').'" onclick="pruefungAnlegen(\''.$row_stud->uid.'\',\''.$pr_datum.'\',\''.$pr_note.'\',\''.$pr_le_id.'\',\''.$pr_punkte.'\')"><td>
							</tr>';
					}
					echo "</table>";
					echo "</span>";
					echo "</td>";
				}
				else
				{
					if (!is_null($note_lv) || !is_null($znote))
						echo "<td colspan='2'><span id='span_Termin2_".$row_stud->uid."'><input type='button' name='anlegen' value='".$p->t('benotungstool/anlegen')."' onclick='pruefungAnlegen(\"".$row_stud->uid."\",\"\",\"\",\"\",\"\")'></span></td>";
					else
						echo "<td colspan='2'></td>";
				}

				if(defined('CIS_GESAMTNOTE_PRUEFUNG_TERMIN3') && CIS_GESAMTNOTE_PRUEFUNG_TERMIN3)
				{
					// Pruefung 3. Termin
					if (key_exists($row_stud->uid, $studpruef_arr_t3))
					{
						echo "<td colspan='2'>";
						echo "<span id='span_Termin3_".$row_stud->uid."'>";
						echo "<table>";
						$le_id_arr = array();
						$le_id_arr = array_keys($studpruef_arr_t3[$row_stud->uid]);
						foreach ($le_id_arr as $le_id_stud)
						{
							$pr_note = $studpruef_arr_t3[$row_stud->uid][$le_id_stud]["note"];
							$pr_punkte = $studpruef_arr_t3[$row_stud->uid][$le_id_stud]["punkte"];
							$pr_datum = $studpruef_arr_t3[$row_stud->uid][$le_id_stud]["datum"];
							$pr_le_id = $le_id_stud;

							if($pr_punkte!='')
								$pr_notenbezeichnung = $noten_array[$pr_note]['bezeichnung'].' ('.number_format($pr_punkte,2).')';
							else
								$pr_notenbezeichnung = $noten_array[$pr_note]['bezeichnung'];

							echo '<tr>
									<td class="td_datum">'.$pr_datum.'</td>
									<td class="td_note">'.$pr_notenbezeichnung.'</td>
									<td><input type="button" name="anlegen" value="'.$p->t('global/aendern').'" onclick="pruefungAnlegen(\''.$row_stud->uid.'\',\''.$pr_datum.'\',\''.$pr_note.'\',\''.$pr_le_id.'\',\''.$pr_punkte.'\',\'Termin3\')"><td>
								</tr>';
						}
						echo "</table>";
						echo "</span>";
						echo "</td>";
					}
					else
					{
						if (!is_null($note_lv) || !is_null($znote))
							echo "<td colspan='2'><span id='span_Termin3_".$row_stud->uid."'><input type='button' name='anlegen' value='".$p->t('benotungstool/anlegen')."' onclick='pruefungAnlegen(\"".$row_stud->uid."\",\"\",\"\",\"\",\"\",\"Termin3\")'></span></td>";
						else
							echo "<td colspan='2'></td>";
					}
				}

				// komm Pruefung
				if (key_exists($row_stud->uid,$studpruef_komm))
				{
					echo "<td colspan='2'>";
					echo "<span id='span_".$row_stud->uid."'>";
					echo "<table>";
					$le_id_arr = array();
					$le_id_arr = array_keys($studpruef_komm[$row_stud->uid]);
					foreach ($le_id_arr as $le_id_stud)
					{
						$pr_note = $studpruef_komm[$row_stud->uid][$le_id_stud]["note"];
						$pr_punkte = $studpruef_komm[$row_stud->uid][$le_id_stud]["punkte"];
						$pr_datum = $studpruef_komm[$row_stud->uid][$le_id_stud]["datum"];
						$pr_le_id = $le_id_stud;

						if($pr_punkte!='')
							$pr_notenbezeichnung = $noten_array[$pr_note]['bezeichnung'].' ('.number_format($pr_punkte,2).')';
						else
							$pr_notenbezeichnung = $noten_array[$pr_note]['bezeichnung'];

						echo '<tr>
								<td class="td_datum">'.$pr_datum.'</td>
								<td class="td_note">'.$pr_notenbezeichnung.'</td>
							</tr>';
					}
					echo "</table>";
					echo "</span>";
					echo "</td>";
				}
				else
				{
						echo "<td colspan='2'></td>";
				}

				echo "</tr>";
				$i++;
			}
		}

// Fusszeile
echo "
	<tr style='font-weight:bold;' align='center'>
		<th style='font-weight:bold;'>&Sigma;</th>
		<th style='font-weight:bold;' title='".$p->t('benotungstool/anzahlDerStudenten')."'>$summe_stud</th>
		<th colspan='6'></td>
		<th style='color:red; font-weight:bold;' title='".$p->t('benotungstool/anzahlNegativerBeurteilungen')."'>$summe_ng</th>
		<th style='font-weight:bold;' colspan='2' title='".$p->t('benotungstool/anzahlNachpruefungen')."'>$summe_t2</th>
		<th style='font-weight:bold;' colspan='2' title='".$p->t('benotungstool/anzahlNachpruefungen')."'>$summe_t3</th>
		<th style='font-weight:bold;' colspan='2' title='".$p->t('benotungstool/anzahlKommisionellePruefungen')."'>$summe_komm</th>
	</tr>
</table>
</td></tr>

</table>
";
?>

<div id="nachpruefung_div" style="position:absolute; top:100px; left:300px; width:400px; height:200px; background-color:#cccccc; visibility:hidden; border-style:solid; border-width:1px; border-color:#333333;" ></div>

</body>
</html>
