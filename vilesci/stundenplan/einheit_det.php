<?php
include('../config.inc.php');
include('../../include/studiengang.class.php');
include('../../include/einheit.class.php');
include('../../include/person.class.php');
include('../../include/student.class.php');

if(!$conn=pg_pconnect(CONN_STRING))
   die("Fehler beim Aufbau der Datenbankconnection");
   
$kurzbz=(isset($_GET['kurzbz'])?$_GET['kurzbz']:$_POST['einheit_id']);

if (isset($_POST['new'])) 
{
	$e=new einheit($conn);
	$e->kurzbz=addslashes($kurzbz);
	$e->addStudent($_POST['student_id']);
	
} else if ($_GET['type']=='delete')
{
	
	$e=new einheit($conn);
	$e->kurzbz=addslashes($kurzbz);
	$e->deleteStudent($_GET['uid']);	
}

?>

<html>
<head>
<title>Einheit Details</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body>
<H1>Einheit <?php echo $kurzbz ?></H1>

<?php

$e=new einheit($conn);
$e->kurzbz=addslashes($kurzbz);
$studenten=$e->getStudenten();

$student=new student($conn);
$studentenAlle=$student->getAll();

?>

<FORM name="newpers" method="post" action="einheit_det.php">
  <INPUT type="hidden" name="type" value="new">
    
  <SELECT name="student_id">
    <?php
		$num_rows=count($studentenAlle);
		for ($i=0;$i<$num_rows;$i++)
		{			
			echo "<option value=\"".$studentenAlle[$i]->uid."\">".$studentenAlle[$i]->nachname." ".$studentenAlle[$i]->vornamen." - ".$studentenAlle[$i]->uid."</option>";
		}
		?>
  </SELECT>
  
  <INPUT type="hidden" name="einheit_id" value="<?php echo $kurzbz; ?>">
  <INPUT type="submit" name="new" value="Hinzuf&uuml;gen">
</FORM>
<HR>
<table class="liste">
<tr class="liste"><th>UID</th><th>Vornamen</th><th>Nachname</th></tr>

<?php



	$num_rows=count($studenten);
	for ($j=0; $j<$num_rows;$j++)
	{		

		echo "<tr class='liste".($j%2)."'>";
	    echo "<td>".$studenten[$j]->uid."</td>";
		echo "<td>".$studenten[$j]->vornamen."</td>";
		echo "<td>".$studenten[$j]->nachname."</td>";
		echo "<td><a href=\"einheit_det.php?uid=".$studenten[$j]->uid."&type=delete&kurzbz=$kurzbz\">Delete</a></td>";
	    echo "</tr>\n";
	}

?>
</table>
</body>
</html>