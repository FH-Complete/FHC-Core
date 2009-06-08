<?php
	include('config.inc.php');
	if(!($dbh = @mysql_connect($dbhost, $dbuser, $dbpasswd)))
	{
        die("Error: Cannot connect to database $dbhost");
	}
	mysql_select_db($dbname);
	switch ($stgid)
	{
		//BEL
		case 1:	$reihung[1]=1;	//Algebra
				$reihung[2]=3;	//Schaetzen
				$reihung[3]=2;	//Funktionen
				$reihung[4]=4;	//Schlussfolgerungen
				$reihung[6]=0;	//Grundlagen der Elektrotechnik
				$reihung[7]=5;	//Persönlichkeit
				$reihung[8]=6;	//Englisch
				$reihung[10]=0;	//Physik
				break;
		//BIF
		case 2:	$reihung[1]=1;	//Algebra
				$reihung[2]=3;	//Schaetzen
				$reihung[3]=2;	//Funktionen
				$reihung[4]=4;	//Schlussfolgerungen
				$reihung[6]=0;	//Grundlagen der Elektrotechnik
				$reihung[7]=5;	//Persönlichkeit
				$reihung[8]=6;	//Englisch
				$reihung[10]=0;	//Physik
				break;
		//BEW
		case 3:	$reihung[1]=1;	//Algebra
			$reihung[2]=3;	//Schaetzen
			$reihung[3]=2;	//Funktionen
			$reihung[4]=4;	//Schlussfolgerungen
			$reihung[6]=0;	//Grundlagen der Elektrotechnik
			$reihung[7]=0;	//Persönlichkeit
			$reihung[8]=5;	//Englisch
			$reihung[10]=0;	//Physik
			break;
		//BICSS
		case 4:	$reihung[1]=1;	//Algebra
				$reihung[2]=3;	//Schaetzen
				$reihung[3]=2;	//Funktionen
				$reihung[4]=4;	//Schlussfolgerungen
				$reihung[6]=0;	//Grundlagen der Elektrotechnik
				$reihung[7]=5;	//Persönlichkeit
				$reihung[8]=6;	//Englisch
				$reihung[10]=0;	//Physik
				break;
		//BSET
		case 5:	$reihung[1]=1;	//Algebra
				$reihung[2]=3;	//Schaetzen
				$reihung[3]=2;	//Funktionen
				$reihung[4]=4;	//Schlussfolgerungen
				$reihung[6]=0;	//Grundlagen der Elektrotechnik
				$reihung[7]=6;	//Persönlichkeit
				$reihung[8]=5;	//Englisch
				$reihung[10]=7;	//Physik
				break;
		//BWI
		case 6:	$reihung[1]=1;	//Algebra
				$reihung[2]=3;	//Schaetzen
				$reihung[3]=2;	//Funktionen
				$reihung[4]=4;	//Schlussfolgerungen
				$reihung[6]=0;	//Grundlagen der Elektrotechnik
				$reihung[7]=5;	//Persönlichkeit
				$reihung[8]=6;	//Englisch
				$reihung[10]=0;	//Physik
				break;
		//BITS
		case 7:	$reihung[1]=1;	//Algebra
				$reihung[2]=3;	//Schaetzen
				$reihung[3]=2;	//Funktionen
				$reihung[4]=4;	//Schlussfolgerungen
				$reihung[6]=0;	//Grundlagen der Elektrotechnik
				$reihung[7]=5;	//Persönlichkeit
				$reihung[8]=6;	//Englisch
				$reihung[10]=0;	//Physik
				break;
		//BBME
		case 8:	$reihung[1]=1;	//Algebra
				$reihung[2]=3;	//Schaetzen
				$reihung[3]=2;	//Funktionen
				$reihung[4]=4;	//Schlussfolgerungen
				$reihung[6]=0;	//Grundlagen der Elektrotechnik
				$reihung[7]=0;	//Persönlichkeit
				$reihung[8]=5;	//Englisch
				$reihung[10]=6;	//Physik
				break;
		//BMR
		case 9:	$reihung[1]=1;	//Algebra
			$reihung[2]=3;	//Schaetzen
			$reihung[3]=2;	//Funktionen
			$reihung[4]=4;	//Schlussfolgerungen
			$reihung[6]=0;	//Grundlagen der Elektrotechnik
			$reihung[7]=5;	//Persönlichkeit
			$reihung[8]=6;	//Englisch
			$reihung[10]=7;	//Physik
			break;
		//BIWI
		case 10:$reihung[1]=2;	//Algebra
			$reihung[2]=0;	//Schaetzen
			$reihung[3]=0;	//Funktionen
			$reihung[4]=1;	//Schlussfolgerungen
			$reihung[6]=0;	//Grundlagen der Elektrotechnik
			$reihung[7]=0;	//Persönlichkeit
			$reihung[8]=3;	//Englisch
			$reihung[10]=4;	//Physik
			break;
	}
	for ($i=1;$i<11;$i++)
	{
		if (($i==5)||($i==9))
			$i++;
		$query  = "update rt_gruppen set reihung=$reihung[$i] where id=$i";
		//echo $query.'<br>';
		$result = mysql_query($query, $dbh);
	}
	if ($stgid==10 && $susi=='true')
	{
		$query="UPDATE rt_gruppen SET zeit=30, faktor='-0.50', bezeichnung='Algebra' WHERE id=1;";
		$result = mysql_query($query, $dbh);
		$query="UPDATE rt_gruppen SET zeit=10, faktor='-0.25', bezeichnung='Schaetzen' WHERE id=2;";
		$result = mysql_query($query, $dbh);
		$query="UPDATE rt_gruppen SET zeit=15, faktor='-0.50', bezeichnung='Funktionen' WHERE id=3;";
		$result = mysql_query($query, $dbh);
		$query="UPDATE rt_gruppen SET zeit=20, faktor='-0.15', bezeichnung='Schlussfolgerungen' WHERE id=4;";
		$result = mysql_query($query, $dbh);
		$query="UPDATE rt_gruppen SET zeit=25, faktor='-0.50', bezeichnung='Grundlagen' WHERE id=6;";
		$result = mysql_query($query, $dbh);
		$query="UPDATE rt_gruppen SET zeit=45, faktor='-0.00', bezeichnung='Persoenlichkeit' WHERE id=7;";
		$result = mysql_query($query, $dbh);
		$query="UPDATE rt_gruppen SET zeit=40, faktor='-0.25', bezeichnung='Englisch' WHERE id=8;";
		$result = mysql_query($query, $dbh);
		$query="UPDATE rt_gruppen SET zeit=30, faktor='-0.25', bezeichnung='Physik-Technik' WHERE id=10;";
		$result = mysql_query($query, $dbh);
	}
	else
	{
		$query="UPDATE rt_gruppen SET zeit=13, faktor='-0.50', bezeichnung='Algebra' WHERE id=1;";
		$result = mysql_query($query, $dbh);
		$query="UPDATE rt_gruppen SET zeit=10, faktor='-0.25', bezeichnung='Schaetzen' WHERE id=2;";
		$result = mysql_query($query, $dbh);
		$query="UPDATE rt_gruppen SET zeit=15, faktor='-0.50', bezeichnung='Funktionen' WHERE id=3;";
		$result = mysql_query($query, $dbh);
		$query="UPDATE rt_gruppen SET zeit=20, faktor='-0.15', bezeichnung='Schlussfolgerungen' WHERE id=4;";
		$result = mysql_query($query, $dbh);
		$query="UPDATE rt_gruppen SET zeit=25, faktor='-0.50', bezeichnung='Grundlagen' WHERE id=6;";
		$result = mysql_query($query, $dbh);
		$query="UPDATE rt_gruppen SET zeit=45, faktor='-0.00', bezeichnung='Persoenlichkeit' WHERE id=7;";
		$result = mysql_query($query, $dbh);
		$query="UPDATE rt_gruppen SET zeit=20, faktor='-0.25', bezeichnung='Englisch' WHERE id=8;";
		$result = mysql_query($query, $dbh);
		$query="UPDATE rt_gruppen SET zeit=20, faktor='-0.25', bezeichnung='Physik-Technik' WHERE id=10;";
		$result = mysql_query($query, $dbh);
	}
	echo 'Datenbank wurde initialisiert!';
	mysql_close($dbh);
?>
