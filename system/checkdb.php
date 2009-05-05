<?php


require ('../config.inc.php');
require ('database.inc.php');

// Datenbank Verbindung
//if (!$conn = pg_pconnect("host=localhost dbname=conquearth user=pam password="))
if (!$conn = pg_pconnect(CONN_STRING))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!'.pg_last_error($conn));

echo '<H1>Systemcheck!</H1>';

echo '<H2>Pruefe Tabellen und Attribute!</H2>';

//var_dump($datatypes);
$tabs=array_keys($tabellen);
//print_r($tabs);
$i=0;
foreach ($tabellen AS $tabelle)
{
	$sql_query2='';
	$pk='';
	// Tabelle pruefen
	//var_dump($tabelle);
	$sql_query="SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname='".$schemas[$tabelle['schemaid']]['caption']."' AND tablename='".$tabelle['caption']."';";
	if (!$result=pg_query($conn,$sql_query))
		echo '<BR><strong>'.$tabs[$i].': '.pg_last_error($conn).' </strong><BR>';
	else
	{
		if (pg_num_rows($result)==0)
		{
			$sql_query= 'CREATE TABLE '.$schemas[$tabelle['schemaid']]['caption'].'.'.$tabelle['caption']." (";
			foreach ($tabelle['attribute'] AS $attribut)
			{
				if ($datatypes[$attribut['datatypeid']]['caption']!='geometry')
				{
					//echo $datatypes[$attribut['datatypeid']]['caption'];
					$sql_query.= $attribut['caption'].' ';
					if ($attribut['pk'])
						$pk.=$attribut['caption'].',';
					$sql_query.=$datatypes[$attribut['datatypeid']]['caption'];
					if ($datatypes[$attribut['datatypeid']]['length']==1)
						$sql_query.='('.$attribut['datatypeparam1'].')';
					if ($datatypes[$attribut['datatypeid']]['length']==2)
						$sql_query.='('.$attribut['datatypeparam1'].','.$attribut['datatypeparam2'].')';
					if ($attribut['notnull'])
						$sql_query.=' NOT NULL';
					if ($attribut['unique'])
						$sql_query.=' UNIQUE';
					if ($attribut['defaultvalue']!="")
						$sql_query.=' DEFAULT '.$attribut['defaultvalue'];
					if ($attribut['checkconstraint']!="")
						$sql_query.=' CHECK ('.$attribut['checkconstraint'].')';
					$sql_query.=', ';
				}
				else
					$sql_query2.="SELECT AddGeometryColumn('','".$tabelle['caption']."','".$attribut['caption']."',-1,'POINT',2);";
			}
			$sql_query=substr($sql_query,0,-2);
			if ($pk!="")
				$sql_query.=', CONSTRAINT "pk_'.$schemas[$tabelle['schemaid']]['caption'].'_'.$tabelle['caption'].'" PRIMARY KEY ('.substr($pk,0,-1).')';
			$sql_query.=');';
			//echo $sql_query.'<BR>'.$sql_query2;
			if (!$res_attr=pg_query($conn,$sql_query.$sql_query2))
				echo '<BR><strong>'.$schemas[$tabelle['schemaid']]['caption'].'.'.$tabelle['caption'].': '.pg_last_error($conn).' </strong><BR>'.$sql_query.'<BR>'.$sql_query_nn.'<BR>';
			else
				echo 'Tabelle '.$schemas[$tabelle['schemaid']]['caption'].'.'.$tabelle['caption'].' wurde erfolgreich angelegt!<BR>';
		}
		else
		{
			// Attribute pruefen
			foreach ($tabelle['attribute'] AS $attribut)
			{
				//var_dump($attribut);
				$sql_query="SELECT nspname AS schemaname, relname AS tablename, pg_get_userbyid(relowner) AS tableowner, attname AS attribute
						FROM pg_catalog.pg_attribute JOIN pg_catalog.pg_class ON (attrelid=relfilenode) JOIN pg_namespace ON (oid=relnamespace)
						WHERE relkind='r' AND nspname='".$schemas[$tabelle['schemaid']]['caption']."' AND relname='".$tabelle['caption']."'
							AND attname='".$attribut['caption']."'; ";
				if (!$res_attr=pg_query($conn,$sql_query))
					echo '<BR><strong>'.$attribut['caption'].': '.pg_last_error($conn).' </strong><BR>';
				else
					if (pg_num_rows($res_attr)==1)
						echo $schemas[$tabelle['schemaid']]['caption'].'.'.$tabelle['caption'].'.'.$attribut['caption'].': OK - ';
					else if (pg_num_rows($res_attr)==0)
					{
						$sql_query_nn='';
						echo $schemas[$tabelle['schemaid']]['caption'].'.'.$tabelle['caption'].'.'.$attribut['caption'].' ist nicht angelegt!<BR>';
						$sql_query='ALTER TABLE '.$schemas[$tabelle['schemaid']]['caption'].'.'.$tabelle['caption'].'
							ADD COLUMN '.$attribut['caption'].' ';
						$sql_query.=$datatypes[$attribut['datatypeid']]['caption'];
						if ($datatypes[$attribut['datatypeid']]['length']==1)
							$sql_query.='('.$attribut['datatypeparam1'].')';
						if ($datatypes[$attribut['datatypeid']]['length']==2)
							$sql_query.='('.$attribut['datatypeparam1'].','.$attribut['datatypeparam2'].')';
						if ($attribut['unique'])
							$sql_query.=' UNIQUE';
						if ($attribut['defaultvalue']!="")
							$sql_query.=' DEFAULT '.$attribut['defaultvalue'];
						else
							$attribut['defaultvalue']=$datatypes[$attribut['datatypeid']]['default'];
						if ($attribut['checkconstraint']!="")
							$sql_query.=' CHECK ('.$attribut['checkconstraint'].')';
						if ($attribut['notnull'])
						{
							$sql_query_nn.='UPDATE '.$schemas[$tabelle['schemaid']]['caption'].'.'.$tabelle['caption'].' 
								SET '.$attribut['caption'].'='.$attribut['defaultvalue'].';';
							$sql_query_nn.='ALTER TABLE '.$schemas[$tabelle['schemaid']]['caption'].'.'.$tabelle['caption'].' 
								ALTER COLUMN '.$attribut['caption'].' SET NOT NULL;';
						}
						$sql_query.=';';
						//echo $sql_query;
						if (!$res_attr=pg_query($conn,$sql_query.$sql_query_nn))
							echo '<BR><strong>'.$attribut['caption'].': '.pg_last_error($conn).' </strong><BR>'.$sql_query.'<BR>'.$sql_query_nn.'<BR>';
						else
							echo $schemas[$tabelle['schemaid']]['caption'].'.'.$tabelle['caption'].'.'.$attribut['caption'].' wurde erfolgreich hinzugefuegt!<BR>';
					}
			}
		}
	}

	flush();
	$i++;
}

