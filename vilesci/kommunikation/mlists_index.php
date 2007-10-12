<?php
/**
 * Changes:	23.10.2004: Anpassung an neues DB-Schema (WM)
 */
	include('../../config.inc.php');
	include('../../../include/functions.inc.php');
	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
	if ($type=='new')
	{
		$sql_query="INSERT INTO tbl_mailgrp (mailgrp_kurzbz, beschreibung, studiengang_kz) VALUES ('$mgkurzbz','$beschreibung',$stgid)";
		echo $sql_query;
		$result=pg_exec($conn, $sql_query);
	}
	$sql_query="SELECT studiengang_kz, kurzbz FROM tbl_studiengang ORDER BY kurzbz";
	//echo $sql_query."<br>";
	$result_stg=pg_exec($conn, $sql_query);
	if(!$result_stg)
		error ("studiengang not found!");
	$sql_query="SELECT mailgrp_kurzbz AS mailgrpkurzbz, tbl_studiengang.kurzbz AS stgkurzbz, tbl_mailgrp.beschreibung, generiert FROM tbl_mailgrp join tbl_studiengang using(studiengang_kz)  ORDER BY stgkurzbz, mailgrpkurzbz";
	if(!($erg=pg_exec($conn, $sql_query)))
		die(pg_errormessage($conn));
	$num_rows=pg_numrows($erg);
?>

<html>
<head>
<title>Detail Studenten</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
</head>

<body class="background_main">
<h2>Mailing Gruppen</h2>
<A href="index.html" class="linkblue">&lt;&lt; Back</A><BR>
<BR>Anzahl:
<?php echo $num_rows; ?>
<br>
<br>
<table border="0">
<tr class="liste"><th></th><th>Alias</th><th>Stg</th><th>Beschreibung</th></tr>
<?php
	for ($i=0; $i<$num_rows; $i++)
	{
		$mgkurzbz=pg_result($erg,$i,"mailgrpkurzbz");
		$stgkurzbz=pg_result($erg,$i,"stgkurzbz");
		$beschreibung=pg_result($erg,$i,"beschreibung");
		$generiert=pg_result($erg,$i,"generiert");
		//$id=pg_result($erg,$i,"studiengang_kz");

		echo "<tr class='liste".($i%2)."'>";
		echo "<td><a href='mlists_det.php?mailgrpid=$mgkurzbz&bez=$mgkurzbz' class='linkblue'>Details</a></td>";
		echo "<td><a href='mailto:$mgkurzbz@technikum-wien.at' class='linkgreen'>$mgkurzbz</a></td>";
		if($generiert=='f')
		{
			echo "<td>$stgkurzbz</td>";
			echo "<td>$beschreibung</td>";
		}
		else
		{
			echo "<td><font color='#AAAAAA'>$stgkurzbz</font></td>";
			echo "<td><font color='#AAAAAA'>$beschreibung</font></td>";
		}
		echo "</tr>";

	}
?>
</table>

<?php
	if ($PHP_AUTH_USER=='pam')
	{ ?>
		<FORM name="newgrp" method="post" action="mlists_index.php">
		Neu:<BR>
		<INPUT type="hidden" name="type" value="new">
	  	Alias:
		<INPUT type="text" name="mgkurzbz" maxlength="15" size="8">
		Stg:
		<SELECT name="stgid">
		    <?php
			$num_rows=pg_numrows($result_stg);
			for ($i=0;$i<$num_rows;$i++)
			{
				$row=pg_fetch_object ($result_stg, $i);
				if ($stgid==$row->id)
					echo "<option value=\"$row->studiengang_kz\" selected>$row->kurzbz</option>";
				else
					echo "<option value=\"$row->studiengang_kz\">$row->kurzbz</option>";
			}
			?>
		</SELECT>
		Beschreibung:
		<INPUT type="text" name="beschreibung" maxlength="50" size="15">
		<INPUT type="submit" name="Abschicken" value="Save">
	</FORM>
	<?php
	}
	else
	{
		echo '<BR><BR>Fuer neue Mail-Verteiler, wenden sie sich bitte an die <a href="mailto:vilesci@technikum-wien.at?subject=Bitte um neuen Mail-Verteiler&body=
			Neuer Verteiler:%20%0DBeischreibung:%20" class="linkblue">Administration</a>';
	}
?>
</body>
</html>
