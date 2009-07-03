<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<HTML>
<HEAD>
	<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<TITLE>Lehrveranstaltungsplan Technikum-Wien</TITLE>
	<script type="text/javascript">
		<!--
		function MM_jumpMenu(targ,selObj,restore)
		{ //v3.0
	  		eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
	  		if (restore)
	  			selObj.selectedIndex=0;
		}
		-->
	</script>
	<LINK rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
</HEAD>

<BODY id="inhalt">
<H2><table class="tabcontent">
	<tr>
	<td>&nbsp;<a class="Item" href="index.php">Lehrveranstaltungsplan</a> &gt;&gt; Wochenplan</td>
	<td align="right"><A href="help/index.html" class="hilfe" target="_blank">HELP&nbsp;</A></td>
	</tr>
	</table>
</H2>
<?php
/****************************************************************************
 * Script: 			stpl_week.php
 * Descr:  			Das Script dient zum Navigieren im Stundenplan.
 *					Ein Lektor kann auch einen Saal reservieren
 * Verzweigungen: 	nach stpl_detail.php
 *					von index.php
 * Author: 			Christian Paminger
 * Erstellt: 		21.8.2001
 * Update: 			15.11.2004 von Christian Paminger
 *****************************************************************************/



//$type='ort';
//$ort_kurzbz='EDV6.08';
//$datum=1102260015;

	require_once('../../../config/cis.config.inc.php');
  require_once('../../../include/basis_db.class.php');
  if (!$db = new basis_db())
      die('Fehler beim Oeffnen der Datenbankverbindung');
  
//require_once('../../../include/globals.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/wochenplan.class.php');
require_once('../../../include/benutzerberechtigung.class.php');

$uid=get_uid();

// Deutsche Umgebung
//$loc_de=setlocale(LC_ALL, 'de_AT@euro', 'de_AT','de_DE@euro', 'de_DE');
//setlocale(LC_ALL, $loc_de);

// Variablen uebernehmen
if (isset($_GET['type']))
	$type=$_GET['type'];
if (isset($_POST['type']))
	$type=$_POST['type'];
if (isset($_GET['datum']))
	$datum=$_GET['datum'];
if (isset($_POST['datum']))
	$datum=$_POST['datum'];

if (isset($_GET['ort_kurzbz']))
	$ort_kurzbz=$_GET['ort_kurzbz'];
else if (isset($_POST['ort_kurzbz']))
	$ort_kurzbz=$_POST['ort_kurzbz'];
else
	$ort_kurzbz=null;

if (isset($_GET['pers_uid']))
	$pers_uid=$_GET['pers_uid'];

if (isset($_GET['stg_kz']))
	$stg_kz=$_GET['stg_kz'];
else if (isset($_POST['stg_kz']))
	$stg_kz=$_POST['stg_kz'];
else
	$stg_kz=null;

if (isset($_POST['sem']))
	$sem=$_POST['sem'];
else if (isset($_GET['sem']))
	$sem=$_GET['sem'];
else
	$sem=null;

if (isset($_POST['ver']))
	$ver=$_POST['ver'];
else if (isset($_GET['ver']))
	$ver=$_GET['ver'];
else
	$ver=null;

if (isset($_POST['grp']))
	$grp=$_POST['grp'];
else if (isset($_GET['grp']))
	$grp=$_GET['grp'];
else
	$grp=null;

if (isset($_POST['gruppe_kurzbz']))
	$gruppe_kurzbz=$_POST['gruppe_kurzbz'];
else if (isset($_GET['gruppe_kurzbz']))
	$gruppe_kurzbz=$_GET['gruppe_kurzbz'];
else
	$gruppe_kurzbz=null;

if (isset($_POST['user_uid']))
	$user_uid=$_POST['user_uid'];
if (isset($_POST['reserve']))
	$reserve=$_POST['reserve'];
if (isset($_POST['beschreibung']))
	$beschreibung=$_POST['beschreibung'];
if (isset($_POST['titel']))
	$titel=$_POST['titel'];

$berechtigung=new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if ($berechtigung->isBerechtigt('raumres'))
	$raumres=true;
else
	$raumres=false;

// Datums Format
if(!$erg_std=$db->db_query("SET datestyle TO ISO; SET search_path TO campus;"))
{
	die($db->db_last_error());
}

// Authentifizierung
if (check_student($uid))
	$user='student';
elseif (check_lektor($uid))
	$user='lektor';
else
{
	//die("Cannot set usertype!");
	//GastAccountHack
	$user='student';
}

    // User bestimmen
if (!isset($type))
	$type=$user;
if (!isset($pers_uid))
	$pers_uid=$uid;

//echo $uid.':'.$user_uid;
//var_dump($_POST);

// Reservieren
if (isset($reserve) && ($user=='lektor' || $raumres))
{
	if(!$erg_std=$db->db_query("SELECT * FROM lehre.tbl_stunde ORDER BY stunde"))
	{
		die($db->db_last_error());
	}
	$num_rows_std=$db->db_num_rows($erg_std);
	$count=0;
	for ($t=1;$t<7;$t++)
		for ($j=0;$j<$num_rows_std;$j++)
		{
			$stunde=$db->db_result($erg_std,$j,'"stunde"');
			$var='reserve'.$t.'_'.$stunde;
			//echo $$var;
			if (isset($_POST[$var]))
				$$var=$_POST[$var];
			if (isset($$var))
			{
				$datum_res=$$var;
				//echo $datum_res;
				$query="INSERT INTO campus.tbl_reservierung
							(datum, uid, ort_kurzbz, stunde, beschreibung, titel, studiengang_kz )
						VALUES
							('$datum_res', '$user_uid', '$ort_kurzbz', $stunde, '$beschreibung', '$titel', 0)"; // semester, verband, gruppe, gruppe_kurzbz,
				//echo $query;
				if(!($erg=$db->db_query($query)))
					echo $db->db_last_error();
				$count++;
			}
		}
}

// Stundenplan erstellen
$stdplan=new wochenplan($type);
if (!isset($datum))
	$datum=mktime();

// Benutzergruppe
$stdplan->user=$user;
// aktueller Benutzer
$stdplan->user_uid=$uid;

// Zusaetzliche Daten laden
if (! $stdplan->load_data($type,$pers_uid,$ort_kurzbz,$stg_kz,$sem,$ver,$grp,$gruppe_kurzbz) )
{
	die($stdplan->errormsg);
}

//echo 'Datum:'.$datum.'<BR>';
// Stundenplan einer Woche laden
if (! $stdplan->load_week($datum))
{
	die($stdplan->errormsg);
}

// Kopfbereich drucken
if (! $stdplan->draw_header())
{
	die($stdplan->errormsg);
}

// Stundenplan der Woche drucken
$stdplan->draw_week($raumres,$uid);

if (isset($count))
	echo "Es wurden $count Stunden reserviert!<BR>";
?>
<HR>
<P>Fehler und Feedback bitte an <A class="Item" href="mailto:<?php echo MAIL_LVPLAN?>">LV-Koordinationsstelle</A>.</P>
</BODY>
</HTML>
