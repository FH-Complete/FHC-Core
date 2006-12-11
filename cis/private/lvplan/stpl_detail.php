<?php
/****************************************************************************
 * Script: 			stpl_detail.php
 * Descr:  			Das Script dient zur Detailanzeige eines Eintrags im Stundenplan.
 *					Es wird in Verbandsplan und Reservierungen gesucht.
 * Verzweigungen: 	von stpl_week.php
 * Author: 			Christian Paminger
 * Erstellt: 		21.8.2001
 * Update: 			11.11.2004 von Christian Paminger
 *****************************************************************************/

include('../config.inc.php');

// Variablen uebernehmen
if (isset($_GET[type]))
	$type=$_GET[type];
if (isset($_GET[datum]))
	$datum=$_GET[datum];
if (isset($_GET[stunde]))
	$stunde=$_GET[stunde];
if (isset($_GET[ort_kurzbz]))
	$ort_kurzbz=$_GET[ort_kurzbz];
if (isset($_GET[pers_uid]))
	$pers_uid=$_GET[pers_uid];
if (isset($_GET[stg_kz]))
	$stg_kz=$_GET[stg_kz];
if (isset($_GET[sem]))
	$sem=$_GET[sem];
if (isset($_GET[ver]))
	$ver=$_GET[ver];
if (isset($_GET[grp]))
	$grp=$_GET[grp];
if (isset($_GET[einheit_kurzbz]))
	$einheit_kurzbz=$_GET[einheit_kurzbz];

if (!$conn = @pg_pconnect(CONN_STRING))
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
//Stundenplan
$sql_query='SELECT vw_stundenplan.*, tbl_lehrfach.bezeichnung, tbl_person.titel AS pers_titel, tbl_person.nachname, tbl_person.vornamen';
$sql_query.=' FROM vw_stundenplan, tbl_lehrfach, tbl_person';
$sql_query.=" WHERE datum='$datum' AND stunde=$stunde AND tbl_person.uid=vw_stundenplan.uid AND tbl_lehrfach.lehrfach_nr=vw_stundenplan.lehrfach_nr";
if ($type=='lektor')
    $sql_query.=" AND vw_stundenplan.uid='$pers_uid' ";
elseif ($type=='ort')
    $sql_query.=" AND ort_kurzbz='$ort_kurzbz' ";
else
{
    $sql_query.=' AND vw_stundenplan.studiengang_kz='.$stg_kz.' AND (vw_stundenplan.semester='.$sem;
    if ($type=='student')
		$sql_query.=' OR vw_stundenplan.semester='.($sem+1);
	$sql_query.=')';
    //if ($ver!='0')
	//	$sql_query.=" AND (verband='$ver' OR verband IS NULL OR verband='0')";
    //if ($grp!='0')
	//	$sql_query.=" AND (gruppe='$grp' OR gruppe IS NULL OR gruppe='0')";
}
$sql_query.=' ORDER BY unr ASC, stg_kurzbz, vw_stundenplan.semester, verband, gruppe, einheit_kurzbz, uid LIMIT 100';
//echo $sql_query.'<BR>';
$erg_stpl=pg_exec($conn, $sql_query);
$num_rows_stpl=pg_numrows($erg_stpl);

//Reservierungen
$sql_query="SELECT vw_reservierung.*, tbl_person.titel AS pers_titel, tbl_person.vornamen,tbl_person.nachname FROM vw_reservierung, tbl_person WHERE datum='$datum' AND stunde=$stunde";
if (isset($ort_kurzbz))
    $sql_query.=" AND vw_reservierung.ort_kurzbz='$ort_kurzbz'";
if ($type=='lektor')
    $sql_query.=" AND vw_reservierung.uid='$pers_uid' ";
$sql_query.=" AND vw_reservierung.uid=tbl_person.uid";
if ($type=='verband' || $type=='student')
    $sql_query.=" AND studiengang_kz='$stg_kz' AND (semester='$sem' OR semester=0 OR semester IS NULL)";
$sql_query.=' ORDER BY  titel LIMIT 100';
//echo $sql_query.'<BR>';
$erg_repl=pg_exec($conn, $sql_query);
$num_rows_repl=pg_numrows($erg_repl);
?>

