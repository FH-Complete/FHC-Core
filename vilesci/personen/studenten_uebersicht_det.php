<?php
/**
 * Changes:	22.10.2004: Anpassung an neues DB-Schema (WM)
 */
	include('../config.inc.php');
	include('../../include/functions.inc.php');
	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
	$sql_query="SELECT uid, titelpre, vorname, nachname, UPPER(typ::varchar(1) || kurzbz) as kurzbz, semester, verband, gruppe, matrikelnr FROM campus.vw_student JOIN public.tbl_studiengang USING(studiengang_kz) WHERE true ";
	if (isset($_GET['stg_kz']))
		$sql_query.="AND studiengang_kz='".addslashes($_GET['stg_kz'])."' ";
	if (isset($_GET['sem']) && is_numeric($_GET['sem']))
		$sql_query.="AND semester=".$_GET['sem']." ";
	if (isset($_GET['ver']))
		$sql_query.="AND verband='".addslashes($_GET['ver'])."' ";
	if (isset($_GET['grp']) && is_numeric($_GET['grp']))
		$sql_query.="AND gruppe=".$_GET['grp']." ";
	$sql_query.="ORDER BY nachname, kurzbz, semester, verband";
	if(!($erg=pg_query($conn, $sql_query)))
		die(pg_errormessage($conn));
	$num_rows=pg_num_rows($erg);
?>

<html>
<head>
<title>Detail Studenten</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body class="background_main">
<h4>Detailansicht</h4>
Results: <?php echo $num_rows; ?><br>
<br>
<table border="0">
<tr><th>Titel</th><th>Vornamen</th><th>Nachname</th><th>STG</th><th>Sem.</th><th>Verband</th><th>Gruppe</th><th>Matrikelnr.</th><th>eMail</th></tr>
<?php
	for ($i=0; $row=pg_fetch_object($erg); $i++)
	{
		$zeile=$i % 2;

		$vorname=$row->vorname;
		$nachname=$row->nachname;
		$stgkurzbz=$row->kurzbz;
		$titel=$row->titelpre;
		$matrikelnr=$row->matrikelnr;
		$sem=$row->semester;
		$ver=$row->verband;
		$grp=$row->gruppe;
		$id=$row->uid;
		$emailtw=$id.'@technikum-wien.at';
		?>
		<tr class="liste<?php echo $zeile; ?>">
		<td><?php echo $titel; ?></td>
		<td><?php echo $vorname; ?></td>
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
