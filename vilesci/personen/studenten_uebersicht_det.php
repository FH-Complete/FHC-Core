<?php
/**
 * Changes:	22.10.2004: Anpassung an neues DB-Schema (WM)
 */
	include('../config.inc.php');
	include('../../include/functions.inc.php');
	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
	$sql_query="SELECT tbl_student.uid, p.titel, p.vornamen, p.nachname, kurzbz, p.email, semester, verband, gruppe, matrikelnr FROM tbl_person as p join tbl_student using(uid), tbl_studiengang WHERE tbl_student.studiengang_kz=tbl_studiengang.studiengang_kz ";
	if (isset($_GET['stg_kz']))
		$sql_query.="AND tbl_student.studiengang_kz='".addslashes($_GET['stg_kz'])."' ";
	if (isset($_GET['sem']) && is_numeric($_GET['sem']))
		$sql_query.="AND semester=".$_GET['sem']." ";
	if (isset($_GET['ver']))
		$sql_query.="AND verband='".addslashes($_GET['ver'])."' ";
	if (isset($_GET['grp']) && is_numeric($_GET['grp']))
		$sql_query.="AND gruppe=".$_GET['grp']." ";
	$sql_query.="ORDER BY nachname, kurzbz, semester, verband";
	if(!($erg=pg_exec($conn, $sql_query)))
		die(pg_errormessage($conn));
	$num_rows=pg_numrows($erg);
?>

<html>
<head>
<title>Detail Studenten</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body class="background_main">
<h4>Detailansicht</h4>
Results: <?php echo $num_rows; ?><br>
<br>
<table border="0">
<tr bgcolor="<?php echo $cfgThBgcolor; ?>"><th>Titel</th><th>Vornamen</th><th>Nachname</th><th>STG</th><th>Sem.</th><th>Verband</th><th>Gruppe</th><th>Matrikelnr.</th><th>eMail</th></tr>
<?php
	for ($i=0; $i<$num_rows; $i++)
	{
		$zeile=$i % 2;

		$vornamen=pg_result($erg,$i,"vornamen");
		$nachname=pg_result($erg,$i,"nachname");
		$stgkurzbz=pg_result($erg,$i,"kurzbz");
		$titel=pg_result($erg,$i,"titel");
		$email=pg_result($erg,$i,"email");
		$matrikelnr=pg_result($erg,$i,"matrikelnr");
		$sem=pg_result($erg,$i,"semester");
		$ver=pg_result($erg,$i,"verband");
		$grp=pg_result($erg,$i,"gruppe");
		$id=pg_result($erg,$i,"uid");
		$emailtw=$id.'@technikum-wien.at';
		?>
		<tr class="liste<?php echo $zeile; ?>">
		<td><?php echo $titel; ?></td>
		<td><?php echo $vornamen; ?></td>
		<td><?php echo $nachname; ?></td>
		<td><?php echo $stgkurzbz; ?></td>
		<td><?php echo $sem; ?></td>
		<td><?php echo $ver; ?></td>
		<td><?php echo $grp; ?></td>
		<td><?php echo $matrikelnr; ?></td>
		<td><a href="mailto:<?php echo $emailtw; ?>"><?php echo $emailtw; ?></a></td>
		<td><a href="student_edit.php?id=<?php echo $id; ?>" class="linkblue">Edit</a></td>
		</tr>
		<?php
	}
?>
</table>
</body>
</html>
