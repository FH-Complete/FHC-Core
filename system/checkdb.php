<?php
/* Copyright (C) 2009 Technikum-Wien
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
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 */
/*
 * Script zur Pruefung der Datenbank
 * 
 * database.inc.php enthaelt die Struktur der Datenbank. Diese wird mit der Produktivdatenbank
 * verglichen und eventuelle Aenderungen werden angezeigt.
 */

require_once('../config/system.config.inc.php');
require_once('database.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');

$db = new basis_db();
$uid=get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('admin'))
	die('Sie haben keine Berechtigung fuer diese Seite');

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
		{
			return (this.match("^"+str)==str)
		}

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
				if(div.id && (div.id.startsWith("schema.") || div.id.startsWith("table.") || div.id.startsWith("attrib.")))
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
				if(div.id && (div.id.startsWith("table.") || div.id.startsWith("attrib.")))
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

echo '<H2>Datenbank Pr&uuml;fung</H2>';

if(isset($_POST['commit']))
{
	if(isset($_POST['qry']))
	{
		if(!$db->db_query($_POST['qry']))
			echo $db->db_last_error();
	}
}

$obj=array();
$obj['']=array();
$obj['']['error']=false;

//Schema pruefen
foreach ($schemas as $schema)
{
	$obj[$schema['name']]=array();
	$obj[$schema['name']]['error']=false;
	
	$qry = "SELECT table_schema FROM information_schema.tables WHERE table_schema='".$schema['name']."'";
	if($db->db_query($qry))
	{
		if(!$db->db_num_rows()>0)
		{
			$obj[$schema['name']]['qry']='CREATE SCHEMA '.$schema['name'].';';
			$obj[$schema['name']]['error']=true;
		}
	}
}


//var_dump($datatypes);
$tabs=array_keys($tabellen);
//print_r($tabs);

$i=0;
foreach ($tabellen AS $tabelle)
{
	if(!isset($tabelle['schemaid']) || $tabelle['schemaid']=='')
	{
		//Tabelle auslassen, wenn kein Schema angegeben ist
		echo 'Tabelle '.$tabelle['name'].' ist keinem Schema zugeordnet!<br>';
		continue;
	}
	$obj[$schemas[$tabelle['schemaid']]['name']]['tables'][$tabelle['name']]=array();
	$sql_query2='';
	$pk='';
	// Tabelle pruefen
	
	$sql_query="SELECT table_name FROM information_schema.tables WHERE table_schema='".$schemas[$tabelle['schemaid']]['name']."' AND table_name='".$tabelle['name']."';";
	if (!$db->db_query($sql_query))
		echo '<BR><strong>'.$tabs[$i].': '.$db->db_last_error().' </strong><BR>';
	else
	{
		if ($db->db_num_rows()==0)
		{
			$sql_query= 'CREATE TABLE '.$schemas[$tabelle['schemaid']]['name'].'.'.$tabelle['name']." (";
			foreach ($tabelle['attribute'] AS $attribut)
			{
				if ($datatypes[$attribut['datatypeid']]['name']!='geometry')
				{
					$sql_query.= $attribut['name'].' ';
					if ($attribut['pk'])
						$pk.=$attribut['name'].',';
					$sql_query.=$datatypes[$attribut['datatypeid']]['name'];
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
					$sql_query2.="SELECT AddGeometryColumn('','".$tabelle['name']."','".$attribut['name']."',-1,'POINT',2);";
			}
			$sql_query=substr($sql_query,0,-2);
			if ($pk!="")
				$sql_query.=', CONSTRAINT "pk_'.$schemas[$tabelle['schemaid']]['name'].'_'.$tabelle['name'].'" PRIMARY KEY ('.substr($pk,0,-1).')';
			$sql_query.=');';
					
			$obj[$schemas[$tabelle['schemaid']]['name']]['tables'][$tabelle['name']]['qry']=$sql_query;
			$obj[$schemas[$tabelle['schemaid']]['name']]['error']=true;
		}
		else
		{
			// Attribute pruefen
			foreach ($tabelle['attribute'] AS $attribut)
			{
				$obj[$schemas[$tabelle['schemaid']]['name']]['tables'][$tabelle['name']]['attribute'][$attribut['name']]=array();
				
				$qry_query="SELECT column_name FROM information_schema.columns 
							WHERE table_schema=".$schemas[$tabelle['schemaid']]['name']."' 
							AND table_name='".$tabelle['name']."' AND column_name='".$attribut['name']."'; ";
				if ($db->db_query($sql_query))
				{
					if ($db->db_num_rows()==0)
					{
						$sql_query_nn='';
						
						$sql_query='ALTER TABLE '.$schemas[$tabelle['schemaid']]['name'].'.'.$tabelle['name'].'
							ADD COLUMN '.$attribut['name'].' ';
						$sql_query.=$datatypes[$attribut['datatypeid']]['name'];
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
							$sql_query_nn.='UPDATE '.$schemas[$tabelle['schemaid']]['name'].'.'.$tabelle['name'].' 
								SET '.$attribut['name'].'='.$attribut['defaultvalue'].';';
							$sql_query_nn.='ALTER TABLE '.$schemas[$tabelle['schemaid']]['name'].'.'.$tabelle['name'].' 
								ALTER COLUMN '.$attribut['name'].' SET NOT NULL;';
						}
						$sql_query.=';';
						
						$obj[$schemas[$tabelle['schemaid']]['name']]['tables'][$tabelle['name']]['attribute'][$attribut['name']]['qry']=$sql_query;
						$obj[$schemas[$tabelle['schemaid']]['name']]['error']=true;
						$obj[$schemas[$tabelle['schemaid']]['name']]['tables'][$tabelle['name']]['error']=true;
					}
					$obj[$schemas[$tabelle['schemaid']]['name']]['tables'][$tabelle['name']]['attribute'][$attribut['name']]['datatype']=$datatypes[$attribut['datatypeid']]['name'];
					$obj[$schemas[$tabelle['schemaid']]['name']]['tables'][$tabelle['name']]['attribute'][$attribut['name']]['attribute']=$attribut;
				}
			}
		}
	}

	flush();
	$i++;
}

