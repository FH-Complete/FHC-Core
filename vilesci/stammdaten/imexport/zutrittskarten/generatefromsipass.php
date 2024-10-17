<?php
/* Copyright (C) 2009 FH Technikum-Wien
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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 *			Alexander Nimmervoll	< nimm@technikum-wien.at >
 */
/**
 * Generiert ein Updatefile fuer das Zutrittskartensystem
 */
require_once('../../../../config/vilesci.config.inc.php');
require_once('../../../../include/basis_db.class.php');
if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$sipass=array();
$i=0;
$k=0;
$key_nummer=0;
$update=false;
$custom=array(array());
$error=false;
$fausgabe='<table>';

// zugriff auf mssql-datenbank
if (!$conn_ext=mssql_connect (SIPASS_DB_SERVER, SIPASS_DB_USER, SIPASS_DB_PASSWD))
	die('Fehler beim Verbindungsaufbau!');
mssql_select_db(SIPASS_DB_DB, $conn_ext);

//letzte Nummer
$sql_query="SELECT max(asco.employee.reference) AS last_keynr FROM asco.employee;";
//echo $sql_query;
if(!$result=mssql_query($sql_query,$conn_ext))
	die(mssql_get_last_message().'<BR>'.$sql_query);
if ($row=mssql_fetch_object($result))
	$key_nummer=$row->last_keynr+1;
else
	die('Letzte Nummer konnte nicht eruiert werden!');

//einlesen der custom. daten von sipass
$qry="SELECT * FROM asco.employee_custom_data";
if($result = mssql_query($qry,$conn_ext))
{
	while($row=mssql_fetch_object($result))
	{
		$custom[$row->emp_id][$row->field_id]=$row->char_value;
	}
}
else 
{
	die("Einlesen der SiPass-custom-data fehlgeschlagen!");
}

$qry="SELECT * FROM asco.employee LEFT OUTER JOIN asco.access_groups ON (asco.employee.acc_grp_id=asco.access_groups.acc_grp_id)";
if($result_ext = mssql_query($qry,$conn_ext))
{
	while($row=mssql_fetch_object($result_ext))
	{	

		//if ((int)$row->card_no==2147483647)  print "######".$row->emp_id." ####".$row->card_no."\n";
		$sipass[$i]->command='';
		$sipass[$i]->reference=$row->reference;
		$sipass[$i]->last_name=$row->last_name;
		$sipass[$i]->first_name=$row->first_name;
		$sipass[$i]->card_no=$row->card_no;
		$sipass[$i]->start_date=date('d.m.Y',strtotime($row->start_date));
		$sipass[$i]->end_date=date('d.m.Y',strtotime($row->end_date));
		$sipass[$i]->acc_grp_name=$row->acc_grp_name;
		if(isset($custom[$row->emp_id][7]))
		{
			$sipass[$i]->uid=$custom[$row->emp_id][7];   //UID
		}
		else
		{
			$sipass[$i]->uid="";
		}
		if(isset($custom[$row->emp_id][8]))
		{
			$sipass[$i]->matrikelnr=$custom[$row->emp_id][8];   //Matrikelnr.
		}
		else
		{
			$sipass[$i]->matrikelnr="";
		}
		/*if(isset($custom[$row->emp_id][9]))
		{
			$sipass[$i][9]=$custom[$row->emp_id][9];   //Stg./Verwaltung
		}
		else
		{
			$sipass[$i][9]="";
		}*/
		$i++;
	}
}
else 
{
	die("SiPass-Abfrage fehlgeschlagen!");
}

$ldap_host="pdc1.technikum-wien.at";
$ldap_port=389;
$ldap_basedn="ou=People,dc=technikum-wien,dc=at";


$ldap_conn = ldap_connect("pdc1.technikum-wien.at",389);
ldap_set_option($ldap_conn,LDAP_OPT_PROTOCOL_VERSION,3);
$ldap_result = ldap_bind($ldap_conn);

$ldap_search="(".LDAP_CARD_NUMBER."=*)";
$ldap_result=ldap_search($ldap_conn, $ldap_basedn, $ldap_search);

