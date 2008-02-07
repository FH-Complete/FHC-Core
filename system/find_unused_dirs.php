<?php
// **************************************
// Syncronisiert alle Noten
// FAS -> VILESCI
// setzt vorraus: - tbl_sprache
//                - tbl_studiengang
// einschraenkung auf studiengang_fk per http-get:
// sync_fas_vilesci_note_stg.php?stg_von=x&stg_bis=y
// **************************************
	require_once('../cis/config.inc.php');
	//$adress='fas_sync@technikum-wien.at';


	$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");



	$headtext='';
	$text='';


	$lv_arr = array();				//array (lehrveranstaltung_fk->lehrveranstaltung_id)


	$qry = "select * from lehre.tbl_lehrveranstaltung";
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			$lv_arr[$row->studiengang_kz][$row->semester][$row->lehreverzeichnis] = 1;
		}
	}

	$text .= "";

	//query bauen: falls http-get-einschraenkungen fuer student_fk
	//sync_fas_vilesci_note.php?student_fk_von=x&student_fk_bis=y

	/*	
	$getstr = "";
	$sqlstr = "SELECT DISTINCT student_fk FROM note";
	if (isset($_REQUEST["student_fk_von"]))
		$getstr .= " student_fk >='".$_REQUEST["student_fk_von"]."'";
	if (isset($_REQUEST["student_fk_bis"]))
	{
		if ($getstr != "")
			$getstr .= " AND";

		$getstr .= " student_fk <='".$_REQUEST["student_fk_bis"]."'";
	}
	if ($getstr != "")
		$getstr = " WHERE ".$getstr;

	$sqlstr = $sqlstr.$getstr." order by student_fk";
	*/
	$getstr = "";	
	$sqlstr = "select * from tbl_studiengang";
	if (isset($_REQUEST["stg_von"]))		
		$getstr .= " studiengang_kz >= '".$_REQUEST["stg_von"]."'";
	if (isset($_REQUEST["stg_bis"]))
	{
		if ($getstr != "")
			$getstr .= " AND";		
		$getstr .= " studiengang_kz <= '".$_REQUEST["stg_bis"]."'";
	}
	if ($getstr != "")
		$getstr = " WHERE".$getstr;
		
	$sqlstr = $sqlstr.$getstr." order by kurzbzlang";
	//echo $sqlstr;
	//$sqlstr ="select count(*) from note, student where note.student_fk = student.student_pk and student.studiengang_fk = '16' and (note.student_fk=12217 or note.student_fk = 10704);";
	if($result = pg_query($conn, $sqlstr))
	{

		while($row = pg_fetch_object($result))
		{			$text .= "<hr><b>".$row->kurzbzlang."</b><br>";
			for ($i=1; $i <= $row->max_semester; $i++)
			{				
				$dir = "/documents/documents/".strtolower($row->kurzbzlang)."/".$i."/";
				$text .= "*** ".$i." ***<br>";
				if (is_dir($dir))
				{		
					$files = scandir($dir);
					foreach ($files as $f)
					{
						if (is_dir($dir.$f) && $f != "." && $f != "..")
						{
							 if ($row->studiengang_kz == 999) 
                                    $text .= $f."<br>";
                            else if (!key_exists($f, $lv_arr[$row->studiengang_kz][$i]))
                            {
                                    //echo $row->studiengang_kz."/".$i."<br>";
                                    $text .= $f."<br>";
                            }
							
						}
					}
				}
			}
		}
	}
	/*
	if (mail($adress,"FAS - Vilesci (Noten/Pruefungen)",$headtext."\n\n<html><body>".$text."</body></html>","From: vilesci@technikum-wien.at\nContent-Type: text/html\n"))
		$sendmail=true;
	else
		$sendmail=false;
	}
	*/
	

?>

<html>
<head>
	<title>Unused Dirs</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
/*
if ($sendmail)
	echo 'Mail wurde verschickt an '.$adress.'!<br>';
else
	echo "Mail konnte nicht verschickt werden!<br>";
*/
echo $headtext;
echo "<br><br>";
echo $text;

?>
</body>
</html>