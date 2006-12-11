<?php
	include('../config.inc.php');
	include('../../include/functions.inc.php');
	// Verbindung aufbauen
	$conn_string = "host=calgone.technikum-wien.at port=5432 dbname=vilesci user=pam password=bla";
	$conn=pg_connect(CONN_STRING) or die ("Unable to connect to SQL-Server");

	$start=time();
	$statements=0;
	$reihen=0;
	$bytes=0;
	$sql_query="SELECT id, kurzbz FROM studiengang ORDER BY kurzbz";
	$result_stg=pg_exec($conn, $sql_query);
	if(!$result_stg)
		die ("studiengang not found!");
	$num_rows=pg_numrows($result_stg);
	for ($i=0;$i<$num_rows;$i++)
	{
		$row=pg_fetch_object ($result_stg, $i);
		for ($sem=1;$sem<9;$sem+=2)
		{
			$datum=getdate();
			for ($k=0;$k<15;$k++)
			{
				$datum=montag($datum);
				$datum_begin=$datum;
				$datum_end=jump_week($datum_begin, 1);
				$datum_begin=$datum_begin[year]."-".$datum_begin[mon]."-".$datum_begin[mday];
				$datum_end=$datum_end[year]."-".$datum_end[mon]."-".$datum_end[mday];
				$sql_query='SELECT unr, datum, stunde_id, ort_id, ortkurzbz, lektorkurzbz, einheitkurzbz FROM vweinheitenplan';
				$sql_query.=" WHERE datum>='$datum_begin' AND datum<'$datum_end'";
				$sql_query.=' AND studiengang_id='.$row->id.' AND semester='.$sem;
				$sql_query.=' ORDER BY  datum  ASC, stunde_id  ASC';
				//echo $sql_query.'<br>';
				//Datenbankabfrage
				if(!($stpl_tbl=pg_exec($conn, $sql_query)))
					die(pg_last_error($this->conn));
				$reihen+=pg_numrows($stpl_tbl);
				//$bytes+=strlen($stpl_tbl);

				$sql_query='SELECT unr, datum, stunde_id, ortkurzbz, ort_id, lehrfachkurzbz, farbe, lektorkurzbz, lektor_id,';
				$sql_query.=' studiengang_id, stgkurzbz, semester, verband, gruppe, lektor_id FROM vwstundenplan';
				$sql_query.=" WHERE datum>='$datum_begin' AND datum<'$datum_end'";
				$sql_query.=' AND studiengang_id='.$row->id.' AND semester='.$sem;
				$sql_query.=' ORDER BY  datum  ASC, stunde_id  ASC';
				//echo $sql_query."<br>";
				//Datenbankabfrage
				if(!($stpl_tbl=pg_exec($conn, $sql_query)))
					die(pg_last_error($this->conn));
				$reihen+=pg_numrows($stpl_tbl);
				//$bytes+=strlen($stpl_tbl);

				$datum=jump_week($datum, 1);
				$statements++;
			}
		}
	}
	$ende=time();
	$zeit=$ende-$start;

?>
<html>
<head>
<title>Performance Test</title>
<link href="../../skin/cis.css" rel="stylesheet" type="text/css">
</head>

<BODY>
	<H1> Stundenplan Performance Test</H1>
	Anzahl der Statements: <?php echo $statements; ?><BR>
	Anzahl der Datens?tze: <?php echo $reihen; ?><BR>
	<!--Datentransfer in Byte: <?php echo $bytes; ?><BR>-->
	Vergangene Zeit [sec]: <?php echo $zeit; ?><BR>

<HR>
<P class=little>
    Erstellt am 24.8.2001 von <A href="mailto:pam@technikum-wien.at">Christian Paminger</A>.<BR>
    Letzte &Auml;nderung am 11.9.2004 von <A href="mailto:pam@technikum-wien.at">Christian Paminger</A>.
</P>
</body>
</html>