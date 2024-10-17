<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<HTML>

<HEAD>
	<title>Zutrittskarten</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/cis.css" rel="stylesheet" type="text/css">
</HEAD>

<BODY>

<h1>Zutrittskarten Import</h1>
<?php
		require_once('../../../../config/vilesci.config.inc.php');
		require_once('../../../../include/basis_db.class.php');
		if (!$db = new basis_db())
			die('Es konnte keine Verbindung zum Server aufgebaut werden.');
		
	//Tabelle leeren
	$sql_query="DELETE FROM sync.tbl_zutrittskarte;";
	$result=$db->db_query($sql_query);
	if(!$result)
			die($db->db_last_error().'<BR>'.$i.'<BR>'.$sql_query);
	if(isset($_FILES['datei']['tmp_name']))
	{
		//Extension herausfiltern
   	$ext = explode('.',$_FILES['datei']['name']);
    $ext = mb_strtolower($ext[count($ext)-1]);
    //--check if csv or txt
    if ($ext=='csv' || $ext=='txt')
    {
			$filename = $_FILES['datei']['tmp_name'];
			//File oeffnen
			$fp = file($filename);
			$anz=count($fp);
			for ($i=1;$i<$anz;$i++)
			{
				echo $fp[$i].'<br>';
				$endpos=strpos($fp[$i],9);
				$key=substr($fp[$i],0,$endpos);
				//echo $key.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$name=substr($fp[$i],$beginpos,$endpos-$beginpos);
				//echo $name.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$firstname=substr($fp[$i],$beginpos,$endpos-$beginpos);
				//echo $firstname.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$groupe=substr($fp[$i],$beginpos,$endpos-$beginpos);
				//echo $groupe.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$logaswnumber=substr($fp[$i],$beginpos,$endpos-$beginpos);
				//echo $logaswnumber.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$physaswnumber=substr($fp[$i],$beginpos,$endpos-$beginpos);
				//echo $physaswnumber.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$validstart=substr($fp[$i],$beginpos,$endpos-$beginpos);
				//echo $validstart.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$validend=trim(substr($fp[$i],$beginpos,$endpos-$beginpos));
				//echo $validend.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$text1=trim(substr($fp[$i],$beginpos,$endpos-$beginpos));
				//echo $text1.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$text2=trim(substr($fp[$i],$beginpos,$endpos-$beginpos));
				//echo $text2.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$text3=trim(substr($fp[$i],$beginpos,$endpos-$beginpos));
				//echo $text3.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$text4=trim(substr($fp[$i],$beginpos,$endpos-$beginpos));
				//echo $text4.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$text5=trim(substr($fp[$i],$beginpos,$endpos-$beginpos));
				//echo $text5.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$text6=trim(substr($fp[$i],$beginpos,$endpos-$beginpos));
				//echo $text6.'<br>';
				$beginpos=$endpos+1;
				$endpos=strlen($fp[$i]);
				$pin=trim(substr($fp[$i],$beginpos,$endpos-$beginpos));
				//echo $pin.'<br>';
				$sql_query="INSERT INTO sync.tbl_zutrittskarte (key,name,firstname,groupe,logaswnumber,physaswnumber,validstart,validend,text1,text2,text3,text4,text5,text6,pin)
					VALUES ('$key','$name','$firstname','$groupe','$logaswnumber','$physaswnumber',";
				if ($validstart=='')
					$sql_query.="NULL,";
				else
					$sql_query.="'$validstart',";
				if ($validend=='')
					$sql_query.="NULL,";
				else
					$sql_query.="'$validend',";
				$sql_query.="'$text1','$text2','$text3','$text4','$text5','$text6','$pin')";
				$result=$db->db_query($sql_query);
				//echo $sql_query;
				if(!$result)
						die($db->db_last_error().'<BR>'.$i.'<BR>'.$sql_query);
			}
		}
		else
			echo "<h3>File ist keine gueltige Textdatei</h3>";
	}

?>
Datenimport abgeschlossen!
</BODY>
</html>
