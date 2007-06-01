<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<HTML>

<HEAD>
	<title>Zutrittskarten</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="../../skin/cis.css" rel="stylesheet" type="text/css">
</HEAD>

<BODY>

<h1>Zutrittskarten Import</h1>
<?php
	require('../../vilesci/config.inc.php');
	$conn=pg_pconnect(CONN_STRING);
	//var_dump($_FILES);
	if(isset($_FILES['datei']['tmp_name']))
	{
		//Extension herausfiltern
    	$ext = explode('.',$_FILES['datei']['name']);
        $ext = strtolower($ext[count($ext)-1]);

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
				//echo $lektor.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$name=substr($fp[$i],$beginpos,$endpos-$beginpos);
				//echo $wochentag.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$firstname=substr($fp[$i],$beginpos,$endpos-$beginpos);
				//echo $stunde_id.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$group=substr($fp[$i],$beginpos,$endpos-$beginpos);
				//echo $lehrfach.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$logaswnumber=substr($fp[$i],$beginpos,$endpos-$beginpos);
				//echo $ort.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$unr=substr($fp[$i],$beginpos,$endpos-$beginpos);
				//echo $unr.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$physaswnumber=substr($fp[$i],$beginpos,$endpos-$beginpos);
				//echo $keineahnung.'<br>';
				$beginpos=$endpos+1;
				$endpos=strpos($fp[$i],9,$beginpos);
				$validstart=substr($fp[$i],$beginpos,$endpos-$beginpos);
				//echo $klassenbez.'<br>';
				$beginpos=$endpos+1;
				$endpos=strlen($fp[$i]);
				$validend=trim(substr($fp[$i],$beginpos,$endpos-$beginpos));
				//echo $jahreswochen.'<br>';
				$beginpos=$endpos+1;
				$endpos=strlen($fp[$i]);
				$text1=trim(substr($fp[$i],$beginpos,$endpos-$beginpos));
				//echo $text1.'<br>';
				$beginpos=$endpos+1;
				$endpos=strlen($fp[$i]);
				$text2=trim(substr($fp[$i],$beginpos,$endpos-$beginpos));
				//echo $text2.'<br>';
				$beginpos=$endpos+1;
				$endpos=strlen($fp[$i]);
				$text3=trim(substr($fp[$i],$beginpos,$endpos-$beginpos));
				//echo $text3.'<br>';
				$beginpos=$endpos+1;
				$endpos=strlen($fp[$i]);
				$text4=trim(substr($fp[$i],$beginpos,$endpos-$beginpos));
				//echo $text4.'<br>';
				$beginpos=$endpos+1;
				$endpos=strlen($fp[$i]);
				$text5=trim(substr($fp[$i],$beginpos,$endpos-$beginpos));
				//echo $text5.'<br>';
				$beginpos=$endpos+1;
				$endpos=strlen($fp[$i]);
				$text6=trim(substr($fp[$i],$beginpos,$endpos-$beginpos));
				//echo $text6.'<br>';
				$beginpos=$endpos+1;
				$endpos=strlen($fp[$i]);
				$pin=trim(substr($fp[$i],$beginpos,$endpos-$beginpos));
				//echo $pin.'<br>';
					$sql_query="INSERT INTO sync.zutrittskarte (key,name,firstname,group,logaswnumber,physaswnumber,validstart,validend,text1,text2,text3,text4,text5,text6,pin)
					VALUES ('$key','$name','$firstname','$group','$logaswnumber','$physaswnumber','$validstart','$validend','$text1','$text2','$text3','$text4','$text5','$text6','$pin')";
			//	$result=pg_exec($conn, $sql_query);
				echo $sql_query;
			//	if(!$result)
			//		die(pg_errormessage().'<BR>'.$i.'<BR>'.$sql_query);
			}
		}
		else
			echo "<b>File ist keine gueltige Textdatei</b><br />";
	}

?>
Datenimport abgeschlossen!
</BODY>
</html>
