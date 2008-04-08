<?php
/* Copyright (C) 2008 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 */
/*
Finden aller aktiven Studenten, die das Studium noch nicht beendet aber trotzdem keinen aktuellen Status haben
*/
require_once('../config.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/functions.inc.php');

if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");

function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

$studiensemester=new studiensemester($conn);
$ssem=$studiensemester->getaktorNext();
$alle=0;
$abab=0;
$now=0;
$hit=0;

echo "Semester: ".$ssem."<br>";
$qry="SELECT DISTINCT tbl_prestudent.prestudent_id, tbl_prestudent.studiengang_kz FROM public.tbl_person 
	JOIN public.tbl_prestudent USING(person_id) 
	JOIN public.tbl_student ON(tbl_prestudent.prestudent_id=tbl_student.prestudent_id)
	JOIN public.tbl_prestudentrolle ON(tbl_prestudent.prestudent_id=tbl_prestudentrolle.prestudent_id)  
	WHERE tbl_person.aktiv AND rolle_kurzbz!='Incoming' ORDER by tbl_prestudent.studiengang_kz;";

if ($result=pg_query($conn, $qry))
{
	while($row=pg_fetch_object($result))
	{
		$alle=pg_num_rows($result);
		$qry_chk="SELECT prestudent_id FROM public.tbl_prestudentrolle WHERE (rolle_kurzbz='Abgewiesener' OR rolle_kurzbz='Abbrecher' OR rolle_kurzbz='Absolvent') AND prestudent_id='".$row->prestudent_id."';";
		if ($result_chk=pg_query($conn, $qry_chk))
		{
			if(pg_num_rows($result_chk)==0)
			{
				$qry_chk2="SELECT prestudent_id FROM public.tbl_prestudentrolle WHERE studiensemester_kurzbz='".$ssem."' AND prestudent_id='".$row->prestudent_id."';";
				if ($result_chk2=pg_query($conn, $qry_chk2))
				{
					if(pg_num_rows($result_chk2)==0)
					{
						$qry_erg="SELECT nachname,vorname, tbl_prestudent.studiengang_kz, matrikelnr FROM public.tbl_person JOIN public.tbl_prestudent USING(person_id) JOIN public.tbl_student ON(tbl_prestudent.prestudent_id=tbl_student.prestudent_id) WHERE tbl_prestudent.prestudent_id='".$row->prestudent_id."';";
						if ($result_erg=pg_query($conn, $qry_erg))
						{
							if($row_erg=pg_fetch_object($result_erg))
							{
								echo "<br>".sprintf("[%04s]\n", $row_erg->studiengang_kz).", ".$row_erg->matrikelnr.", ".$row_erg->nachname.", ".$row_erg->vorname;
								$hit++;
							}
						}
					}
					else 
					{
						$now++;
					}
				}
			}
			else 
			{
				$abab++;
			}
		}
	}
}
echo "<br><b>Ergebnis: </b><br>Lost Souls: ".$hit."<br>in akt. Sem.: ".$now."<br>fertig: ".$abab."<br>gesamt: ".$alle;