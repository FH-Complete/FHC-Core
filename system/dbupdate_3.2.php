<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Andreas Moik <moik@technikum-wien.at>,
 *
 * Beschreibung:
 * Dieses Skript prueft die Datenbank auf aktualitaet, dabei werden fehlende Attribute angelegt.
 */

//Defaultwerte fuer diverse Tabellen
if($result = @$db->db_query("SELECT column_default FROM information_schema.columns WHERE table_schema='public' AND table_name='tbl_studiengang' AND column_name='testtool_sprachwahl'"))
{
	if($row = $db->db_fetch_object($result))
	{
		if($row->column_default=='')
		{
			// Defaultwerte in tbl_studiengang
			$qry = "ALTER TABLE public.tbl_studiengang ALTER COLUMN testtool_sprachwahl SET DEFAULT true;
			ALTER TABLE public.tbl_studiengang ALTER COLUMN projektarbeit_note_anzeige SET DEFAULT true;
			ALTER TABLE lehre.tbl_studienplan ALTER COLUMN testtool_sprachwahl SET DEFAULT true;
			ALTER TABLE lehre.tbl_studienplan ALTER COLUMN aktiv SET DEFAULT true;";

			if(!$db->db_query($qry))
				echo '<strong>public.tbl_studiengang: '.$db->db_last_error().'</strong><br>';
			else
				echo 'Defaultwerte für tbl_studiengang und tbl_studienplan gesetzt.<br>';
		}
	}
}

