<HTML>
<BODY>
<?php
	include('../../config.inc.php');
	$conn=pg_connect(CONN_STRING);

	$field=file($userfile);
	$anz=count($field);
	for ($i=0;$i<$anz;$i++)
	{
		//echo $field[$i].'<br>';
		$endpos=strpos($field[$i],9);
		$lektor=substr($field[$i],0,$endpos);
		//echo $lektor.'<br>';
		$beginpos=$endpos+1;
		$endpos=strpos($field[$i],9,$beginpos);
		$wochentag=substr($field[$i],$beginpos,$endpos-$beginpos);
		//echo $wochentag.'<br>';
		$beginpos=$endpos+1;
		$endpos=strpos($field[$i],9,$beginpos);
		$stunde_id=substr($field[$i],$beginpos,$endpos-$beginpos);
		//echo $stunde_id.'<br>';
		$beginpos=$endpos+1;
		$endpos=strpos($field[$i],9,$beginpos);
		$lehrfach=substr($field[$i],$beginpos,$endpos-$beginpos);
		//echo $lehrfach.'<br>';
		$beginpos=$endpos+1;
		$endpos=strpos($field[$i],9,$beginpos);
		$ort=substr($field[$i],$beginpos,$endpos-$beginpos);
		//echo $ort.'<br>';
		$beginpos=$endpos+1;
		$endpos=strpos($field[$i],9,$beginpos);
		$unr=substr($field[$i],$beginpos,$endpos-$beginpos);
		//echo $unr.'<br>';
		$beginpos=$endpos+1;
		$endpos=strpos($field[$i],9,$beginpos);
		$keineahnung=substr($field[$i],$beginpos,$endpos-$beginpos);
		//echo $keineahnung.'<br>';
		$beginpos=$endpos+1;
		$endpos=strpos($field[$i],9,$beginpos);
		$klassenbez=substr($field[$i],$beginpos,$endpos-$beginpos);
		//echo $klassenbez.'<br>';
		$beginpos=$endpos+1;
		$endpos=strlen($field[$i]);
		$jahreswochen=trim(substr($field[$i],$beginpos,$endpos-$beginpos));
		//echo $jahreswochen.'<br>';

		$sql_query="INSERT INTO untis (lektor,wochentag,stunde,lehrfach,ort,unr,jahreswochen,klassenbez) VALUES ('$lektor','$wochentag','$stunde_id','$lehrfach','$ort','$unr','$jahreswochen','$klassenbez')";
		$result=pg_exec($conn, $sql_query);
		if(!$result)
			die(pg_errormessage().'<BR>'.$i.'<BR>'.$sql_query);
	}
?>
Finished <BR>
<A href="index.html">Zur&uuml;ck</A>
</BODY>
</HTML>