for ($ldapentry=ldap_first_entry($ldap_conn,$ldap_result); $ldapentry!=false; $ldapentry=ldap_next_entry($ldap_conn,$ldapentry))
{
     $uids=ldap_get_values($ldap_conn,$ldapentry,"uid");
     $uid=$uids[0];

     $sns=ldap_get_values($ldap_conn,$ldapentry,"sn");
     $sn=$sns[0];

     $givennames=ldap_get_values($ldap_conn,$ldapentry,"givenname");
     $givenname=$givennames[0];

     $gids=ldap_get_values($ldap_conn,$ldapentry,"gidnumber");
     $gid=$gids[0];

     $emplnrs=ldap_get_attributes($ldap_conn,$ldapentry);
     if (isset($emplnrs["employeenumber"]))
     {
        $matrikelnummer=ldap_get_values($ldap_conn,$ldapentry,"employeenumber");
		$matrikelnr=$matrikelnummer[0];
     }
     else 
     {
		$matrikelnr='';
     }
    
     $ous=@ldap_get_values($ldap_conn,$ldapentry,"ou");
     $stg_kurzbz="";
     if ($ous)
     {
        for ($k=0;$k<$ous["count"];$k++)
        {
			if (strlen($ous[$k])==3) 
				$stg_kurzbz=$ous[$k];
        }
     }

     $ldapnumbers=ldap_get_values($ldap_conn,$ldapentry,LDAP_CARD_NUMBER);

     for ($n=0; $n < $ldapnumbers["count"]; $n++)
     {
		$update=false;
		$cardnumber=preg_replace('/^0*/','',$ldapnumbers[$n]);

		//überprüfen, ob bereits vorhanden
		for($j=0;$j<$i;$j++)
		{
			if($sipass[$j]->card_no==$cardnumber)
			{
				$upd=FALSE;
				if($sipass[$j]->last_name!=trim($sn))
				{
					$sipass[$j]->last_name_old=$sipass[$j]->last_name;
					$sipass[$j]->last_name=trim($sn);
					$sipass[$j]->update.=' last_name';
					$upd=TRUE;
				}
				if($sipass[$j]->first_name!=trim($givenname))
				{
					$sipass[$j]->first_name_old=$sipass[$j]->first_name;
					$sipass[$j]->first_name=trim($givenname);
					$sipass[$j]->update.=' first_name';
					$upd=TRUE;
				}

				/*
				if($sipass[$j]->start_date!=date('d.m.Y',strtotime($row->tag.'.'.$row->monat.'.'.$row->jahr)))
				{
					$sipass[$j]->start_date=date('d.m.Y',strtotime($row->tag.'.'.$row->monat.'.'.$row->jahr));
					$sipass[$j]->update.=' start_date';
					$upd=TRUE;
				}
				if($sipass[$j]->end_date!=date('d.m.Y',strtotime($row->tag.'.'.$row->monat.'.'.($row->jahr+5))))
				{
					$sipass[$j]->end_date=date('d.m.Y',strtotime($row->tag.'.'.$row->monat.'.'.($row->jahr+5)));
					$sipass[$j]->update.=' end_date';
					$upd=TRUE;
				}
				*/
				if($sipass[$j]->uid!=trim($uid))
				{
					$sipass[$j]->uid=trim($uid);
					$sipass[$j]->update.=' uid';
					$upd=TRUE;
				}
				if(trim($matrikelnr)!='' && $sipass[$j]->matrikelnr!=trim($matrikelnr))
				{
					$sipass[$j]->matrikelnr=trim($matrikelnr);
					$sipass[$j]->update.=' matrikelnr';
					$upd=TRUE;
				}
				if($gid==101 || $gid==120)
				{
					if($sipass[$j]->acc_grp_name!="Verwaltung" && substr($sipass[$j]->acc_grp_name,0,1)!='#')
					{
						$sipass[$j]->acc_grp_name="Verwaltung";
						$sipass[$j]->update.=' acc_grp_name';
						$upd=TRUE;
					}
				}
				else
				{
					if($sipass[$j]->acc_grp_name!=trim($stg_kurzbz) && substr($sipass[$j]->acc_grp_name,0,1)!='#')
					{
						$sipass[$j]->acc_grp_name_old=$sipass[$j]->acc_grp_name;
						$sipass[$j]->acc_grp_name=trim($stg_kurzbz);
						$sipass[$j]->update.=' acc_grp_name';
						$upd=TRUE;
					}
				}
				// Update nur wenn Gruppe nicht mit # beginnt
				if($upd && substr($sipass[$j]->acc_grp_name,0,1)!='#')
				{
					$sipass[$j]->command="U";
				}
				else
				{
					$sipass[$j]->command="V"; //kein update, wird auch nicht gelöscht
				}
				$update=true;
				break;
			}
		}
		if(!$update)
		{
			//wenn nicht gefunden, dann append
			if($sn!='' && $givenname!='' && $cardnumber!='') //&&$row->tag!='' && $row->monat!='' && $row->jahr!='')
			{
				$sipass[$i]->command="A";
				$sipass[$i]->reference=$key_nummer;
				$sipass[$i]->last_name=trim($sn);
				$sipass[$i]->first_name=trim($givenname);
				$sipass[$i]->card_no=str_replace(" ","",$cardnumber);
				$sipass[$i]->start_date=date("d.m.Y");
				$sipass[$i]->uid=trim($uid);
				$sipass[$i]->matrikelnr=trim($matrikelnr);
				if($gid==101 || $gid==120)
				{
					$sipass[$i]->acc_grp_name="Verwaltung";
					$sipass[$i]->end_date=date("d.m.Y",mktime(0, 0, 0, date("m"),   date("d"),   date("Y")+15));
					
				}
				else
				{
					$sipass[$i]->acc_grp_name=$stg_kurzbz;
					$sipass[$i]->end_date=date("d.m.Y",mktime(0, 0, 0, date("m"),   date("d"),   date("Y")+15));
				}
				$key_nummer++;
				$i++;
			}
		}
	}
}

