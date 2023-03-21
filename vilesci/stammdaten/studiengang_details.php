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
{
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
}

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/studiengang'))
{
	die('Sie haben keine Berechtigung fuer diese Seite');
}

$date=new datum();

$reload = false;  // neuladen der liste im oberen frame
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
$neu = true;
$oe_kurzbz='';
$moodle = true;
$projektarbeit_note_anzeige = true;
$sprache = '';
$testtool_sprachwahl = false;
$studienplaetze = '';
$orgform_kurzbz = '';
$lgartcode='';
$melderelevant = false;
$foerderrelevant = false;
$standort_code='';
$melde_studiengang_kz = '';
$schick = filter_input(INPUT_POST, 'schick');
$onlinebewerbung = false;

if($schick)
{
	$studiengang_kz = filter_input(INPUT_POST, 'studiengang_kz');
	$neu = filter_input(INPUT_POST, 'neu', FILTER_VALIDATE_BOOLEAN);

	if($neu)
	{
		if(!$rechte->isBerechtigt('basis/studiengang', null, 'suid'))
		{
			die('Sie haben keine Rechte fuer diese Aktion');
		}
	}
	else
	{
		$stg_hlp = new studiengang();
		if(!$stg_hlp->load($studiengang_kz))
		{
			die('Fehler beim Laden des Studienganges: '.$stg_hlp->errormsg);
		}

		if(!$rechte->isBerechtigt('basis/studiengang', $stg_hlp->oe_kurzbz, 'su'))
		{
			die('Sie haben keine Rechte fuer diese Aktion');
		}
	}

	$kurzbz = filter_input(INPUT_POST, 'kurzbz');
	$kurzbzlang = filter_input(INPUT_POST, 'kurzbzlang');
	$typ = filter_input(INPUT_POST, 'typ');
	$bezeichnung = filter_input(INPUT_POST, 'bezeichnung');
	$english = filter_input(INPUT_POST, 'english');
	$farbe = filter_input(INPUT_POST, 'farbe');
	$email = filter_input(INPUT_POST, 'email');
	$telefon = filter_input(INPUT_POST, 'telefon');
	$max_semester = filter_input(INPUT_POST, 'max_semester');
	$max_verband = filter_input(INPUT_POST, 'max_verband');
	$max_gruppe = filter_input(INPUT_POST, 'max_gruppe');
	$erhalter_kz = filter_input(INPUT_POST, 'erhalter_kz');
	$bescheid = filter_input(INPUT_POST, 'bescheid');
	$bescheidbgbl1 = filter_input(INPUT_POST, 'bescheidbgbl1');
	$bescheidbgbl2 = filter_input(INPUT_POST, 'bescheidbgbl2');
	$bescheidgz = filter_input(INPUT_POST, 'bescheidgz');
	$bescheidvom = filter_input(INPUT_POST, 'bescheidvom');
	$oe_kurzbz = filter_input(INPUT_POST, 'oe_kurzbz');
	$oe_parent_kurzbz = filter_input(INPUT_POST, 'oe_parent_kurzbz');
	$titelbescheidvom = filter_input(INPUT_POST, 'titelbescheidvom');
	$zusatzinfo_html = filter_input(INPUT_POST, 'zusatzinfo_html');
	$moodle = filter_input(INPUT_POST, 'moodle', FILTER_VALIDATE_BOOLEAN);
	$projektarbeit_note_anzeige = filter_input(INPUT_POST, 'projektarbeit_note_anzeige', FILTER_VALIDATE_BOOLEAN);
	$sprache = filter_input(INPUT_POST, 'sprache');
	$testtool_sprachwahl = filter_input(INPUT_POST, 'testtool_sprachwahl', FILTER_VALIDATE_BOOLEAN);
	$studienplaetze = filter_input(INPUT_POST, 'studienplaetze');
	$orgform_kurzbz = filter_input(INPUT_POST, 'orgform_kurzbz');
	$lgartcode = filter_input(INPUT_POST, 'lgartcode');
	$aktiv = filter_input(INPUT_POST, 'aktiv', FILTER_VALIDATE_BOOLEAN);
	$onlinebewerbung = filter_input(INPUT_POST, 'onlinebewerbung', FILTER_VALIDATE_BOOLEAN);
	$mischform = filter_input(INPUT_POST, 'mischform', FILTER_VALIDATE_BOOLEAN);
	$melderelevant = filter_input(INPUT_POST, 'melderelevant', FILTER_VALIDATE_BOOLEAN);
	$foerderrelevant = filter_input(INPUT_POST, 'foerderrelevant', FILTER_VALIDATE_BOOLEAN);
	$standort_code = filter_input(INPUT_POST, 'standort_code');
	$melde_studiengang_kz = filter_input(INPUT_POST, 'melde_studiengang_kz');

	$ext_id = filter_input(INPUT_POST, 'ext_id');

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
		$sg_update->onlinebewerbung = $onlinebewerbung;
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
		$sg_update->melderelevant = $melderelevant;
		$sg_update->foerderrelevant = $foerderrelevant;
		$sg_update->standort_code = $standort_code;
		$sg_update->melde_studiengang_kz = $melde_studiengang_kz;

		$sg_update->bescheidvom=$date->formatDatum($sg_update->bescheidvom,'Y-m-d');
		$sg_update->titelbescheidvom=$date->formatDatum($sg_update->titelbescheidvom,'Y-m-d');

		$neu = filter_input(INPUT_POST, 'neu', FILTER_VALIDATE_BOOLEAN);

		if ($neu)
		{
			$sg_update->new = true;
		}

		if(!$sg_update->save())
		{
			$errorstr .= $sg_update->errormsg;
		}
	}

	$reload = true;
}