// Constraints pruefen
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
			{
				if(isset($tabelle['schemaid']))
					return $schemas[$tabelle['schemaid']]['name'].'.'.$tabelle['name'];
				else
					return 'public.'.$tabelle['name'];
			}
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
					$attributes.=$attribute['name'].', ';
	return substr($attributes,0,-2);
}

foreach ($relations AS $relation)
{
	$sql_query='';
	$pk='';
	// Auf Foreign Key pruefen
	
	if (count($relation['foreignkeys'])>0)
	{
		$parentattr='';
		$childattr='';
		foreach ($relation['foreignkeys'] AS $foreignkey)
		{		
			$sql_query='';
			$parenttable=getTablenameFromAttributIDs($foreignkey['attrparent']);
			$childtable=getTablenameFromAttributIDs($foreignkey['attrchild']);
			$parentattr.=getAttributesnameFromAttributIDs($foreignkey['attrparent']).', ';
			$childattr.=getAttributesnameFromAttributIDs($foreignkey['attrchild']).', ';
		}
		
		$parentattr = substr($parentattr, 0, -2);
		$childattr = substr($childattr, 0, -2);
			
			list($schema, $tablename) = explode(".", $childtable);
			
			$qry = "SELECT 1 FROM information_schema.key_column_usage 
					WHERE table_schema='".$schema."' AND table_name='".$tablename."' AND constraint_name='".$relation['name']."'";
			if($db->db_query($qry))
			{
				if($db->db_num_rows()==0)
				{
					$sql_query='ALTER TABLE '.$childtable.' ADD CONSTRAINT '.$relation['name'].' FOREIGN KEY ('.$childattr.') REFERENCES '.$parenttable.' ('.$parentattr.') ';
					$sql_query.='ON UPDATE CASCADE ON DELETE RESTRICT;';
					
					if(isset($obj[$schema]) && 
					   isset($obj[$schema]['tables'][$tablename]) && 
					   isset($obj[$schema]['tables'][$tablename]['attribute']) && 
					   isset($obj[$schema]['tables'][$tablename]['attribute'][$childattr]['qry']))
						$obj[$schema]['tables'][$tablename]['attribute'][$childattr]['qry'].=$sql_query;
					else 
						$obj[$schema]['tables'][$tablename]['attribute'][$childattr]['qry']=$sql_query;
					$obj[$schema]['error']=true;
					$obj[$schema]['tables'][$tablename]['error']=true;
				}
			}		
	}

	flush();
	$i++;
}


// Gegenpruefung

//Prueft ob ein Schema in database.inc.php vorhanden ist
function schemaExists($schema)
{
	global $schemas;
	
	foreach ($schemas AS $schemata)
	{
		if($schemata['name']==$schema)
			return true;
	}
	return false;
}

