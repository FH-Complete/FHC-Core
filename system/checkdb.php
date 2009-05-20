<?php
/* Copyright (C) 2006 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/*
 * Script zur Pruefung der Datenbank
 * 
 * database.inc.php enthaelt die Struktur der Datenbank. Diese wird mit der Produktivdatenbank
 * verglichen und eventuelle Aenderungen werden angezeigt.
 */

require_once('../vilesci/config.inc.php');
require_once('database.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!'.pg_last_error($conn));

$uid=get_uid();

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('admin'))
	die('Sie haben keine Berechtigung für diese Seite');

echo '
<html>
<head>
<title>Datenbank Check</title>
<meta http-equiv="Content-Type" content="text/html; charset="UTF-8">
<link rel="stylesheet" href="../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../include/js/tablesort/table.css" type="text/css">
<script src="../include/js/tablesort/table.js" type="text/javascript"></script>
<script  type="text/javascript">
String.prototype.startsWith = function(str)
{return (this.match("^"+str)==str)}

function display(id)
{
	if(id.startsWith("table."))
		disableTables();
	if(id.startsWith("schema."))
		disableSchemas();
	
	document.getElementById(id).style.display="block";
	return false;
}

function disableSchemas()
{
	elem = document.getElementsByTagName("div");
	
	for(i=0;i<elem.length;i++)
	{
		div = elem[i];
		if(div.id && (div.id.startsWith("schema.") || div.id.startsWith("table.") || div.id.startsWith("attributes.")))
		{
			document.getElementById(div.id).style.display="none";
		}
	}
}

function disableTables()
{
	elem = document.getElementsByTagName("div");
	
	for(i=0;i<elem.length;i++)
	{
		div=elem[i];
		if(div.id && (div.id.startsWith("table.") || div.id.startsWith("attributes.")))
		{
			document.getElementById(div.id).style.display="none";
		}
	}
}

</script>
<style>
.box
{
	border: 1px solid black;
	border: 2px solid #E6E6CC;
	float: left;
	margin: 10px;
	padding: 3px;
}

.boxhead
{
	background-color: #F3F3E9; 
	border:0; 
	text-align: center;
	border-bottom: 1px solid #E6E6CC; 
	font-weight: bold; 
}

</style>
</head>
<body>';

echo '<H2>Datenbank Pr&uuml;fung</H2><br />';
$obj=array();
$obj['']=array();
$obj['']['error']=false;
$schemas['']=array
	(
    		"id" => "" ,
    		"name" => "" ,
    		"caption" => "" ,
    		"comments" => "" ,
    		"ordinal" => "0"
	);

//Schema pruefen
foreach ($schemas as $schema)
{
	$obj[$schema['caption']]=array();
	$obj[$schema['caption']]['error']=false;
	
	$qry = "SELECT nspname FROM pg_namespace WHERE nspname='".$schema['caption']."'";
	if($result = pg_query($conn, $qry))
	{
		if(!pg_num_rows($result)>0)
		{
			$obj[$schema['caption']]['qry']='CREATE SCHEMA '.$schema['caption'].';';
			$obj[$schema['caption']]['error']=true;
		}
	}
}


//var_dump($datatypes);
$tabs=array_keys($tabellen);
//print_r($tabs);

