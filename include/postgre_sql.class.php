<?php
//-------------------------------------------------------------------------------------------------
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>,
 *          Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>.
 */

#--------------------------------------------------------------------------------------------------
/*
*
* @classe postgre_sql 
*
* @param connectSQL Datenbankverbindung
*
* @return - kein Retourn des Konstruktors
*
*/
class postgre_sql
{
	protected $error="";   			// Letzter aufgetretene Fehler

	protected $connectSQL; 			// Verbindungsobjekt zur Datenbank

	protected $connectstringSQL=""; // Datenbankverbindungsstring 

	protected $stringSQL; 			// Letzte Datenbankverarbeitungsstring
	protected $resourceSQL;			// Letzter Datenbankzugriff  
	protected $resultSQL;			// Datenabfrageergebnis
		
	protected $tableSQL;				// Tabelle auf die zugegriffen wird
	protected $tableStruckturSQL;		// Tabellenstucktur

	protected $encodingSQL=null; 		// Datenencoding Clint
	
	protected $newRecord;			// Switch Datengefunde 
		
	protected $schemaSQL="public"; // Datenbankschema
//---Konstruktor----------------------------------------------------------------------------------------------
       function postgre_sql($connectSQL)   // Konstruktor
       {
           $this->setConnectSQL($connectSQL);
       }    
//---ERROR----------------------------------------------------------------------------------------------
       function getError() 
       {
           return $this->error;
       }
       function setError($error) 
       {
           $this->error=$error;
       }
//---// Verbindungsobjekt zur Datenbank----------------------------------------------------------------------------------------------
       function getConnectSQL() 
       {
           return $this->connectSQL;
       }
       function setConnectSQL($connectSQL) 
       {
           $this->connectSQL=$connectSQL;
       }  	   
//-----schemaSQL--------------------------------------------------------------------------------------------
       function getSchemaSQL() 
       {
           return (!empty($this->schemaSQL)?$this->schemaSQL.'.':'');
       }
       function setSchemaSQL($schemaSQL) 
       {
           $this->schemaSQL=$schemaSQL;
       }
//-------------------------------------------------------------------------------------------------
       function getResourceSQL() 
       {
           return $this->resourceSQL;
       }
       function setResourceSQL($resourceSQL) 
       {
           $this->resourceSQL=$resourceSQL;
       }  	   
//-------------------------------------------------------------------------------------------------
       function getEncodingSQL() 
       {
           return $this->encodingSQL;
       }
       function setEncodingSQL($encodingSQL) 
       {
           $this->encodingSQL=$encodingSQL;
       }  	
//-------------------------------------------------------------------------------------------------
       function getStringSQL() 
       {
           return $this->stringSQL;
       }
       function setStringSQL($stringSQL) 
       {
           $this->stringSQL=$stringSQL;
       }
//---SQL Verbindungsstring -------------------------------------------------------------------------
       function getConnectstringSQL() 
       {
           return $this->connectstringSQL;
       }
       function setConnectstringSQL($connectstringSQL) 
       {
           $this->connectstringSQL=$connectstringSQL;
       }  	   
//-------------------------------------------------------------------------------------------------
       function getNewRecord() 
       {
           return $this->newRecord;
       }
       function setNewRecord($newRecord) 
       {
              $this->newRecord=$newRecord;
       }
//-------------------------------------------------------------------------------------------------
       function getResultSQL() 
       {
           return $this->resultSQL;
       }
       function setResultSQL($resultSQL) 
       {
		$this->free_result(); // Vorherige Ergebnisse entfernen
		$this->resultSQL=$resultSQL;
		if ($this->resultSQL!=null) 
		{		
			if ($this->resultSQL)
				$this->setNewRecord(false);
			else
				$this->setNewRecord(true);
		}
       }
//-------------------------------------------------------------------------------------------------
       function getTableSQL() 
       {
           return $this->tableSQL;
       }
       function setTableSQL($tableSQL) 
       {
		$this->tableSQL=$tableSQL;
       }
#--------------------------------------------------------------------------------------------------
/*
*
* @dbFehler Setzt den Fehlertext fuer einen Resourcenfehler, oder der letzte Fehler der aufgetreten ist
*
* @param - keine Parameter
*
* @return Retour wird der ermittelte Fehlertext, bzw false wenn kein Fehler gefunden wurde
*
*/
       function dbFehler()
       {

		$this->setResultSQL(null); 

		if ($this->getResourceSQL())
		{	
			$this->setError(@pg_result_error($this->getResourceSQL()));
			if ($this->getError()) return $this->getError();
		}	  

		if ($this->getConnectSQL())
		{
			$this->setError(@pg_last_error($this->getConnectSQL())); 
			if ($this->getError()) return $this->getError();
		}	  
		
		if ($this->getError()) return $this->getError();
		return '';
       }	
       #--------------------------------------------------------------------------------------------------
       /*
       *
       * @dbConnect Setzt eine SQL Abfrage in der DB ab, und liefert den Result der Abfrage als Objekt retour
       *
       * @param connectstringSQL Verbindung zur Datenbank moegliche uebergabe eines Datenbankstring 
       *
       * @return Verbindungs Objekt zur Datenbank 
       *
       */
       function dbConnect($connectstringSQL="")
       {
	   
	   		$this->setConnectSQL(null);
	   		if (!empty($connectstringSQL))
				$this->setConnectstringSQL($connectstringSQL);
			if ($connectSQL=@pg_pconnect($this->getConnectstringSQL()))
			{		
				$this->setConnectSQL($connectSQL);
				return $this->getConnectSQL();
			}
			return	$this->dbFehler();
	   }	      
       #--------------------------------------------------------------------------------------------------
       /*
       *
       * @dbQuery Setzt eine SQL Abfrage in der DB ab, und liefert den Result der Abfrage als Objekt retour
       *
       * @param stringSQL Datenbankabfrage bzw. SQL-Abfrage wenn nicht vorher mittels setStringSQL gesetzt wurde
       *
       * @return Resource der SQL Anfrage / Abfrage 
       *
       */
       function dbQuery($stringSQL="")
       {
		// Initialisieren DB Resource		
		$this->setNewRecord(true);
		$this->setResourceSQL(null);
			  
		// SQL Befehl aus Parameter oder aus der ClassenVariable entnehmen 
              if (empty($stringSQL)) 
			$stringSQL=$this->getStringSQL();
		// Letzten SQL Befehl merken
		$this->setStringSQL($stringSQL);
		// Encoding
              if($this->getEncodingSQL()!="" && $this->getEncodingSQL()!=null)
        		$stringSQL="SET CLIENT_ENCODING TO '".$this->getEncodingSQL()."'; ".$stringSQL;
		
		if (!$resourceSQL=@pg_query($this->getConnectSQL(),$stringSQL))
              {
                 $this->dbFehler();
         	  	 return false;
              }
              $this->setResourceSQL($resourceSQL);
              return $this->getResourceSQL();
       }
       #--------------------------------------------------------------------------------------------------
       /*
       *
       * @fetch_object Setzt den Select in der DB ab, und liefert den Result der Abfrage in Objektform retour
       *				Die Funktion wird fuer Insert,Delete,Update, Transaktionen benoetigt
       *
       * @param stringSQL Datenbankabfrage bzw. SQL-Abfrage wenn nicht vorher mittels setStringSQL gesetzt wurde
       *
       * @return Ergebniss der Datenabfrage, oder Fehlerinformation
       *
       */
       function free_result()
       {
		if ($this->getResultSQL() && $this->getResourceSQL())
		{
			return @pg_free_result($this->getResourceSQL());	
              }
       	return false;
       }	
       #--------------------------------------------------------------------------------------------------
       /*
       *
       * @fetch_object Setzt den Select in der DB ab, und liefert den Result der Abfrage in Objektform retour
       *				Die Funktion wird fuer Insert,Delete,Update, Transaktionen benoetigt
       *
       * @param stringSQL Datenbankabfrage bzw. SQL-Abfrage wenn nicht vorher mittels setStringSQL gesetzt wurde
       *
       * @return Ergebniss der Datenabfrage, oder Fehlerinformation
       *
       */
       function fetch_array($stringSQL="")
       {
              if (!$this->dbQuery($stringSQL))
       	  	return false;
              if(!$resultSQL=@pg_fetch_array($this->getResourceSQL(),null,PGSQL_ASSOC))	
              {
                 $this->dbFehler();
         	  	 return false;
              }	
              $this->setResultSQL($resultSQL);
       	return true;
       }	
       #--------------------------------------------------------------------------------------------------
       /*
       *
       * @fetch_object Setzt den Select in der DB ab, und liefert den Result der Abfrage in Objektform retour
       *				Die Funktion wird fuer Insert,Delete,Update, Transaktionen benoetigt
       *
       * @param stringSQL Datenbankabfrage bzw. SQL-Abfrage wenn nicht vorher mittels setStringSQL gesetzt wurde
       *
       * @return Ergebniss der Datenabfrage, oder Fehlerinformation
       *
       */
       function fetch_object($stringSQL="")
       {
              if (!$this->dbQuery($stringSQL))
       	  	return false;
              if(!$resultSQL=@pg_fetch_object($this->getResourceSQL()))	
              {
                 $this->dbFehler();
         	  	 return false;
              }	
              $this->setResultSQL($resultSQL);
       	return true;
       }
       #--------------------------------------------------------------------------------------------------
       /*
       *
       * @fetch_all Liefert zu einem SQL Select alle gefundenen Daten in einem Array
       *
       * @param stringSQL Datenbankabfrage bzw. SQL-Abfrage wenn nicht vorher mittels setStringSQL gesetzt wurde
       *
       * @return Array der gefunden Daten bzw. Leer oder eine DB Fehlernachricht
       *
       */
       function fetch_all($stringSQL="")
       {
              if (!$this->dbQuery($stringSQL))
			return false;
              if(!$resultSQL=@pg_fetch_all($this->getResourceSQL()))	
              {
			$this->dbFehler();
			return false;
              }	
              $this->setResultSQL($resultSQL);
       	return true;
      }
       #--------------------------------------------------------------------------------------------------
       /*
       *
       * @fetch_object_all Liefert zu einem SQL Select alle gefundenen Daten in einem Array
       *
       * @param stringSQL Datenbankabfrage bzw. SQL-Abfrage wenn nicht vorher mittels setStringSQL gesetzt wurde
       *
       * @return Array der gefunden Daten bzw. Leer oder eine DB Fehlernachricht
       *
       */
       function fetch_all_object($stringSQL="")
       {
              if (!$this->dbQuery($stringSQL))
			return false;
		$resultSQL=array();
		if(!$resultSQL[]=@pg_fetch_object($this->getResourceSQL()))
              {
			$this->dbFehler();
			return false;
              }	
	
		while ($res=@pg_fetch_object($this->getResourceSQL()))
			if ($res) $resultSQL[]=$res;
			
             	$this->setResultSQL($resultSQL);
       	return true;
      }      
       #--------------------------------------------------------------------------------------------------
       /*
       *
       * @setTableStruckturSQL Liefert zu einer Tabelle die Strucktur und Feldinformationen
       *
       * @param tableSQL Tabelle zu der die Informationen geliefert werden sollen
       *
       * @return Array der gefunden Tabellenstrucktur
       *
       */
       function getTableStruckturSQL() 
       {
           return $this->tableStruckturSQL;
       }
       function setTableStruckturSQL($tableSQL="") 
       {
		if (!empty($tableSQL))
			$this->setTableSQL($tableSQL);
		$tableSQL=$this->getTableSQL();

             # listet die Datenbanken-Tabellenfelder auf
		$this->tableStruckturSQL=array();
                           $cTmpSQL="
                                   SELECT
                                     a.attnum,
                                     a.attname AS field,
                                     a.attname AS fildname,
                                     t.typname AS type,
                                     a.attlen AS length,
                                     a.atttypmod AS lengthvar,
                                     a.attnotnull AS notnull
                                   FROM
                                     pg_class c,
                                     pg_attribute a,
                                     pg_type t
                                   WHERE
                                     c.relname = '".$tableSQL."'
                                     AND a.attnum > 0
                                     AND a.attrelid = c.oid
                                     AND a.atttypid = t.oid
                                     ORDER BY a.attnum;
                                 ";
       // Datenbankabfrage
				$this->setStringSQL($cTmpSQL);
				unset($cTmpSQL);
				$this->setResultSQL(null);
				if (!$this->fetch_all()) 
					return false;    

				$arrTmpResultSQL=$this->getResultSQL();
				$this->setResultSQL(null);
                          # listet die Anzahl der Felder auf 
  				for ($i=0;$i<count($arrTmpResultSQL);$i++) 
                            {  
					if (isset($feldname) && $feldname==$arrTmpResultSQL[$i]['fildname'])
						continue;
					$feldname= $arrTmpResultSQL[$i]['fildname'];#gibt den Feldnamen an
					$type=$arrTmpResultSQL[$i]['type'];#gibt den Feldtyp zurück
					$laenge=($arrTmpResultSQL[$i]['length']==-1?$arrTmpResultSQL[$i]['lengthvar']:$arrTmpResultSQL[$i]['length']) ; #gibt die Länge des Feldes zurück
      				      	$flags=($arrTmpResultSQL[$i]['notnull']=='t'?' not null ':' null ');

                                   $cTmpSQL="
                                          SELECT
                                            pg_attribute.attname::text as PK
                                          FROM
                                            pg_attribute
                                          JOIN
                                            pg_class ON pg_attribute.attrelid = pg_class.oid
                                          JOIN
                                            pg_namespace ON pg_namespace.oid = pg_class.relnamespace
                                          LEFT JOIN
                                            pg_constraint ON conrelid = pg_class.oid AND pg_constraint.contype = 'p'
                                          WHERE
                                            pg_attribute.attname = '".$feldname."' AND
                                            pg_class.relname = '".$tableSQL."' AND
                                            pg_attribute.attnum = ANY (pg_constraint.conkey)
                                          ORDER BY
                                            pg_attribute.attnum;
                                        ";

					$this->setStringSQL($cTmpSQL);
					unset($cTmpSQL);
					$this->setResultSQL(null);
					if ($this->fetch_array()) 
                                        $flags.=($this->resultSQL['pk']=="$feldname"?',  primary_key ':' ');

				      	$this->tableStruckturSQL[]=array("name"=>$feldname,"flag"=>$flags,"type"=>$type,"laenge"=>$laenge);
				}			
			return $this->getTableStruckturSQL();
		}      
 } // Ende Class kommune