//Prueft ob eine Tabelle in database.inc.php vorhanden ist
function tableExists($schema, $table)
{
	global $schemas;
	global $tabellen;
	
	foreach ($schemas AS $schemata)
	{
		if($schemata['name']==$schema)
			$schemaid=$schemata['id'];
	}
	
	foreach ($tabellen as $tabelle)
	{
		if($tabelle['name']==$table && $tabelle['schemaid']==$schemaid)
			return true;
	}
	
	return false;	
}
//Prueft ob eine Tabelle in database.inc.php vorhanden ist
function attributExists($schema, $table, $attribut)
{
	global $schemas;
	global $tabellen;
	
	foreach ($schemas AS $schemata)
	{
		if($schemata['name']==$schema)
			$schemaid=$schemata['id'];
	}
	
	foreach ($tabellen as $tabelle)
	{
		if($tabelle['name']==$table && $tabelle['schemaid']==$schemaid)
		{
			foreach($tabelle['attribute'] as $attr)
			{
				if($attr['name']==$attribut)
					return true;
			}
			return false;
		}
	}
	
	return false;	
}
// Schema

$additionalElements='';

$sql_query="SELECT table_schema FROM information_schema.tables 
			WHERE 	table_schema != 'pg_catalog' 
				AND table_schema != 'information_schema' 
				AND table_schema != 'sync' 
				AND table_schema != 'papaya' 
			GROUP BY table_schema";
if ($result=$db->db_query($sql_query))
{
	while ($row_schema=$db->db_fetch_object($result))
	{
		if (!schemaExists($row_schema->table_schema))
		{
			$additionalElements.='Schema '.$row_schema->table_schema."\n";
		}
		else
		{
			//Tabellen
			$qry = "SELECT table_name FROM information_schema.tables WHERE table_schema='".$row_schema->table_schema."'";
			if ($result_table=$db->db_query($qry))
			{
				while($row_table = $db->db_fetch_object($result_table))
				{
					if(!tableExists($row_schema->table_schema, $row_table->table_name))
					{
						$additionalElements.='Tabelle '.$row_schema->table_schema.'.'.$row_table->table_name."\n";
					}
					else 
					{
						//Attribute
						$qry = "SELECT column_name FROM information_schema.columns 
								WHERE table_schema='".$row_schema->table_schema."'
									AND table_name='".$row_table->table_name."'";
						
						if($result_attrib = $db->db_query($qry))
						{
							while($row_attrib = $db->db_fetch_object($result_attrib))
							{
								if(!attributExists($row_schema->table_schema, $row_table->table_name, $row_attrib->column_name))
								{
									$additionalElements.='Attribut '.$row_schema->table_schema.'.'.$row_table->table_name.'.'.$row_attrib->column_name."\n";
								}
								else 
								{
									//KEYs Pruefen
								}
							}
						}
					}
				}				
			}
		}
	}
}
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
		$rows = strlen($qry)/50+1;
		
		if($rows>10)
			$rows = 10;
	}
	else 
	{
		$cols=strlen($qry);
		$rows=1;
	}
	$ret.='<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
	$ret.='<br /><textarea readonly="true" cols="'.$cols.'" rows="'.$rows.'" name="qry">'.$qry.'</textarea>';
	$ret.='<br /><br /><input type="submit" value="Commit" name="commit" /></form>';
	$ret.='</div>';
	
	return $ret;
}
$out_schema.= '<div class="box" id="schema">';
$out_schema.= '<div class="boxhead">Schema</div>';
$out_schema.= '<table>';

$gesamtqry = '';
$gesamtqry_att='';

