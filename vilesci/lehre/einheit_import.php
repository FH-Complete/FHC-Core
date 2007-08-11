<HTML>
<BODY>
<?php
	include('../config.inc.php');
	$conn=pg_connect($conn_string);
	
	$field=file($userfile);
	$anz=count($field);
	for ($i=0;$i<$anz;$i++)
	{
		//echo $field[$i].'<br>';
		$enduid=strpos($field[$i],'"',1);
		//echo $enduid.'<br>';
		$uid=substr($field[$i],1,$enduid-1);
		//echo $uid.'<br>';
		$begineinh=strpos($field[$i],'"',$enduid+2)+1;
		//echo $begineinh.'<br>';
		$endeinh=strpos($field[$i],'"',$begineinh);
		//echo $endeinh.'<br>';
		$einheit=substr($field[$i],$begineinh,$endeinh-$begineinh);
		//echo $einheit.'<br>';
		
		$sql_query="SELECT * FROM einheit WHERE kurzbz='$einheit'";
		$result=pg_exec($conn, $sql_query);
		$rows=pg_numrows($result);
		if ($rows==0)
		{
			$result_ins=pg_exec($conn, "INSERT INTO einheit (kurzbz) VALUES ('$einheit')");
			if(!$result_ins) 
				error(pg_errormessage());
			$sql_query="SELECT * FROM einheit WHERE kurzbz='$einheit'";
			$result=pg_exec($conn, $sql_query);
		}
		$row=pg_fetch_object($result,0);
		$einheit_id=$row->id;
		
		$sql_query="SELECT * FROM student WHERE uid='$uid'";
		//echo $sql_query.'<BR>';
		$result=pg_exec($conn, $sql_query);
		if(!$result) 
				error(pg_errormessage());
		$rows=pg_numrows($result);
		if ($rows==0)
			die("Student $uid not found!");
		$row=pg_fetch_object($result,0);
		$student_id=$row->id;
		
		$sql_query="SELECT * FROM einheitstudent WHERE einheit_id=$einheit_id AND student_id=$student_id";
		$result=pg_exec($conn, $sql_query);
		$rows=pg_numrows($result);
		if ($rows==0)
		{
			$result_ins=pg_exec($conn, "INSERT INTO einheitstudent (einheit_id, student_id) VALUES ($einheit_id, $student_id)");
			if(!$result_ins) 
				error(pg_errormessage());
			$result=pg_exec($conn, $sql_query);
		}		
	}
?>
Finished <BR>
<A href="einheit_menu.php">Zur&uuml;ck</A> 
</BODY>
</HTML>