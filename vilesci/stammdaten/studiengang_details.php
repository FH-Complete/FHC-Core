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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/**
 * Seite zur Wartung der Studiengaenge
 */
require_once('../../config/vilesci.config.inc.php');		
require_once('../../include/globals.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/erhalter.class.php');
require_once('../../include/benutzerberechtigung.class.php');

	if (!$db = new basis_db())
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');
		
	$user = get_uid();
	
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);
	
	if(!$rechte->isBerechtigt('basis/studiengang'))
		die('Sie haben keine Berechtigung fuer diese Seite');
	
	$date=new datum();
	
	$reloadstr = '';  // neuladen der liste im oberen frame
	$htmlstr = '';
	$errorstr = '';
	$sel = '';
	$chk = '';

	$sg_var = new studiengang();
	$sg_var->getAllTypes();
	$studiengang_typ_arr = $sg_var->studiengang_typ_arr;
	
	$studiengang_kz = '';
	$kurzbz = '';
	$kurzbzlang = '';
	$typ = '';
	$bezeichnung = '';
	$english = '';
	$farbe = '';
	$email = '';
	$telefon = '';
	$max_semester = '';
	$max_verband = '';
	$max_gruppe = '';
	$erhalter_kz = '';
	$bescheid = '';
	$bescheidbgbl1 = '';
	$bescheidbgbl2 = '';
	$bescheidgz = '';
	$bescheidvom = '';
	$titelbescheidvom = '';
	$zusatzinfo_html = '';
	$ext_id = '';
	$aktiv = true;
	$mischform = true;
	$neu = 'true';
	$oe_kurzbz='';
	$moodle = true;
	$projektarbeit_note_anzeige = true;
	$sprache = '';
	$testtool_sprachwahl = false;
	$studienplaetze = '';
	$orgform_kurzbz = '';
	$lgartcode='';
	
	if(isset($_POST['schick']))
	{
		$studiengang_kz = $_POST['studiengang_kz'];
		
		if($_POST['neu']=='true')
		{
			if(!$rechte->isBerechtigt('basis/studiengang', null, 'suid'))
				die('Sie haben keine Rechte fuer diese Aktion');
		}
		else 
		{
			$stg_hlp = new studiengang();
			if(!$stg_hlp->load($studiengang_kz))
				die('Fehler beim Laden des Studienganges: '.$stg_hlp->errormsg);
			
			if(!$rechte->isBerechtigt('basis/studiengang', $stg_hlp->oe_kurzbz, 'su'))
				die('Sie haben keine Rechte fuer diese Aktion');
		}
		
		$kurzbz = $_POST['kurzbz'];
		$kurzbzlang = $_POST['kurzbzlang'];
		$typ = $_POST['typ'];
		$bezeichnung = $_POST['bezeichnung'];
		$english = $_POST['english'];
		$farbe = $_POST['farbe'];
		$email = $_POST['email'];
		$telefon = $_POST['telefon'];
		$max_semester = $_POST['max_semester'];
		$max_verband = $_POST['max_verband'];
		$max_gruppe = $_POST['max_gruppe'];
		$erhalter_kz = $_POST['erhalter_kz'];
		$bescheid = $_POST['bescheid'];
		$bescheidbgbl1 = $_POST['bescheidbgbl1'];
		$bescheidbgbl2 = $_POST['bescheidbgbl2'];
		$bescheidgz = $_POST['bescheidgz'];
		$bescheidvom = $_POST['bescheidvom'];
		$oe_kurzbz = $_POST['oe_kurzbz'];
		$oe_parent_kurzbz = $_POST['oe_parent_kurzbz'];
		$titelbescheidvom = $_POST['titelbescheidvom'];
		$zusatzinfo_html = $_POST['zusatzinfo_html'];
		$moodle = isset($_POST['moodle']);
		$projektarbeit_note_anzeige = isset($_POST['projektarbeit_note_anzeige']);
		$sprache = $_POST['sprache'];
		$testtool_sprachwahl = isset($_POST['testtool_sprachwahl']);
		$studienplaetze = $_POST['studienplaetze'];
		$orgform_kurzbz = $_POST['orgform_kurzbz'];
		$lgartcode = $_POST['lgartcode'];
		$aktiv = isset($_POST['aktiv']);
		$mischform = isset($_POST['mischform']);
			
		$ext_id = $_POST['ext_id'];
		
		
		
		$oe_error=false;
		if($oe_kurzbz=='')
		{
			$oe=new organisationseinheit();
			$oe->new=true;
			$oe->oe_kurzbz = strtolower($typ.$kurzbz);
			$oe->kurzzeichen = strtolower($typ.$kurzbz);
			$oe->oe_parent_kurzbz = $oe_parent_kurzbz;
			$oe->bezeichnung = $kurzbzlang;
			$oe->organisationseinheittyp_kurzbz = 'Studiengang';
			$oe->aktiv = true;
			$oe->mailverteiler = false;
			
			if(!$oe->save())
			{
				echo '<br><br>Fehler beim Anlegen der Organisationseinheit: '.$oe->errormsg;
				$oe_error=true;
			}
			else
			{
				echo '<br><br>Organisationseinheit '.$oe->oe_kurzbz.' angelegt';
				echo '<br>kurzbz '.$kurzbz;
				echo '<br>kurzbzlang '.$kurzbzlang;
				$oe_kurzbz=$oe->oe_kurzbz;
			}
		}

		if(!$oe_error)
		{
			$sg_update = new studiengang();
			$sg_update->studiengang_kz = $studiengang_kz;
			$sg_update->kurzbz = $kurzbz;
			$sg_update->kurzbzlang = $kurzbzlang;
			$sg_update->typ = $typ;
			$sg_update->bezeichnung = $bezeichnung;
			$sg_update->english = $english;
			$sg_update->farbe = $farbe;
			$sg_update->email = $email;
			$sg_update->telefon = $telefon;
			$sg_update->max_semester = $max_semester;
			$sg_update->max_verband = $max_verband;
			$sg_update->max_gruppe = $max_gruppe;
			$sg_update->erhalter_kz = $erhalter_kz;
			$sg_update->bescheid = $bescheid;
			$sg_update->bescheidbgbl1 = $bescheidbgbl1;
			$sg_update->bescheidbgbl2 = $bescheidbgbl2;
			$sg_update->bescheidgz = $bescheidgz;
			$sg_update->bescheidvom = $bescheidvom;
			$sg_update->titelbescheidvom = $titelbescheidvom;
			$sg_update->zusatzinfo_html = $zusatzinfo_html;
			$sg_update->aktiv = $aktiv;
			$sg_update->mischform = $mischform;
			$sg_update->ext_id = $ext_id;
			$sg_update->oe_kurzbz = $oe_kurzbz;
			$sg_update->moodle = $moodle;
			$sg_update->projektarbeit_note_anzeige = $projektarbeit_note_anzeige;
			$sg_update->sprache = $sprache;
			$sg_update->testtool_sprachwahl = $testtool_sprachwahl;
			$sg_update->studienplaetze = $studienplaetze;
			$sg_update->orgform_kurzbz = $orgform_kurzbz;
			$sg_update->lgartcode = $lgartcode;
			
			$sg_update->bescheidvom=$date->formatDatum($sg_update->bescheidvom,'Y-m-d');
			$sg_update->titelbescheidvom=$date->formatDatum($sg_update->titelbescheidvom,'Y-m-d');

			if ($_POST['neu'] == 'true')
				$sg_update->new = true;

			if(!$sg_update->save())
			{
				$errorstr .= $sg_update->errormsg;
			}
		}
		$reloadstr .= '<script type="text/javascript">';
		$reloadstr .= '	parent.uebersicht_studiengang.location.href="studiengang_uebersicht.php";';
		$reloadstr .= '</script>';
	}



	if ((isset($_REQUEST['studiengang_kz'])) && ((!isset($_REQUEST['neu'])) || ($_REQUEST['neu']!= 'true')))
	{
		$studiengang_kz = $_REQUEST['studiengang_kz'];
			
		$sg = new studiengang($studiengang_kz);
		if ($sg->errormsg!='')
			die($sg->errormsg);
		$studiengang_kz = $sg->studiengang_kz;
		$kurzbz = $sg->kurzbz;
		$kurzbzlang = $sg->kurzbzlang;
		$typ = $sg->typ;
		$bezeichnung = $sg->bezeichnung;
		$english = $sg->english;
		$farbe = $sg->farbe;
		$email = $sg->email;
		$telefon = $sg->telefon;
		$max_semester = $sg->max_semester;
		$max_verband = $sg->max_verband;
		$max_gruppe = $sg->max_gruppe;
		$erhalter_kz = $sg->erhalter_kz;
		$bescheid = $sg->bescheid;
		$bescheidbgbl1 = $sg->bescheidbgbl1;
		$bescheidbgbl2 = $sg->bescheidbgbl2;
		$bescheidgz = $sg->bescheidgz;
		$bescheidvom = $sg->bescheidvom;
		$titelbescheidvom = $sg->titelbescheidvom;
		$zusatzinfo_html = $sg->zusatzinfo_html;
		$ext_id = $sg->ext_id;
		$aktiv = $sg->aktiv;
		$mischform = $sg->mischform;
		$oe_kurzbz = $sg->oe_kurzbz;
		$neu = 'false';
		$moodle = $sg->moodle;
		$projektarbeit_note_anzeige = $sg->projektarbeit_note_anzeige;
		$sprache = $sg->sprache;
		$testtool_sprachwahl = $sg->testtool_sprachwahl;
		$studienplaetze = $sg->studienplaetze;
		$orgform_kurzbz = $sg->orgform_kurzbz;
		$lgartcode = $sg->lgartcode;
	}

	$erh = new erhalter();

   	if (!$erh->getAll('kurzbz'))
       	die($erh->errormsg);
		
	$htmlstr .= "<br><div class='kopf'>Studiengang <b>".$bezeichnung."</b></div>\n";
	$htmlstr .= "<form action='studiengang_details.php' method='POST' name='studiengangform'>\n";
	$htmlstr .= "<table class='detail'>\n";


	$htmlstr .= "	<tr><td colspan='3'>&nbsp;</tr>\n";
	$htmlstr .= "	<tr>\n";

	// ertse Spalte start
	$htmlstr .= "		<td valign='top'>\n";

	$htmlstr .= '			<table>
				<tr>
					<td>Kennzahl</td>
					<td><input class="detail" type="text" name="studiengang_kz" size="16" maxlength="5" value="'.$studiengang_kz.'"';
	if($neu=='true')
		$htmlstr .= ' onchange="submitable()"';
	else
		$htmlstr .= ' style="background-color:#eeeeee;" readonly="readonly"';
	$htmlstr .= '></td>
				</tr>';
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Kurzbezeichnung</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='kurzbz' size='16' maxlength='3' value='".$kurzbz."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>KurzbezeichnungLang</td>\n";
	$htmlstr .= " 					<td><input class='detail' type='text' name='kurzbzlang' size='16' maxlength='8' value='".$kurzbzlang."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Max Semester</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='max_semester' size='16' maxlength='2' value='".$max_semester."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Max Verband</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='max_verband' size='16' maxlength='1' value='".$max_verband."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Max Gruppe</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='max_gruppe' size='16' maxlength='1' value='".$max_gruppe."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>OrgformKurzbz</td>\n";
	$htmlstr .= "					<td><SELECT name='orgform_kurzbz' onchange='submitable()'>";
	$qry = "SELECT orgform_kurzbz FROM bis.tbl_orgform ORDER BY orgform_kurzbz";
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			if($row->orgform_kurzbz == $orgform_kurzbz)
				$selected = 'selected';
			else 
				$selected = '';
			
			$htmlstr .= "			<option value='$row->orgform_kurzbz' $selected>$row->orgform_kurzbz</option>";
		}
	}
	$htmlstr .= "                  </SELECT></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td valign='top'>Aktiv</td>\n";
	$htmlstr .= " 					<td>\n";
	if($aktiv)
		$chk = "checked";
	else
		$chk = '';
	$htmlstr .= "						<input type='checkbox' name='aktiv' ".$chk." onchange='submitable()'>";
	$htmlstr .= " 					</td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td valign='top'>Testtool-Sprachwahl</td>\n";
	$htmlstr .= " 					<td>\n";
	if($testtool_sprachwahl)
		$chk = "checked";
	else
		$chk = '';
	$htmlstr .= "						<input type='checkbox' name='testtool_sprachwahl' ".$chk." onchange='submitable()'>";
	$htmlstr .= " 					</td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td valign='top'>Moodle</td>\n";
	$htmlstr .= " 					<td>\n";
	if($moodle)
		$chk = "checked";
	else
		$chk = '';
	$htmlstr .= "						<input type='checkbox' name='moodle' ".$chk." onchange='submitable()'>";
	$htmlstr .= " 					</td>\n";
	$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td valign='top'>Projektarbeitsnote</td>\n";
	$htmlstr .= " 					<td>\n";
	if($projektarbeit_note_anzeige)
		$chk = "checked";
	else
		$chk = '';
	$htmlstr .= "						<input type='checkbox' name='projektarbeit_note_anzeige' ".$chk." onchange='submitable()'>";
	$htmlstr .= " 					</td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td valign='top'>Mischform</td>\n";
	$htmlstr .= " 					<td>\n";
	
	if($mischform)
		$chk = "checked";
	else
		$chk = '';
	$htmlstr .= "						<input type='checkbox' name='mischform' ".$chk." onchange='submitable()'>";
	$htmlstr .= " 					</td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "			</table>\n";

	$htmlstr .= "		</td>\n";
	// 2. Spalte start
	$htmlstr .= "		<td valign='top'>\n";

	$htmlstr .= "			<table>\n";

	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Erhalter</td>\n";
	$htmlstr .= "					<td>";
	$htmlstr .= "						<select name='erhalter_kz' onchange='submitable()'>\n";

	foreach($erh->result as $erhalter)
	{
		if ($erhalter_kz == $erhalter->erhalter_kz)
			$sel = " selected";
		else
			$sel = '';
		$htmlstr .= "							<option value='".$erhalter->erhalter_kz."'".$sel.">".$erhalter->bezeichnung."</option>\n";
	}
	$htmlstr .= "						</select>\n";
	$htmlstr .= "					</td>\n";
	$htmlstr .= "				</tr>\n";

	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Typ</td>\n";
	$htmlstr .= "					<td>";
	
	$htmlstr .= "						<select name='typ' onchange='submitable()'>\n";
	$htmlstr .= "							<option value=''></option>\n";

	foreach(array_keys($studiengang_typ_arr) as $typkey)
	{
		if ($typ == $typkey)
			$sel = " selected";
		else
			$sel = '';
		$htmlstr .= "							<option value='".$typkey."'".$sel.">".$studiengang_typ_arr[$typkey]."</option>\n";
	}
	$htmlstr .= "						</select>\n";
	$htmlstr .= "					</td>\n";
	$htmlstr .= "				</tr>\n";



	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Farbe</td>\n";
	$htmlstr .= " 					<td><input class='detail' type='text' name='farbe' size='16' maxlength='6' value='".$farbe."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Bescheidbgbl1</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='bescheidbgbl1' size='16' maxlength='16' value='".$bescheidbgbl1."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Bescheidbgbl2</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='bescheidbgbl2' size='16' maxlength='16' value='".$bescheidbgbl2."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Bescheidgz</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='bescheidgz' size='16' maxlength='16' value='".$bescheidgz."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Bescheidvom</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' id='bescheidvom' name='bescheidvom' size='16' maxlength='10' value='".$date->formatDatum($bescheidvom,'d.m.Y')."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Titelbescheidvom</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' id='titelbescheidvom' name='titelbescheidvom' size='16' maxlength='10' value='".$date->formatDatum($titelbescheidvom,'d.m.Y')."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Sprache</td>\n";
	$htmlstr .= "					<td><SELECT name='sprache' onchange='submitable()'>";
	$htmlstr .= "					<option value=''>-- keine Auswahl --</option>";
	$qry = "SELECT sprache FROM public.tbl_sprache ORDER BY sprache";
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			if($row->sprache == $sprache)
				$selected = 'selected';
			else 
				$selected = '';
			
			$htmlstr .= "			<option value='$row->sprache' $selected>$row->sprache</option>";
		}
	}
	$htmlstr .= "                  </SELECT></td>\n";
	$htmlstr .= "				</tr>\n";	
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>LehrgangsartCode</td>\n";
	$htmlstr .= "					<td><SELECT name='lgartcode' onchange='submitable()'>";
	$htmlstr .= "					<option value=''>-- keine Auswahl --</option>";
	$qry = "SELECT * FROM bis.tbl_lgartcode ORDER BY lgartcode";
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			if($row->lgartcode == $lgartcode)
				$selected = 'selected';
			else 
				$selected = '';
			
			$htmlstr .= '
			<option value="'.$row->lgartcode.'" '.$selected.'>'.$row->lgartcode.' - '.$row->kurzbz.'</option>';
		}
	}
	$htmlstr .= "                  </SELECT></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "			</table>\n";

	$htmlstr .= "		</td>\n";
	// 3. Spalte start
	$htmlstr .= "		<td valign='top'>\n";

	$htmlstr .= "			<table>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Bezeichnung</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='bezeichnung' size='50' maxlength='128' value='".$bezeichnung."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>English</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='english' size='50' maxlength='128' value='".$english."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Email</td>\n";
	$htmlstr .= " 					<td><input class='detail' type='text' name='email' size='50' maxlength='64' value='".$email."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Telefon</td>\n";
	$htmlstr .= " 					<td><input class='detail' type='text' name='telefon' size='50' maxlength='32' value='".$telefon."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Studienplätze</td>\n";
	$htmlstr .= " 					<td><input class='detail' type='text' name='studienplaetze' size='5' maxlength='5' value='".$studienplaetze."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Ext ID</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='ext_id' size='16' maxlength='16' value='".$ext_id."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td valign='top'>Bescheid</td>\n";
	$htmlstr .= " 					<td><textarea name='bescheid' cols='37' rows='5' onchange='submitable()'>".$bescheid."</textarea></td>\n";
	$htmlstr .= "				</tr>\n";
	
	$htmlstr .= "			</table>\n";

	$htmlstr .= "		</td>\n";

	$htmlstr .= '	</tr>
	<tr>
		<td colspan="2">
			<table>
				<tr>
					<td valign="top">Zusatzinfo</td>
 					<td><textarea id="zusatzinfo_html" class="mceEditor" name="zusatzinfo_html" cols="50" rows="4" onchange="submitable()">'.$zusatzinfo_html.'</textarea></td>
				</tr>
			</table>
		</td>
		<td valign="top">
			<table>
				<tr>
					<td>Organisationseinheit<br>
					<SELECT id="oe_kurzbz" name="oe_kurzbz" onchange="submitable();toggleOeParentDiv()">
			<option value="">-- neue Organisationseinheit anlegen --</option>';
	$qry = 'SELECT oe_kurzbz, organisationseinheittyp_kurzbz, bezeichnung FROM public.tbl_organisationseinheit ORDER BY organisationseinheittyp_kurzbz, bezeichnung';
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			if($row->oe_kurzbz == $oe_kurzbz)
				$selected = 'selected';
			else 
				$selected = '';
			
			$htmlstr .= '	<option value="'.$row->oe_kurzbz.'" '.$selected.'>'.$row->organisationseinheittyp_kurzbz.' '.$row->bezeichnung.'</option>';
		}
	}
	$htmlstr .= '
				</SELECT></td></tr>
				<tr>
					<td valign="top">
						<div id="oe_parent_div">übergeordnete Organisationseinheit<br>
						<SELECT name="oe_parent_kurzbz" onchange="submitable()">';

	$qry = 'SELECT oe_kurzbz, organisationseinheittyp_kurzbz, bezeichnung FROM public.tbl_organisationseinheit ORDER BY organisationseinheittyp_kurzbz, bezeichnung';
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$htmlstr .= '	<option value="'.$row->oe_kurzbz.'">'.$row->organisationseinheittyp_kurzbz.' '.$row->bezeichnung.'</option>';
		}
	}
	
	$htmlstr .= '
			</SELECT></div><script type="text/javascript">toggleOeParentDiv();</script>
		</td></tr></table>
	</td></tr></table><br>';

	
	$htmlstr .= "<div align='right' id='sub'>\n";
	$htmlstr .= "	<span id='submsg' style='color:red; visibility:hidden;'>Datensatz ge&auml;ndert!&nbsp;&nbsp;</span>\n";
	$htmlstr .= "	<input type='hidden' name='neu' value='".$neu."'>";
	$htmlstr .= "	<input type='submit' value='Speichern' name='schick'>\n";
	$htmlstr .= "	<input type='button' value='Reset' onclick='unchanged()'>\n";
	$htmlstr .= "</div>";
	$htmlstr .= "</form>";
	$htmlstr .= "<div class='inserterror'>".$errorstr."</div>"
	

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Studiengang - Details</title>