if ((isset($_REQUEST['studiengang_kz'])) && ((!isset($_REQUEST['neu'])) || ($_REQUEST['neu']!= 'true')))
{
	$studiengang_kz = $_REQUEST['studiengang_kz'];

	$sg = new studiengang($studiengang_kz);

	if ($sg->errormsg!='')
	{
		die($sg->errormsg);
	}

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
	$onlinebewerbung = $sg->onlinebewerbung;
	$mischform = $sg->mischform;
	$oe_kurzbz = $sg->oe_kurzbz;
	$neu = false;
	$moodle = $sg->moodle;
	$projektarbeit_note_anzeige = $sg->projektarbeit_note_anzeige;
	$sprache = $sg->sprache;
	$testtool_sprachwahl = $sg->testtool_sprachwahl;
	$studienplaetze = $sg->studienplaetze;
	$orgform_kurzbz = $sg->orgform_kurzbz;
	$lgartcode = $sg->lgartcode;
	$melderelevant = $sg->melderelevant;
	$foerderrelevant = $sg->foerderrelevant;
	$standort_code = $sg->standort_code;
	$melde_studiengang_kz = $sg->melde_studiengang_kz;
}

$erh = new erhalter();

if (!$erh->getAll('kurzbz'))
{
	die($erh->errormsg);
} ?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Studiengang - Details</title>
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<script src="../../include/js/mailcheck.js"></script>
		<script src="../../include/js/datecheck.js"></script>
		<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
		<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
		<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css"/>
		<script type="text/javascript" src="../../include/tiny_mce/tiny_mce.js"></script>
		<script type="text/javascript" src="studiengang_details.js"></script>
	</head>
	<body style="background-color:#eeeeee;">

		<br>
		<div class="kopf">
			Studiengang
			<b><?php echo $bezeichnung ?></b>
		</div>
		<form action="studiengang_details.php" method="POST" name="studiengangform">
			<table class="detail">
				<tr>
					<td colspan="3">
					</td>
				</tr>
				<tr>
					<td valign="top">
						<table>
							<tr>
								<td>Kennzahl</td>
								<td>
									<input class="detail" type="text" name="studiengang_kz" size="16" maxlength="5" value="<?php echo $studiengang_kz ?>"
								<?php if($neu): ?>
									onchange="submitable()"
								<?php else: ?>
									style="background-color:#eeeeee;" readonly="readonly"
								<?php endif; ?>
								></td>
							</tr>
							<tr>
								<td>Kurzbezeichnung</td>
								<td>
									<input class="detail" type="text" name="kurzbz" size="16" maxlength="3" value="<?php echo $kurzbz ?>" onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td>KurzbezeichnungLang</td>
								<td>
									<input class="detail" type="text" name="kurzbzlang" size="16" maxlength="8" value="<?php echo $kurzbzlang ?>" onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td>Max Semester</td>
								<td>
									<input class="detail" type="text" name="max_semester" size="16" maxlength="2" value="<?php echo $max_semester ?>" onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td>Max Verband</td>
								<td>
									<input class="detail" type="text" name="max_verband" size="16" maxlength="1" value="<?php echo $max_verband ?>" onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td>Max Gruppe</td>
								<td>
									<input class="detail" type="text" name="max_gruppe" size="16" maxlength="1" value="<?php echo $max_gruppe ?>" onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td>OrgformKurzbz</td>
								<td>
									<SELECT name="orgform_kurzbz" onchange="submitable()">
										<?php
										$qry = 'SELECT orgform_kurzbz '
												. 'FROM bis.tbl_orgform '
												. 'ORDER BY orgform_kurzbz';

										if($result = $db->db_query($qry))
										{
											while($row = $db->db_fetch_object($result))
											{
												if($row->orgform_kurzbz == $orgform_kurzbz)
													$selected = 'selected';
												else
													$selected = ''; ?>

												<option value="<?php echo $row->orgform_kurzbz ?>" <?php echo $selected ?>>
													<?php echo $row->orgform_kurzbz ?>
												</option>
												<?php
											}
										} ?>
									</SELECT>
								</td>
							</tr>
							<tr>
								<td valign="top">Aktiv</td>
								<td>
									<input type="hidden" name="aktiv" value="0">
									<input type="checkbox" name="aktiv" <?php echo $aktiv ? 'checked' : '' ?> onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td valign="top">Onlinebewerbung</td>
								<td>
									<input type="hidden" name="onlinebewerbung" value="0">
									<input type="checkbox" name="onlinebewerbung" <?php echo $onlinebewerbung ? 'checked' : '' ?> onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td valign="top">Testtool-Sprachwahl</td>
								<td>
									<input type="hidden" name="testtool_sprachwahl" value="0">
									<input type="checkbox" name="testtool_sprachwahl" <?php echo $testtool_sprachwahl ? 'checked' : '' ?> onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td valign="top">Moodle</td>
								<td>
									<input type="hidden" name="moodle" value="0">
									<input type="checkbox" name="moodle" <?php echo $moodle ? 'checked' : '' ?> onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td valign="top">Projektarbeitsnote</td>
								<td>
									<input type="hidden" name="projektarbeit_note_anzeige" value="0">
									<input type="checkbox" name="projektarbeit_note_anzeige" <?php echo $projektarbeit_note_anzeige ? 'checked' : '' ?> onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td valign="top">Mischform</td>
								<td>
									<input type="hidden" name="mischform" value="0">
									<input type="checkbox" name="mischform" <?php echo $mischform ? 'checked' : '' ?> onchange="submitable()">
								</td>
							</tr>
						</table>
					</td>
					<td valign="top">
						<table>
							<tr>
								<td>Erhalter</td>
								<td>
									<select name="erhalter_kz" onchange="submitable()">
										<?php foreach($erh->result as $erhalter)
										{
											if ($erhalter_kz == $erhalter->erhalter_kz)
												$sel = 'selected';
											else
												$sel = ''; ?>
											<option value="<?php echo $erhalter->erhalter_kz ?>" <?php echo $sel ?>>
												<?php echo $erhalter->bezeichnung ?>
											</option>
										<?php } ?>
									</select>
								</td>
							</tr>
							<tr>
								<td>Typ</td>
								<td>
									<select name="typ" onchange="submitable()">
									<option value=""></option>

									<?php foreach(array_keys($studiengang_typ_arr) as $typkey):
										if ($typ == $typkey)
											$sel = 'selected';
										else
											$sel = ''; ?>
										<option value="<?php echo $typkey ?>" <?php echo $sel ?>>
											<?php echo $studiengang_typ_arr[$typkey] ?>
										</option>
									<?php endforeach; ?>
									</select>
								</td>
							</tr>
							<tr>
								<td>Farbe</td>
								<td>
									<input class="detail" type="text" name="farbe" size="16" maxlength="6" value="<?php echo $farbe ?>" onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td>Bescheidbgbl1</td>
								<td>
									<input class="detail" type="text" name="bescheidbgbl1" size="16" maxlength="16" value="<?php echo $bescheidbgbl1 ?>" onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td>Bescheidbgbl2</td>
								<td>
									<input class="detail" type="text" name="bescheidbgbl2" size="16" maxlength="16" value="<?php echo $bescheidbgbl2 ?>" onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td>Bescheidgz</td>
								<td>
									<input class="detail" type="text" name="bescheidgz" size="16" maxlength="16" value="<?php echo $bescheidgz ?>" onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td>Bescheidvom</td>
								<td>
									<input class="detail" type="text" id="bescheidvom" name="bescheidvom" size="16" maxlength="10" value="<?php echo $date->formatDatum($bescheidvom,"d.m.Y") ?>" onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td>Titelbescheidvom</td>
								<td>
									<input class="detail" type="text" id="titelbescheidvom" name="titelbescheidvom" size="16" maxlength="10" value="<?php echo $date->formatDatum($titelbescheidvom,"d.m.Y") ?>" onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td>Sprache</td>
								<td>
									<select name="sprache" onchange="submitable()">
										<option value="">-- keine Auswahl --</option>
										<?php
										$qry = 'SELECT sprache '
												. 'FROM public.tbl_sprache '
												. 'ORDER BY sprache';

										if($result = $db->db_query($qry)):
											while($row = $db->db_fetch_object($result)):
												if($row->sprache == $sprache)
													$selected = 'selected';
												else
													$selected = ''; ?>

												<option value="<?php echo $row->sprache ?>" <?php echo $selected ?>>
													<?php echo $row->sprache ?>
												</option>
											<?php endwhile; ?>
										<?php endif; ?>
									</select>
								</td>
							</tr>
							<tr>
								<td>LehrgangsartCode</td>
								<td>
									<select name="lgartcode" onchange="submitable()">
										<option value="">-- keine Auswahl --</option>
										<?php
										$qry = 'SELECT * '
											. 'FROM bis.tbl_lgartcode '
											. 'ORDER BY lgartcode';

										if($result = $db->db_query($qry)):
											while($row = $db->db_fetch_object($result)):
												if($row->lgartcode == $lgartcode)
													$selected = 'selected';
												else
													$selected = ''; ?>

												<option value="<?php echo $row->lgartcode ?>" <?php echo $selected ?>>
													<?php echo $row->lgartcode ?> - <?php echo $row->kurzbz ?>
												</option>
											<?php endwhile; ?>
										<?php endif; ?>
									</select>
								</td>
							</tr>
							<tr>
								<td>Standort</td>
								<td>
									<select name="standort_code" onchange="submitable()">
										<option value="">-- keine Auswahl --</option>
										<?php
										$qry = 'SELECT standort_code, bezeichnung '
											. 'FROM bis.tbl_bisstandort '
											. 'WHERE aktiv '
											. 'ORDER BY bezeichnung';

										if($result = $db->db_query($qry)):
											while($row = $db->db_fetch_object($result)):
												if($row->standort_code == $standort_code)
													$selected = 'selected';
												else
													$selected = ''; ?>

												<option value="<?php echo $row->standort_code ?>" <?php echo $selected ?>>
													<?php echo $row->bezeichnung ?> - <?php echo $row->standort_code ?>
												</option>
											<?php endwhile; ?>
										<?php endif; ?>
									</select>
								</td>
							</tr>
							<tr>
								<td valign="top">Melderelevant</td>
								<td>
									<input type="hidden" name="melderelevant" value="0">
									<input type="checkbox" name="melderelevant" <?php echo $melderelevant ? 'checked' : '' ?> onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td valign="top">F&ouml;rderrelevant</td>
								<td>
									<input type="hidden" name="foerderrelevant" value="0">
									<input type="checkbox" name="foerderrelevant" <?php echo $foerderrelevant ? 'checked' : '' ?> onchange="submitable()">
								</td>
							</tr>
						</table>
					</td>
					<td valign="top">
						<table>
							<tr>
								<td>Meldestudiengangskennzahl</td>
								<td>
									<input class="detail" type="text" name="melde_studiengang_kz" size="16" maxlength="7" value="<?php echo $melde_studiengang_kz ?>" onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td>Bezeichnung</td>
								<td>
									<input class="detail" type="text" name="bezeichnung" size="50" maxlength="128" value="<?php echo $bezeichnung ?>" onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td>English</td>
								<td>
									<input class="detail" type="text" name="english" size="50" maxlength="128" value="<?php echo $english ?>" onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td>Email</td>
								<td>
									<input class="detail" type="text" name="email" size="50" maxlength="64" value="<?php echo $email ?>" onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td>Telefon</td>
								<td>
									<input class="detail" type="text" name="telefon" size="50" maxlength="32" value="<?php echo $telefon ?>" onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td>Studienplätze</td>
								<td>
									<input class="detail" type="text" name="studienplaetze" size="5" maxlength="5" value="<?php echo $studienplaetze ?>" onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td>Ext ID</td>
								<td>
									<input class="detail" type="text" name="ext_id" size="16" maxlength="16" value="<?php echo $ext_id ?>" onchange="submitable()">
								</td>
							</tr>
							<tr>
								<td valign="top">Bescheid</td>
								<td>
									<textarea name="bescheid" cols="37" rows="5" onchange="submitable()"><?php echo $bescheid ?></textarea>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<table>
							<tr>
								<td valign="top">Zusatzinfo</td>
								<td>
									<textarea id="zusatzinfo_html" class="mceEditor" name="zusatzinfo_html" cols="50" rows="4" onchange="submitable()">
										<?php echo $zusatzinfo_html ?>
									</textarea>
								</td>
							</tr>
						</table>
					</td>
					<td valign="top">
						<table>
							<tr>
								<td>Organisationseinheit<br>
									<select id="oe_kurzbz" name="oe_kurzbz" onchange="submitable();toggleOeParentDiv()">
										<option value="">-- neue Organisationseinheit anlegen --</option>
											<?php
											$qry = 'SELECT oe_kurzbz, organisationseinheittyp_kurzbz, bezeichnung '
												. 'FROM public.tbl_organisationseinheit '
												. 'ORDER BY organisationseinheittyp_kurzbz, bezeichnung';

											if($result = $db->db_query($qry)):
												while($row = $db->db_fetch_object($result)):
													if($row->oe_kurzbz == $oe_kurzbz)
														$selected = 'selected';
													else
														$selected = ''; ?>

													<option value="<?php echo $row->oe_kurzbz ?>" <?php echo $selected ?>>
														<?php echo $row->organisationseinheittyp_kurzbz ?>
														<?php echo $row->bezeichnung ?>
													</option>
												<?php endwhile; ?>
											<?php endif; ?>
									</select>
								</td>
							</tr>
							<tr>
								<td valign="top">
									<div id="oe_parent_div">übergeordnete Organisationseinheit<br>
										<select name="oe_parent_kurzbz" onchange="submitable()">
											<?php
											$qry = 'SELECT oe_kurzbz, organisationseinheittyp_kurzbz, bezeichnung '
													. 'FROM public.tbl_organisationseinheit '
													. 'ORDER BY organisationseinheittyp_kurzbz, bezeichnung';

											if($result = $db->db_query($qry)):
												while($row = $db->db_fetch_object($result)): ?>
													<option value="<?php echo $row->oe_kurzbz ?>">
														<?php echo $row->organisationseinheittyp_kurzbz ?>
														<?php echo $row->bezeichnung ?>
													</option>
												<?php endwhile; ?>
											<?php endif; ?>

										</select>
									</div>
									<script type="text/javascript">
										toggleOeParentDiv();
									</script>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<br>

			<div align="right" id="sub">
				<span id="submsg" style="color:red; visibility:hidden;">
					Datensatz geändert!
				</span>
				<input type="hidden" name="neu" value="<?php echo var_export($neu, true) ?>">
				<input type="submit" value="Speichern" name="schick">
				<input type="button" value="Reset" onclick="unchanged()">
			</div>
		</form>
		<div class="inserterror"><?php echo $errorstr ?></div>


		<?php if($reload): ?>
			<script type="text/javascript">
				parent.uebersicht_studiengang.location.href = "studiengang_uebersicht.php";
			</script>
		<?php endif; ?>
	</body>
</html>