$i=0;
foreach ($tabellen AS $tabelle)
{
	$obj[$schemas[$tabelle['schemaid']]['caption']]['tables'][$tabelle['caption']]=array();
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
			//if (!$res_attr=pg_query($conn,$sql_query.$sql_query2))
			//	echo '<BR><strong>'.$schemas[$tabelle['schemaid']]['caption'].'.'.$tabelle['caption'].': '.pg_last_error($conn).' </strong><BR>'.$sql_query.'<BR>'.$sql_query_nn.'<BR>';
			//else
			//	echo 'Tabelle '.$schemas[$tabelle['schemaid']]['caption'].'.'.$tabelle['caption'].' wurde erfolgreich angelegt!<BR>';
			
			$obj[$schemas[$tabelle['schemaid']]['caption']]['tables'][$tabelle['caption']]['qry']=$sql_query;
			$obj[$schemas[$tabelle['schemaid']]['caption']]['error']=true;
		}
		else
		{
			// Attribute pruefen
			foreach ($tabelle['attribute'] AS $attribut)
			{
				$obj[$schemas[$tabelle['schemaid']]['caption']]['tables'][$tabelle['caption']]['attribute'][$attribut['caption']]=array();
				
				//var_dump($attribut);
				$sql_query="SELECT nspname AS schemaname, relname AS tablename, pg_get_userbyid(relowner) AS tableowner, attname AS attribute
						FROM pg_catalog.pg_attribute JOIN pg_catalog.pg_class ON (attrelid=relfilenode) JOIN pg_namespace ON (oid=relnamespace)
						WHERE relkind='r' AND nspname='".$schemas[$tabelle['schemaid']]['caption']."' AND relname='".$tabelle['caption']."'
							AND attname='".$attribut['caption']."'; ";
				if (!$res_attr=pg_query($conn,$sql_query))
				{
					//echo '<BR><strong>'.$attribut['caption'].': '.pg_last_error($conn).' </strong><BR>';
				}
				else
				{
					if (pg_num_rows($res_attr)==1)
					{
						//echo $schemas[$tabelle['schemaid']]['caption'].'.'.$tabelle['caption'].'.'.$attribut['caption'].': OK - ';
					}
					else if (pg_num_rows($res_attr)==0)
					{
						$sql_query_nn='';
						//echo $schemas[$tabelle['schemaid']]['caption'].'.'.$tabelle['caption'].'.'.$attribut['caption'].' ist nicht angelegt!<BR>';
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
						//if (!$res_attr=pg_query($conn,$sql_query.$sql_query_nn))
						//	echo '<BR><strong>'.$attribut['caption'].': '.pg_last_error($conn).' </strong><BR>'.$sql_query.'<BR>'.$sql_query_nn.'<BR>';
						//else
						//	echo $schemas[$tabelle['schemaid']]['caption'].'.'.$tabelle['caption'].'.'.$attribut['caption'].' wurde erfolgreich hinzugefuegt!<BR>';
						$obj[$schemas[$tabelle['schemaid']]['caption']]['tables'][$tabelle['caption']]['attribute'][$attribut['caption']]['qry']=$sql_query;
						$obj[$schemas[$tabelle['schemaid']]['caption']]['error']=true;
					}
					$obj[$schemas[$tabelle['schemaid']]['caption']]['tables'][$tabelle['caption']]['attribute'][$attribut['caption']]['datatype']=$datatypes[$attribut['datatypeid']]['caption'];
					$obj[$schemas[$tabelle['schemaid']]['caption']]['tables'][$tabelle['caption']]['attribute'][$attribut['caption']]['attribute']=$attribut;
				}
			}
		}
	}

	flush();
	$i++;
}
/*
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
*/
$out_schema="\n";
$out_schema_data="\n";
$out_tbl="\n";
$out_tbl_data="\n";
$out_att="\n";
$out_att_data="\n";

function querybox($title, $qry, $id)
{
	$ret="\n";
	$ret.='<div class="box" id="'.$id.'" style="display: none">';
	$ret.='<div class="boxhead">'.$title.'</div>';
	if(strlen($qry)>50)
	{
		$cols=55;
		$rows = strlen($qry)/50;
		
		if($rows>10)
			$rows = 10;
	}
	else 
	{
		$cols=strlen($qry);
		$rows=1;
	}
	$ret.='<br /><textarea readonly="true" cols="'.$cols.'" rows="'.$rows.'">'.$qry.'</textarea>';
	$ret.='<br /><br /><input type="button" value="Commit" />';
	$ret.='</div>';
	
	return $ret;
}
$out_schema.= '<div class="box" id="schema">';
$out_schema.= '<div class="boxhead">Schema</div>';
$out_schema.= '<table>';