<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<script src="../../include/js/mailcheck.js"></script>
<script src="../../include/js/datecheck.js"></script>
<script type="text/javascript" src="../../include/js/jquery1.9.min.js"></script>	
<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css"/>
<script type="text/javascript" src="../../include/tiny_mce/tiny_mce.js"></script>

<script type="text/javascript">
$(function() {
	$("#bescheidvom,#titelbescheidvom").datepicker();
});

tinyMCE.init({
		mode:'specific_textareas', 
		editor_selector:"mceEditor",
		theme : "advanced",
		language : "de",
		file_browser_callback: "FHCFileBrowser",
		
		plugins : "spellchecker,pagebreak,style,layer,table,advhr,advimage,advlink,inlinepopups,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking",
			
		// Theme options
        theme_advanced_buttons1 : "code, bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontsizeselect",
        theme_advanced_buttons2 : "", //tablecontrols,|,hr,removeformat,visualaid
		theme_advanced_buttons3 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "center",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,
        force_br_newlines : true,
        force_p_newlines : false,
        forced_root_block : '',
        editor_deselector : "mceNoEditor"		});

function unchanged()
{
		document.studiengangform.reset();
		document.studiengangform.schick.disabled = true;
		document.getElementById("submsg").style.visibility="hidden";
		checkmail();
		checkdate(document.studiengangform.bescheidvom);
		checkdate(document.studiengangform.titelbescheidvom);
		checkrequired(document.studiengangform.kurzbz);
		checkrequired(document.studiengangform.bezeichnung);
		checkrequired(document.studiengangform.studiengang_kz);
		

}