echo '<H2>Pruefe Constraints!</H2>';

function getTablenameFromAttributIDs($attr)
{
	global $tabellen;
	global $schemas;
	$attributid=null;
	foreach ($attr AS $attribut)
		$attributid=$attribut;
	foreach ($tabellen AS $tabelle)
		foreach ($tabelle['attribute'] AS $attribut)
			if ($attribut['id']==$attributid)
				return $schemas[$tabelle['schemaid']]['caption'].'.'.$tabelle['caption'];
	return false;
}
function getAttributesnameFromAttributIDs($attr)
{
	global $tabellen;
	global $schemas;
	$attributes='';	
	foreach ($attr AS $attributid)
		foreach ($tabellen AS $tabelle)
			foreach ($tabelle['attribute'] AS $attribute)
				if ($attribute['id']==$attributid)
					$attributes.=$attribute['caption'].', ';
	return substr($attributes,0,-2);
}

//Alter table campus.tbl_paabgabe add Constraint projektarbeit_paabgabe foreign key (projektarbeit_id) references lehre.tbl_projektarbeit (projektarbeit_id) on update cascade on delete restrict;

foreach ($relations AS $relation)
{
	$sql_query='';
	$pk='';
	// Auf Foreign Key pruefen
	//var_dump($relation);
	if (count($relation['foreignkeys'])>0)
	{
		foreach ($relation['foreignkeys'] AS $foreignkey)
		{		
			$parenttable=getTablenameFromAttributIDs($foreignkey['attrparent']);
			$childtable=getTablenameFromAttributIDs($foreignkey['attrchild']);
			$parentattr=getAttributesnameFromAttributIDs($foreignkey['attrparent']);
			$childattr=getAttributesnameFromAttributIDs($foreignkey['attrchild']);
			//$constrname=str_replace('.','_',);
			$sql_query='ALTER TABLE '.$childtable.' ADD CONSTRAINT '.$relation['caption'].' FOREIGN KEY ('.$childattr.') REFERENCES '.$parenttable.' ('.$parentattr.') ';
			$sql_query.='ON UPDATE CASCADE ON DELETE RESTRICT;';
			//if (refintegritychildupdate)
			//	$sql_query.='
			echo $sql_query.'<BR>';
		}
	}

	flush();
	$i++;
}
			
echo '<H2>Gegenpruefung!</H2>';
$sql_query="SELECT schemaname,tablename FROM pg_catalog.pg_tables WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema' AND schemaname != 'sync' AND schemaname != 'papaya';";
if (!$result=@pg_query($conn,$sql_query))
		echo '<BR><strong>'.pg_last_error($conn).' </strong><BR>';
	else
		while ($row=pg_fetch_object($result))
		{
			$fulltablename=$row->schemaname.'.'.$row->tablename;
			if (!isset($tabellen[$fulltablename]))
				echo 'Tabelle '.$fulltablename.' existiert in der DB, aber nicht in diesem Skript!<BR>';
			else
				if (!$result_fields=@pg_query($conn,"SELECT * FROM $fulltablename LIMIT 1;"))
					echo '<BR><strong>'.pg_last_error($conn).' </strong><BR>';
				else
					for ($i=0; $i<pg_num_fields($result_fields); $i++)
					{
						$found=false;
						$fieldnameDB=pg_field_name($result_fields,$i);
						foreach ($tabellen[$fulltablename] AS $fieldnameARRAY)
							if ($fieldnameDB==$fieldnameARRAY)
							{
								$found=true;
								break;
							}
						if (!$found)
							echo 'Attribut '.$fulltablename.'.<strong>'.$fieldnameDB.'</strong> existiert in der DB, aber nicht in diesem Skript!<BR>';
					}
		}
?>