foreach ($obj as $schema=>$value)
{
	
	//Schema
	$out_schema.= "\n";
	$out_schema.= '<tr>';
	if($value['error'])
	{
		if(isset($value['qry']) && $value['qry']!='')
		{
			$out_schema_data.=querybox($schema, $value['qry'], 'schema.'.$schema);
			$img = "exclamation.png";
		}
		else 
		{
			$img = "error_go.png";
		}
		$out_schema.= '<td><a href="#" onclick="display(\'schema.'.$schema.'\')"><img src="../skin/images/'.$img.'" /></a></td>';
		$out_schema.= '<td><a href="#" onclick="display(\'schema.'.$schema.'\')">'.$schema.'</a></td>';
		
		
		
	}
	else 
	{
		$out_schema.= '<td></td>';
		$out_schema.= '<td><a href="#" onclick="display(\'schema.'.$schema.'\')">'.$schema.'</a></td>';
	}
	$out_schema.= '</tr>';

	//Tabelle
	if(isset($value['tables']))
	{
		$out_tbl.= "\n";
		$out_tbl.= '<div class="box" id="schema.'.$schema.'" style="display: none">';
		$out_tbl.= '<div class="boxhead">'.$schema.'</div>';
		$out_tbl.= '<table>';
		
		foreach ($value['tables'] as $tables=>$tabvalue) 
		{
			$out_tbl.= '<tr>';
			if(isset($tabvalue['qry']) && $tabvalue['qry']!='')
			{
				$out_tbl.= '<td><a href="#" onclick="display(\'table.'.$tables.'\')"><img src="../skin/images/exclamation.png" /></a></td>';
				$out_tbl.= '<td><a href="#" onclick="display(\'table.'.$tables.'\')">'.$tables.'</a></td>';
				
				$out_tbl_data.=querybox($tables, $tabvalue['qry'], 'table.'.$tables);
				
			}
			else 
			{
				$out_tbl.= '<td></td>';
				$out_tbl.= '<td><a href="#" onclick="display(\'table.'.$tables.'\')">'.$tables.'</a></td>';
			}
			$out_tbl.= '</tr>';
		
			//Attribute
			if(isset($tabvalue['attribute']))
			{
				$out_att.= "\n";
				$out_att.= '<div class="box" id="table.'.$tables.'" style="display: none">';
				$out_att.= '<div class="boxhead">'.$tables.'</div>';
				$out_att.= '<table>';
				
				foreach ($tabvalue['attribute'] as $attrib=>$attvalue)
				{
					$out_att.= '<tr>';
					if(isset($attvalue['qry']) && $attvalue['qry']!='')
					{
						$out_att.= '<td><a href="#" onclick="display(\'attrib.'.$attrib.'\')"><img src="../skin/images/exclamation.png" /></a></td>';
						$out_att.= '<td><a href="#" onclick="display(\'attrib.'.$attrib.'\')">'.$attrib.'</a></td>';
						
						$out_att_data.=querybox($attrib, $attvalue['qry'], 'attrib.'.$attrib);
					}
					else 
					{
						$out_att.= '<td></td>';
						$out_att.= '<td><a href="#" onclick="display(\'attrib.'.$attrib.'\')">'.$attrib.'</a></td>';
						$out_att.= '<td>&nbsp;<a href="#" onclick="display(\'attrib.'.$attrib.'\')">'.$attvalue['datatype'].($attvalue['attribute']['length']!=''?' ('.$attvalue['attribute']['length'].')':'').'</a></td>';
						$out_att.= '<td>&nbsp;<a href="#" onclick="display(\'attrib.'.$attrib.'\')">'.
									($attvalue['attribute']['unique']=='1'?'U':'').
									($attvalue['attribute']['notnull']=='1'?'NN':'').
									'</a></td>';
						
					}
					$out_att.= '</tr>';
				}
				$out_att.= '</table>';
				$out_att.= '</div>';
			}				
		}
		$out_tbl.= '</table>';
		$out_tbl.= '</div>';
		
	}
}
$out_schema.= '</table>';
$out_schema.= '</div>';

echo $out_schema;
echo $out_schema_data;
echo $out_tbl;
echo $out_tbl_data;
echo $out_att;
echo $out_att_data;
?>