function checkmail()
{
	/*
	if((document.studiengangform.email.value != '')&&(!emailCheck(document.studiengangform.email.value)))
	{
		//document.studiengangform.schick.disabled = true;
		document.studiengangform.email.className="input_error";
		return false;

	}
	else
	{
		document.studiengangform.email.className = "input_ok";
		//document.studiengangform.schick.disabled = false;
		//document.getElementById("submsg").style.visibility="visible";
		return true;
	}*/
	return true;
}

function checkdate(feld)
{
	if ((feld.value != '') && (!dateCheck(feld)))
	{
		//document.studiengangform.schick.disabled = true;
		feld.className = "input_error";
		return false;
	}
	else
	{
		if(feld.value != '')
			feld.value = dateCheck(feld);

		feld.className = "input_ok";
		return true;
	}
}

function checkrequired(feld)
{
	if(feld.value == '')
	{
		feld.className = "input_error";
		return false;
	}
	else
	{
		feld.className = "input_ok";
		return true;
	}
}

function submitable()
{
	mail = checkmail();
	date1 = true;//checkdate(document.studiengangform.bescheidvom);
	date2 = true;//checkdate(document.studiengangform.titelbescheidvom);
	required1 = checkrequired(document.studiengangform.kurzbz);
	required2 = checkrequired(document.studiengangform.bezeichnung);
	required3 = checkrequired(document.studiengangform.studiengang_kz);

	if((!mail) || (!date1) || (!date2) || (!required1) || (!required2) || (!required3))
	{
		document.studiengangform.schick.disabled = true;
		document.getElementById("submsg").style.visibility="hidden";
	}
	else
	{
		document.studiengangform.schick.disabled = false;
		document.getElementById("submsg").style.visibility="visible";

	}
}

function toggleOeParentDiv()
{
	if(document.getElementById("oe_kurzbz").value=="")
		document.getElementById("oe_parent_div").style.visibility="visible";
	else
		document.getElementById("oe_parent_div").style.visibility="hidden";
}

</script>
</head>
<body style="background-color:#eeeeee;">

<?php
	echo $htmlstr;
	echo $reloadstr;
?>

</body>
</html>