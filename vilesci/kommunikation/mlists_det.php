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
		$sql_query="INSERT INTO tbl_personmailgrp (uid, mailgrp_kurzbz) VALUES ('".$_POST['personid']."','".$_POST['mailgrpid']."')";
		//echo $sql_query;
		$result=pg_exec($conn, $sql_query);
	}
	if ($type=='del')
	{
		$sql_query="DELETE FROM tbl_personmailgrp WHERE mailgrp_kurzbz='".$_GET['mailgrpid']."' and uid='".$_GET['uid']."'";
		$result=pg_exec($conn, $sql_query);
	}

	$sql_query="SELECT uid, nachname, vornamen, uid FROM tbl_person ORDER BY upper(nachname), vornamen, uid";
	$result_pers=pg_exec($conn, $sql_query);
	if(!$result_pers)
		die (pg_errormessage($conn));
	$sql_query="SELECT nachname, vornamen, tbl_person.uid FROM tbl_personmailgrp join tbl_person using(uid) WHERE mailgrp_kurzbz='$mailgrpid'  ORDER BY upper(nachname), vornamen, tbl_person.uid";
	//echo $sql_query;
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
<A href="mlists_index.php" class="linkblue">&lt;&lt; Back</A><BR>
<BR>
<A href="mailto:<?php echo $bez; ?>@technikum-wien.at" class="linkgreen"><?php echo $bez; ?>@technikum-wien.at</A>
<BR>
Anzahl:
<?php echo $num_rows; ?>
<br>
<br>
<?php
  $sql_query_gen = "SELECT generiert FROM tbl_mailgrp where mailgrp_kurzbz='".$_GET['mailgrpid']."'";
  $result_gen=pg_exec($conn,$sql_query_gen);
  $row_gen = pg_fetch_object($result_gen);

  if($row_gen->generiert!='t')
  {
?>
<FORM name="newpers" method="post" action="mlists_det.php">
  <INPUT type="hidden" name="type" value="new">
  <SELECT name="personid">
    <?php
		$num_rows_pers=pg_numrows($result_pers);
		for ($i=0;$i<$num_rows_pers;$i++)
		{
			$row=pg_fetch_object ($result_pers, $i);
			echo "<option value=\"$row->uid\">$row->nachname $row->vornamen - $row->uid</option>";
		}
		?>
  </SELECT>
  <INPUT type="hidden" name="mailgrpid" value="<?php echo $mailgrpid; ?>">
  <INPUT type="hidden" name="bez" value="<?php echo $bez; ?>">
  <INPUT type="submit" name="Abschicken" value="Hinzuf&uuml;gen">
</FORM>
<?php
  } ?>
<table class='liste'>
<tr class='liste'"><th></th><th>Nachname</th><th>Vornamen</th><th>uid</th></tr>
<?php
	for ($i=0; $i<$num_rows; $i++)
	{


		$nachname=pg_result($erg,$i,"nachname");
		$vornamen=pg_result($erg,$i,"vornamen");
		$uid=pg_result($erg,$i,"uid");

		?>
		<tr class='liste<?php echo $i%2; ?>'>
		<td><a href="mlists_det.php?uid=<?php echo $uid.'&type=del&mailgrpid='.$mailgrpid.'&bez='.$bez; ?>" class="linkblue">Delete</a></td>
		<td><?php echo $nachname; ?></td>
		<td><?php echo $vornamen; ?></td>
		<td><?php echo $uid; ?></td>
		</tr>
		<?php
	}
?>
</table>

</body>
</html>