<html>
<head>
    <title>Lehrveranstaltungsplan Details</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <link rel="stylesheet" href="../../skin/cis.css" type="text/css">
</head>
<body class="background_main">
<H2>Lehrveranstaltungsplan &rArr; Details</H2>
Datum: <?php echo $datum; ?><BR>
Stunde: <?php echo $stunde; ?><BR><BR>

<table class="stdplan">
<?php
if ($num_rows_stpl>0)
echo '<tr> <th>UNr</th><th>Lektor</th><th>Ort</th><th>Lehrfach</th><th>Bezeichnung</th><th>Verband</th><th>Einheit</th> </tr>';
for ($i=0; $i<$num_rows_stpl; $i++)
{
    $unr=pg_result($erg_stpl,$i,"unr");
    $ortkurzbz=pg_result($erg_stpl,$i,"ort_kurzbz");
    $lehrfachkurzbz=pg_result($erg_stpl,$i,"lehrfach");
    $bezeichnung=pg_result($erg_stpl,$i,"bezeichnung");
    $pers_kurzbz=pg_result($erg_stpl,$i,"lektor");
    $pers_titel=pg_result($erg_stpl,$i,"pers_titel");
    $pers_vornamen=pg_result($erg_stpl,$i,"vornamen");
    $pers_nachname=pg_result($erg_stpl,$i,"nachname");
    $pers_email=pg_result($erg_stpl,$i,"uid").'@technikum-wien.at';
    $stgkurzbz=pg_result($erg_stpl,$i,"stg_kurzbz");
    $semester=pg_result($erg_stpl,$i,"semester");
    $verband=pg_result($erg_stpl,$i,"verband");
    $gruppe=pg_result($erg_stpl,$i,"gruppe");
    $einheit_kurzbz=pg_result($erg_stpl,$i,"einheit_kurzbz");
    ?>
    <tr class="<?php echo 'liste'.$i%2; ?>">
        <td><?php echo $unr; ?></td>
        <td><A href="mailto:<?php echo $pers_email; ?>"><?php echo $pers_titel.' '.$pers_vornamen.' '.$pers_nachname; ?></A></td>
        <td><?php echo $ortkurzbz; ?></td>
        <td><?php echo $lehrfachkurzbz; ?></td>
        <td><?php echo $bezeichnung; ?></td>
        <td><A href="mailto:<?php echo strtolower($stgkurzbz).$semester.strtolower($verband).$gruppe; ?>@technikum-wien.at">
        <?php echo $stgkurzbz.'-'.$semester.$verband.$gruppe; ?></A></td>
        <td><A href="mailto:<?php echo strtolower($einheit_kurzbz); ?>@technikum-wien.at">
        <?php echo $einheit_kurzbz; ?></A></td>
    </tr>
    <?php
}
?>
</table><BR>
<?php
if ($num_rows_repl>0)
{
    echo '<h2>Reservierungen</h2>';
    echo '<table class="stdplan">';
    echo '<tr><th>Titel</th><th>Ort</th><th>Person</th><th>Beschreibung</th></tr>';
    for ($i=0; $i<$num_rows_repl; $i++)
    {
        $titel=pg_result($erg_repl,$i,"titel");
        $ortkurzbz=pg_result($erg_repl,$i,"ort_kurzbz");
        $pers_titel=pg_result($erg_repl,$i,"pers_titel");
   		$pers_vornamen=pg_result($erg_repl,$i,"vornamen");
   		$pers_nachname=pg_result($erg_repl,$i,"nachname");
    	$pers_email=pg_result($erg_repl,$i,"uid").'@technikum-wien.at';
    	$beschreibung=pg_result($erg_repl,$i,"beschreibung");
        echo '<tr class="liste'.($i%2).'">';
        echo '<td >'.$titel.'</td>';
        echo '<td >'.$ortkurzbz.'</td>';
        echo '<td ><A href="mailto:'.$pers_email.'">'.$pers_titel.' '.$pers_vornamen.' '.$pers_nachname.'</A></td>';
        echo '<td >'.$beschreibung.'</td></tr>';
    }
    echo '</table>';
}
?>
<P>Fehler und Feedback bitte an <A href="mailto:lvplan@technikum-wien.at">LV-Koordinationsstelle</A>.</P>
</body></html>