$ausdruck='';
for($j=0;$j<$i;$j++)
{
	if(trim($sipass[$j]->command==''))
	{
		$sipass[$j]->command='D';
		if (substr($sipass[$j]->acc_grp_name,0,1)!='#')
		{
			$ausdruck.=$sipass[$j]->command."\t"; 		// Command
			$ausdruck.=$sipass[$j]->reference."\t";		// ID
			$ausdruck.=$sipass[$j]->last_name."\t";		// Lastname
			$ausdruck.=$sipass[$j]->first_name."\t";		// Firstname
			$ausdruck.=$sipass[$j]->acc_grp_name."\t";	// Access Group
			$ausdruck.=$sipass[$j]->card_no."\n";		// Cardnumber
		}
	}
	else
	{
		if(trim($sipass[$j]->command!='V'))
		{
			$ausdruck.=$sipass[$j]->command."\t"; 			// Command
			$ausdruck.=$sipass[$j]->reference."\t";			// ID
			$ausdruck.=$sipass[$j]->last_name."\t";			// Lastname
			$ausdruck.=$sipass[$j]->first_name."\t";			// Firstname
			$ausdruck.=$sipass[$j]->acc_grp_name."\t";		// Access Group
			$ausdruck.=$sipass[$j]->card_no."\t";			// Cardnumber
			$ausdruck.=$sipass[$j]->start_date."\t";			// Valid From
			$ausdruck.=$sipass[$j]->end_date."\t";			// Valid till
			$ausdruck.="0\t";						// CardState
			$ausdruck.=$sipass[$j]->uid."\t";				// Text1 UID
			$ausdruck.=$sipass[$j]->matrikelnr."\t";			// Text2 Matrikelnummer
			if (isset($sipass[$j]->last_name_old))
				$ausdruck.=$sipass[$j]->last_name_old;		// Text3 // alter Vorname
			$ausdruck.="\t";
			if (isset($sipass[$j]->first_name_old))
				$ausdruck.=$sipass[$j]->first_name_old;		// Text4 // alter Nachname
			$ausdruck.="\t";
			if (isset($sipass[$j]->acc_grp_name_old))
				$ausdruck.=$sipass[$j]->acc_grp_name_old;	// Text5 // alte Accessgroup
			$ausdruck.="\t";
			if (isset($sipass[$j]->update))
				$ausdruck.=$sipass[$j]->update;			// Text6 // Update
			$ausdruck.="\n";
		}
	}
}
header("Content-Type: text/text");
header("Content-Disposition: attachment; filename=\"SiPassZutrittskartenUpdate". "_" . date("d_m_Y") . ".txt\"");
echo $ausdruck;

?>
