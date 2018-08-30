<?php
/*
 * Copyright (C) 2006 Technikum-Wien
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA
 * .
 * Authors: Manfred Kindl <manfred.kindl@technikum-wien.at>
 */

require_once ('../../config/cis.config.inc.php');
require_once ('../../config/global.config.inc.php');
require_once ('../../include/functions.inc.php');
require_once ('../../include/person.class.php');
require_once ('../../include/prestudent.class.php');
require_once ('../../include/benutzerberechtigung.class.php');
require_once ('../../include/akte.class.php');
require_once ('../../include/dokument.class.php');
require_once ('../../include/mail.class.php');
require_once ('../../include/phrasen.class.php');
require_once ('../../include/dms.class.php');
require_once ('../../include/fotostatus.class.php');
require_once ('../../include/studiensemester.class.php');
require_once ('../../include/nation.class.php');
require_once ('../../include/personlog.class.php');
//require_once ('../bewerbung.config.inc.php');
//require_once ('../include/functions.inc.php');

header("Content-Type: text/html; charset=utf-8");

// session_cache_limiter('none'); //muss gesetzt werden sonst funktioniert der Download mit IE8 nicht
// session_start();

$sprache = getSprache();
$p = new phrasen($sprache);
$log = new personlog();

$db = new basis_db();

if (isset($_GET['lang']))
	setSprache($_GET['lang']);

$person_id = isset($_GET['person_id']) ? $_GET['person_id'] : '';
$typ = isset($_GET['typ']) ? $_GET['typ'] : 'akte'; // Parameter ob das Bild aus der Akte oder der Person geladen werden soll
$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('basis/fhausweis','suid'))
{
	die($rechte->errormsg);
}

$dokumenttyp = 'Lichtbil';

$error = '';
$message = '';
$dokumenttyp_upload = '';