foreach ($obj as $schema=>$value)
{
	
	//Schema
	$out_schema.= "\n";
	$out_schema.= '<tr>';
	if($value['error'])
	{
		if(isset($value['qry']) && $value['qry']!='')
		{
			$gesamtqry .= $value['qry']."\n";
			$out_schema_data.=querybox($schema, $value['qry'], 'schema.'.$schema);
			$img = "exclamation.png";
		}
		else 
		{
			$img = "error_go.png";
		}
		$out_schema.= '<td><a href="#" onclick="display(\'schema.'.$schema.'\'); return false;"><img src="../skin/images/'.$img.'" /></a></td>';
		$out_schema.= '<td><a href="#" onclick="display(\'schema.'.$schema.'\'); return false;">'.$schema.'</a></td>';
		
		
		
	}
	else 
	{
		$out_schema.= '<td></td>';
		$out_schema.= '<td><a href="#" onclick="display(\'schema.'.$schema.'\'); return false;">'.$schema.'</a></td>';
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
				$gesamtqry .= $tabvalue['qry']."\n";
				$out_tbl.= '<td><a href="#" onclick="display(\'table.'.$schema.$tables.'\'); return false;"><img src="../skin/images/exclamation.png" /></a></td>';
				$out_tbl.= '<td><a href="#" onclick="display(\'table.'.$schema.$tables.'\'); return false;">'.$tables.'</a></td>';
				
				$out_tbl_data.=querybox($tables, $tabvalue['qry'], 'table.'.$schema.$tables);
				
			}
			else 
			{
				if(isset($tabvalue['error']) && $tabvalue['error'])
				{
					$out_tbl.= '<td><a href="#" onclick="display(\'table.'.$schema.$tables.'\'); return false;"><img src="../skin/images/error_go.png" /></a></td>';
					$out_tbl.= '<td><a href="#" onclick="display(\'table.'.$schema.$tables.'\'); return false;">'.$tables.'</a></td>';
				}
				else 
				{
					$out_tbl.= '<td></td>';
					$out_tbl.= '<td><a href="#" onclick="display(\'table.'.$schema.$tables.'\'); return false;">'.$tables.'</a></td>';
				}
			}
			$out_tbl.= '</tr>';
		
			//Attribute
			if(isset($tabvalue['attribute']))
			{
				$out_att.= "\n";
				$out_att.= '<div class="box" id="table.'.$schema.$tables.'" style="display: none">';
				$out_att.= '<div class="boxhead">'.$tables.'</div>';
				$out_att.= '<table>';
				
				foreach ($tabvalue['attribute'] as $attrib=>$attvalue)
				{
					$out_att.= '<tr>';
					if(isset($attvalue['qry']) && $attvalue['qry']!='')
					{
						$gesamtqry_att .= $attvalue['qry']."\n";
						$out_att.= '<td><a href="#" onclick="display(\'attrib.'.$schema.$tables.$attrib.'\'); return false;"><img src="../skin/images/exclamation.png" /></a></td>';
						$out_att.= '<td><a href="#" onclick="display(\'attrib.'.$schema.$tables.$attrib.'\'); return false;">'.$attrib.'</a></td>';
						
						$out_att_data.=querybox($attrib, $attvalue['qry'], 'attrib.'.$schema.$tables.$attrib);
					}
					else 
					{
						$out_att.= '<td></td>';
						$out_att.= '<td><a href="#" onclick="display(\'attrib.'.$schema.$tables.$attrib.'\'); return false;">'.$attrib.'</a></td>';
						$out_att.= '<td>&nbsp;<a href="#" onclick="display(\'attrib.'.$schema.$tables.$attrib.'\'); return false;">'.(isset($attvalue['datatype'])?$attvalue['datatype']:'').($attvalue['attribute']['length']!=''?' ('.$attvalue['attribute']['length'].')':'').'</a></td>';
						$out_att.= '<td>&nbsp;<a href="#" onclick="display(\'attrib.'.$schema.$tables.$attrib.'\'); return false;">'.
									(isset($attvalue['attribute'])?($attvalue['attribute']['unique']=='1'?'U':''):'').
									(isset($attvalue['attribute'])?($attvalue['attribute']['notnull']=='1'?'NN':''):'').
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

echo '<a href="#" onclick="display(\'schema.gesamtqry\'); return false;"><img src="../skin/images/system-software-update.png" title="Gesamtsystem aktualisieren" alt="Gesamtsystem aktualisieren"></a>&nbsp;&nbsp;';
echo '<a href="#" onclick="display(\'schema.additionalelements\'); return false;"><img src="../skin/images/user-trash-full.png" title="Element die in der DB sind, aber nicht in diesem Script" alt="Element die in der DB sind, aber nicht in diesem Script"><br /></a>';
echo $out_schema;
echo querybox('Gesamtsystem aktualisieren',$gesamtqry.$gesamtqry_att, 'schema.gesamtqry');
echo querybox('Element die in der DB sind, aber nicht in diesem Script',$additionalElements, 'schema.additionalelements');
echo $out_schema_data;
echo $out_tbl;
echo $out_tbl_data;
echo $out_att;
echo $out_att_data;
?>