//Spalte studiensemester_kurzbz für Reihungstest
if(!$result = @$db->db_query("SELECT studiensemester_kurzbz FROM public.tbl_reihungstest LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_reihungstest ADD COLUMN studiensemester_kurzbz varchar(16);
		ALTER TABLE public.tbl_reihungstest ADD CONSTRAINT fk_reihungsteset_studiensemester FOREIGN KEY (studiensemester_kurzbz) REFERENCES public.tbl_studiensemester (studiensemester_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_reihungstest: '.$db->db_last_error().'</strong><br>';
	else
		echo 'public.tbl_reihungstest: Spalte studiensemester_kurzbz hinzugefuegt';
}

// Neue Spalte beschreibung_mehrsprachig bei tbl_dokument
if(!@$db->db_query("SELECT dokumentbeschreibung_mehrsprachig FROM public.tbl_dokument LIMIT 1"))
{
	$qry = "
	ALTER TABLE public.tbl_dokument ADD COLUMN dokumentbeschreibung_mehrsprachig text[];
	";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_dokument '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte dokumentbeschreibung_mehrsprachig in public.tbl_dokument hinzugefügt';
}

// Neue Spalte beschreibung_mehrsprachig bei tbl_dokumentstudiengang
if(!@$db->db_query("SELECT beschreibung_mehrsprachig FROM public.tbl_dokumentstudiengang LIMIT 1"))
{
	$qry = "
	ALTER TABLE public.tbl_dokumentstudiengang ADD COLUMN beschreibung_mehrsprachig text[];
	";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_dokumentstudiengang '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte beschreibung_mehrsprachig in public.tbl_dokumentstudiengang hinzugefügt';
}

// Berechtigungen fuer web User erteilen
if($result = @$db->db_query("SELECT * FROM information_schema.role_table_grants WHERE table_name='tbl_frage' AND table_schema='testtool' AND grantee='web' AND privilege_type='DELETE'"))
{
	if($db->db_num_rows($result)==0)
	{

		$qry = "GRANT DELETE ON testtool.tbl_frage TO web;
			GRANT DELETE ON testtool.tbl_gebiet TO web;
			GRANT SELECT, UPDATE, INSERT, DELETE ON testtool.tbl_ablauf TO web;
			GRANT SELECT, UPDATE ON testtool.tbl_ablauf_ablauf_id_seq TO web;
			";

		if(!$db->db_query($qry))
			echo '<strong>Testtool Berechtigungen: '.$db->db_last_error().'</strong><br>';
		else
			echo 'Löschrechte fuer Testtool Tabellen fuer web user gesetzt ';
	}
}

// Neue Spalte Gewicht bei tbl_lehreinheit
if(!@$db->db_query("SELECT gewicht FROM lehre.tbl_lehreinheit LIMIT 1"))
{
	$qry = "
	ALTER TABLE lehre.tbl_lehreinheit ADD COLUMN gewicht smallint DEFAULT 1;;
	";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_lehreinheit '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte gewicht in lehre.tbl_lehreinheit hinzugefügt';
}

// Neue Spalte bewerbung_abgeschicktamum bei tbl_prestudentstatus
if(!@$db->db_query("SELECT bewerbung_abgeschicktamum FROM public.tbl_prestudentstatus LIMIT 1"))
{
	$qry = "
	ALTER TABLE public.tbl_prestudentstatus ADD COLUMN bewerbung_abgeschicktamum timestamp;
	";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_prestudentstatus '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte bewerbung_abgeschicktamum in public.tbl_prestudentstatus hinzugefügt';
}

//Spalte benotung in lehre.tbl_lehrveranstaltung
if (!$result = @$db->db_query("SELECT benotung FROM lehre.tbl_lehrveranstaltung LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN benotung boolean NOT NULL DEFAULT TRUE;";

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_lehrveranstaltung: ' . $db->db_last_error() . '</strong><br>';
	else
		echo ' lehre.tbl_lehrveranstaltung: Spalte benotung hinzugefügt.<br>';
}

//Spalte lvinfo in lehre.tbl_lehrveranstaltung
if (!$result = @$db->db_query("SELECT lvinfo FROM lehre.tbl_lehrveranstaltung LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN lvinfo boolean NOT NULL DEFAULT TRUE;";

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_lehrveranstaltung: ' . $db->db_last_error() . '</strong><br>';
	else
		echo ' lehre.tbl_lehrveranstaltung: Spalte lvinfo hinzugefügt.<br>';
}




// tbl_bisorgform hinzufuegen
if(!$result = @$db->db_query("SELECT 1 FROM bis.tbl_bisorgform LIMIT 1;"))
{
	$qry = "

	CREATE TABLE bis.tbl_bisorgform
	(
		bisorgform_kurzbz varchar(3) NOT NULL,
		code smallint,
		bezeichnung varchar(64)
	);

	COMMENT ON TABLE bis.tbl_bisorgform IS 'Offizielle OrgFormen fuer die BIS-Meldung';

	ALTER TABLE bis.tbl_bisorgform ADD CONSTRAINT pk_bisorgform_kurzbz PRIMARY KEY (bisorgform_kurzbz);

	GRANT SELECT ON bis.tbl_bisorgform TO vilesci;
	GRANT SELECT ON bis.tbl_bisorgform TO web;
	";

	if(!$db->db_query($qry))
		echo '<strong>Dokumentenupload fuer Notizen: '.$db->db_last_error().'</strong><br>';
	else
		echo ' Tabelle bis.tbl_orgform hinzugefuegt!<br>';
}

//Spalte bisorgform_kurzbz für tbl_orgform
if(!$result = @$db->db_query("SELECT bisorgform_kurzbz FROM bis.tbl_orgform LIMIT 1"))
{
	$qry = "ALTER TABLE bis.tbl_orgform ADD COLUMN bisorgform_kurzbz varchar(3);
	ALTER TABLE bis.tbl_orgform ADD CONSTRAINT fk_orgform_bisorgform FOREIGN KEY (bisorgform_kurzbz) REFERENCES bis.tbl_bisorgform (bisorgform_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;";

	if(!$db->db_query($qry))
		echo '<strong>bis.tbl_orgform: '.$db->db_last_error().'</strong><br>';
	else
		echo 'bis.tbl_orgform: Spalte bisorgform_kurzbz hinzugefuegt';
}

//Spalte curriculum in lehre.tbl_studienordnung_lehrveranstaltung
if (!$result = @$db->db_query("SELECT curriculum FROM lehre.tbl_studienplan_lehrveranstaltung LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_studienplan_lehrveranstaltung ADD COLUMN curriculum BOOLEAN DEFAULT TRUE;";

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_studienplan_lehrveranstaltung: ' . $db->db_last_error() . '</strong><br>';
	else
		echo ' lehre.tbl_studienplan_lehrveranstaltung: Spalte curriculum hinzugefügt.<br>';
}

//Spalte export in lehre.tbl_studienordnung_lehrveranstaltung
if (!$result = @$db->db_query("SELECT export FROM lehre.tbl_studienplan_lehrveranstaltung LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_studienplan_lehrveranstaltung ADD COLUMN export BOOLEAN DEFAULT TRUE;";

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_studienplan_lehrveranstaltung: ' . $db->db_last_error() . '</strong><br>';
	else
		echo ' lehre.tbl_studienplan_lehrveranstaltung: Spalte export hinzugefügt.<br>';
}

//Spalte lehrauftrag in lehre.tbl_lehrveranstaltung
if (!$result = @$db->db_query("SELECT lehrauftrag FROM lehre.tbl_lehrveranstaltung LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN lehrauftrag BOOLEAN DEFAULT TRUE;";

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_lehrveranstaltung: ' . $db->db_last_error() . '</strong><br>';
	else
		echo ' lehre.tbl_lehrveranstaltung: Spalte lehrauftrag hinzugefügt.<br>';
}


//sozialversicherungsnummer auf char(16) erhöhen
/**********************************************************ANFANG SVNR ÄNDERUNG**************************************************************************/
if($result = @$db->db_query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='public' AND TABLE_NAME='tbl_person' AND COLUMN_NAME = 'svnr' AND DATA_TYPE='character varying' AND character_maximum_length='16';"))
{
	if($db->db_num_rows($result)==0)
	{
		//********************************GENERIC********************************
		$views=array();
		$success = true;

		//********************************GET ALL NEEDED VIEWS********************************

		$qry="
			SELECT column_name as spalte, table_name as tabelle, table_schema as schema
			FROM information_schema.columns
			WHERE
				column_name in('svnr')
				AND data_type='character'
				AND character_maximum_length='10'
			ORDER BY table_name DESC, column_name";


		if($result = $db->db_query($qry))
		{
			$db->db_query('BEGIN');
			while($row = $db->db_fetch_object($result))
			{
				$comment = "";

				//Alle Views die Spalten enthalten die geaendert werden loeschen
				if(substr($row->tabelle,0,3)=='vw_')
				{
					$qry_view = "SELECT * FROM pg_views WHERE viewname='$row->tabelle' AND schemaname='$row->schema'";
					if($result_view = $db->db_query($qry_view))
					{
						if($row_view = $db->db_fetch_object($result_view))
						{
							if($row_view->schemaname != "public")
								$key = $row_view->schemaname.".".$row_view->viewname;
							else
								$key = $row_view->viewname;

							if(!isset($views[$key]))
							{
								$privileges = array();

								//get all privileges of this view
								$qry_view_priv = "SELECT *
									FROM information_schema.role_table_grants
										WHERE table_schema='".$row_view->schemaname."'
										AND table_name='".$row_view->viewname."';";

								if($result_view_priv = $db->db_query($qry_view_priv))
								{
									while($row_view_priv = $db->db_fetch_object($result_view_priv))
									{
										$privileges[] = array(
											"grantee" => $row_view_priv->grantee,
											"privilege_type" => $row_view_priv->privilege_type,
											);
									}
								}




								//get the comment of the view
								$qry_view_comment = "SELECT nspname, cl.relname, obj_description(cl.oid)
									FROM pg_class cl, pg_catalog.pg_namespace ns
									WHERE ns.oid=cl.relnamespace
									AND cl.relname='".$row_view->viewname."'
									AND nspname='".$row_view->schemaname."';";

								if($result_view_comment = $db->db_query($qry_view_comment))
								{
									if($row_view_comment = $db->db_fetch_object($result_view_comment))
									{
										$comment = $row_view_comment->obj_description;
									}
								}




								//save the view informations for later
								$views[$key]['definition']=$row_view->definition;
								$views[$key]['schema']=$row_view->schemaname;
								$views[$key]['viewname']=$row_view->viewname;
								$views[$key]['dropped']=false;
								$views[$key]['privileges']=$privileges;
								$views[$key]['comment']=$comment;


								//resolve dependencys
								echo "resolving deps for " . $key."<br>";
								$qry_RECURSIVE_DEPS =
										"WITH RECURSIVE dep_recursive AS (

											SELECT
													0 AS \"level\",
													'".$key."' AS \"dep_name\",
													'' AS \"dep_table\",
													'' AS \"dep_type\",
													'' AS \"ref_name\",
													'' AS \"ref_type\"

											UNION ALL

											SELECT
													level + 1 AS \"level\",
													depedencies.dep_name,
													depedencies.dep_table,
													depedencies.dep_type,
													depedencies.ref_name,
													depedencies.ref_type
											FROM (
													WITH classType AS (
															SELECT
																	oid,
																	CASE relkind
																			WHEN 'm' THEN 'MATERIALIZED VIEW'::text
																			WHEN 'r' THEN 'TABLE'::text
																			WHEN 'i' THEN 'INDEX'::text
																			WHEN 'S' THEN 'SEQUENCE'::text
																			WHEN 'v' THEN 'VIEW'::text
																			WHEN 'c' THEN 'TYPE'::text
																			WHEN 't' THEN 'TABLE'::text
																	END AS \"type\"
															FROM pg_class
													)

													SELECT DISTINCT
															CASE classid
																	WHEN 'pg_class'::regclass THEN objid::regclass::text
																	WHEN 'pg_type'::regclass THEN objid::regtype::text
																	WHEN 'pg_proc'::regclass THEN objid::regprocedure::text
																	WHEN 'pg_constraint'::regclass THEN (SELECT conname FROM pg_constraint WHERE OID = objid)
																	WHEN 'pg_attrdef'::regclass THEN 'default'
																	WHEN 'pg_rewrite'::regclass THEN (SELECT ev_class::regclass::text FROM pg_rewrite WHERE OID = objid)
																	WHEN 'pg_trigger'::regclass THEN (SELECT tgname FROM pg_trigger WHERE OID = objid)
																	ELSE objid::text
															END AS \"dep_name\",
															CASE classid
																	WHEN 'pg_constraint'::regclass THEN (SELECT conrelid::regclass::text FROM pg_constraint WHERE OID = objid)
																	WHEN 'pg_attrdef'::regclass THEN (SELECT adrelid::regclass::text FROM pg_attrdef WHERE OID = objid)
																	WHEN 'pg_trigger'::regclass THEN (SELECT tgrelid::regclass::text FROM pg_trigger WHERE OID = objid)
																	ELSE ''
															END AS \"dep_table\",
															CASE classid
																	WHEN 'pg_class'::regclass THEN (SELECT TYPE FROM classType WHERE OID = objid)
																	WHEN 'pg_type'::regclass THEN 'TYPE'
																	WHEN 'pg_proc'::regclass THEN 'FUNCTION'
																	WHEN 'pg_constraint'::regclass THEN 'TABLE CONSTRAINT'
																	WHEN 'pg_attrdef'::regclass THEN 'TABLE DEFAULT'
																	WHEN 'pg_rewrite'::regclass THEN (SELECT TYPE FROM classType WHERE OID = (SELECT ev_class FROM pg_rewrite WHERE OID = objid))
																	WHEN 'pg_trigger'::regclass THEN 'TRIGGER'
																	ELSE objid::text
															END AS \"dep_type\",
															CASE refclassid
																	WHEN 'pg_class'::regclass THEN refobjid::regclass::text
																	WHEN 'pg_type'::regclass THEN refobjid::regtype::text
																	WHEN 'pg_proc'::regclass THEN refobjid::regprocedure::text
																	ELSE refobjid::text
															END AS \"ref_name\",
															CASE refclassid
																	WHEN 'pg_class'::regclass THEN (SELECT TYPE FROM classType WHERE OID = refobjid)
																	WHEN 'pg_type'::regclass THEN 'TYPE'
																	WHEN 'pg_proc'::regclass THEN 'FUNCTION'
																	ELSE refobjid::text
															END AS \"ref_type\",
															CASE deptype
																	WHEN 'n' THEN 'normal'
																	WHEN 'a' THEN 'automatic'
																	WHEN 'i' THEN 'internal'
																	WHEN 'e' THEN 'extension'
																	WHEN 'p' THEN 'pinned'
															END AS \"dependency type\"
													FROM pg_catalog.pg_depend
													WHERE deptype = 'n'
													AND refclassid NOT IN (2615, 2612)

											) depedencies
											JOIN dep_recursive ON (dep_recursive.dep_name = depedencies.ref_name)
											WHERE depedencies.ref_name NOT IN(depedencies.dep_name, depedencies.dep_table)

									)

									SELECT
											MAX(level) AS \"level\",
											dep_name,
											MIN(dep_table) AS \"dep_table\",
											MIN(dep_type) AS \"dep_type\",
											string_agg(ref_name, ', ') AS \"ref_names\",
											string_agg(ref_type, ', ') AS \"ref_types\"
									FROM dep_recursive
									WHERE level > 0
									AND dep_type='VIEW'
									GROUP BY dep_name
									ORDER BY level desc, dep_name;";

								if($res_RECURSIVE_DEPS = $db->db_query($qry_RECURSIVE_DEPS))
								{
									while($rrd = $db->db_fetch_object($res_RECURSIVE_DEPS))
									{
										$comment_deps = "";

										echo "<span style='margin-left:20px;'>added " .$rrd->dep_name."</span><br>";
										if(strpos($rrd->dep_name,".") !== false)
											$qry_view_deps = "SELECT * FROM pg_views WHERE (schemaname || '.' || viewname)='$rrd->dep_name'";
										else
											$qry_view_deps = "SELECT * FROM pg_views WHERE viewname='$rrd->dep_name' AND schemaname='public'";

										if($result_view_deps = $db->db_query($qry_view_deps))
										{
											if($row_view_deps = $db->db_fetch_object($result_view_deps))
											{
												$key_deps = $row_view_deps->schemaname.".".$row_view_deps->viewname;
												if(!isset($views[$key_deps]))
												{
													$privileges = array();

													//get all privileges of this view
													$qry_view_priv = "SELECT *
														FROM information_schema.role_table_grants
															WHERE table_schema='".$row_view_deps->schemaname."'
															AND table_name='".$row_view_deps->viewname."';";

													if($result_view_priv = $db->db_query($qry_view_priv))
													{
														while($row_view_priv = $db->db_fetch_object($result_view_priv))
														{
															$privileges[] = array(
																"grantee" => $row_view_priv->grantee,
																"privilege_type" => $row_view_priv->privilege_type,
																);
														}
													}



													//get the comment of the view
													$qry_view_comment = "SELECT nspname, cl.relname, obj_description(cl.oid)
														FROM pg_class cl, pg_catalog.pg_namespace ns
														WHERE ns.oid=cl.relnamespace
														AND cl.relname='".$row_view_deps->viewname."'
														AND nspname='".$row_view_deps->schemaname."';";

													if($result_view_comment = $db->db_query($qry_view_comment))
													{
														if($row_view_comment = $db->db_fetch_object($result_view_comment))
														{
															$comment_deps = $row_view_comment->obj_description;
														}
													}



													$views[$key_deps]['definition']=$row_view_deps->definition;
													$views[$key_deps]['schema']=$row_view_deps->schemaname;
													$views[$key_deps]['viewname']=$row_view_deps->viewname;
													$views[$key_deps]['dropped']=false;
													$views[$key_deps]['privileges']=$privileges;
													$views[$key_deps]['comment']=$comment_deps;
												}
											}
											else
											{
												echo "<span style='margin-left:40px;'>view " . $rrd->dep_name . " not found!<br>";
												var_dump($qry_view_deps);
												echo "</span><br>";
											}
										}
									}
								}
							}
						}
					}
				}
			}
			//********************************DROP ALL VIEWS RECURSIVELY********************************
			echo "<br><br>LÖSCHEN:<br>";
			if(!drop_all_views_recursively($db, $views)){$success = false;}





			//********************************CHANGE DATATYPE********************************
			$qry_alter = "
			ALTER TABLE public.tbl_person ALTER COLUMN svnr TYPE varchar(16);";

			if(!$db->db_query($qry_alter))
				echo '<strong>public.tbl_person: '.$db->db_last_error().'</strong><br>';
			else
				echo 'public.tbl_person: svnr auf varchar(16) erhöht<br>';

			//********************************CREATE ALL VIEWS AGAIN********************************
			echo "<br><br>NEU ANLEGEN:<br>";
			if(!create_all_views_recursively($db, $views)){$success = false;}

			if($success)
				$db->db_query('COMMIT');
			else
				$db->db_query('ROLLBACK');
		}
	}
}



//RECURSIVE DROP FUNCTIONS
function recursiveDrop($db, &$allviews, $lastcount)
{

	$nc = 0;
	foreach($allviews as $vk => $v)
	{
		if(!$allviews[$vk]["dropped"])
		{
			$db->db_query('SAVEPOINT drop_'.$v['schema'].'_'.$v['viewname'].';');

			$qry_drp_view = "DROP VIEW ".$vk.";";
			if(@$db->db_query($qry_drp_view))
			{
				echo $vk ." DROPPED<br>";
				$allviews[$vk]["dropped"] = true;
				continue;
			}
			$nc ++;		//count the not dropped
			$db->db_query('ROLLBACK TO drop_'.$v['schema'].'_'.$v['viewname'].';');
		}
	}

	if($lastcount == 0)
		return true;


	if($nc == $lastcount)
	{
		echo "<br><br>ENDLESS!<br>";
		printAllUndroppedViews($allviews);
		return false;
	}



	$lastcount = $nc;
	return recursiveDrop($db, $allviews, $lastcount);
}




function drop_all_views_recursively($db, &$allviews)
{
	return recursiveDrop($db, $allviews, count($allviews));
}




function printAllUndroppedViews($allviews)
{
	foreach($allviews as $vk => $v)
	{
		if(!$v["dropped"])
		{
			echo $vk.'<br>';
			//var_dump($v);
		}
	}
}





//RECURSIVE CREATE FUNCTIONS
function recursiveCreate($db, &$allviews, $lastcount)
{
	$nc = 0;
	foreach($allviews as $vk => $v)
	{
		if($allviews[$vk]["dropped"])
		{
			$db->db_query('SAVEPOINT create_'.$v['schema'].'_'.$v['viewname'].';');

			$qry_drp_view = "CREATE VIEW ".$vk." AS ".$v["definition"].";";
			if($v["comment"] != "")
			{
				$qry_drp_view .= "COMMENT ON VIEW $vk IS '".$v["comment"]."';";
			}

			if(@$db->db_query($qry_drp_view))
			{
				echo $vk ." CREATED<br>";

				foreach($v["privileges"] as $p)
				{
					$qry_add_privileges = "GRANT ".$p["privilege_type"]." ON ".$vk." TO ".$p["grantee"].";";
					if(!$db->db_query($qry_add_privileges))
						echo "<div style='color:red;'> ACHTUNG: Konnte ".$p["grantee"]." keine ".$p["privilege_type"]." rechte an $vk gewähren!</div>";
				}

				$allviews[$vk]["dropped"] = false;
				continue;
			}
			$nc ++;		//count the not created
			$db->db_query('ROLLBACK TO create_'.$v['schema'].'_'.$v['viewname'].';');
		}
	}

	if($lastcount == 0)
		return true;



	if($nc == $lastcount)
	{
		echo "<br><br>ENDLESS!<br>";
		printAllDroppedViews($allviews);
		return false;
	}


	$lastcount = $nc;
	return recursiveCreate($db, $allviews, $lastcount);
}




function create_all_views_recursively($db, &$allviews)
{
	return recursiveCreate($db, $allviews, count($allviews));
}




function printAllDroppedViews($allviews)
{
	foreach($allviews as $vk => $v)
	{
		if($v["dropped"])
		{
			echo $vk.'<br>';
			//var_dump($v);
		}
	}
}

/***********************************************************ENDE SVNR ÄNDERUNG***********************************************************/


//SVNR check auf char_length(16) || char_length(10) einfuegen

if($result = @$db->db_query("SELECT * FROM information_schema.table_constraints WHERE constraint_schema='public' AND table_name='tbl_person' AND constraint_name='chk_person_svnr' LIMIT 1;"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "ALTER TABLE public.tbl_person ADD CONSTRAINT chk_person_svnr CHECK ((char_length(svnr) = 10) OR (char_length(svnr) = 16) OR svnr IS NULL);";

		if(!$db->db_query($qry))
		{
			echo '<strong>public.tbl_person: '.$db->db_last_error().'</strong><br>';
			$qry = "SELECT * FROM public.tbl_person WHERE char_length(svnr) != 10 AND char_length(svnr) != 16 AND svnr IS NOT NULL;";
			$res = $db->db_query($qry);
			while($r = $db->db_fetch_object($res))
				echo $r->person_id . ": " . $r->vorname . " " . $r->nachname . ": '" . $r->svnr."'<br>";
		}
		else
			echo 'public.tbl_person: Spalte svnr: Check auf char_length(10) oder char_length(16) hinzugefuegt';
	}
}

//uhrzeit zu tbl_abschlusspruefung hinzufuegen
if(!$result = @$db->db_query("SELECT uhrzeit from lehre.tbl_abschlusspruefung LIMIT 1;"))
{
	$qry="ALTER TABLE lehre.tbl_abschlusspruefung ADD COLUMN uhrzeit time;";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_abschlusspruefung: '.$db->db_last_error().'</strong><br>';
	else
		echo 'lehre.tbl_abschlusspruefung: spalte uhrzeit hinzugefügt';
}

//Tabelle lehre.tbl_studienordnungstatus
if (!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_studienordnungstatus LIMIT 1;"))
{
		$qry = "CREATE TABLE lehre.tbl_studienordnungstatus
			(
				status_kurzbz varchar(32) NOT NULL,
				bezeichnung varchar(256),
				reihenfolge integer
			);

		ALTER TABLE lehre.tbl_studienordnungstatus ADD CONSTRAINT pk_studienordnungstatus PRIMARY KEY (status_kurzbz);

		GRANT SELECT ON lehre.tbl_studienordnungstatus TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_studienordnungstatus TO vilesci;

		INSERT INTO lehre.tbl_studienordnungstatus (status_kurzbz, bezeichnung, reihenfolge) VALUES ('development', 'in Bearbeitung', 1);
		INSERT INTO lehre.tbl_studienordnungstatus (status_kurzbz, bezeichnung, reihenfolge) VALUES ('review', 'in Review', 2);
		INSERT INTO lehre.tbl_studienordnungstatus (status_kurzbz, bezeichnung, reihenfolge) VALUES ('approved', 'genehmigt', 3);
		INSERT INTO lehre.tbl_studienordnungstatus (status_kurzbz, bezeichnung, reihenfolge) VALUES ('expired', 'ausgelaufen', 4);
		INSERT INTO lehre.tbl_studienordnungstatus (status_kurzbz, bezeichnung, reihenfolge) VALUES ('notApproved', 'nicht genehmigt', 5);
	";

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_studienordnungstatus: ' . $db->db_last_error() . '</strong><br>';
	else
		echo ' lehre.tbl_studienordnungstatus: Tabelle hinzugefuegt<br>';
}

//Spalte status_kurzbz in lehre.tbl_studienordnung
if (!$result = @$db->db_query("SELECT status_kurzbz FROM lehre.tbl_studienordnung LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_studienordnung ADD COLUMN status_kurzbz varchar(32);
		ALTER TABLE lehre.tbl_studienordnung ADD CONSTRAINT fk_studienordnung_status_kurzbz FOREIGN KEY (status_kurzbz) REFERENCES lehre.tbl_studienordnungstatus (status_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		UPDATE lehre.tbl_studienordnung SET status_kurzbz = 'approved';
		";

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_studienordnung: ' . $db->db_last_error() . '</strong><br>';
	else
		echo ' lehre.tbl_studienordnung: Spalte status_kurzbz hinzugefügt.<br>';
}

//Spalte standort_id in lehre.tbl_studienordnung
if (!$result = @$db->db_query("SELECT standort_id FROM lehre.tbl_studienordnung LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_studienordnung ADD COLUMN standort_id integer;
		ALTER TABLE lehre.tbl_studienordnung ADD CONSTRAINT studienordnung_standort_id FOREIGN KEY (standort_id) REFERENCES public.tbl_standort (standort_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		";

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_studienordnung: ' . $db->db_last_error() . '</strong><br>';
	else
		echo ' lehre.tbl_studienordnung: Spalte standort_id hinzugefügt.<br>';
}

//Spalte ects_stpl in lehre.tbl_studienplan
if (!$result = @$db->db_query("SELECT ects_stpl FROM lehre.tbl_studienplan LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_studienplan ADD COLUMN ects_stpl numeric(5,2);";

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_studienplan: ' . $db->db_last_error() . '</strong><br>';
	else
		echo ' lehre.tbl_studienplan: Spalte ects_stpl hinzugefügt.<br>';
}

//Spalte pflicht_sws in lehre.tbl_studienplan
if (!$result = @$db->db_query("SELECT pflicht_sws FROM lehre.tbl_studienplan LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_studienplan ADD COLUMN pflicht_sws integer;";

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_studienplan: ' . $db->db_last_error() . '</strong><br>';
	else
		echo ' lehre.tbl_studienplan: Spalte pflicht_sws hinzugefügt.<br>';
}

//Spalte pflicht_lvs in lehre.tbl_studienplan
if (!$result = @$db->db_query("SELECT pflicht_lvs FROM lehre.tbl_studienplan LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_studienplan ADD COLUMN pflicht_lvs integer;";

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_studienplan: ' . $db->db_last_error() . '</strong><br>';
	else
		echo ' lehre.tbl_studienplan: Spalte pflicht_lvs hinzugefügt.<br>';
}

// Tabelle Studienplan_Semester
if (!$result = @$db->db_query("SELECT 1 FROM lehre.tbl_studienplan_semester LIMIT 1;"))
{
		$qry = "CREATE TABLE lehre.tbl_studienplan_semester
			(
				studienplan_semester_id integer NOT NULL,
				studienplan_id integer NOT NULL,
				studiensemester_kurzbz varchar(16) NOT NULL,
				semester smallint NOT NULL
			);

		CREATE SEQUENCE lehre.tbl_studienplan_semester_studienplan_semester_id
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE lehre.tbl_studienplan_semester ADD CONSTRAINT pk_studienplan_semester PRIMARY KEY (studienplan_semester_id);
		ALTER TABLE lehre.tbl_studienplan_semester ALTER COLUMN studienplan_semester_id SET DEFAULT nextval('lehre.tbl_studienplan_semester_studienplan_semester_id');

		ALTER TABLE lehre.tbl_studienplan_semester ADD CONSTRAINT fk_studienplan_semester_studienplan_id FOREIGN KEY (studienplan_id) REFERENCES lehre.tbl_studienplan (studienplan_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE lehre.tbl_studienplan_semester ADD CONSTRAINT fk_studienplan_semester_studiensemester FOREIGN KEY (studiensemester_kurzbz) REFERENCES public.tbl_studiensemester (studiensemester_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON lehre.tbl_studienplan_semester TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON lehre.tbl_studienplan_semester TO vilesci;
		GRANT SELECT, UPDATE ON lehre.tbl_studienplan_semester_studienplan_semester_id TO vilesci;
	";

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_studienplan_semester: ' . $db->db_last_error() . '</strong><br>';
	else
		echo ' lehre.tbl_studienplan_semester: Tabelle hinzugefuegt<br>';


	$qry_select = "SELECT * from lehre.tbl_studienordnung_semester JOIN lehre.tbl_studienplan using(studienordnung_id);";
	if($res_select = $db->db_query($qry_select))
	{
		$studienplan_semester_insert_error = false;
		while($row = $db->db_fetch_object($res_select))
		{
			$qry_insert = "INSERT INTO lehre.tbl_studienplan_semester (studienplan_id, studiensemester_kurzbz, semester) VALUES (".$db->db_add_param($row->studienplan_id).",".$db->db_add_param($row->studiensemester_kurzbz).",".$db->db_add_param($row->semester, FHC_INTEGER).");";
			if(!$db->db_query($qry_insert))
				$studienplan_semester_insert_error = true;
		}

		if ($studienplan_semester_insert_error)
			echo '<strong>lehre.tbl_studienplan_semester Konvertierung: ' . $db->db_last_error() . '</strong><br>';
		else
			echo ' lehre.tbl_studienplan_semester Konvertierung: abgeschlossen<br>';
	}

}

//Tabelle public.tbl_bewerbungstermine
if (!$result = @$db->db_query("SELECT 1 FROM public.tbl_bewerbungstermine LIMIT 1;"))
{
		$qry = "CREATE TABLE public.tbl_bewerbungstermine
			(
				bewerbungstermin_id integer NOT NULL,
				studiengang_kz integer NOT NULL,
				studiensemester_kurzbz varchar(16) NOT NULL,
				beginn timestamp,
				ende timestamp,
				nachfrist boolean default false,
				nachfrist_ende timestamp,
				anmerkung text,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

			CREATE SEQUENCE public.tbl_bewerbungstermine_bewerbungstermin_id_seq
			INCREMENT BY 1
			NO MAXVALUE
			NO MINVALUE
			CACHE 1;

		ALTER TABLE public.tbl_bewerbungstermine ADD CONSTRAINT pk_bewerbungstermin_id PRIMARY KEY (bewerbungstermin_id);
		ALTER TABLE public.tbl_bewerbungstermine ALTER COLUMN bewerbungstermin_id SET DEFAULT nextval('public.tbl_bewerbungstermine_bewerbungstermin_id_seq');
		ALTER TABLE public.tbl_bewerbungstermine ADD CONSTRAINT fk_bewerbungstermin_studiensemester FOREIGN KEY (studiensemester_kurzbz) REFERENCES public.tbl_studiensemester (studiensemester_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE public.tbl_bewerbungstermine ADD CONSTRAINT fk_bewerbungstermin_studiengang FOREIGN KEY (studiengang_kz) REFERENCES public.tbl_studiengang (studiengang_kz) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON public.tbl_bewerbungstermine TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_bewerbungstermine TO vilesci;
		GRANT SELECT, UPDATE ON public.tbl_bewerbungstermine_bewerbungstermin_id_seq TO vilesci;
	";

	if (!$db->db_query($qry))
		echo '<strong>public.tbl_bewerbungstermine: ' . $db->db_last_error() . '</strong><br>';
	else
		echo ' public.tbl_bewerbungstermine: Tabelle hinzugefuegt<br>';
}

//Tabelle lehre.tbl_studienplatz Spalte APZ
if (!$result = @$db->db_query("SELECT APZ FROM lehre.tbl_studienplatz LIMIT 1;"))
{
		$qry = "ALTER TABLE lehre.tbl_studienplatz ADD COLUMN APZ integer;";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_studienplatz '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte APZ in lehre.tbl_studienplatz hinzugefügt';
}

//Tabelle lehre.tbl_studienplatz Spalte studienplan_id
if (!$result = @$db->db_query("SELECT studienplan_id FROM lehre.tbl_studienplatz LIMIT 1;"))
{
		$qry = "ALTER TABLE lehre.tbl_studienplatz ADD COLUMN studienplan_id integer;
		ALTER TABLE lehre.tbl_studienplatz ADD CONSTRAINT fk_studienplatz_studienplan FOREIGN KEY (studienplan_id) REFERENCES lehre.tbl_studienplan (studienplan_id) ON DELETE RESTRICT ON UPDATE CASCADE;";

	if(!$db->db_query($qry))
		echo '<strong>lehre.tbl_studienplatz '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte studienplan_id in lehre.tbl_studienplatz hinzugefügt';
}


//Tabelle lehre.tbl_studienplatz Spalte studienplan_id
if ($result = @$db->db_query("SELECT studienplan_id FROM lehre.tbl_studienplatz WHERE studienplan_id IS NOT NULL;"))
{
	if(!$db->db_num_rows($result))
	{
		$result = @$db->db_query("SELECT studienplan_id FROM lehre.tbl_studienplatz WHERE studienplan_id IS NULL;");
		$count = $db->db_num_rows($result);
		echo "<br>Insgesamt <span style='color:green;'>$count</span> zu bearbeitende Einträge in tbl_studienplatz gefunden<br>";

		$qry = "
			Select *,
			(
				SELECT studienplan_id FROM lehre.tbl_studienplan
				JOIN lehre.tbl_studienordnung using(studienordnung_id)
				WHERE studiengang_kz=tbl_studienplatz.studiengang_kz
				AND tbl_studienplan.orgform_kurzbz=tbl_studienplatz.orgform_kurzbz
				AND EXISTS
				(
					SELECT 1 FROM lehre.tbl_studienordnung_semester
					WHERE studienordnung_id=tbl_studienplan.studienordnung_id
					AND studiensemester_kurzbz=tbl_studienplatz.studiensemester_kurzbz
				) lIMIT 1
			) as studienplan_id_neu
			FROM lehre.tbl_studienplatz;
		";

		if(!$result = $db->db_query($qry))
			die('<strong>lehre.tbl_studienplatz '.$db->db_last_error().'</strong><br>');

		$count_not_found = 0;

		while($row = $db->db_fetch_object($result))
		{
			//handle null
			if($row->studienplan_id_neu !== null)
			{
				//look if found the studienplan exists
				$qry_search = "
					SELECT *
					FROM lehre.tbl_studienplan
						WHERE studienplan_id=".$db->db_add_param($row->studienplan_id_neu, FHC_INTEGER).";";

				if($result_search = $db->db_query($qry_search))
				{
					$tmpFoundRows = $db->db_num_rows($result_search);
					if($tmpFoundRows == 1)
					{
						//one entry found (=success)
						$qry_update = "UPDATE lehre.tbl_studienplatz SET studienplan_id = ".$db->db_add_param($row->studienplan_id_neu, FHC_INTEGER)."
							WHERE studienplatz_id=".$db->db_add_param($row->studienplatz_id, FHC_INTEGER).";";

						if($result_update = $db->db_query($qry_update))
						{
							continue;
						}
						echo "<strong>" . $row->studienplan_id_neu . ": fehler beim update!</strong><br>";
					}
					else if($tmpFoundRows < 1)
					{
						echo "<strong>" . $row->studienplan_id_neu . " nicht gefunden!</strong><br>";
					}
					else
					{
						echo "<strong>" . $row->studienplan_id_neu . " gibt es mehr als ein mal!</strong><br>";
					}
				}
			}

			$count_not_found ++;
		}


		//calculate the quote
		if($count_not_found)
			$quote = ($count_not_found)/$count*100;
		else
			$quote = 0;
		echo "<strong>unbehandelte:</strong> <span style='color:red;'>" . $count_not_found . "</span><br>";
		echo "<strong>Die Quote beträgt:</strong> <span style='color:red;'>" . (100-round($quote, 4)) . "%</span><br>";


		$qry_updated = "SELECT * FROM lehre.tbl_studienplatz WHERE studienplan_id IS NOT NULL;";
		if($result_updated = $db->db_query($qry_updated))
		{
			while($row = $db->db_fetch_object($result_updated))
				echo "Für STG $row->studiengang_kz wurde studienplan_id $row->studienplan_id eingesetzt<br>";
		}
	}
}



//Tabelle bis.tbl_zgvgruppe
if (!$result = @$db->db_query("SELECT 1 FROM bis.tbl_zgvgruppe LIMIT 1;"))
{
	$qry = "
		CREATE TABLE bis.tbl_zgvgruppe
		(
			gruppe_kurzbz varchar(16),
			bezeichnung varchar(256)
		);

		ALTER TABLE bis.tbl_zgvgruppe ADD CONSTRAINT uk_zgvgruppe_gruppe_kurzbz UNIQUE (gruppe_kurzbz);

		GRANT SELECT ON bis.tbl_zgvgruppe TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON bis.tbl_zgvgruppe TO vilesci;
	";

	if (!$db->db_query($qry))
		echo '<strong>bis.tbl_zgvgruppe: ' . $db->db_last_error() . '</strong><br>';
	else
		echo 'bis.tbl_zgvgruppe: Tabelle hinzugefuegt<br>';
}



//Tabelle bis.tbl_zgvgruppe_zuordnung
if (!$result = @$db->db_query("SELECT 1 FROM bis.tbl_zgvgruppe_zuordnung LIMIT 1;"))
{
	$qry = "
		CREATE TABLE bis.tbl_zgvgruppe_zuordnung
		(
			zgvgruppe_id integer NOT NULL,
			studiengang_kz integer,
			zgv_code integer,
			zgvmas_code integer,
			gruppe_kurzbz varchar(16)
		);

		CREATE SEQUENCE bis.tbl_zgvgruppe_zuordnung_zgvgruppe_id_seq
			INCREMENT BY 1
			NO MAXVALUE
			NO MINVALUE
			CACHE 1;

		ALTER TABLE bis.tbl_zgvgruppe_zuordnung ALTER COLUMN zgvgruppe_id SET DEFAULT nextval('bis.tbl_zgvgruppe_zuordnung_zgvgruppe_id_seq');
		ALTER TABLE bis.tbl_zgvgruppe_zuordnung ADD CONSTRAINT pk_zgvgruppe_id PRIMARY KEY (zgvgruppe_id);

		ALTER TABLE bis.tbl_zgvgruppe_zuordnung ADD CONSTRAINT fk_zgvgruppe_zuordnung_studiengang FOREIGN KEY (studiengang_kz) REFERENCES public.tbl_studiengang (studiengang_kz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE bis.tbl_zgvgruppe_zuordnung ADD CONSTRAINT fk_zgvgruppe_zuordnung_zgv FOREIGN KEY (zgv_code) REFERENCES bis.tbl_zgv (zgv_code) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE bis.tbl_zgvgruppe_zuordnung ADD CONSTRAINT fk_zgvgruppe_zuordnung_zgvmaster FOREIGN KEY (zgvmas_code) REFERENCES bis.tbl_zgvmaster (zgvmas_code) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE bis.tbl_zgvgruppe_zuordnung ADD CONSTRAINT fk_zgvgruppe_zuordnung_zgvgruppe FOREIGN KEY (gruppe_kurzbz) REFERENCES bis.tbl_zgvgruppe (gruppe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON bis.tbl_zgvgruppe_zuordnung TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON bis.tbl_zgvgruppe_zuordnung TO vilesci;
		GRANT SELECT, UPDATE ON bis.tbl_zgvgruppe_zuordnung_zgvgruppe_id_seq TO vilesci;
	";

	if (!$db->db_query($qry))
		echo '<strong>bis.tbl_zgvgruppe_zuordnung: ' . $db->db_last_error() . '</strong><br>';
	else
		echo 'bis.tbl_zgvgruppe_zuordnung: Tabelle hinzugefuegt<br>';
}


// Neue Spalte anzahl_ma bei fue.tbl_projekt
if(!@$db->db_query("SELECT anzahl_ma FROM fue.tbl_projekt LIMIT 1"))
{
	$qry = "
	ALTER TABLE fue.tbl_projekt ADD COLUMN anzahl_ma integer;
	";

	if(!$db->db_query($qry))
		echo '<strong>fue.tbl_projekt '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte anzahl_ma in fue.tbl_projekt hinzugefügt';
}


// Neue Spalte aufwand_pt bei fue.tbl_projekt
if(!@$db->db_query("SELECT aufwand_pt FROM fue.tbl_projekt LIMIT 1"))
{
	$qry = "
	ALTER TABLE fue.tbl_projekt ADD COLUMN aufwand_pt integer;
	";

	if(!$db->db_query($qry))
		echo '<strong>fue.tbl_projekt '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte aufwand_pt in fue.tbl_projekt hinzugefügt';
}

//Tabelle public.tbl_studienjahr
if (!$result = @$db->db_query("SELECT 1 FROM public.tbl_studienjahr LIMIT 1;"))
{
	$qry = "
		CREATE TABLE public.tbl_studienjahr
		(
			studienjahr_kurzbz varchar(16) NOT NULL,
			bezeichnung varchar(64)

		);

		ALTER TABLE public.tbl_studienjahr ADD CONSTRAINT pk_studienjahr_kurzbz PRIMARY KEY (studienjahr_kurzbz);

		INSERT INTO public.tbl_studienjahr (studienjahr_kurzbz) SELECT DISTINCT s.studienjahr_kurzbz FROM public.tbl_studiensemester s WHERE s.studienjahr_kurzbz is not null;

		ALTER TABLE public.tbl_studiensemester ADD CONSTRAINT fk_studiensemester_studienjahr FOREIGN KEY (studienjahr_kurzbz) REFERENCES public.tbl_studienjahr (studienjahr_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON public.tbl_studienjahr TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_studienjahr TO vilesci;
	";

	if (!$db->db_query($qry))
		echo '<strong>public.tbl_studienjahr: ' . $db->db_last_error() . '</strong><br>';
	else
		echo 'public.tbl_studienjahr: Tabelle hinzugefuegt<br>';
}

//Spalte genehmigung in lehre.tbl_studienplan_lehrveranstaltung
if (!$result = @$db->db_query("SELECT genehmigung FROM lehre.tbl_studienplan_lehrveranstaltung LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_studienplan_lehrveranstaltung ADD COLUMN genehmigung BOOLEAN DEFAULT TRUE;";

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_studienplan_lehrveranstaltung: ' . $db->db_last_error() . '</strong><br>';
	else
		echo ' lehre.tbl_studienplan_lehrveranstaltung: Spalte genehmigung hinzugefügt.<br>';
}

//Spalte pruefungstyp_kurzbz in campus.tbl_pruefungsanmeldung
if (!$result = @$db->db_query("SELECT pruefungstyp_kurzbz FROM campus.tbl_pruefungsanmeldung LIMIT 1;"))
{
	$qry = "ALTER TABLE campus.tbl_pruefungsanmeldung ADD COLUMN pruefungstyp_kurzbz varchar(16);
		ALTER TABLE campus.tbl_pruefungsanmeldung ADD CONSTRAINT fk_pruefungsanmeldung_pruefungstyp_pruefungstyp_kurzbz FOREIGN KEY (pruefungstyp_kurzbz) REFERENCES lehre.tbl_pruefungstyp(pruefungstyp_kurzbz) ON DELETE CASCADE ON UPDATE CASCADE;";

	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_pruefungsanmeldung: ' . $db->db_last_error() . '</strong><br>';
	else
		echo ' campus.tbl_pruefungsanmeldung: Spalte pruefungstyp_kurzbz hinzugefügt.<br>';
}

// Neue Spalte bezeichnung_mehrsprachig bei tbl_status
if(!@$db->db_query("SELECT bezeichnung_mehrsprachig FROM public.tbl_status LIMIT 1"))
{
	$qry = " ALTER TABLE public.tbl_status ADD COLUMN bezeichnung_mehrsprachig varchar(255)[];
			update tbl_status set bezeichnung_mehrsprachig = cast('{'||status_kurzbz||','||status_kurzbz||'}' as varchar[]);";


	if(!$db->db_query($qry))
		echo '<strong>public.tbl_status '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte bezeichnung_mehrsprachig in public.tbl_status hinzugefügt<br>Die ersten beiden Sprachen wurden vorbefüllt. Weitere Übersetzungen sind zu ergänzen!<br>';
}

// Remove NOT NULL constraint on verfasser_uid on public.tbl_notiz
if($result = @$db->db_query("SELECT is_nullable FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'public' AND TABLE_NAME = 'tbl_notiz' AND COLUMN_NAME = 'verfasser_uid' AND is_nullable = 'YES'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = " ALTER TABLE public.tbl_notiz ALTER COLUMN verfasser_uid DROP NOT NULL;";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_notiz '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Removed NOT NULL constraint on "verfasser_uid" from public.tbl_notiz<br>';
	}
}

// LAS Spalte von Smallint auf numeric(5,2)
if($result = $db->db_query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='lehre' AND TABLE_NAME='tbl_lehrveranstaltung' AND COLUMN_NAME = 'las' AND DATA_TYPE='smallint' "))
{
	if($db->db_num_rows($result)>0)
	{
		$qry = " ALTER TABLE lehre.tbl_lehrveranstaltung ALTER COLUMN las TYPE numeric(5,2);";

		if(!$db->db_query($qry))
			echo '<strong>lehre.tbl_lehrveranstaltung '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte las in lehre.tbl_lehrveranstaltung von smallint auf numeric(5,2) geändert<br>';
	}
}

// Fehlender FK bei tbl_studienplan_lehrveranstaltung
if($result = $db->db_query("SELECT * FROM information_schema.table_constraints WHERE constraint_name='fk_studienplan_lehrveranstaltung_parent_id'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "ALTER TABLE lehre.tbl_studienplan_lehrveranstaltung ADD CONSTRAINT fk_studienplan_lehrveranstaltung_parent_id FOREIGN KEY (studienplan_lehrveranstaltung_id_parent) REFERENCES lehre.tbl_studienplan_lehrveranstaltung(studienplan_lehrveranstaltung_id) ON DELETE RESTRICT ON UPDATE CASCADE;";

		if(!$db->db_query($qry))
			echo '<strong>lehre.tbl_studienplan_lehrveranstaltung '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Fehlenden FK bei Tabelle lehre.tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id_parent gesetzt<br>';
	}
}

//Tabelle bis.tbl_zgvgruppe_zuordnung
if (!$result = @$db->db_query("SELECT 1 FROM bis.tbl_zgvgruppe_zuordnung LIMIT 1;"))
{
	$qry = "
		CREATE TABLE bis.tbl_zgvgruppe_zuordnung
		(
			zgvgruppe_id integer NOT NULL,
			studiengang_kz integer,
			zgv_code integer,
			zgvmas_code integer,
			gruppe_kurzbz varchar(16)
		);

		CREATE SEQUENCE bis.tbl_zgvgruppe_zuordnung_zgvgruppe_id_seq
			INCREMENT BY 1
			NO MAXVALUE
			NO MINVALUE
			CACHE 1;

		ALTER TABLE bis.tbl_zgvgruppe_zuordnung ALTER COLUMN zgvgruppe_id SET DEFAULT nextval('bis.tbl_zgvgruppe_zuordnung_zgvgruppe_id_seq');
		ALTER TABLE bis.tbl_zgvgruppe_zuordnung ADD CONSTRAINT pk_zgvgruppe_id PRIMARY KEY (zgvgruppe_id);

		ALTER TABLE bis.tbl_zgvgruppe_zuordnung ADD CONSTRAINT fk_zgvgruppe_zuordnung_studiengang FOREIGN KEY (studiengang_kz) REFERENCES public.tbl_studiengang (studiengang_kz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE bis.tbl_zgvgruppe_zuordnung ADD CONSTRAINT fk_zgvgruppe_zuordnung_zgv FOREIGN KEY (zgv_code) REFERENCES bis.tbl_zgv (zgv_code) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE bis.tbl_zgvgruppe_zuordnung ADD CONSTRAINT fk_zgvgruppe_zuordnung_zgvmaster FOREIGN KEY (zgvmas_code) REFERENCES bis.tbl_zgvmaster (zgvmas_code) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE bis.tbl_zgvgruppe_zuordnung ADD CONSTRAINT fk_zgvgruppe_zuordnung_zgvgruppe FOREIGN KEY (gruppe_kurzbz) REFERENCES bis.tbl_zgvgruppe (gruppe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON bis.tbl_zgvgruppe_zuordnung TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON bis.tbl_zgvgruppe_zuordnung TO vilesci;
		GRANT SELECT, UPDATE ON bis.tbl_zgvgruppe_zuordnung_zgvgruppe_id_seq TO vilesci;
	";

	if (!$db->db_query($qry))
		echo '<strong>bis.tbl_zgvgruppe_zuordnung: ' . $db->db_last_error() . '</strong><br>';
	else
		echo 'bis.tbl_zgvgruppe_zuordnung: Tabelle hinzugefuegt<br>';
}


// Neue Spalte anzahl_ma bei fue.tbl_projekt
if(!@$db->db_query("SELECT anzahl_ma FROM fue.tbl_projekt LIMIT 1"))
{
	$qry = "
	ALTER TABLE fue.tbl_projekt ADD COLUMN anzahl_ma integer;
	";

	if(!$db->db_query($qry))
		echo '<strong>fue.tbl_projekt '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte anzahl_ma in fue.tbl_projekt hinzugefügt';
}


// Neue Spalte aufwand_pt bei fue.tbl_projekt
if(!@$db->db_query("SELECT aufwand_pt FROM fue.tbl_projekt LIMIT 1"))
{
	$qry = "
	ALTER TABLE fue.tbl_projekt ADD COLUMN aufwand_pt integer;
	";

	if(!$db->db_query($qry))
		echo '<strong>fue.tbl_projekt '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Spalte aufwand_pt in fue.tbl_projekt hinzugefügt';
}

//Tabelle public.tbl_studienjahr
if (!$result = @$db->db_query("SELECT 1 FROM public.tbl_studienjahr LIMIT 1;"))
{
	$qry = "
		CREATE TABLE public.tbl_studienjahr
		(
			studienjahr_kurzbz varchar(16) NOT NULL,
			bezeichnung varchar(64)

		);

		ALTER TABLE public.tbl_studienjahr ADD CONSTRAINT pk_studienjahr_kurzbz PRIMARY KEY (studienjahr_kurzbz);

		INSERT INTO public.tbl_studienjahr (studienjahr_kurzbz) SELECT DISTINCT s.studienjahr_kurzbz FROM public.tbl_studiensemester s WHERE s.studienjahr_kurzbz is not null;

		ALTER TABLE public.tbl_studiensemester ADD CONSTRAINT fk_studiensemester_studienjahr FOREIGN KEY (studienjahr_kurzbz) REFERENCES public.tbl_studienjahr (studienjahr_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON public.tbl_studienjahr TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_studienjahr TO vilesci;
	";

	if (!$db->db_query($qry))
		echo '<strong>public.tbl_studienjahr: ' . $db->db_last_error() . '</strong><br>';
	else
		echo 'public.tbl_studienjahr: Tabelle hinzugefuegt<br>';
}

//Spalte genehmigung in lehre.tbl_studienordnung_lehrveranstaltung
if (!$result = @$db->db_query("SELECT genehmigung FROM lehre.tbl_studienplan_lehrveranstaltung LIMIT 1;"))
{
	$qry = "ALTER TABLE lehre.tbl_studienplan_lehrveranstaltung ADD COLUMN genehmigung BOOLEAN DEFAULT TRUE;";

	if (!$db->db_query($qry))
		echo '<strong>lehre.tbl_studienplan_lehrveranstaltung: ' . $db->db_last_error() . '</strong><br>';
	else
		echo ' lehre.tbl_studienplan_lehrveranstaltung: Spalte genehmigung hinzugefügt.<br>';
}

//Spalte pruefungstyp_kurzbz in campus.tbl_pruefungsanmeldung
if (!$result = @$db->db_query("SELECT pruefungstyp_kurzbz FROM campus.tbl_pruefungsanmeldung LIMIT 1;"))
{
	$qry = "ALTER TABLE campus.tbl_pruefungsanmeldung ADD COLUMN pruefungstyp_kurzbz varchar(16);
		ALTER TABLE campus.tbl_pruefungsanmeldung ADD CONSTRAINT fk_pruefungsanmeldung_pruefungstyp_pruefungstyp_kurzbz FOREIGN KEY (pruefungstyp_kurzbz) REFERENCES lehre.tbl_pruefungstyp(pruefungstyp_kurzbz) ON DELETE CASCADE ON UPDATE CASCADE;";

	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_pruefungsanmeldung: ' . $db->db_last_error() . '</strong><br>';
	else
		echo ' campus.tbl_pruefungsanmeldung: Spalte pruefungstyp_kurzbz hinzugefügt.<br>';
}

// Neue Spalte bezeichnung_mehrsprachig bei tbl_status
if(!@$db->db_query("SELECT bezeichnung_mehrsprachig FROM public.tbl_status LIMIT 1"))
{
	$qry = " ALTER TABLE public.tbl_status ADD COLUMN bezeichnung_mehrsprachig varchar(255)[];
			update tbl_status set bezeichnung_mehrsprachig = cast('{'||status_kurzbz||','||status_kurzbz||'}' as varchar[]);";


	if(!$db->db_query($qry))
		echo '<strong>public.tbl_status '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Spalte bezeichnung_mehrsprachig in public.tbl_status hinzugefügt<br>Die ersten beiden Sprachen wurden vorbefüllt. Weitere Übersetzungen sind zu ergänzen!<br>';
}




/************************************  07.16 Vorlage für bewerberakt ************************************/
if($result = $db->db_query("SELECT * FROM public.tbl_vorlage WHERE vorlage_kurzbz='Bewerberakt'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry_oe = "SELECT oe_kurzbz FROM public.tbl_organisationseinheit WHERE oe_parent_kurzbz is null";
		if($result = $db->db_query($qry_oe))
		{
			$qry = "INSERT INTO public.tbl_vorlage(vorlage_kurzbz, bezeichnung, anmerkung,mimetype)
			VALUES('Bewerberakt','Bewerberakt Deckblatt', 'wird als Deckblatt fuer den Bewerberakt verwendet', 'application/vnd.oasis.opendocument.text');";

			$testQuery = "SELECT setval('seq_vorlagestudiengang_vorlagestudiengang_id', max(vorlagestudiengang_id)) FROM tbl_vorlagestudiengang;";
			$testResult = $db->db_query($testQuery);

			$text = file_get_contents('xsl/Bewerberakt.xsl');

			while($row = $db->db_fetch_object($result))
			{
				$qry.="INSERT INTO public.tbl_vorlagestudiengang(vorlage_kurzbz, studiengang_kz, version, text,
				oe_kurzbz, style, berechtigung, anmerkung_vorlagestudiengang, aktiv) VALUES(
				'Bewerberakt',0,0,".$db->db_add_param($text).",".$db->db_add_param($row->oe_kurzbz).",null,null,'',true);";
			}
		}

		if(!$db->db_query($qry))
			echo '<strong>Bewerberakt Dokumentenvorlage: '.$db->db_last_error().'</strong><br>';
		else
			echo 'Bewerberakt Dokumentenvorlage hinzugefuegt<br>';
	}
}





// public.tbl_vorlagedokument hinzufuegen
if($result = @$db->db_query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='public' AND TABLE_NAME='tbl_vorlagedokument';"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "
		CREATE TABLE public.tbl_vorlagedokument
		(
			vorlagedokument_id integer NOT NULL,
			sort integer NOT NULL,
			vorlagestudiengang_id bigint NOT NULL,
			dokument_kurzbz varchar(8) NOT NULL
		);



		CREATE SEQUENCE public.tbl_vorlagedokument_vorlagedokument_id_seq
			INCREMENT BY 1
			NO MAXVALUE
			NO MINVALUE
			CACHE 1;


		COMMENT ON TABLE public.tbl_vorlagedokument IS 'Verknuepft mehrere tbl_dokument mit einer tbl_vorlagestudiengang';
		ALTER TABLE public.tbl_vorlagedokument ADD CONSTRAINT pr_vorlagedokument_id PRIMARY KEY (vorlagedokument_id);
		ALTER TABLE public.tbl_vorlagedokument ALTER COLUMN vorlagedokument_id SET DEFAULT nextval('public.tbl_vorlagedokument_vorlagedokument_id_seq');

		ALTER TABLE public.tbl_vorlagedokument ADD CONSTRAINT fk_tbl_vorlagedokument_tbl_dokument FOREIGN KEY (dokument_kurzbz) REFERENCES public.tbl_dokument (dokument_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE public.tbl_vorlagedokument ADD CONSTRAINT fk_tbl_vorlagedokument_tbl_vorlagestudiengang FOREIGN KEY (vorlagestudiengang_id) REFERENCES public.tbl_vorlagestudiengang (vorlagestudiengang_id) ON DELETE RESTRICT ON UPDATE CASCADE;


		GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_vorlagedokument TO vilesci;
		GRANT SELECT ON public.tbl_vorlagedokument TO web;
		GRANT SELECT, UPDATE ON public.tbl_vorlagedokument_vorlagedokument_id_seq TO vilesci;
		";


		if(!$db->db_query($qry))
			echo '<strong>public.tbl_vorlagedokument: '.$db->db_last_error().'</strong><br>';
		else
			echo ' Tabelle public.tbl_vorlagedokument hinzugefuegt!<br>';
	}
}


// Neue Spalte studienplan_id in public.tbl_bewerbungstermine
if(!$result = @$db->db_query("SELECT studienplan_id FROM public.tbl_bewerbungstermine LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_bewerbungstermine ADD COLUMN studienplan_id integer;
	ALTER TABLE public.tbl_bewerbungstermine ADD CONSTRAINT fk_bewerbungstermine_studienplan_id FOREIGN KEY (studienplan_id) REFERENCES lehre.tbl_studienplan(studienplan_id) ON DELETE RESTRICT ON UPDATE CASCADE;";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_bewerbungstermine '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neue Spalte studienplan_id in Tabelle public.tbl_bewerbungstermine hinzugefügt<br>';
}


// Attribute verpflichtend und buttontext in public.tbl_ampel
if(!$result = @$db->db_query("SELECT verpflichtend FROM public.tbl_ampel LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_ampel ADD COLUMN verpflichtend boolean NOT NULL DEFAULT FALSE;
			ALTER TABLE public.tbl_ampel ADD COLUMN buttontext varchar(64)[];";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_ampel '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neue Spalten "verpflichtend" und "buttontext" in Tabelle public.tbl_ampel hinzugefügt<br>';
}

// Neue Spalte lieferbedingungen in public.tbl_firma
if(!$result = @$db->db_query("SELECT lieferbedingungen FROM public.tbl_firma LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_firma ADD COLUMN lieferbedingungen varchar(256);";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_firma '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neue Spalte lieferbedingungen in Tabelle public.tbl_firma hinzugefügt<br>';

}

// 10.16 neuer webservicetyp reports
$result = $db->db_query("SELECT 1 FROM system.tbl_webservicetyp WHERE webservicetyp_kurzbz='reports'");
if($db->db_num_rows($result) < 1)
{
	$qry = "INSERT INTO system.tbl_webservicetyp (webservicetyp_kurzbz, beschreibung) VALUES('reports', 'Logtyp fuer reports');";
	if(!$db->db_query($qry))
		echo '<strong>system.tbl_webservicetyp '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neuen Eintrag "reports" in Tabelle system.tbl_webservicetyp eingefuegt<br>';
}

// Gemeinsame Studien
if(!$result = @$db->db_query("SELECT 1 FROM bis.tbl_mobilitaet LIMIT 1"))
{
	$qry = "
	CREATE TABLE bis.tbl_mobilitaetstyp
	(
		mobilitaetstyp_kurzbz varchar(32) NOT NULL,
		bezeichnung varchar(255),
		aktiv boolean NOT NULL DEFAULT true
	);
	ALTER TABLE bis.tbl_mobilitaetstyp ADD CONSTRAINT pk_mobilitaetstyp PRIMARY KEY (mobilitaetstyp_kurzbz);
	GRANT SELECT, INSERT, UPDATE, DELETE ON bis.tbl_mobilitaetstyp TO vilesci;

	CREATE TABLE bis.tbl_gsstudientyp
	(
		gsstudientyp_kurzbz varchar(32) NOT NULL,
		bezeichnung varchar(255),
		studientyp_code varchar(64)
	);
	ALTER TABLE bis.tbl_gsstudientyp ADD CONSTRAINT pk_gsstudientyp PRIMARY KEY (gsstudientyp_kurzbz);
	GRANT SELECT, INSERT, UPDATE, DELETE ON bis.tbl_gsstudientyp TO vilesci;

	INSERT INTO bis.tbl_gsstudientyp(gsstudientyp_kurzbz, bezeichnung, studientyp_code) VALUES('Intern','Interner Studierender','I');
	INSERT INTO bis.tbl_gsstudientyp(gsstudientyp_kurzbz, bezeichnung, studientyp_code) VALUES('Extern','Externer Studierender','E');

	ALTER TABLE public.tbl_prestudent ADD COLUMN gsstudientyp_kurzbz varchar(32) DEFAULT 'Intern';
	ALTER TABLE public.tbl_prestudent ADD CONSTRAINT fk_prestudent_gsstudientyp FOREIGN KEY (gsstudientyp_kurzbz) REFERENCES bis.tbl_gsstudientyp (gsstudientyp_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

	CREATE TABLE bis.tbl_gsprogrammtyp
	(
		gsprogrammtyp_kurzbz varchar(32) NOT NULL,
		bezeichnung varchar(255),
		programmtyp_code varchar(64)
	);
	ALTER TABLE bis.tbl_gsprogrammtyp ADD CONSTRAINT pk_gsprogrammtyp PRIMARY KEY (gsprogrammtyp_kurzbz);
	GRANT SELECT, INSERT, UPDATE, DELETE ON bis.tbl_gsprogrammtyp TO vilesci;

	CREATE TABLE bis.tbl_gsprogramm
	(
		gsprogramm_id integer NOT NULL,
		bezeichnung varchar(255),
		programm_code bigint,
		gsprogrammtyp_kurzbz varchar(32)
	);
	ALTER TABLE bis.tbl_gsprogramm ADD CONSTRAINT pk_gsprogramm PRIMARY KEY (gsprogramm_id);
	GRANT SELECT, INSERT, UPDATE, DELETE ON bis.tbl_gsprogramm TO vilesci;
	ALTER TABLE bis.tbl_gsprogramm ADD CONSTRAINT fk_gsprogrammtyp_gsprogramm FOREIGN KEY (gsprogrammtyp_kurzbz) REFERENCES bis.tbl_gsprogrammtyp (gsprogrammtyp_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

	CREATE SEQUENCE bis.tbl_gsprogramm_gsprogramm_id_seq
	 INCREMENT BY 1
	 NO MAXVALUE
	 NO MINVALUE
	 CACHE 1;

	ALTER TABLE bis.tbl_gsprogramm ALTER COLUMN gsprogramm_id SET DEFAULT nextval('bis.tbl_gsprogramm_gsprogramm_id_seq');
	GRANT SELECT, UPDATE ON bis.tbl_gsprogramm_gsprogramm_id_seq TO vilesci;

	ALTER TABLE public.tbl_firma ADD COLUMN partner_code varchar(20);

	CREATE TABLE bis.tbl_mobilitaet
	(
		mobilitaet_id integer NOT NULL,
		mobilitaetstyp_kurzbz varchar(32) NOT NULL,
		prestudent_id integer NOT NULL,
		studiensemester_kurzbz varchar(16) NOT NULL,
		mobilitaetsprogramm_code integer,
		gsprogramm_id integer,
		firma_id integer,
		status_kurzbz varchar(20),
		ausbildungssemester smallint,
		insertvon varchar(32),
		insertamum timestamp,
		updateamum  timestamp,
		updatevon varchar(32)
	);
	ALTER TABLE bis.tbl_mobilitaet ADD CONSTRAINT pk_mobilitaet PRIMARY KEY (mobilitaet_id);
	GRANT SELECT, INSERT, UPDATE, DELETE ON bis.tbl_mobilitaet TO vilesci;

	ALTER TABLE bis.tbl_mobilitaet ADD CONSTRAINT fk_prestudent_mobilitaet FOREIGN KEY (prestudent_id) REFERENCES public.tbl_prestudent (prestudent_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE bis.tbl_mobilitaet ADD CONSTRAINT fk_studiensemester_mobilitaet FOREIGN KEY (studiensemester_kurzbz) REFERENCES public.tbl_studiensemester (studiensemester_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE bis.tbl_mobilitaet ADD CONSTRAINT fk_mobilitaetsprogrammcode_mobilitaet FOREIGN KEY (mobilitaetsprogramm_code) REFERENCES bis.tbl_mobilitaetsprogramm (mobilitaetsprogramm_code) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE bis.tbl_mobilitaet ADD CONSTRAINT fk_gsprogramm_mobilitaet FOREIGN KEY (gsprogramm_id) REFERENCES bis.tbl_gsprogramm (gsprogramm_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE bis.tbl_mobilitaet ADD CONSTRAINT fk_firma_mobilitaet FOREIGN KEY (firma_id) REFERENCES public.tbl_firma (firma_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE bis.tbl_mobilitaet ADD CONSTRAINT fk_status_mobilitaet FOREIGN KEY (status_kurzbz) REFERENCES public.tbl_status (status_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE bis.tbl_mobilitaet ADD CONSTRAINT fk_moblitaetstyp_mobilitaet FOREIGN KEY (mobilitaetstyp_kurzbz) REFERENCES bis.tbl_mobilitaetstyp (mobilitaetstyp_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

	CREATE SEQUENCE bis.tbl_mobilitaet_mobilitaet_id_seq
	 INCREMENT BY 1
	 NO MAXVALUE
	 NO MINVALUE
	 CACHE 1;

	ALTER TABLE bis.tbl_mobilitaet ALTER COLUMN mobilitaet_id SET DEFAULT nextval('bis.tbl_mobilitaet_mobilitaet_id_seq');
	GRANT SELECT, UPDATE ON bis.tbl_mobilitaet_mobilitaet_id_seq TO vilesci;

	INSERT INTO bis.tbl_gsprogrammtyp(gsprogrammtyp_kurzbz, bezeichnung, programmtyp_code) VALUES('Joint', 'Joint Degree','Joint');
	INSERT INTO bis.tbl_gsprogrammtyp(gsprogrammtyp_kurzbz, bezeichnung, programmtyp_code) VALUES('Double', 'Double Degree','Double');
	INSERT INTO bis.tbl_gsprogrammtyp(gsprogrammtyp_kurzbz, bezeichnung, programmtyp_code) VALUES('Multiple', 'Multiple Degree','Multiple');

	INSERT INTO bis.tbl_mobilitaetstyp(mobilitaetstyp_kurzbz, bezeichnung, aktiv) VALUES('GS', 'Gemeinsame Studien',true);
	INSERT INTO bis.tbl_mobilitaetstyp(mobilitaetstyp_kurzbz, bezeichnung, aktiv) VALUES('Incoming', 'Incoming',false);
	INSERT INTO bis.tbl_mobilitaetstyp(mobilitaetstyp_kurzbz, bezeichnung, aktiv) VALUES('Outgoing', 'Outgoing',false);
	";
	if(!$db->db_query($qry))
		echo '<strong>Gemeinsame Studien '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neue Tabelle fuer Gemeinsame Studien/Mobilitaet hinzugefügt<br>';
}

// Anmerkung bei Konto
if(!$result = @$db->db_query("SELECT anmerkung FROM public.tbl_konto LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_konto ADD COLUMN anmerkung text";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_konto '.$db->db_last_error().'</strong><br>';
	else
		echo 'public.tbl_konto: Spalte Anmerkung hinzugefuegt!<br>';
}

// Studienplan_id zu testtool.tbl_ablauf
if(!$result = @$db->db_query("SELECT studienplan_id FROM testtool.tbl_ablauf LIMIT 1"))
{
	$qry = "ALTER TABLE testtool.tbl_ablauf ADD COLUMN studienplan_id integer;
			ALTER TABLE testtool.tbl_ablauf ADD CONSTRAINT fk_studienplan_id FOREIGN KEY (studienplan_id) REFERENCES lehre.tbl_studienplan (studienplan_id) ON DELETE RESTRICT ON UPDATE CASCADE;";

	if(!$db->db_query($qry))
		echo '<strong>testtool.tbl_ablauf '.$db->db_last_error().'</strong><br>';
	else
		echo 'testtool.tbl_ablauf: Spalte studienplan_id hinzugefuegt!<br>';
}

// Aktiv-Attribut zu testtool.tbl_frage
if(!$result = @$db->db_query("SELECT aktiv FROM testtool.tbl_frage LIMIT 1"))
{
	$qry = "ALTER TABLE testtool.tbl_frage ADD COLUMN aktiv boolean DEFAULT TRUE;
			UPDATE testtool.tbl_frage SET aktiv=TRUE;";

	if(!$db->db_query($qry))
		echo '<strong>testtool.tbl_frage '.$db->db_last_error().'</strong><br>';
	else
		echo 'testtool.tbl_frage: Spalte aktiv hinzugefuegt und auf TRUE gesetzt!<br>';
}

// Aktiv-Attribut zu testtool.tbl_vorschlag
if(!$result = @$db->db_query("SELECT aktiv FROM testtool.tbl_vorschlag LIMIT 1"))
{
	$qry = "ALTER TABLE testtool.tbl_vorschlag ADD COLUMN aktiv boolean DEFAULT TRUE;
			UPDATE testtool.tbl_vorschlag SET aktiv=TRUE;";

	if(!$db->db_query($qry))
		echo '<strong>testtool.tbl_vorschlag '.$db->db_last_error().'</strong><br>';
	else
		echo 'testtool.tbl_vorschlag: Spalte aktiv hinzugefuegt!<br>';
}

// Bezeichnung_mehrsprachig in testtool.tbl_gebiet
if(!$result = @$db->db_query("SELECT bezeichnung_mehrsprachig FROM testtool.tbl_gebiet LIMIT 1"))
{
	$qry = "ALTER TABLE testtool.tbl_gebiet ADD COLUMN bezeichnung_mehrsprachig varchar(255)[];";

	if(!$db->db_query($qry))
		echo '<strong>testtool.tbl_gebiet '.$db->db_last_error().'</strong><br>';
	else
		echo 'testtool.tbl_gebiet: Spalte bezeichnung_mehrsprachig hinzugefuegt!<br>';

	// Bezeichnung_mehrsprachig aus existierender Bezeichnung vorausfuellen. Ein Eintrag fuer jede Sprache mit Content aktiv.
	$qry_help = "SELECT index FROM public.tbl_sprache WHERE content=TRUE;";
	if(!$result = $db->db_query($qry_help))
		echo '<strong>tbl_gebiet bezeichnung_mehrsprachig: Fehler beim ermitteln der Sprachen: '.$db->db_last_error().'</strong>';
	else
	{
		$qry='';
		while($row = $db->db_fetch_object($result))
			$qry.= "UPDATE testtool.tbl_gebiet set bezeichnung_mehrsprachig[".$row->index."] = bezeichnung;";

		if(!$db->db_query($qry))
			echo '<strong>Setzen der bezeichnung_mehrsprachig fehlgeschlagen: '.$db->db_last_error().'</strong><br>';
		else
			echo 'testtool.tbl_gebiet: bezeichnung_mehrprachig automatisch aus existierender Bezeichnung uebernommen<br>';
	}
}

// Removing schema kommune
if($result = @$db->db_query("SELECT 1 FROM kommune.tbl_match"))
{
	$qry = "DROP SCHEMA kommune CASCADE";

	if(!$db->db_query($qry))
		echo '<strong>kommune '.$db->db_last_error().'</strong><br>';
	else
		echo 'kommune schema is dropped<br>';
}

// Aufnahmegruppe boolean tbl_gruppe, FK prestudent und reihungstest zu gruppe
if(!$result = @$db->db_query("SELECT aufnahmegruppe FROM public.tbl_gruppe"))
{
	$qry = "ALTER TABLE public.tbl_gruppe ADD COLUMN aufnahmegruppe boolean DEFAULT false NOT NULL;
	ALTER TABLE public.tbl_prestudent ADD COLUMN aufnahmegruppe_kurzbz varchar(32);
	ALTER TABLE public.tbl_prestudent ADD CONSTRAINT fk_prestudent_aufnahmegruppe FOREIGN KEY (aufnahmegruppe_kurzbz) REFERENCES public.tbl_gruppe (gruppe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	ALTER TABLE public.tbl_reihungstest ADD COLUMN aufnahmegruppe_kurzbz varchar(32);
	ALTER TABLE public.tbl_reihungstest ADD CONSTRAINT fk_reihungstest_aufnahmegruppe FOREIGN KEY (aufnahmegruppe_kurzbz) REFERENCES public.tbl_gruppe (gruppe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;";

	if(!$db->db_query($qry))
		echo '<strong>aufnahmegruppe '.$db->db_last_error().'</strong><br>';
	else
		echo 'Boolean aufnahmegruppe zu Gruppen hinzugefuegt. aufnahmegruppe_kurzbz zu tbl_prestudent und tbl_reihungstest hinzugefuegt<br>';
}

// Removing column "r" from tbl_statistik
if ($result = @$db->db_query("SELECT r FROM public.tbl_statistik LIMIT 1;"))
{
	$qry = "ALTER TABLE public.tbl_statistik DROP COLUMN r;";

	if (!$db->db_query($qry))
		echo '<strong>public.tbl_statistik: ' . $db->db_last_error() . '</strong><br>';
	else
		echo ' public.tbl_statistik: Spalte r entfernt.<br>';
}


// Removing column "php" from tbl_statistik
if ($result = @$db->db_query("SELECT php FROM public.tbl_statistik LIMIT 1;"))
{
	$qry = "ALTER TABLE public.tbl_statistik DROP COLUMN php;";

	if (!$db->db_query($qry))
		echo '<strong>public.tbl_statistik: ' . $db->db_last_error() . '</strong><br>';
	else
		echo ' public.tbl_statistik: Spalte php entfernt.<br>';
}

// vorlage_kurzbz von 16 auf 32 Zeichen
if($result = $db->db_query("SELECT character_maximum_length FROM information_schema.columns WHERE column_name='vorlage_kurzbz' AND table_name='tbl_vorlage' AND table_schema='public';"))
{
	if($row = $db->db_fetch_object($result))
	{
		if($row->character_maximum_length==16)
		{
			$qry = "ALTER TABLE public.tbl_vorlage ALTER COLUMN vorlage_kurzbz TYPE varchar(32);
			ALTER TABLE public.tbl_vorlagestudiengang ALTER COLUMN vorlage_kurzbz TYPE varchar(32);
			";
			if(!$db->db_query($qry))
				echo '<strong>public.tbl_vorlage: '.$db->db_last_error().'</strong><br>';
			else
				echo 'public.tbl_vorlage: Spalte vorlage_kurzbz auf 32 Zeichen verlaengert<br>';
		}
	}
}

// Nummer in campus.tbl_beispiel von smallint auf integer aendern
if($result = $db->db_query("SELECT data_type FROM information_schema.columns WHERE column_name='nummer' AND table_name='tbl_beispiel' AND table_schema='campus';"))
{
	if($row = $db->db_fetch_object($result))
	{
		if($row->data_type=='smallint')
		{
			$qry = "ALTER TABLE campus.tbl_beispiel ALTER COLUMN nummer TYPE integer;";

			if(!$db->db_query($qry))
				echo '<strong>campus.tbl_beispiel: '.$db->db_last_error().'</strong><br>';
			else
				echo 'campus.tbl_beispiel: Spalte nummer von smallint auf integer geändert<br>';
		}
	}
}

// Index fuer prestudent und prestudentstatus
if($result = $db->db_query("SELECT * FROM pg_class WHERE relname='idx_prestudent_person'"))
{
	if($db->db_num_rows($result)==0)
	{

		$qry = "
		CREATE INDEX idx_prestudent_person ON public.tbl_prestudent USING btree (person_id);
		CREATE INDEX idx_prestudentstatus_prestudent ON public.tbl_prestudentstatus USING btree (prestudent_id);
		";

		if(!$db->db_query($qry))
			echo '<strong>Indizes: '.$db->db_last_error().'</strong><br>';
		else
			echo 'Diverse Indizes fuer Prestudent und Prestudentstatus hinzugefuegt';
	}
}

// *********** CI ***************
// public.tbl_ci_apikey
if(!$result = @$db->db_query("SELECT 1 FROM public.ci_apikey LIMIT 1"))
{
	$qry = "
		CREATE TABLE public.ci_apikey
		(
			apikey_id serial,
			key varchar(100) NOT NULL,
			level integer,
			ignore_limits integer,
			date_created date DEFAULT now()
		);
		ALTER TABLE public.ci_apikey ADD CONSTRAINT pk_ci_apikey PRIMARY KEY (apikey_id);

		GRANT SELECT ON ci_apikey TO vilesci;
		";

	if(!$db->db_query($qry))
		echo '<strong>APIKEY: '.$db->db_last_error().'</strong><br>';
	else
		echo 'Neue Tabelle public.ci_apikey hinzugefuegt';
}

// public.tbl_msg_message
if(!$result = @$db->db_query("SELECT 1 FROM public.tbl_msg_message LIMIT 1"))
{
	$qry = "
		CREATE TABLE public.tbl_msg_message
		(
			message_id serial,
			person_id bigint,
			subject varchar(256),
			body text,
			priority smallint DEFAULT 0,
			relationmessage_id bigint,
			oe_kurzbz varchar(32),
			insertamum timestamp DEFAULT now(),
			insertvon varchar(32)
		);
		ALTER TABLE public.tbl_msg_message ADD CONSTRAINT pk_tbl_msg_message PRIMARY KEY (message_id);
		ALTER TABLE public.tbl_msg_message ADD CONSTRAINT fk_tbl_msg_message_person_id FOREIGN KEY (person_id)
			REFERENCES public.tbl_person (person_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE public.tbl_msg_message ADD CONSTRAINT fk_tbl_msg_message_relationmessage_id FOREIGN KEY (relationmessage_id)
			REFERENCES public.tbl_msg_message (message_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE public.tbl_msg_message ADD CONSTRAINT fk_tbl_msg_message_oe_kurzbz FOREIGN KEY (oe_kurzbz)
			REFERENCES public.tbl_organisationseinheit (oe_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

		COMMENT ON COLUMN public.tbl_msg_message.person_id IS 'Sender';
		COMMENT ON COLUMN public.tbl_msg_message.priority IS 'Codex in config/message.php';

		GRANT SELECT ON public.tbl_msg_message TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_msg_message TO vilesci;
		GRANT SELECT, UPDATE ON public.tbl_msg_message_message_id_seq TO vilesci;

		CREATE TABLE public.tbl_msg_recipient
		(
			message_id bigint NOT NULL,
			person_id bigint NOT NULL,
			token varchar(128),
			sent timestamp DEFAULT NULL,
			sentinfo text DEFAULT NULL,
			insertamum timestamp DEFAULT now() NOT NULL,
			insertvon varchar(32)
		);

		ALTER TABLE public.tbl_msg_recipient ADD CONSTRAINT pk_tbl_msg_recipient PRIMARY KEY (message_id, person_id);
		ALTER TABLE public.tbl_msg_recipient ADD CONSTRAINT fk_tbl_msg_recipient_person_id FOREIGN KEY (person_id)
			REFERENCES public.tbl_person (person_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE public.tbl_msg_recipient ADD CONSTRAINT fk_tbl_msg_recipient_message_id FOREIGN KEY (message_id)
			REFERENCES public.tbl_msg_message (message_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE public.tbl_msg_recipient ADD CONSTRAINT uk_tbl_msg_recipient_token UNIQUE (token);

		COMMENT ON COLUMN public.tbl_msg_recipient.person_id IS 'Receiver';
		COMMENT ON COLUMN public.tbl_msg_recipient.sent IS 'If NULL not sent, otherwise the shipping date';

		GRANT SELECT ON public.tbl_msg_recipient TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_msg_recipient TO vilesci;


		CREATE TABLE public.tbl_msg_status
		(
			message_id bigint NOT NULL,
			person_id bigint NOT NULL,
			status smallint NOT NULL,
			statusinfo text,
			insertamum timestamp DEFAULT now() NOT NULL,
			insertvon varchar(32),
			updateamum timestamp DEFAULT now() NOT NULL,
			updatevon varchar(32)
		);

		ALTER TABLE public.tbl_msg_status ADD CONSTRAINT pk_tbl_msg_status PRIMARY KEY (message_id, person_id, status);
		ALTER TABLE public.tbl_msg_status ADD CONSTRAINT fk_tbl_msg_status_person_id FOREIGN KEY (person_id)
			REFERENCES public.tbl_person (person_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE public.tbl_msg_status ADD CONSTRAINT fk_tbl_msg_status_message_id FOREIGN KEY (message_id)
			REFERENCES public.tbl_msg_message (message_id) ON DELETE RESTRICT ON UPDATE CASCADE;

		COMMENT ON COLUMN public.tbl_msg_status.person_id IS 'Receiver';

		GRANT SELECT ON public.tbl_msg_status TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_msg_status TO vilesci;

		CREATE TABLE public.tbl_msg_attachment
		(
			attachment_id serial,
			message_id bigint NOT NULL,
			name text,
			filename text
		);
		ALTER TABLE public.tbl_msg_attachment ADD CONSTRAINT pk_tbl_msg_attachment PRIMARY KEY (attachment_id);
		ALTER TABLE public.tbl_msg_attachment ADD CONSTRAINT fk_tbl_msg_attachment_message_id FOREIGN KEY (message_id)
			REFERENCES public.tbl_msg_message (message_id) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON public.tbl_msg_attachment TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_msg_attachment TO vilesci;
		GRANT SELECT ON public.tbl_msg_status TO web;
		GRANT SELECT, UPDATE ON public.tbl_msg_attachment_attachment_id_seq TO vilesci;
		";

	if(!$db->db_query($qry))
		echo '<strong>Messages: '.$db->db_last_error().'</strong><br>';
	else
		echo 'Neue Tabellen public.tbl_msg_* für Messaging hinzugefügt';
}

// system.tbl_app
if(!$result = @$db->db_query("SELECT 1 FROM system.tbl_app LIMIT 1"))
{
	$qry = "
		CREATE TABLE system.tbl_app
		(
			app varchar(32) NOT NULL
		);
		ALTER TABLE system.tbl_app ADD CONSTRAINT pk_tbl_app PRIMARY KEY (app);

		GRANT SELECT ON system.tbl_app TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON system.tbl_app TO vilesci;

		CREATE TABLE system.tbl_phrase
		(
			phrase_id serial,
			app varchar(32) NOT NULL,
			phrase varchar(64) NOT NULL,
			insertamum timestamp DEFAULT now() NOT NULL,
			insertvon varchar(32)
		);
		ALTER TABLE system.tbl_phrase ADD CONSTRAINT pk_tbl_phrase PRIMARY KEY (phrase_id);
		ALTER TABLE system.tbl_phrase ADD CONSTRAINT fk_tbl_phrase FOREIGN KEY (app)
			REFERENCES system.tbl_app (app) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON system.tbl_phrase TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON system.tbl_phrase TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON system.tbl_phrase_phrase_id_seq TO vilesci;

		CREATE TABLE system.tbl_phrasentext
		(
			phrasentext_id serial,
			phrase_id bigint NOT NULL,
			sprache varchar(32) NOT NULL,
			orgeinheit_kurzbz varchar(32),
			orgform_kurzbz varchar(32),
			text text,
			description text,
			insertamum timestamp DEFAULT now() NOT NULL,
			insertvon varchar(32)
		);
		ALTER TABLE system.tbl_phrasentext ADD CONSTRAINT pk_tbl_phrasentext PRIMARY KEY (phrasentext_id);
		ALTER TABLE system.tbl_phrasentext ADD CONSTRAINT fk_tbl_phrasentext_phrase_id FOREIGN KEY (phrase_id)
			REFERENCES system.tbl_phrase (phrase_id) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON system.tbl_phrasentext TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON system.tbl_phrasentext TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON system.tbl_phrasentext_phrasentext_id_seq TO vilesci;
		";

	if(!$db->db_query($qry))
		echo '<strong>Phrasen: '.$db->db_last_error().'</strong><br>';
	else
		echo 'Neue Tabelle system.tbl_app, system.tbl_phrase, system.tbl_phrasentext hinzugefuegt';
}

// public.tbl_vorlage.attribute
if(!$result = @$db->db_query("SELECT attribute FROM public.tbl_vorlage LIMIT 1"))
{
	$qry = "
		ALTER TABLE public.tbl_vorlage ADD COLUMN attribute json;

		ALTER TABLE public.tbl_vorlagestudiengang ADD COLUMN sprache varchar(16);
		ALTER TABLE public.tbl_vorlagestudiengang ADD COLUMN subject text;
		ALTER TABLE public.tbl_vorlagestudiengang ADD COLUMN orgform_kurzbz varchar(3);

		ALTER TABLE public.tbl_vorlagestudiengang ADD CONSTRAINT fk_tbl_vorlagestudiengang_sprache FOREIGN KEY (sprache)
			REFERENCES public.tbl_sprache (sprache) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE public.tbl_vorlagestudiengang ADD CONSTRAINT fk_tbl_vorlagestudiengang_orgform_kurzbz FOREIGN KEY (orgform_kurzbz)
			REFERENCES bis.tbl_orgform (orgform_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		";

	if(!$db->db_query($qry))
		echo '<strong>VorlageStudiengang: '.$db->db_last_error().'</strong><br>';
	else
		echo 'Spalte attribute zu tbl_vorlage hinzugefügt. Spalte sprache, subject und orgform_kurzbz zu tbl_vorlagestudiengang hinzugefügt';
}

// tbl_reihungstest
if(!$result = @$db->db_query("SELECT stufe FROM public.tbl_reihungstest LIMIT 1"))
{
	$qry = "
		ALTER TABLE public.tbl_reihungstest ADD COLUMN stufe smallint;
		ALTER TABLE public.tbl_reihungstest ADD COLUMN anmeldefrist date;

		ALTER TABLE public.tbl_ort ADD COLUMN arbeitsplaetze integer;
		ALTER TABLE public.tbl_prestudentstatus ADD COLUMN rt_stufe smallint DEFAULT NULL;

		CREATE TABLE public.tbl_rt_studienplan
		(
			reihungstest_id integer,
			studienplan_id integer
		);

		ALTER TABLE public.tbl_rt_studienplan ADD CONSTRAINT pk_tbl_rt_studienplan PRIMARY KEY (reihungstest_id, studienplan_id);
		ALTER TABLE public.tbl_rt_studienplan ADD CONSTRAINT fk_tbl_rt_studienplan_reihungstest_id FOREIGN KEY (reihungstest_id)
			REFERENCES public.tbl_reihungstest (reihungstest_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE public.tbl_rt_studienplan ADD CONSTRAINT fk_tbl_rt_studienplan_studienplan_id FOREIGN KEY (studienplan_id)
			REFERENCES lehre.tbl_studienplan (studienplan_id) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON public.tbl_rt_studienplan TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_rt_studienplan TO vilesci;

		CREATE TABLE public.tbl_rt_person
		(
			rt_person_id serial,
			person_id integer,
			rt_id integer,
			studienplan_id integer,
			anmeldedatum date,
			teilgenommen boolean DEFAULT false,
			ort_kurzbz varchar(16),
			punkte numeric(8,4) DEFAULT NULL
		);

		ALTER TABLE public.tbl_rt_person ADD CONSTRAINT pk_tbl_rt_person PRIMARY KEY (rt_person_id);
		ALTER TABLE public.tbl_rt_person ADD CONSTRAINT fk_tbl_rt_person_ort_kurzbz FOREIGN KEY (ort_kurzbz)
			REFERENCES public.tbl_ort (ort_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE public.tbl_rt_person ADD CONSTRAINT fk_tbl_rt_person_reihungstest_id FOREIGN KEY (rt_id)
			REFERENCES public.tbl_reihungstest (reihungstest_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE public.tbl_rt_person ADD CONSTRAINT fk_tbl_rt_person_studienplan_id FOREIGN KEY (studienplan_id)
			REFERENCES lehre.tbl_studienplan (studienplan_id) ON DELETE RESTRICT ON UPDATE CASCADE;

		ALTER TABLE public.tbl_rt_person ADD CONSTRAINT uk_tbl_rt_person_person_id_rt_id_studienplan_id UNIQUE (person_id, rt_id, studienplan_id);

		GRANT SELECT ON public.tbl_rt_person TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_rt_person TO vilesci;
		GRANT SELECT, UPDATE, INSERT, DELETE ON tbl_rt_person_rt_person_id_seq TO vilesci;

		CREATE TABLE public.tbl_rt_ort
		(
			rt_id integer NOT NULL,
			ort_kurzbz varchar(16) NOT NULL,
			uid varchar(32)
		);
		ALTER TABLE public.tbl_rt_ort ADD CONSTRAINT pk_tbl_rt_ort PRIMARY KEY (rt_id, ort_kurzbz);
		ALTER TABLE public.tbl_rt_ort ADD CONSTRAINT fk_tbl_rt_ort_reihungstest_id FOREIGN KEY (rt_id)
			REFERENCES public.tbl_reihungstest (reihungstest_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE public.tbl_rt_ort ADD CONSTRAINT fk_tbl_rt_ort_ort_kurzbz FOREIGN KEY (ort_kurzbz)
			REFERENCES public.tbl_ort (ort_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE public.tbl_rt_ort ADD CONSTRAINT fk_tbl_rt_ort_uid FOREIGN KEY (uid)
			REFERENCES public.tbl_benutzer (uid) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON public.tbl_rt_ort TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_rt_ort TO vilesci;

		-- Fuegt einen Datensatz in die tbl_rt_ort ein, falls die ort_kurzbz aus der tbl_reihungstest gesetzt und noch nicht in tbl_rt_ort vorhanden ist

		INSERT INTO public.tbl_rt_ort (rt_id, ort_kurzbz, uid)
		SELECT reihungstest_id, ort_kurzbz, NULL FROM public.tbl_reihungstest WHERE tbl_reihungstest.ort_kurzbz IS NOT NULL
		AND NOT EXISTS (SELECT ort_kurzbz FROM public.tbl_rt_ort WHERE ort_kurzbz=tbl_reihungstest.ort_kurzbz AND rt_id=tbl_reihungstest.reihungstest_id);

		-- Setzt die studiensemester_kurzbz (Nur WS) bei allen Reihungstests, wo sie aktuell NULL ist und ein Reihungstestdatum gesetzt ist
		UPDATE public.tbl_reihungstest rt
		SET studiensemester_kurzbz = (
				CASE
					WHEN (
							SELECT studiensemester_kurzbz
							FROM PUBLIC.tbl_studiensemester
							WHERE rt.datum + INTERVAL '8 month' BETWEEN tbl_studiensemester.start
									AND tbl_studiensemester.ende ORDER BY studiensemester_kurzbz DESC LIMIT 1
							) LIKE 'WS%'
						THEN (
								SELECT studiensemester_kurzbz
								FROM PUBLIC.tbl_studiensemester
								WHERE rt.datum + INTERVAL '8 month' BETWEEN tbl_studiensemester.start
										AND tbl_studiensemester.ende ORDER BY studiensemester_kurzbz DESC LIMIT 1
								)
					ELSE (
							CASE
								WHEN (
										SELECT studiensemester_kurzbz
										FROM PUBLIC.tbl_studiensemester
										WHERE rt.datum + INTERVAL '3 month' BETWEEN tbl_studiensemester.start
												AND tbl_studiensemester.ende ORDER BY studiensemester_kurzbz DESC LIMIT 1
										) LIKE 'WS%'
									THEN (
											SELECT studiensemester_kurzbz
											FROM PUBLIC.tbl_studiensemester
											WHERE rt.datum + INTERVAL '3 month' BETWEEN tbl_studiensemester.start
													AND tbl_studiensemester.ende ORDER BY studiensemester_kurzbz DESC LIMIT 1
											)
								ELSE (
										SELECT studiensemester_kurzbz
										FROM PUBLIC.tbl_studiensemester
										WHERE rt.datum BETWEEN tbl_studiensemester.start
												AND tbl_studiensemester.ende ORDER BY studiensemester_kurzbz DESC LIMIT 1
										)
										END
							)
					END
				)
		WHERE rt.studiensemester_kurzbz IS NULL
			AND rt.datum IS NOT NULL;


		-- Views fuer neue Tabellen anpassen

		CREATE OR REPLACE VIEW testtool.vw_auswertung_ablauf AS
			SELECT tbl_gebiet.gebiet_id,
			tbl_gebiet.bezeichnung AS gebiet,
			tbl_ablauf.reihung,
			tbl_gebiet.maxpunkte,
			tbl_pruefling.pruefling_id,
			tbl_pruefling.prestudent_id,
			tbl_person.vorname,
			tbl_person.nachname,
			tbl_person.gebdatum,
			tbl_person.geschlecht,
			tbl_pruefling.semester,
			upper(tbl_studiengang.typ::character varying(1)::text || tbl_studiengang.kurzbz::text) AS stg_kurzbz,
			tbl_studiengang.bezeichnung AS stg_bez,
			tbl_pruefling.registriert,
			tbl_pruefling.idnachweis,
			(
				SELECT sum(tbl_vorschlag.punkte) AS sum
				FROM
					testtool.tbl_vorschlag
					JOIN testtool.tbl_antwort USING (vorschlag_id)
					JOIN testtool.tbl_frage USING (frage_id)
				WHERE
					tbl_antwort.pruefling_id = tbl_pruefling.pruefling_id
					AND tbl_frage.gebiet_id = tbl_gebiet.gebiet_id
			) AS punkte,
			tbl_rt_person.rt_id as reihungstest_id,
			tbl_ablauf.gewicht
			FROM
				testtool.tbl_pruefling
				JOIN testtool.tbl_ablauf ON (tbl_ablauf.studiengang_kz = tbl_pruefling.studiengang_kz)
				JOIN testtool.tbl_gebiet USING (gebiet_id)
				JOIN public.tbl_prestudent USING (prestudent_id)
				JOIN public.tbl_person USING (person_id)
				JOIN public.tbl_rt_person USING (person_id)
				JOIN lehre.tbl_studienplan ON (tbl_studienplan.studienplan_id=tbl_rt_person.studienplan_id)
				JOIN lehre.tbl_studienordnung ON(tbl_studienordnung.studienordnung_id = tbl_studienplan.studienordnung_id)
				JOIN public.tbl_studiengang ON (tbl_prestudent.studiengang_kz = tbl_studiengang.studiengang_kz)
			WHERE
				NOT (tbl_ablauf.gebiet_id IN ( SELECT tbl_kategorie.gebiet_id FROM testtool.tbl_kategorie))
				AND tbl_studienordnung.studiengang_kz=tbl_pruefling.studiengang_kz;

		CREATE OR REPLACE VIEW testtool.vw_auswertung_kategorie_semester AS
		SELECT
			tbl_kategorie.kategorie_kurzbz,
			tbl_person.vorname,
			tbl_person.nachname,
			tbl_person.gebdatum,
			tbl_person.geschlecht,
			tbl_prestudent.prestudent_id,
			tbl_rt_person.rt_id as reihungstest_id,
			tbl_gebiet.gebiet_id,
			upper(tbl_studiengang.typ::character varying(1)::text || tbl_studiengang.kurzbz::text) AS stg_kurzbz,
			tbl_studiengang.bezeichnung AS stg_bez,
			tbl_pruefling.registriert,
			tbl_pruefling.idnachweis,
			tbl_pruefling.semester,
			tbl_pruefling.pruefling_id,
			(
				SELECT
					sum(tbl_vorschlag.punkte) AS sum
				FROM
					testtool.tbl_vorschlag
					JOIN testtool.tbl_antwort USING (vorschlag_id)
					JOIN testtool.tbl_frage USING (frage_id)
				WHERE
					tbl_antwort.pruefling_id = tbl_pruefling.pruefling_id
					AND tbl_frage.gebiet_id = tbl_gebiet.gebiet_id
					AND tbl_frage.kategorie_kurzbz::text = tbl_kategorie.kategorie_kurzbz::text
			) AS punkte
		FROM
			testtool.tbl_pruefling
			JOIN testtool.tbl_ablauf ON (tbl_ablauf.studiengang_kz = tbl_pruefling.studiengang_kz)
			JOIN testtool.tbl_gebiet USING (gebiet_id)
			JOIN testtool.tbl_kategorie USING (gebiet_id)
			JOIN public.tbl_prestudent USING (prestudent_id)
			JOIN public.tbl_person USING (person_id)
			JOIN public.tbl_studiengang ON (tbl_prestudent.studiengang_kz = tbl_studiengang.studiengang_kz)
			JOIN public.tbl_rt_person USING(person_id)
			JOIN lehre.tbl_studienplan ON(tbl_studienplan.studienplan_id=tbl_rt_person.studienplan_id)
			JOIN lehre.tbl_studienordnung ON(tbl_studienordnung.studienordnung_id = tbl_studienplan.studienordnung_id)
		WHERE
			tbl_studienordnung.studiengang_kz = tbl_pruefling.studiengang_kz;

		CREATE OR REPLACE VIEW testtool.vw_auswertung_kategorie AS
		SELECT
			tbl_kategorie.kategorie_kurzbz,
			tbl_person.vorname,
			tbl_person.nachname,
			tbl_person.gebdatum,
			tbl_person.geschlecht,
			tbl_prestudent.prestudent_id,
			tbl_rt_person.rt_id as reihungstest_id,
			tbl_gebiet.gebiet_id,
			upper(tbl_studiengang.typ::character varying(1)::text || tbl_studiengang.kurzbz::text) AS stg_kurzbz,
			tbl_studiengang.bezeichnung AS stg_bez,
			tbl_pruefling.registriert,
			tbl_pruefling.idnachweis,
			tbl_pruefling.semester,
			tbl_pruefling.pruefling_id,
 			(
				SELECT
					sum(tbl_vorschlag.punkte) AS sum
				FROM
					testtool.tbl_vorschlag
					JOIN testtool.tbl_antwort USING (vorschlag_id)
					JOIN testtool.tbl_frage USING (frage_id)
				WHERE
					tbl_antwort.pruefling_id = tbl_pruefling.pruefling_id
					AND tbl_frage.gebiet_id = tbl_gebiet.gebiet_id
					AND tbl_frage.kategorie_kurzbz::text = tbl_kategorie.kategorie_kurzbz::text
			) AS punkte
			FROM
				testtool.tbl_pruefling
				JOIN testtool.tbl_ablauf ON (tbl_ablauf.studiengang_kz = tbl_pruefling.studiengang_kz AND tbl_ablauf.semester = tbl_pruefling.semester)
				JOIN testtool.tbl_gebiet USING (gebiet_id)
				JOIN testtool.tbl_kategorie USING (gebiet_id)
				JOIN public.tbl_prestudent USING (prestudent_id)
				JOIN public.tbl_person USING (person_id)
				JOIN public.tbl_studiengang ON (tbl_prestudent.studiengang_kz = tbl_studiengang.studiengang_kz)
				JOIN public.tbl_rt_person USING(person_id)
				JOIN lehre.tbl_studienplan ON (tbl_studienplan.studienplan_id = tbl_rt_person.studienplan_id)
				JOIN lehre.tbl_studienordnung ON (tbl_studienordnung.studienordnung_id = tbl_studienplan.studienordnung_id)
			WHERE
				tbl_studienordnung.studiengang_kz=tbl_pruefling.studiengang_kz;

			CREATE OR REPLACE VIEW testtool.vw_auswertung AS
			SELECT
				tbl_gebiet.gebiet_id,
				tbl_gebiet.bezeichnung AS gebiet,
				tbl_gebiet.maxpunkte,
				tbl_pruefling.pruefling_id,
				tbl_pruefling.prestudent_id,
				tbl_person.vorname,
				tbl_person.nachname,
				tbl_person.gebdatum,
				tbl_person.geschlecht,
				tbl_pruefling.semester,
				upper(tbl_studiengang.typ::character varying(1)::text || tbl_studiengang.kurzbz::text) AS stg_kurzbz,
				tbl_studiengang.bezeichnung AS stg_bez,
				tbl_pruefling.registriert,
				tbl_pruefling.idnachweis,
				(
					SELECT
						sum(tbl_vorschlag.punkte) AS sum
					FROM
						testtool.tbl_vorschlag
						JOIN testtool.tbl_antwort USING (vorschlag_id)
						JOIN testtool.tbl_frage USING (frage_id)
					WHERE
						tbl_antwort.pruefling_id = tbl_pruefling.pruefling_id
						AND tbl_frage.gebiet_id = tbl_gebiet.gebiet_id
				) AS punkte,
				tbl_rt_person.rt_id as reihungstest_id,
				tbl_ablauf.gewicht,
				tbl_person.person_id
			FROM
				testtool.tbl_pruefling
				JOIN testtool.tbl_ablauf ON (tbl_ablauf.studiengang_kz = tbl_pruefling.studiengang_kz AND tbl_ablauf.semester = tbl_pruefling.semester)
				JOIN testtool.tbl_gebiet USING (gebiet_id)
				JOIN public.tbl_prestudent USING (prestudent_id)
				JOIN public.tbl_person USING (person_id)
				JOIN public.tbl_studiengang ON tbl_prestudent.studiengang_kz = tbl_studiengang.studiengang_kz
				JOIN public.tbl_rt_person USING (person_id)
				JOIN lehre.tbl_studienplan ON (tbl_studienplan.studienplan_id = tbl_rt_person.studienplan_id)
				JOIN lehre.tbl_studienordnung ON (tbl_studienordnung.studienordnung_id = tbl_studienplan.studienordnung_id)
			WHERE
				tbl_studienordnung.studiengang_kz = tbl_prestudent.studiengang_kz
				AND NOT (tbl_ablauf.gebiet_id IN ( SELECT tbl_kategorie.gebiet_id
					FROM testtool.tbl_kategorie));

		";

	if(!$db->db_query($qry))
		echo '<strong>Reihungstest: '.$db->db_last_error().'</strong><br>';
	else
		echo 'Neue Tabellen public.tbl_rt_person tbl_rt_ort tbl_rt_studienplan hinzugefuegt.
			Auswertungsviews auf neues DB Schema angepasst.
			Zur Migration der Reihungstests muss zusätzlich die Datei
			system/dbupdate_3.2/migrate_reihungstest.php gestartet werden!!';
}

// public.tbl_akte nachgereicht_am
if(!$result = @$db->db_query("SELECT nachgereicht_am FROM public.tbl_akte LIMIT 1"))
{
	$qry = "
		ALTER TABLE public.tbl_akte ADD COLUMN nachgereicht_am date;
		";

	if(!$db->db_query($qry))
		echo '<strong>Akte nachgereicht_am: '.$db->db_last_error().'</strong><br>';
	else
		echo 'Spalte nachgereicht_am zu tbl_akte hinzugefügt.';
}

// public.tbl_person zugangscode_timestamp
if(!$result = @$db->db_query("SELECT zugangscode_timestamp FROM public.tbl_person LIMIT 1"))
{
	$qry = "
		ALTER TABLE public.tbl_person ADD COLUMN zugangscode_timestamp timestamp;
		";

	if(!$db->db_query($qry))
		echo '<strong>Person zugangscode_timestamp: '.$db->db_last_error().'</strong><br>';
	else
		echo 'Spalte zugangscode_timestamp zu tbl_person hinzugefügt.';
}

// public.tbl_person zugangscode_timestamp
if(!$result = @$db->db_query("SELECT consrc FROM pg_constraint WHERE conname='chk_person_svnr'"))
{
	if($row = $db->db_fetch_object($result))
	{
		if(stristr($row->consrc,'char_length(svnr::text) = 12 OR')===false)
		{
			$qry = "
				ALTER TABLE public.tbl_person DROP CONSTRAINT chk_person_svnr;
				ALTER TABLE public.tbl_person
					ADD CONSTRAINT chk_person_svnr
						CHECK (
							char_length(svnr::text) = 10 OR
							char_length(svnr::text) = 12 OR
							char_length(svnr::text) = 16 OR
							svnr IS NULL
						);
				";

			if(!$db->db_query($qry))
				echo '<strong>Person SVNR: '.$db->db_last_error().'</strong><br>';
			else
				echo 'Check Constraint für tbl_person.svnr geändert. Kann jetzt auch 12 Zeichen lang sein';
		}
	}
}

// Neue Spalte studienplan_id in public.tbl_bewerbungstermine
if(!$result = @$db->db_query("SELECT 1 FROM public.tbl_status_grund LIMIT 1"))
{
	$qry = "
	CREATE TABLE public.tbl_status_grund
	(
		statusgrund_id serial,
		status_kurzbz varchar(20),
		aktiv boolean DEFAULT false,
		bezeichnung_mehrsprachig varchar(255)[],
		beschreibung text[]

	);

	ALTER TABLE public.tbl_status_grund ADD CONSTRAINT pk_tbl_status_grund PRIMARY KEY (statusgrund_id);
	ALTER TABLE public.tbl_status_grund ADD CONSTRAINT fk_tbl_status_grund_status_kurzbz FOREIGN KEY (status_kurzbz)
		REFERENCES public.tbl_status (status_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

	GRANT SELECT ON public.tbl_status_grund TO web;
	GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_status_grund TO vilesci;
	GRANT SELECT, UPDATE, INSERT, DELETE ON public.tbl_status_grund_statusgrund_id_seq TO vilesci;
	";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_status_grund '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neue Tabelle public.tbl_status_grund hinzugefügt<br>';
}

// statusgrund_kurzbz auf statusgrund_id aendern falls diese bereits falsch angelegt wurde
if($result = @$db->db_query("SELECT statusgrund_kurzbz FROM public.tbl_status_grund LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_status_grund RENAME COLUMN statusgrund_kurzbz TO statusgrund_id;";

	if(!$db->db_query($qry))
		echo '<strong>Statusgrund_kurzbz '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>statusgrund_kurzbz auf statusgrund_id geändert<br>';
}

// Remove NOT NULL constraint on mitarbeiter_uid on public.tbl_dokumentprestudent
if($result = @$db->db_query("SELECT is_nullable FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'public' AND TABLE_NAME = 'tbl_dokumentprestudent' AND COLUMN_NAME = 'mitarbeiter_uid' AND is_nullable = 'YES'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = " ALTER TABLE public.tbl_dokumentprestudent ALTER COLUMN mitarbeiter_uid DROP NOT NULL;";

		if(!$db->db_query($qry))
			echo '<strong>public.tbl_dokumentprestudent '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Removed NOT NULL constraint on "mitarbeiter_uid" from public.tbl_dokumentprestudent<br>';
	}
}

// nachreichbar public.tbl_dokumentstudiengang
if(!$result = @$db->db_query("SELECT nachreichbar FROM public.tbl_dokumentstudiengang LIMIT 1"))
{
	$qry = "ALTER TABLE public.tbl_dokumentstudiengang ADD COLUMN nachreichbar boolean NOT NULL DEFAULT false;";

	if(!$db->db_query($qry))
		echo '<strong>public.tbl_dokumentstudiengang '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>Neue Spalte nachreichbar in Tabelle public.tbl_dokumentstudiengang<br>';

}

// anmerkung und beschreibung status von 20 auf 255 Zeichen verlaengern
if($result = $db->db_query("SELECT character_maximum_length FROM information_schema.columns WHERE column_name='anmerkung' AND table_name='tbl_status' AND table_schema='public';"))
{
	if($row = $db->db_fetch_object($result))
	{
		if($row->character_maximum_length==20)
		{
			$qry = "ALTER TABLE public.tbl_status ALTER COLUMN beschreibung TYPE varchar(255);
			ALTER TABLE public.tbl_status ALTER COLUMN anmerkung TYPE varchar(255);
			";
			if(!$db->db_query($qry))
				echo '<strong>public.tbl_status: '.$db->db_last_error().'</strong><br>';
			else
				echo 'public.tbl_status: Spalte anmerkung und beschreibung auf 255 Zeichen verlaengert<br>';
		}
	}
}

// Fehlender PK bei bis.tbl_zgvgruppe
if($result = $db->db_query("SELECT * FROM information_schema.table_constraints WHERE constraint_name='pk_zgvgruppe_gruppe_kurzbz'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "ALTER TABLE bis.tbl_zgvgruppe ADD CONSTRAINT pk_zgvgruppe_gruppe_kurzbz PRIMARY KEY (gruppe_kurzbz);";

		if(!$db->db_query($qry))
			echo '<strong>bis.tbl_zgvgruppe '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Fehlenden PK bei Tabelle bis.tbl_zgvgruppe gesetzt<br>';
	}
}

// Fehlender PK bei system.tbl_server
if($result = $db->db_query("SELECT * FROM information_schema.table_constraints WHERE constraint_name='pk_server_server_kurzbz'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "ALTER TABLE system.tbl_server ADD CONSTRAINT pk_server_server_kurzbz PRIMARY KEY (server_kurzbz);";

		if(!$db->db_query($qry))
			echo '<strong>system.tbl_server '.$db->db_last_error().'</strong><br>';
		else
			echo '<br>Fehlenden PK bei Tabelle system.tbl_server gesetzt<br>';
	}
}

// *** Pruefung und hinzufuegen der neuen Attribute und Tabellen
echo '<H2>Pruefe Tabellen und Attribute!</H2>';

echo '<br><br><br>';

$tabellen=array(
	"bis.tbl_bisorgform" => array("bisorgform_kurzbz","code","bezeichnung"),
	"bis.tbl_archiv"  => array("archiv_id","studiensemester_kurzbz","meldung","html","studiengang_kz","insertamum","insertvon","typ"),
	"bis.tbl_ausbildung"  => array("ausbildungcode","ausbildungbez","ausbildungbeschreibung"),
	"bis.tbl_berufstaetigkeit"  => array("berufstaetigkeit_code","berufstaetigkeit_bez","berufstaetigkeit_kurzbz"),
	"bis.tbl_beschaeftigungsart1"  => array("ba1code","ba1bez","ba1kurzbz"),
	"bis.tbl_beschaeftigungsart2"  => array("ba2code","ba2bez"),
	"bis.tbl_beschaeftigungsausmass"  => array("beschausmasscode","beschausmassbez","min","max"),
	"bis.tbl_besqual"  => array("besqualcode","besqualbez"),
	"bis.tbl_bisfunktion"  => array("bisverwendung_id","studiengang_kz","sws","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"bis.tbl_bisio"  => array("bisio_id","mobilitaetsprogramm_code","nation_code","von","bis","zweck_code","student_uid","updateamum","updatevon","insertamum","insertvon","ext_id","ort","universitaet","lehreinheit_id"),
	"bis.tbl_bisverwendung"  => array("bisverwendung_id","ba1code","ba2code","vertragsstunden","beschausmasscode","verwendung_code","mitarbeiter_uid","hauptberufcode","hauptberuflich","habilitation","beginn","ende","updateamum","updatevon","insertamum","insertvon","ext_id","dv_art","inkludierte_lehre"),
	"bis.tbl_bundesland"  => array("bundesland_code","kurzbz","bezeichnung"),
	"bis.tbl_entwicklungsteam"  => array("mitarbeiter_uid","studiengang_kz","besqualcode","beginn","ende","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"bis.tbl_gemeinde"  => array("gemeinde_id","plz","name","ortschaftskennziffer","ortschaftsname","bulacode","bulabez","kennziffer"),
	"bis.tbl_gsstudientyp" => array("gsstudientyp_kurzbz","bezeichnung","studientyp_code"),
	"bis.tbl_gsprogrammtyp" => array("gsprogrammtyp_kurzbz","bezeichnung","programmtyp_code"),
	"bis.tbl_gsprogramm" => array("gsprogramm_id","programm_code","bezeichnung","gsprogrammtyp_kurzbz"),
	"bis.tbl_hauptberuf"  => array("hauptberufcode","bezeichnung"),
	"bis.tbl_lgartcode"  => array("lgartcode","kurzbz","bezeichnung","beantragung","lgart_biscode"),
	"bis.tbl_mobilitaet" => array("mobilitaet_id","prestudent_id","mobilitaetstyp_kurzbz","studiensemester_kurzbz","mobilitaetsprogramm_code","gsprogramm_id","firma_id","status_kurzbz","ausbildungssemester","insertvon","insertamum","updatevon","updateamum"),
	"bis.tbl_mobilitaetstyp" => array("mobilitaetstyp_kurzbz","bezeichnung","aktiv"),
	"bis.tbl_mobilitaetsprogramm"  => array("mobilitaetsprogramm_code","kurzbz","beschreibung","sichtbar","sichtbar_outgoing"),
	"bis.tbl_nation"  => array("nation_code","entwicklungsstand","eu","ewr","kontinent","kurztext","langtext","engltext","sperre"),
	"bis.tbl_orgform"  => array("orgform_kurzbz","code","bezeichnung","rolle","bisorgform_kurzbz"),
	"bis.tbl_verwendung"  => array("verwendung_code","verwendungbez"),
	"bis.tbl_zgv"  => array("zgv_code","zgv_bez","zgv_kurzbz","bezeichnung"),
	"bis.tbl_zgvmaster"  => array("zgvmas_code","zgvmas_bez","zgvmas_kurzbz","bezeichnung"),
	"bis.tbl_zgvdoktor" => array("zgvdoktor_code", "zgvdoktor_bez", "zgvdoktor_kurzbz","bezeichnung"),
	"bis.tbl_zweck"  => array("zweck_code","kurzbz","bezeichnung"),
	"bis.tbl_zgvgruppe"  => array("gruppe_kurzbz","bezeichnung"),
	"bis.tbl_zgvgruppe_zuordnung"  => array("zgvgruppe_id" ,"studiengang_kz","zgv_code","zgvmas_code","gruppe_kurzbz"),
	"campus.tbl_abgabe"  => array("abgabe_id","abgabedatei","abgabezeit","anmerkung"),
	"campus.tbl_anwesenheit"  => array("anwesenheit_id","uid","einheiten","datum","anwesend","lehreinheit_id","anmerkung","ext_id"),
	"campus.tbl_beispiel"  => array("beispiel_id","uebung_id","nummer","bezeichnung","punkte","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_benutzerlvstudiensemester"  => array("uid","studiensemester_kurzbz","lehrveranstaltung_id"),
	"campus.tbl_content"  => array("content_id","template_kurzbz","updatevon","updateamum","insertamum","insertvon","oe_kurzbz","menu_open","aktiv","beschreibung"),
	"campus.tbl_contentchild"  => array("contentchild_id","content_id","child_content_id","updatevon","updateamum","insertamum","insertvon","sort"),
	"campus.tbl_contentgruppe"  => array("content_id","gruppe_kurzbz","insertamum","insertvon"),
	"campus.tbl_contentlog"  => array("contentlog_id","contentsprache_id","uid","start","ende"),
	"campus.tbl_contentsprache"  => array("contentsprache_id","content_id","sprache","version","sichtbar","content","reviewvon","reviewamum","updateamum","updatevon","insertamum","insertvon","titel","gesperrt_uid"),
	"campus.tbl_coodle"  => array("coodle_id","titel","beschreibung","coodle_status_kurzbz","dauer","endedatum","insertamum","insertvon","updateamum","updatevon","ersteller_uid"),
	"campus.tbl_coodle_ressource"  => array("coodle_ressource_id","coodle_id","uid","ort_kurzbz","email","name","zugangscode","insertamum","insertvon","updateamum","updatevon"),
	"campus.tbl_coodle_termin"  => array("coodle_termin_id","coodle_id","datum","uhrzeit","auswahl"),
	"campus.tbl_coodle_ressource_termin"  => array("coodle_ressource_id","coodle_termin_id","insertamum","insertvon"),
	"campus.tbl_coodle_status"  => array("coodle_status_kurzbz","bezeichnung"),
	"campus.tbl_dms"  => array("dms_id","oe_kurzbz","dokument_kurzbz","kategorie_kurzbz"),
	"campus.tbl_dms_kategorie"  => array("kategorie_kurzbz","bezeichnung","beschreibung","parent_kategorie_kurzbz"),
	"campus.tbl_dms_kategorie_gruppe" => array("kategorie_kurzbz","gruppe_kurzbz","insertamum","insertvon"),
	"campus.tbl_dms_version"  => array("dms_id","version","filename","mimetype","name","beschreibung","letzterzugriff","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_erreichbarkeit"  => array("erreichbarkeit_kurzbz","beschreibung","farbe"),
	"campus.tbl_feedback"  => array("feedback_id","betreff","text","datum","uid","lehrveranstaltung_id","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_freebusy"  => array("freebusy_id","uid","freebusytyp_kurzbz","url","aktiv","bezeichnung","insertamum","insertvon","updateamum","updatevon"),
	"campus.tbl_freebusytyp" => array("freebusytyp_kurzbz","bezeichnung","beschreibung","url_vorlage"),
	"campus.tbl_infoscreen"  => array("infoscreen_id","bezeichnung","beschreibung","ipadresse"),
	"campus.tbl_infoscreen_content"  => array("infoscreen_content_id","infoscreen_id","content_id","gueltigvon","gueltigbis","insertamum","insertvon","updateamum","updatevon","refreshzeit","exklusiv"),
	"campus.tbl_legesamtnote"  => array("student_uid","lehreinheit_id","note","benotungsdatum","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_lehre_tools" => array("lehre_tools_id","bezeichnung","kurzbz","basis_url","logo_dms_id"),
	"campus.tbl_lehre_tools_organisationseinheit" => array("lehre_tools_id","oe_kurzbz","aktiv"),
	"campus.tbl_lehrveranstaltung_pruefung" => array("lehrveranstaltung_pruefung_id","lehrveranstaltung_id","pruefung_id"),
	"campus.tbl_lvgesamtnote"  => array("lehrveranstaltung_id","studiensemester_kurzbz","student_uid","note","mitarbeiter_uid","benotungsdatum","freigabedatum","freigabevon_uid","bemerkung","updateamum","updatevon","insertamum","insertvon","punkte","ext_id"),
	"campus.tbl_lvinfo"  => array("lehrveranstaltung_id","sprache","titel","lehrziele","lehrinhalte","methodik","voraussetzungen","unterlagen","pruefungsordnung","anmerkung","kurzbeschreibung","genehmigt","aktiv","updateamum","updatevon","insertamum","insertvon","anwesenheit"),
	"campus.tbl_news"  => array("news_id","uid","studiengang_kz","fachbereich_kurzbz","semester","betreff","text","datum","verfasser","updateamum","updatevon","insertamum","insertvon","datum_bis","content_id"),
	"campus.tbl_notenschluessel"  => array("lehreinheit_id","note","punkte"),
	"campus.tbl_notenschluesseluebung"  => array("uebung_id","note","punkte"),
	"campus.tbl_paabgabetyp"  => array("paabgabetyp_kurzbz","bezeichnung"),
	"campus.tbl_paabgabe"  => array("paabgabe_id","projektarbeit_id","paabgabetyp_kurzbz","fixtermin","datum","kurzbz","abgabedatum", "insertvon","insertamum","updatevon","updateamum"),
	"campus.tbl_pruefungsfenster" => array("pruefungsfenster_id","studiensemester_kurzbz","oe_kurzbz","start","ende"),
	"campus.tbl_pruefung" => array("pruefung_id","mitarbeiter_uid","studiensemester_kurzbz","pruefungsfenster_id","pruefungstyp_kurzbz","titel","beschreibung","methode","einzeln","storniert","insertvon","insertamum","updatevon","updateamum","pruefungsintervall"),
	"campus.tbl_pruefungstermin" => array("pruefungstermin_id","pruefung_id","von","bis","teilnehmer_max","teilnehmer_min","anmeldung_von","anmeldung_bis","ort_kurzbz","sammelklausur"),
	"campus.tbl_pruefungsanmeldung" => array("pruefungsanmeldung_id","uid","pruefungstermin_id","lehrveranstaltung_id","status_kurzbz","wuensche","reihung","kommentar","statusupdatevon","statusupdateamum","anrechnung_id","pruefungstyp_kurzbz"),
	"campus.tbl_pruefungsstatus" => array("status_kurzbz","bezeichnung"),
	"campus.tbl_reservierung"  => array("reservierung_id","ort_kurzbz","studiengang_kz","uid","stunde","datum","titel","beschreibung","semester","verband","gruppe","gruppe_kurzbz","veranstaltung_id","insertamum","insertvon"),
	"campus.tbl_resturlaub"  => array("mitarbeiter_uid","resturlaubstage","mehrarbeitsstunden","updateamum","updatevon","insertamum","insertvon","urlaubstageprojahr"),
	"campus.tbl_studentbeispiel"  => array("student_uid","beispiel_id","vorbereitet","probleme","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_studentuebung"  => array("student_uid","mitarbeiter_uid","abgabe_id","uebung_id","note","mitarbeitspunkte","punkte","anmerkung","benotungsdatum","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_template"  => array("template_kurzbz","bezeichnung","xsd","xslt_xhtml","xslfo_pdf"),
	"campus.tbl_uebung"  => array("uebung_id","gewicht","punkte","angabedatei","freigabevon","freigabebis","abgabe","beispiele","statistik","bezeichnung","positiv","defaultbemerkung","lehreinheit_id","maxstd","maxbsp","liste_id","prozent","nummer","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_veranstaltung"  => array("veranstaltung_id","titel","beschreibung","veranstaltungskategorie_kurzbz","inhalt","start","ende","freigabevon","freigabeamum","updateamum","updatevon","insertamum","insertvon"),
	"campus.tbl_veranstaltungskategorie"  => array("veranstaltungskategorie_kurzbz","bezeichnung","bild","farbe"),
	"campus.tbl_zeitaufzeichnung"  => array("zeitaufzeichnung_id","uid","aktivitaet_kurzbz","projekt_kurzbz","start","ende","beschreibung","oe_kurzbz_1","oe_kurzbz_2","insertamum","insertvon","updateamum","updatevon","ext_id","service_id","kunde_uid"),
	"campus.tbl_zeitsperre"  => array("zeitsperre_id","zeitsperretyp_kurzbz","mitarbeiter_uid","bezeichnung","vondatum","vonstunde","bisdatum","bisstunde","vertretung_uid","updateamum","updatevon","insertamum","insertvon","erreichbarkeit_kurzbz","freigabeamum","freigabevon"),
	"campus.tbl_zeitsperretyp"  => array("zeitsperretyp_kurzbz","beschreibung","farbe"),
	"campus.tbl_zeitwunsch"  => array("stunde","mitarbeiter_uid","tag","gewicht","updateamum","updatevon","insertamum","insertvon"),
	"fue.tbl_aktivitaet"  => array("aktivitaet_kurzbz","beschreibung","sort"),
	"fue.tbl_aufwandstyp" => array("aufwandstyp_kurzbz","bezeichnung"),
	"fue.tbl_projekt"  => array("projekt_kurzbz","nummer","titel","beschreibung","beginn","ende","oe_kurzbz","budget","farbe","aufwandstyp_kurzbz","ressource_id","anzahl_ma","aufwand_pt"),
	"fue.tbl_projektphase"  => array("projektphase_id","projekt_kurzbz","projektphase_fk","bezeichnung","typ","beschreibung","start","ende","budget","insertamum","insertvon","updateamum","updatevon","personentage","farbe","ressource_id"),
	"fue.tbl_projekttask"  => array("projekttask_id","projektphase_id","bezeichnung","beschreibung","aufwand","mantis_id","insertamum","insertvon","updateamum","updatevon","projekttask_fk","erledigt","ende","ressource_id","scrumsprint_id"),
	"fue.tbl_projekt_dokument"  => array("projekt_dokument_id","projektphase_id","projekt_kurzbz","dms_id"),
	"fue.tbl_projekt_ressource"  => array("projekt_ressource_id","projekt_kurzbz","projektphase_id","ressource_id","funktion_kurzbz","beschreibung","aufwand"),
	"fue.tbl_ressource"  => array("ressource_id","student_uid","mitarbeiter_uid","betriebsmittel_id","firma_id","bezeichnung","beschreibung","insertamum","insertvon","updateamum","updatevon"),
	"fue.tbl_scrumteam" => array("scrumteam_kurzbz","bezeichnung","punkteprosprint","tasksprosprint","gruppe_kurzbz"),
	"fue.tbl_scrumsprint" => array("scrumsprint_id","scrumteam_kurzbz","sprint_kurzbz","sprintstart","sprintende","insertamum","insertvon","updateamum","updatevon"),
	"lehre.tbl_abschlussbeurteilung"  => array("abschlussbeurteilung_kurzbz","bezeichnung","bezeichnung_english"),
	"lehre.tbl_abschlusspruefung"  => array("abschlusspruefung_id","student_uid","vorsitz","pruefer1","pruefer2","pruefer3","abschlussbeurteilung_kurzbz","akadgrad_id","pruefungstyp_kurzbz","datum","uhrzeit","sponsion","anmerkung","updateamum","updatevon","insertamum","insertvon","ext_id","note"),
	"lehre.tbl_akadgrad"  => array("akadgrad_id","akadgrad_kurzbz","studiengang_kz","titel","geschlecht"),
	"lehre.tbl_anrechnung"  => array("anrechnung_id","prestudent_id","lehrveranstaltung_id","begruendung_id","lehrveranstaltung_id_kompatibel","genehmigt_von","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"lehre.tbl_anrechnung_begruendung"  => array("begruendung_id","bezeichnung"),
	"lehre.tbl_betreuerart"  => array("betreuerart_kurzbz","beschreibung"),
	"lehre.tbl_ferien"  => array("bezeichnung","studiengang_kz","vondatum","bisdatum"),
	"lehre.tbl_lehreinheit"  => array("lehreinheit_id","lehrveranstaltung_id","studiensemester_kurzbz","lehrfach_id","lehrform_kurzbz","stundenblockung","wochenrythmus","start_kw","raumtyp","raumtypalternativ","sprache","lehre","anmerkung","unr","lvnr","updateamum","updatevon","insertamum","insertvon","ext_id","lehrfach_id_old","gewicht"),
	"lehre.tbl_lehreinheitgruppe"  => array("lehreinheitgruppe_id","lehreinheit_id","studiengang_kz","semester","verband","gruppe","gruppe_kurzbz","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_lehreinheitmitarbeiter"  => array("lehreinheit_id","mitarbeiter_uid","lehrfunktion_kurzbz","semesterstunden","planstunden","stundensatz","faktor","anmerkung","bismelden","updateamum","updatevon","insertamum","insertvon","ext_id","standort_id","vertrag_id"),
	"lehre.tbl_lehrfach"  => array("lehrfach_id","studiengang_kz","fachbereich_kurzbz","kurzbz","bezeichnung","farbe","aktiv","semester","sprache","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_lehrform"  => array("lehrform_kurzbz","bezeichnung","verplanen","bezeichnung_kurz","bezeichnung_lang"),
	"lehre.tbl_lehrfunktion"  => array("lehrfunktion_kurzbz","beschreibung","standardfaktor","sort"),
	"lehre.tbl_lehrmittel" => array("lehrmittel_kurzbz","beschreibung","ort_kurzbz"),
	"lehre.tbl_lehrtyp" => array("lehrtyp_kurzbz","bezeichnung"),
	"lehre.tbl_lehrveranstaltung"  => array("lehrveranstaltung_id","kurzbz","bezeichnung","lehrform_kurzbz","studiengang_kz","semester","sprache","ects","semesterstunden","anmerkung","lehre","lehreverzeichnis","aktiv","planfaktor","planlektoren","planpersonalkosten","plankostenprolektor","koordinator","sort","zeugnis","projektarbeit","updateamum","updatevon","insertamum","insertvon","ext_id","bezeichnung_english","orgform_kurzbz","incoming","lehrtyp_kurzbz","oe_kurzbz","raumtyp_kurzbz","anzahlsemester","semesterwochen","lvnr","farbe","semester_alternativ","old_lehrfach_id","sws","lvs","alvs","lvps","las","benotung","lvinfo","lehrauftrag"),
	"lehre.tbl_lehrveranstaltung_kompatibel" => array("lehrveranstaltung_id","lehrveranstaltung_id_kompatibel"),
	"lehre.tbl_lvangebot" => array("lvangebot_id","lehrveranstaltung_id","studiensemester_kurzbz","gruppe_kurzbz","incomingplaetze","gesamtplaetze","anmeldefenster_start","anmeldefenster_ende","insertamum","insertvon","updateamum","updatevon"),
	"lehre.tbl_lvregel" => array("lvregel_id","lvregeltyp_kurzbz","operator","parameter","lvregel_id_parent","lehrveranstaltung_id","studienplan_lehrveranstaltung_id","insertamum","insertvon","updateamum","updatevon"),
	"lehre.tbl_lvregeltyp" => array("lvregeltyp_kurzbz","bezeichnung"),
	"lehre.tbl_moodle"  => array("lehrveranstaltung_id","lehreinheit_id","moodle_id","mdl_course_id","studiensemester_kurzbz","gruppen","insertamum","insertvon","moodle_version"),
	"lehre.tbl_moodle_version"  => array("moodle_version","bezeichnung","pfad"),
	"lehre.tbl_notenschluessel" => array("notenschluessel_kurzbz","bezeichnung"),
	"lehre.tbl_notenschluesselaufteilung" => array("notenschluesselaufteilung_id","notenschluessel_kurzbz","note","punkte"),
	"lehre.tbl_notenschluesselzuordnung" => array("notenschluesselzuordnung_id","notenschluessel_kurzbz","lehrveranstaltung_id","studienplan_id","oe_kurzbz","studiensemester_kurzbz"),
	"lehre.tbl_note"  => array("note","bezeichnung","anmerkung","farbe","positiv","notenwert","aktiv","lehre"),
	"lehre.tbl_projektarbeit"  => array("projektarbeit_id","projekttyp_kurzbz","titel","lehreinheit_id","student_uid","firma_id","note","punkte","beginn","ende","faktor","freigegeben","gesperrtbis","stundensatz","gesamtstunden","themenbereich","anmerkung","updateamum","updatevon","insertamum","insertvon","ext_id","titel_english","seitenanzahl","abgabedatum","kontrollschlagwoerter","schlagwoerter","schlagwoerter_en","abstract", "abstract_en", "sprache"),
	"lehre.tbl_projektbetreuer"  => array("person_id","projektarbeit_id","betreuerart_kurzbz","note","faktor","name","punkte","stunden","stundensatz","updateamum","updatevon","insertamum","insertvon","ext_id","vertrag_id"),
	"lehre.tbl_projekttyp"  => array("projekttyp_kurzbz","bezeichnung"),
	"lehre.tbl_pruefung"  => array("pruefung_id","lehreinheit_id","student_uid","mitarbeiter_uid","note","pruefungstyp_kurzbz","datum","anmerkung","insertamum","insertvon","updateamum","updatevon","ext_id","pruefungsanmeldung_id","vertrag_id", "punkte"),
	"lehre.tbl_pruefungstyp"  => array("pruefungstyp_kurzbz","beschreibung","abschluss"),
	"lehre.tbl_studienordnung"  => array("studienordnung_id","studiengang_kz","version","gueltigvon","gueltigbis","bezeichnung","ects","studiengangbezeichnung","studiengangbezeichnung_englisch","studiengangkurzbzlang","akadgrad_id","insertamum","insertvon","updateamum","updatevon","ext_id", "status_kurzbz", "standort_id"),
	"lehre.tbl_studienordnungstatus" => array("status_kurzbz","bezeichnung","reihenfolge"),
	"lehre.tbl_studienordnung_semester"  => array("studienordnung_semester_id","studienordnung_id","studiensemester_kurzbz","semester"),
	"lehre.tbl_studienplan" => array("studienplan_id","studienordnung_id","orgform_kurzbz","version","regelstudiendauer","sprache","aktiv","bezeichnung","insertamum","insertvon","updateamum","updatevon","semesterwochen","testtool_sprachwahl","ext_id", "ects_stpl", "pflicht_sws", "pflicht_lvs"),
	"lehre.tbl_studienplan_lehrveranstaltung" => array("studienplan_lehrveranstaltung_id","studienplan_id","lehrveranstaltung_id","semester","studienplan_lehrveranstaltung_id_parent","pflicht","koordinator","insertamum","insertvon","updateamum","updatevon","sort","ext_id", "curriculum","export","genehmigung"),
	"lehre.tbl_studienplan_semester" => array("studienplan_semester_id", "studienplan_id", "studiensemester_kurzbz", "semester"),
	"lehre.tbl_studienplatz" => array("studienplatz_id","studiengang_kz","studiensemester_kurzbz","orgform_kurzbz","ausbildungssemester","gpz","npz","insertamum","insertvon","updateamum","updatevon","ext_id", "apz", "studienplan_id"),
	"lehre.tbl_stunde"  => array("stunde","beginn","ende"),
	"lehre.tbl_stundenplan"  => array("stundenplan_id","unr","mitarbeiter_uid","datum","stunde","ort_kurzbz","gruppe_kurzbz","titel","anmerkung","lehreinheit_id","studiengang_kz","semester","verband","gruppe","fix","updateamum","updatevon","insertamum","insertvon"),
	"lehre.tbl_stundenplandev"  => array("stundenplandev_id","lehreinheit_id","unr","studiengang_kz","semester","verband","gruppe","gruppe_kurzbz","mitarbeiter_uid","ort_kurzbz","datum","stunde","titel","anmerkung","fix","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_stundenplan_betriebsmittel" => array("stundenplan_betriebsmittel_id","betriebsmittel_id","stundenplandev_id","anmerkung","insertamum","insertvon"),
	"lehre.tbl_vertrag"  => array("vertrag_id","person_id","vertragstyp_kurzbz","bezeichnung","betrag","insertamum","insertvon","updateamum","updatevon","ext_id","anmerkung","vertragsdatum","lehrveranstaltung_id"),
	"lehre.tbl_vertrag_vertragsstatus"  => array("vertragsstatus_kurzbz","vertrag_id","uid","datum","ext_id","insertamum","insertvon","updateamum","updatevon"),
	"lehre.tbl_vertragstyp"  => array("vertragstyp_kurzbz","bezeichnung"),
	"lehre.tbl_vertragsstatus"  => array("vertragsstatus_kurzbz","bezeichnung"),
	"lehre.tbl_zeitfenster"  => array("wochentag","stunde","ort_kurzbz","studiengang_kz","gewicht"),
	"lehre.tbl_zeugnis"  => array("zeugnis_id","student_uid","zeugnis","erstelltam","gedruckt","titel","bezeichnung","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"lehre.tbl_zeugnisnote"  => array("lehrveranstaltung_id","student_uid","studiensemester_kurzbz","note","uebernahmedatum","benotungsdatum","bemerkung","updateamum","updatevon","insertamum","insertvon","ext_id","punkte"),
	"public.ci_apikey" => array("apikey_id","key","level","ignore_limits","date_created"),
	"public.tbl_adresse"  => array("adresse_id","person_id","name","strasse","plz","ort","gemeinde","nation","typ","heimatadresse","zustelladresse","firma_id","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_akte"  => array("akte_id","person_id","dokument_kurzbz","uid","inhalt","mimetype","erstelltam","gedruckt","titel","bezeichnung","updateamum","updatevon","insertamum","insertvon","ext_id","dms_id","nachgereicht","anmerkung","titel_intern","anmerkung_intern","nachgereicht_am"),
	"public.tbl_ampel"  => array("ampel_id","kurzbz","beschreibung","benutzer_select","deadline","vorlaufzeit","verfallszeit","insertamum","insertvon","updateamum","updatevon","email","verpflichtend","buttontext"),
	"public.tbl_ampel_benutzer_bestaetigt"  => array("ampel_benutzer_bestaetigt_id","ampel_id","uid","insertamum","insertvon"),
	"public.tbl_aufmerksamdurch"  => array("aufmerksamdurch_kurzbz","beschreibung","ext_id","bezeichnung", "aktiv"),
	"public.tbl_aufnahmeschluessel"  => array("aufnahmeschluessel"),
	"public.tbl_aufnahmetermin" => array("aufnahmetermin_id","aufnahmetermintyp_kurzbz","prestudent_id","termin","teilgenommen","bewertung","protokoll","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"public.tbl_aufnahmetermintyp" => array("aufnahmetermintyp_kurzbz","bezeichnung"),
	"public.tbl_bankverbindung"  => array("bankverbindung_id","person_id","name","anschrift","bic","blz","iban","kontonr","typ","verrechnung","updateamum","updatevon","insertamum","insertvon","ext_id","oe_kurzbz"),
	"public.tbl_benutzer"  => array("uid","person_id","aktiv","alias","insertamum","insertvon","updateamum","updatevon","ext_id","updateaktivvon","updateaktivam","aktivierungscode"),
	"public.tbl_benutzerfunktion"  => array("benutzerfunktion_id","fachbereich_kurzbz","uid","oe_kurzbz","funktion_kurzbz","semester", "datum_von","datum_bis", "updateamum","updatevon","insertamum","insertvon","ext_id","bezeichnung","wochenstunden"),
	"public.tbl_benutzergruppe"  => array("uid","gruppe_kurzbz","studiensemester_kurzbz","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_bewerbungstermine" => array("bewerbungstermin_id","studiengang_kz","studiensemester_kurzbz","beginn","ende","nachfrist","nachfrist_ende","anmerkung", "insertamum", "insertvon", "updateamum", "updatevon","studienplan_id"),
	"public.tbl_buchungstyp"  => array("buchungstyp_kurzbz","beschreibung","standardbetrag","standardtext","aktiv","credit_points"),
	"public.tbl_dokument"  => array("dokument_kurzbz","bezeichnung","ext_id","bezeichnung_mehrsprachig","dokumentbeschreibung_mehrsprachig"),
	"public.tbl_dokumentprestudent"  => array("dokument_kurzbz","prestudent_id","mitarbeiter_uid","datum","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_dokumentstudiengang"  => array("dokument_kurzbz","studiengang_kz","ext_id", "onlinebewerbung", "pflicht","beschreibung_mehrsprachig","nachreichbar"),
	"public.tbl_erhalter"  => array("erhalter_kz","kurzbz","bezeichnung","dvr","logo","zvr"),
	"public.tbl_fachbereich"  => array("fachbereich_kurzbz","bezeichnung","farbe","studiengang_kz","aktiv","ext_id","oe_kurzbz"),
	"public.tbl_filter" => array("filter_id","kurzbz","sql","valuename","showvalue","insertamum","insertvon","updateamum","updatevon","type","htmlattr"),
	"public.tbl_firma"  => array("firma_id","name","anmerkung","firmentyp_kurzbz","updateamum","updatevon","insertamum","insertvon","ext_id","schule","finanzamt","steuernummer","gesperrt","aktiv","lieferbedingungen","partner_code"),
	"public.tbl_firma_mobilitaetsprogramm" => array("firma_id","mobilitaetsprogramm_code","ext_id"),
	"public.tbl_firma_organisationseinheit"  => array("firma_organisationseinheit_id","firma_id","oe_kurzbz","bezeichnung","kundennummer","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_firmentyp"  => array("firmentyp_kurzbz","beschreibung"),
	"public.tbl_firmatag"  => array("firma_id","tag","insertamum","insertvon"),
	"public.tbl_fotostatus"  => array("fotostatus_kurzbz","beschreibung"),
	"public.tbl_funktion"  => array("funktion_kurzbz","beschreibung","aktiv","fachbereich","semester"),
	"public.tbl_geschaeftsjahr"  => array("geschaeftsjahr_kurzbz","start","ende","bezeichnung"),
	"public.tbl_gruppe"  => array("gruppe_kurzbz","studiengang_kz","semester","bezeichnung","beschreibung","sichtbar","lehre","aktiv","sort","mailgrp","generiert","updateamum","updatevon","insertamum","insertvon","ext_id","orgform_kurzbz","gid","content_visible","gesperrt","zutrittssystem","aufnahmegruppe"),
	"public.tbl_kontakt"  => array("kontakt_id","person_id","kontakttyp","anmerkung","kontakt","zustellung","updateamum","updatevon","insertamum","insertvon","ext_id","standort_id"),
	"public.tbl_kontaktmedium"  => array("kontaktmedium_kurzbz","beschreibung"),
	"public.tbl_kontakttyp"  => array("kontakttyp","beschreibung"),
	"public.tbl_konto"  => array("buchungsnr","person_id","studiengang_kz","studiensemester_kurzbz","buchungstyp_kurzbz","buchungsnr_verweis","betrag","buchungsdatum","buchungstext","mahnspanne","updateamum","updatevon","insertamum","insertvon","ext_id","credit_points", "zahlungsreferenz", "anmerkung"),
	"public.tbl_lehrverband"  => array("studiengang_kz","semester","verband","gruppe","aktiv","bezeichnung","ext_id","orgform_kurzbz","gid"),
	"public.tbl_log"  => array("log_id","executetime","mitarbeiter_uid","beschreibung","sql","sqlundo"),
	"public.tbl_mitarbeiter"  => array("mitarbeiter_uid","personalnummer","telefonklappe","kurzbz","lektor","fixangestellt","bismelden","stundensatz","ausbildungcode","ort_kurzbz","standort_id","anmerkung","insertamum","insertvon","updateamum","updatevon","ext_id","kleriker"),
	"public.tbl_msg_attachment" => array("attachment_id","message_id","name","filename"),
	"public.tbl_msg_message" => array("message_id","person_id","subject","body","priority","relationmessage_id","oe_kurzbz","insertamum","insertvon"),
	"public.tbl_msg_recipient" => array("message_id","person_id","token","sent","sentinfo","insertamum","insertvon"),
	"public.tbl_msg_status" => array("message_id","person_id","status","statusinfo","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_notiz"  => array("notiz_id","titel","text","verfasser_uid","bearbeiter_uid","start","ende","erledigt","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"public.tbl_notizzuordnung"  => array("notizzuordnung_id","notiz_id","projekt_kurzbz","projektphase_id","projekttask_id","uid","person_id","prestudent_id","bestellung_id","lehreinheit_id","ext_id","anrechnung_id"),
	"public.tbl_notiz_dokument" => array("notiz_id","dms_id"),
	"public.tbl_ort"  => array("ort_kurzbz","bezeichnung","planbezeichnung","max_person","lehre","reservieren","aktiv","lageplan","dislozierung","kosten","ausstattung","updateamum","updatevon","insertamum","insertvon","ext_id","stockwerk","standort_id","telefonklappe","content_id","m2","gebteil","oe_kurzbz","arbeitsplaetze"),
	"public.tbl_ortraumtyp"  => array("ort_kurzbz","hierarchie","raumtyp_kurzbz"),
	"public.tbl_organisationseinheit" => array("oe_kurzbz", "oe_parent_kurzbz", "bezeichnung","organisationseinheittyp_kurzbz", "aktiv","mailverteiler","freigabegrenze","kurzzeichen","lehre","standort","warn_semesterstunden_frei","warn_semesterstunden_fix","standort_id"),
	"public.tbl_organisationseinheittyp" => array("organisationseinheittyp_kurzbz", "bezeichnung", "beschreibung"),
	"public.tbl_person"  => array("person_id","staatsbuergerschaft","geburtsnation","sprache","anrede","titelpost","titelpre","nachname","vorname","vornamen","gebdatum","gebort","gebzeit","foto","anmerkung","homepage","svnr","ersatzkennzeichen","familienstand","geschlecht","anzahlkinder","aktiv","insertamum","insertvon","updateamum","updatevon","ext_id","bundesland_code","kompetenzen","kurzbeschreibung","zugangscode", "foto_sperre","matr_nr","zugangscode_timestamp"),
	"public.tbl_person_fotostatus"  => array("person_fotostatus_id","person_id","fotostatus_kurzbz","datum","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_personfunktionstandort"  => array("personfunktionstandort_id","funktion_kurzbz","person_id","standort_id","position","anrede"),
	"public.tbl_preincoming"  => array("preincoming_id","person_id","mobilitaetsprogramm_code","zweck_code","firma_id","universitaet","aktiv","bachelorthesis","masterthesis","von","bis","uebernommen","insertamum","insertvon","updateamum","updatevon","anmerkung","zgv","zgv_ort","zgv_datum","zgv_name","zgvmaster","zgvmaster_datum","zgvmaster_ort","zgvmaster_name","program_name","bachelor","master","jahre","person_id_emergency","person_id_coordinator_dep","person_id_coordinator_int","code","deutschkurs1","deutschkurs2","research_area","deutschkurs3","ext_id"),
	"public.tbl_preincoming_lehrveranstaltung"  => array("preincoming_id","lehrveranstaltung_id","insertamum","insertvon"),
	"public.tbl_preinteressent"  => array("preinteressent_id","person_id","studiensemester_kurzbz","firma_id","erfassungsdatum","einverstaendnis","absagedatum","anmerkung","maturajahr","infozusendung","aufmerksamdurch_kurzbz","kontaktmedium_kurzbz","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"public.tbl_preinteressentstudiengang"  => array("studiengang_kz","preinteressent_id","freigabedatum","uebernahmedatum","prioritaet","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_preoutgoing" => array("preoutgoing_id","uid","dauer_von","dauer_bis","ansprechperson","bachelorarbeit","masterarbeit","betreuer","sprachkurs","intensivsprachkurs","sprachkurs_von","sprachkurs_bis","praktikum","praktikum_von","praktikum_bis","behinderungszuschuss","studienbeihilfe","anmerkung_student", "anmerkung_admin", "studienrichtung_gastuniversitaet", "insertamum","insertvon","updateamum","updatevon","projektarbeittitel","ext_id"),
	"public.tbl_preoutgoing_firma" => array("preoutgoing_firma_id","preoutgoing_id","mobilitaetsprogramm_code","firma_id","name","auswahl","ext_id"),
	"public.tbl_preoutgoing_lehrveranstaltung" => array("preoutgoing_lehrveranstaltung_id","preoutgoing_id","bezeichnung","ects","endversion","insertamum","insertvon","updateamum","updatevon","wochenstunden","unitcode"),
	"public.tbl_preoutgoing_preoutgoing_status" => array("status_id","preoutgoing_status_kurzbz","preoutgoing_id","datum","insertamum","insertvon","updateamum","updatevon"),
	"public.tbl_preoutgoing_status" => array("preoutgoing_status_kurzbz","bezeichnung"),
	"public.tbl_prestudent"  => array("prestudent_id","aufmerksamdurch_kurzbz","person_id","studiengang_kz","berufstaetigkeit_code","ausbildungcode","zgv_code","zgvort","zgvdatum","zgvmas_code","zgvmaort","zgvmadatum","aufnahmeschluessel","facheinschlberuf","reihungstest_id","anmeldungreihungstest","reihungstestangetreten","rt_gesamtpunkte","rt_punkte1","rt_punkte2","bismelden","anmerkung","dual","insertamum","insertvon","updateamum","updatevon","ext_id","ausstellungsstaat","rt_punkte3", "zgvdoktor_code", "zgvdoktorort", "zgvdoktordatum","mentor","zgvnation","zgvmanation","zgvdoktornation","gsstudientyp_kurzbz","aufnahmegruppe_kurzbz"),
	"public.tbl_prestudentstatus"  => array("prestudent_id","status_kurzbz","studiensemester_kurzbz","ausbildungssemester","datum","orgform_kurzbz","insertamum","insertvon","updateamum","updatevon","ext_id","studienplan_id","bestaetigtam","bestaetigtvon","fgm","faktiv", "anmerkung","bewerbung_abgeschicktamum","rt_stufe"),
	"public.tbl_raumtyp"  => array("raumtyp_kurzbz","beschreibung","kosten"),
	"public.tbl_reihungstest"  => array("reihungstest_id","studiengang_kz","ort_kurzbz","anmerkung","datum","uhrzeit","updateamum","updatevon","insertamum","insertvon","ext_id","freigeschaltet","max_teilnehmer","oeffentlich","studiensemester_kurzbz","aufnahmegruppe_kurzbz","stufe","anmeldefrist"),
	"public.tbl_rt_ort" => array("rt_id","ort_kurzbz","uid"),
	"public.tbl_rt_person" => array("rt_person_id","person_id","rt_id","studienplan_id","anmeldedatum","teilgenommen","ort_kurzbz","punkte"),
	"public.tbl_rt_studienplan" => array("reihungstest_id","studienplan_id"),
	"public.tbl_status"  => array("status_kurzbz","beschreibung","anmerkung","ext_id","bezeichnung_mehrsprachig"),
	"public.tbl_status_grund" => array("statusgrund_id","status_kurzbz","aktiv","bezeichnung_mehrsprachig","beschreibung"),
	"public.tbl_semesterwochen"  => array("semester","studiengang_kz","wochen"),
	"public.tbl_service" => array("service_id", "bezeichnung","beschreibung","ext_id","oe_kurzbz","content_id"),
	"public.tbl_sprache"  => array("sprache","locale","flagge","index","content","bezeichnung"),
	"public.tbl_standort"  => array("standort_id","adresse_id","kurzbz","bezeichnung","insertvon","insertamum","updatevon","updateamum","ext_id", "firma_id","code"),
	"public.tbl_statistik"  => array("statistik_kurzbz","bezeichnung","url","gruppe","sql","content_id","insertamum","insertvon","updateamum","updatevon","berechtigung_kurzbz","publish","preferences"),
	"public.tbl_student"  => array("student_uid","matrikelnr","prestudent_id","studiengang_kz","semester","verband","gruppe","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_studentlehrverband"  => array("student_uid","studiensemester_kurzbz","studiengang_kz","semester","verband","gruppe","updateamum","updatevon","insertamum","insertvon","ext_id"),
	"public.tbl_studiengang"  => array("studiengang_kz","kurzbz","kurzbzlang","typ","bezeichnung","english","farbe","email","telefon","max_semester","max_verband","max_gruppe","erhalter_kz","bescheid","bescheidbgbl1","bescheidbgbl2","bescheidgz","bescheidvom","orgform_kurzbz","titelbescheidvom","aktiv","ext_id","zusatzinfo_html","moodle","sprache","testtool_sprachwahl","studienplaetze","oe_kurzbz","lgartcode","mischform","projektarbeit_note_anzeige", "onlinebewerbung"),
	"public.tbl_studiengangstyp" => array("typ","bezeichnung","beschreibung"),
	"public.tbl_studienjahr"  => array("studienjahr_kurzbz","bezeichnung"),
	"public.tbl_studiensemester"  => array("studiensemester_kurzbz","bezeichnung","start","ende","studienjahr_kurzbz","ext_id","beschreibung","onlinebewerbung"),
	"public.tbl_tag"  => array("tag"),
	"public.tbl_variable"  => array("name","uid","wert"),
	"public.tbl_vorlage"  => array("vorlage_kurzbz","bezeichnung","anmerkung","mimetype","attribute"),
	"public.tbl_vorlagedokument"  => array("vorlagedokument_id","sort","vorlagestudiengang_id","dokument_kurzbz"),
	"public.tbl_vorlagestudiengang"  => array("vorlagestudiengang_id","vorlage_kurzbz","studiengang_kz","version","text","oe_kurzbz","style","berechtigung","anmerkung_vorlagestudiengang","aktiv","sprache","subject","orgform_kurzbz"),
	"testtool.tbl_ablauf"  => array("ablauf_id","gebiet_id","studiengang_kz","reihung","gewicht","semester", "insertamum","insertvon","updateamum", "updatevon","ablauf_vorgaben_id","studienplan_id"),
	"testtool.tbl_ablauf_vorgaben"  => array("ablauf_vorgaben_id","studiengang_kz","sprache","sprachwahl","content_id","insertamum","insertvon","updateamum", "updatevon"),
	"testtool.tbl_antwort"  => array("antwort_id","pruefling_id","vorschlag_id"),
	"testtool.tbl_frage"  => array("frage_id","kategorie_kurzbz","gebiet_id","level","nummer","demo","insertamum","insertvon","updateamum","updatevon","aktiv"),
	"testtool.tbl_gebiet"  => array("gebiet_id","kurzbz","bezeichnung","beschreibung","zeit","multipleresponse","kategorien","maxfragen","zufallfrage","zufallvorschlag","levelgleichverteilung","maxpunkte","insertamum", "insertvon", "updateamum", "updatevon", "level_start","level_sprung_auf","level_sprung_ab","antwortenprozeile","bezeichnung_mehrsprachig"),
	"testtool.tbl_kategorie"  => array("kategorie_kurzbz","gebiet_id"),
	"testtool.tbl_kriterien"  => array("gebiet_id","kategorie_kurzbz","punkte","typ"),
	"testtool.tbl_pruefling"  => array("pruefling_id","prestudent_id","studiengang_kz","idnachweis","registriert","semester"),
	"testtool.tbl_vorschlag"  => array("vorschlag_id","frage_id","nummer","punkte","insertamum","insertvon","updateamum","updatevon","aktiv"),
	"testtool.tbl_pruefling_frage"  => array("prueflingfrage_id","pruefling_id","frage_id","nummer","begintime","endtime"),
	"testtool.tbl_frage_sprache"  => array("frage_id","sprache","text","bild","audio","insertamum","insertvon","updateamum","updatevon"),
	"testtool.tbl_vorschlag_sprache"  => array("vorschlag_id","sprache","text","bild","audio","insertamum","insertvon","updateamum","updatevon"),
	"system.tbl_app" => array("app"),
	"system.tbl_appdaten" => array("appdaten_id","uid","app","appversion","version","bezeichnung","daten","freigabe","insertamum","insertvon","updateamum","updatevon"),
	"system.tbl_cronjob"  => array("cronjob_id","server_kurzbz","titel","beschreibung","file","last_execute","aktiv","running","jahr","monat","tag","wochentag","stunde","minute","standalone","reihenfolge","updateamum", "updatevon","insertamum","insertvon","variablen"),
	"system.tbl_benutzerrolle"  => array("benutzerberechtigung_id","rolle_kurzbz","berechtigung_kurzbz","uid","funktion_kurzbz","oe_kurzbz","art","studiensemester_kurzbz","start","ende","negativ","updateamum", "updatevon","insertamum","insertvon","kostenstelle_id","anmerkung"),
	"system.tbl_berechtigung"  => array("berechtigung_kurzbz","beschreibung"),
	"system.tbl_phrase" => array("phrase_id","app","phrase","insertamum","insertvon"),
	"system.tbl_phrasentext" => array("phrasentext_id","phrase_id","sprache","orgeinheit_kurzbz","orgform_kurzbz","text","description","insertamum","insertvon"),
	"system.tbl_rolle"  => array("rolle_kurzbz","beschreibung"),
	"system.tbl_rolleberechtigung"  => array("berechtigung_kurzbz","rolle_kurzbz","art"),
	"system.tbl_webservicelog"  => array("webservicelog_id","webservicetyp_kurzbz","request_id","beschreibung","request_data","execute_time","execute_user"),
	"system.tbl_webservicerecht" => array("webservicerecht_id","berechtigung_kurzbz","methode","attribut","insertamum","insertvon","updateamum","updatevon","klasse"),
	"system.tbl_webservicetyp"  => array("webservicetyp_kurzbz","beschreibung"),
	"system.tbl_server"  => array("server_kurzbz","beschreibung"),
	"wawi.tbl_betriebsmittelperson"  => array("betriebsmittelperson_id","betriebsmittel_id","person_id", "anmerkung", "kaution", "ausgegebenam", "retouram","insertamum", "insertvon","updateamum", "updatevon","ext_id","uid"),
	"wawi.tbl_betriebsmittel"  => array("betriebsmittel_id","betriebsmitteltyp","oe_kurzbz", "ort_kurzbz", "beschreibung", "nummer", "hersteller","seriennummer", "bestellung_id","bestelldetail_id", "afa","verwendung","anmerkung","reservieren","updateamum","updatevon","insertamum","insertvon","ext_id","inventarnummer","leasing_bis","inventuramum","inventurvon","anschaffungsdatum","anschaffungswert","hoehe","breite","tiefe","nummer2","verplanen"),
	"wawi.tbl_betriebsmittel_betriebsmittelstatus"  => array("betriebsmittelbetriebsmittelstatus_id","betriebsmittel_id","betriebsmittelstatus_kurzbz", "datum", "updateamum", "updatevon", "insertamum", "insertvon","anmerkung"),
	"wawi.tbl_betriebsmittelstatus"  => array("betriebsmittelstatus_kurzbz","beschreibung"),
	"wawi.tbl_betriebsmitteltyp"  => array("betriebsmitteltyp","beschreibung","anzahl","kaution","typ_code","mastershapename"),
	"wawi.tbl_budget"  => array("geschaeftsjahr_kurzbz","kostenstelle_id","budget"),
	"wawi.tbl_zahlungstyp"  => array("zahlungstyp_kurzbz","bezeichnung"),
	"wawi.tbl_konto"  => array("konto_id","kontonr","beschreibung","kurzbz","aktiv","person_id","insertamum","insertvon","updateamum","updatevon","ext_id","person_id"),
	"wawi.tbl_konto_kostenstelle"  => array("konto_id","kostenstelle_id","insertamum","insertvon"),
	"wawi.tbl_kostenstelle"  => array("kostenstelle_id","oe_kurzbz","bezeichnung","kurzbz","aktiv","insertamum","insertvon","updateamum","updatevon","ext_id","kostenstelle_nr","deaktiviertvon","deaktiviertamum"),
	"wawi.tbl_bestellungtag"  => array("tag","bestellung_id","insertamum","insertvon"),
	"wawi.tbl_bestelldetailtag"  => array("tag","bestelldetail_id","insertamum","insertvon"),
	"wawi.tbl_projekt_bestellung"  => array("projekt_kurzbz","bestellung_id","anteil"),
	"wawi.tbl_bestellung"  => array("bestellung_id","besteller_uid","kostenstelle_id","konto_id","firma_id","lieferadresse","rechnungsadresse","freigegeben","bestell_nr","titel","bemerkung","liefertermin","updateamum","updatevon","insertamum","insertvon","ext_id","zahlungstyp_kurzbz"),
	"wawi.tbl_bestelldetail"  => array("bestelldetail_id","bestellung_id","position","menge","verpackungseinheit","beschreibung","artikelnummer","preisprove","mwst","erhalten","sort","text","updateamum","updatevon","insertamum","insertvon"),
	"wawi.tbl_bestellung_bestellstatus"  => array("bestellung_bestellstatus_id","bestellung_id","bestellstatus_kurzbz","uid","oe_kurzbz","datum","insertamum","insertvon","updateamum","updatevon"),
	"wawi.tbl_bestellstatus"  => array("bestellstatus_kurzbz","beschreibung"),
	"wawi.tbl_buchung"  => array("buchung_id","konto_id","kostenstelle_id","buchungstyp_kurzbz","buchungsdatum","buchungstext","betrag","insertamum","insertvon","updateamum","updatevon","ext_id"),
	"wawi.tbl_buchungstyp"  => array("buchungstyp_kurzbz","bezeichnung"),
	"wawi.tbl_rechnungstyp"  => array("rechnungstyp_kurzbz","beschreibung","berechtigung_kurzbz"),
	"wawi.tbl_rechnung"  => array("rechnung_id","bestellung_id","buchungsdatum","rechnungsnr","rechnungsdatum","transfer_datum","buchungstext","insertamum","insertvon","updateamum","updatevon","rechnungstyp_kurzbz","freigegeben","freigegebenvon","freigegebenamum"),
	"wawi.tbl_rechnungsbetrag"  => array("rechnungsbetrag_id","rechnung_id","mwst","betrag","bezeichnung","ext_id"),
	"wawi.tbl_aufteilung"  => array("aufteilung_id","bestellung_id","oe_kurzbz","anteil","insertamum","insertvon","updateamum","updatevon"),
	"wawi.tbl_aufteilung_default"  => array("aufteilung_id","kostenstelle_id","oe_kurzbz","anteil","insertamum","insertvon","updateamum","updatevon"),
);

$tabs=array_keys($tabellen);
//print_r($tabs);
$i=0;
foreach ($tabellen AS $attribute)
{
	$sql_attr='';
	foreach($attribute AS $attr)
		$sql_attr.=$attr.',';
	$sql_attr=substr($sql_attr, 0, -1);

	if (!@$db->db_query('SELECT '.$sql_attr.' FROM '.$tabs[$i].' LIMIT 1;'))
		echo '<BR><strong>'.$tabs[$i].': '.$db->db_last_error().' </strong><BR>';
	else
		echo $tabs[$i].': OK - ';
	flush();
	$i++;
}

echo '<H2>Gegenpruefung!</H2>';
$error=false;
$sql_query="SELECT schemaname,tablename FROM pg_catalog.pg_tables WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema' AND schemaname != 'sync' AND schemaname != 'addon' AND schemaname != 'reports';";
if (!$result=@$db->db_query($sql_query))
		echo '<BR><strong>'.$db->db_last_error().' </strong><BR>';
	else
		while ($row=$db->db_fetch_object($result))
		{
			$fulltablename=$row->schemaname.'.'.$row->tablename;
			if (!isset($tabellen[$fulltablename]))
			{
				echo 'Tabelle '.$fulltablename.' existiert in der DB, aber nicht in diesem Skript!<BR>';
				$error=true;
			}
			else
				if (!$result_fields=@$db->db_query("SELECT * FROM $fulltablename LIMIT 1;"))
					echo '<BR><strong>'.$db->db_last_error().' </strong><BR>';
				else
					for ($i=0; $i<$db->db_num_fields($result_fields); $i++)
					{
						$found=false;
						$fieldnameDB=$db->db_field_name($result_fields,$i);
						foreach ($tabellen[$fulltablename] AS $fieldnameARRAY)
							if ($fieldnameDB==$fieldnameARRAY)
							{
								$found=true;
								break;
							}
						if (!$found)
						{
							echo 'Attribut '.$fulltablename.'.<strong>'.$fieldnameDB.'</strong> existiert in der DB, aber nicht in diesem Skript!<BR>';
							$error=true;
						}
					}
		}
if($error==false)
	echo '<br>Gegenpruefung fehlerfrei';

?>
