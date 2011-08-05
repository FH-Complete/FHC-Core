<?php
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/student.class.php');
require_once('../../../include/datum.class.php');

$user = get_uid();
$student = new student($user);
$datum_obj = new datum();

$url = 'https://www.service4mobility.com/mobility/BewerbungServlet?identifier=wien20&kz_bew_art=OUT&kz_bew_pers=S&aust_prog=SMS&bew_matr_nr='.rawurlencode(trim($student->matrikelnr)).'&bew_nachname='.rawurlencode($student->nachname).'&bew_vorname='.rawurlencode($student->vorname).'&bew_titel='.rawurlencode($student->titelpre).'&bew_email='.rawurlencode($user.'@'.DOMAIN).'&bew_geb_datum='.rawurlencode($datum_obj->formatDatum($student->gebdatum,'d.m.Y')).'&bew_geschlecht='.rawurlencode(mb_strtoupper($student->geschlecht));

if(isset($_GET['sprache']) && $_GET['sprache']=='en')
	$url.='&sprache=en';

echo '
<html>
	<head>
	<script type="text/javascript">
	window.location.href="'.$url.'"
	</script>
	</head>
<body>
<a href="'.$url.'" class="Item">Sollten Sie nicht automatisch weitergeleitet werden klicke Sie bitte hier</a>
</body>
</html>';
?>