$PHP_SELF = $_SERVER['PHP_SELF']; ?>
<!DOCTYPE HTML>
<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title><?php echo $p->t('bewerbung/fileUpload'); ?></title>
		<link rel="stylesheet" href="../../skin/croppie.css">
		<link rel="stylesheet" type="text/css" href="../../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="../../skin/fhcomplete.css">
		<script type="text/javascript" src="../../vendor/jquery/jqueryV1/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="../../vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
		<script src="../../include/js/croppie.js"></script>

		<script type="text/javascript">
		function showExtensionInfo()
		{
			var typ = 'Lichtbil';
			var extinfo = "";
			var fileReaderSupport = true;

			// Check for support of FileReader.
			if ( !window.FileReader || !window.File || !window.FileList || !window.Blob )
				fileReaderSupport = false;

			// Lichtbilder werden mit Croppie zugeschnitten und in imageupload.php hochgeladen
			if (typ == 'Lichtbil' && fileReaderSupport)
			{
				imageUpload();
			}
			else
			{
				$(".croppie-container").empty();
			}
		};

		function imageUpload()
		{
			$uploadCrop = $(".croppie-container").croppie({
				enableExif: true,
				enforceBoundary: true,
				enableOrientation: true,
				viewport: {
					width: 240,
					height: 320
				},
				boundary: {
					width: 400,
					height: 400
				}
			});

// 			$(".croppie-container").addClass("ready");
// 			$uploadCrop.croppie("bind",
// 			{
// 				url: 'Mani.jpg'
// 			}).then(function()
// 			{
// 				console.log("jQuery bind complete");
// 			});
			
			// Empfehlung von https://www.passbildgroesse.de/ sind 827x1063. Das Seitenverhältnis 828x1104 passt aber besser zum FH-Ausweis
			$("#fileselect").on("change", function () { readFile(this); });
			$("#submitimage").on("click", function (ev)
			{
				// Check ob File gewählt wurde
// 				if ($('input[type=file]').val() == '')
// 				{
// 					$("#messages").empty();
// 					$("#messages").html(	'<div class="alert alert-danger" id="danger-alert_dms_akteupload">'+
// 											'<button type="button" class="close" data-dismiss="alert">x</button>'+
// 											'<strong>No file selected</strong>'+
// 											'</div>');
// 				}
// 				else
				{
					$uploadCrop.croppie("result", {
						type: "base64",
	// 					size: {width: 828, height: 1104},
						size: "original",
						format: 'jpeg',
						backgroundColor: '#DDDDDD'
					}).then(function (resultdata) {
						var src = resultdata;
						var person_id = <?php echo $person_id; ?>;
						var filename = $('input[type=file]').val().split('\\').pop();

						//in imageupload.php wird das Bild verarbeitet und abgespeichert
						$.post(
							"imageupload.php",
							{src: src, person_id: person_id, img_filename: filename, img_type: 'image/jpeg'},
							function(data)
							{
								if (data.type == "success")
								{
									$("#messages").empty();
									$("#messages").html(	'<div class="alert alert-success" id="success-alert_dms_akteupload">'+
															'<button type="button" class="close" data-dismiss="alert">x</button>'+
															'<strong>'+data.msg+'</strong>'+
															'</div>');
									window.setTimeout(function()
									{
										$("#success-alert_dms_akteupload").fadeTo(500, 0).slideUp(500, function(){
											$(this).remove();
										});
										//window.location.href = 'dms_akteupload.php?person_id=<?php echo $person_id; ?>';
										//window.opener.location = 'bewerbung.php?active=dokumente';
									}, 1000);
								}
								else if (data.type == "error")
								{
									$("#messages").empty();
									$("#messages").html(	'<div class="alert alert-danger" id="danger-alert_dms_akteupload">'+
															'<button type="button" class="close" data-dismiss="alert">x</button>'+
															'<strong>'+data.msg+'</strong>'+
															'</div>');
								}
							},
							"json"
						);
					});
				}
			});
		};

		$(function()
		{
			showExtensionInfo();
			window.resizeTo(700, $('#documentForm').height() + 100);
		});

		function readFile(input)
		{
 			if (input.files && input.files[0])
 	 		{
				var reader = new FileReader();

				reader.onload = function (e)
				{
					var image = new Image();
					image.src = e.target.result;

					image.onload = function () {
						// Check auf Filetype
						var splittedSource = this.src.split(';'); // base64 String splitten
						var filetype = splittedSource[0];
						if (filetype != 'data:image/jpeg' && filetype != 'data:image/jpg')
						{
							alert("Das Bild muss von Typ .jpg sein");
							return false;
						}
						// Check auf Bildgroeße
						var height = this.height;
						var width = this.width;
						if (height < 320 || width < 240)
						{
							alert("Das Bild muss mindestens die Auflösung 240x320 Pixel haben.\nBitte wählen Sie ein größeres Bild.");
							return false;
						}
						else
						{
							$(".croppie-container").addClass("ready");
							$uploadCrop.croppie("bind",
							{
								url: e.target.result
							}).then(function()
							{
								console.log("jQuery bind complete");
							});
						}
					};


				}
				reader.readAsDataURL(input.files[0]);
			}
			else
			{
				alert("Sorry - you\'re browser doesn\'t support the FileReader API");
			}
		};

		window.setTimeout(function()
		{
			$("#success-alert_dms_akteupload").fadeTo(500, 0).slideUp(500, function(){
				$(this).remove();
			});
		}, 1500);

		</script>
		<style>
		body
		{
			margin:10px;
		}
		.errorAusstellungsnation
		{
			border-color: #a94442;
		}
		</style>
		</head>
		<body>
<?php
echo '<div class="container" id="messages">';

if ($error === false)
{
	echo '<div class="alert alert-success" id="success-alert_dms_akteupload">
	<button type="button" class="close" data-dismiss="alert">x</button>
	<strong>'.$message.'</strong>
	</div>';
}
elseif ($error === true)
{
	echo '<div class="alert alert-danger" id="danger-alert_dms_akteupload">
	<button type="button" class="close" data-dismiss="alert">x</button>
	<strong>'.$p->t('global/fehleraufgetreten').' </strong>'.$message.'
	</div>';
}
echo '</div>';

if ($person_id != '')
{
	echo '
	<form id="documentForm" method="POST" enctype="multipart/form-data" action="' . $PHP_SELF . '?person_id=' . $person_id . '&dokumenttyp=' . $dokumenttyp . '" class="form-horizontal">
	<div class="container"> <br />
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading"><strong>Upload Image</strong></div>
					<div class="panel-body">';

				// Container für Bildzuschnitt
				echo '<div class=""><img class="croppie-container" src="../../content/bild.php?src='.$typ.'&person_id='.$person_id.'" /></div>';
				echo'
						<div class="">
							<input id="fileselect" type="file" name="file" class="file" />
						</div><br>
						<input id="submitimage" type="button" name="submitimage" value="Upload" class="btn btn-labeled btn-primary">
						<input type="hidden" name="fileupload" id="fileupload">
					</div>
				</div>
			</div>
		</div>
	</div>

	</form>';
}
else
{
	echo $p->t('bewerbung/fehlerKeinePersonId');
}
function resize($filename, $width, $height)
{
	$ext = explode('.', $_FILES['file']['name']);
	$ext = mb_strtolower($ext[count($ext) - 1]);

	// Hoehe und Breite neu berechnen
	list ($width_orig, $height_orig) = getimagesize($filename);

	if ($width && ($width_orig < $height_orig))
	{
		$width = ($height / $height_orig) * $width_orig;
	}
	else
	{
		$height = ($width / $width_orig) * $height_orig;
	}

	$image_p = imagecreatetruecolor($width, $height);

	$image = imagecreatefromjpeg($filename);

	// Bild nur verkleinern aber nicht vergroessern
	if ($width_orig > $width || $height_orig > $height)
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
	else
		$image_p = $image;

	$tmpfname = tempnam(sys_get_temp_dir(), 'FHC');

	imagejpeg($image_p, $tmpfname, 80);

	imagedestroy($image_p);
	@imagedestroy($image);
	return $tmpfname;
}

