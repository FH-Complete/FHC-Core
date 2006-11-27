<?php

/**
 * Liefert die ID eines Studiensemesters aus der FAS Datenbank
 * @param conn_fas Connection zur FAS Datenbank
 *        conn_vilesci Connection zur Vilesci Datenbank
 *        stsem Studiensemester im Format: 'WS2006', 'SS2006'
 */
function getStudiensemesterIdFromName($conn_fas, $stsem)
{
	$qry = 'SELECT studiensemester_pk from studiensemester where art=';
	if(substr($stsem,0,2)=='WS')
		$qry .='1';
	else
		$qry .='2';
	$qry .= ' AND jahr=';
	$qry .= substr($stsem,2,4);
	$stsem_id=0;

	if($result=@pg_query($conn_fas,$qry))
		if($row=pg_fetch_object($result))
			$stsem_id=$row->studiensemester_pk;
	else
		echo pg_last_error($conn_fas);
	return $stsem_id;
}


?>