// Sendet eine Email an die Assistenz, dass ein neues Dokument hochgeladen wurde
function sendDokumentupload($empfaenger_stgkz, $dokument_kurzbz, $orgform_kurzbz, $studiensemester_kurzbz, $prestudent_id, $dms_id)
{
	global $person_id, $p;

	// Array fuer Mailempfaenger. Vorruebergehende Loesung. Kindlm am 28.10.2015
	$empf_array = array();
	if (defined('BEWERBERTOOL_UPLOAD_EMPFAENGER'))
		$empf_array = unserialize(BEWERBERTOOL_UPLOAD_EMPFAENGER);

	$person = new person();
	$person->load($person_id);
	$dokumentbezeichnung = '';

	$studiengang = new studiengang();
	$studiengang->load($empfaenger_stgkz);
	$typ = new studiengang();
	$typ->getStudiengangTyp($studiengang->typ);

	$email = $p->t('bewerbung/emailDokumentuploadStart');
	$email .= '<br><table style="font-size:small"><tbody>';
	$email .= '<tr><td><b>' . $p->t('global/studiengang') . '</b></td><td>' . $typ->bezeichnung . ' ' . $studiengang->bezeichnung . ($orgform_kurzbz != '' ? ' (' . $orgform_kurzbz . ')' : '') . '</td></tr>';
	$email .= '<tr><td><b>' . $p->t('global/studiensemester') . '</b></td><td>' . $studiensemester_kurzbz . '</td></tr>';
	$email .= '<tr><td><b>' . $p->t('global/name') . '</b></td><td>' . $person->vorname . ' ' . $person->nachname . '</td></tr>';
	$email .= '<tr><td><b>' . $p->t('bewerbung/dokument') . '</b></td><td>';
	$akte = new akte();
	$akte->getAkten($person_id, $dokument_kurzbz);
	foreach ($akte->result as $row)
	{
		$dokument = new dokument();
		$dokument->loadDokumenttyp($row->dokument_kurzbz);
		if ($row->insertvon == 'online')
		{
			if ($row->nachgereicht == true)
				$email .= '- ' . $dokument->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE] . ' -> ' . $p->t('bewerbung/dokumentWirdNachgereicht') . '<br>';
			else
				$email .= '<a href="' . APP_ROOT . 'cms/dms.php?id=' . $dms_id . '">' . $dokument->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE] . ' [' . $row->bezeichnung . ']</a><br>';
			$dokumentbezeichnung = $dokument->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE];
		}
	}
	$email .= '</td>';
	$email .= '<tr><td><b>' . $p->t('bewerbung/prestudentID') . '</b></td><td>' . $prestudent_id . '</td></tr>';
	$email .= '</tbody></table>';
	$email .= '<br>' . $p->t('bewerbung/emailBodyEnde');
	
	// An der FHTW werden alle Mails von Bachelor-Studiengängen an das Infocenter geschickt, solange die Bewerbung noch nicht bestätigt wurde
	if (CAMPUS_NAME == 'FH Technikum Wien')
	{
		if(	defined('BEWERBERTOOL_MAILEMPFANG') && 
			BEWERBERTOOL_MAILEMPFANG != '' && 
			$studiengang->typ == 'b')
		{
			$empfaenger = BEWERBERTOOL_MAILEMPFANG;
		}
		else
			$empfaenger = getMailEmpfaenger($studiengang->typ, '', $orgform_kurzbz);
	}
	else 
	{
		$empfaenger = getMailEmpfaenger($empfaenger_stgkz);
	}
	
	$mail = new mail($empfaenger, 'no-reply', $p->t('bewerbung/dokumentuploadZuBewerbung', array(
		$dokumentbezeichnung
	)) . ' ' . $person->vorname . ' ' . $person->nachname, 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
	$mail->setHTMLContent($email);
	if (! $mail->send())
		return false;
	else
		return true;
}

?>
</body>
</html>
