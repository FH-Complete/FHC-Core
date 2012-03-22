<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Webdav Directory fuer DMS
 */
require_once(dirname(__FILE__).'/../include/dms.class.php');
require_once(dirname(__FILE__).'/../include/benutzerberechtigung.class.php');

class DMSDirectory extends Sabre_DAV_Collection 
{
	private $myPath;
	private $kategorie_kurzbz;
	private $kategorie_bezeichnung;
	private $auth;

	function __construct($myPath, $auth=null) 
	{
		if($myPath=='')
		{
			$this->kategorie_kurzbz=null;
			$this->kategorie_bezeichnung='';
			$this->myPath='/';
		}
		else
		{
			$this->myPath = $myPath;
			//KategorieKurzbz ermitteln
			$this->kategorie_kurzbz = $this->getKategorie($myPath);
		
			//Kategorie Bezeichnung ermitteln
			$this->kategorie_bezeichnung = mb_substr($myPath,mb_strrpos($myPath,"/"));
		}
	    $this->auth = $auth;
	}

	function getUser()
	{
		return $this->auth->getCurrentUser();
	}

	/**
	 * ermittelt die Kategorie Kurzbz aus dem Pfadnamen
	 * 
	 */
	function getKategorie($path)
	{
		$parts = explode("/",$path);

		$parent = null;
		foreach($parts as $kat)
		{
			$dms = new dms();
			$dms->getKategorieFromBezeichnung($kat, $parent);
			if(isset($dms->result[0]))
				$parent = $dms->result[0]->kategorie_kurzbz;
			//else
			//	error_log("error katbezsearch kat: $kat parent: $parent");
		}
		return $parent;
	}

	/**
	 * Liefert die Kindelemente des Ordners / der Kategorie
	 */
	function getChildren()
	{
		$dms = new dms();

		//Kategorien holen
		$dms->getKategorie($this->kategorie_kurzbz);
		$children = array();
		// Loop through the directory, and create objects for each node
		foreach($dms->result as $row) 
		{
			if($dms->isBerechtigtKategorie($row->kategorie_kurzbz, $this->getUser()))
				$children[] = $this->getChild($row->bezeichnung);
		}

		if($this->kategorie_kurzbz!='')
		{
			//Dokumente holen
			$dms->getDocuments($this->kategorie_kurzbz);
			foreach($dms->result as $row)
			{
				if(!$dms->isLocked($row->dms_id) || $dms->isBerechtigt($row->dms_id, $this->getUser()))
				{
					$children[] = $this->getChild($row->name);
				}
			}
		}
		return $children;
	}

	/**
	 * Holt das Objekt mit dem entsprechenden Namen
	 */
	function getChild($name) 
	{
		if($name!='')
		{
			$dms = new dms();
			if($dms->getDocumentFromName($name, $this->kategorie_kurzbz) && count($dms->result)>0)
				return new DMSFile($this->myPath.'/'.$name, $this->auth);
			elseif($dms->getKategorieFromBezeichnung($name, $this->kategorie_kurzbz) && count($dms->result)>0)
				return new DMSDirectory($this->myPath.'/'.$name, $this->auth);
			else
			{
				// We have to throw a FileNotFound exception if the file didn't exist
				throw new Sabre_DAV_Exception_FileNotFound('The file with name: ' . $name . ' could not be found');
			}
		}
		else
			return new DMSDirectory($this->myPath.'/'.$name,$this->auth);
	}

	/**
	 * Prueft ob ein Kindelement mit dem Namen existiert
	 */
	function childExists($name) 
	{
		$dms = new dms();
		if($dms->getDocumentFromName($name, $this->kategorie_kurzbz) && count($dms->result)>0)
			return true;
		elseif($dms->getKategorieFromBezeichnung($name, $this->kategorie_kurzbz) && count($dms->result)>0)
			return true;
		else
			return false;
	}

	/**
	 * Liefert den Namen der Kategorie / des Ordners
	 */
	function getName() 
	{
		return $this->kategorie_bezeichnung;
	}

	/**
	 * Loescht die Kategorie / den Ordner
	 */
	function delete()
	{
		$dms = new dms();
		$rechte = new benutzerberechtigung();
		$rechte->getBerechtigungen($this->getUser());
		if($rechte->isBerechtigt('basis/dms',null, 'suid'))
		{
			if(!$dms->deleteKategorie($this->kategorie_kurzbz))
				throw new Sabre_DAV_Exception_MethodNotAllowed('Failed '.$dms->errormsg);
		}
		else
			throw new Sabre_DAV_Exception_MethodNotAllowed('Keine Berechtigung');
	}

	/**
	 * Aendert den Namen einer Kategorie
	 * @oaram $newName
	 */
	function setName($newName)
	{
		$dms = new dms();
		if($dms->loadKategorie($this->kategorie_kurzbz))
		{
			$dms->bezeichnung = $newName;
			$dms->kategorie_kurzbz = $newName;
			$dms->beschreibung = $newName;
			$this->kategorie_bezeichnung = $newName;
			if(!$dms->saveKategorie(false))
			{
				throw new Sabre_DAV_Exception_FileNotFound('Rename Failed');
			}
		}
		else
		{
			throw new Sabre_DAV_Exception_FileNotFound('Directory not found');
		}
	}

	/**
	 * Erstellt eine neue Datei
	 */
	function createFile($name, $data=null)
	{
		$dms = new dms();
		$pos = mb_strrpos($name,'.')+1;
		if($pos>1)
			$ext = '.'.mb_substr($name, $pos);
		else
			$ext ='';
		$filename=uniqid().$ext; 
   		$dms->version='0';
   		$dms->kategorie_kurzbz=$this->kategorie_kurzbz;		
	    $dms->insertamum=date('Y-m-d H:i:s');
    	$dms->insertvon = $this->getUser();
    	//$dms->mimetype= mime_content_type(IMPORT_PATH.$importFile); 
    	$dms->filename = $filename;
    	$dms->name = $name;
    	    	
    	if($dms->save(true))
    	{
			file_put_contents(DMS_PATH.$filename, $data);
	    	if(!chgrp(DMS_PATH.$filename,'dms'))
				echo 'CHGRP failed';
			if(!chmod(DMS_PATH.$filename, 0774))
				echo 'CHMOD failed';
			exec('sudo chown wwwrun '.$filename);	
    	}    	
    	else
    		throw new Sabre_DAV_Exception_MethodNotAllowed('Failed '.$dms->errormsg);
	}

	/**
	 * Erstellt ein neues Directory / Kategorie
	 * @param name Name des Ordners
	 */
	function CreateDirectory($name)
	{
		$dms = new dms();
		if(!$dms->loadKategorie($name))
		{
			$dms->kategorie_kurzbz=$name;
			$dms->bezeichnung = $name;
			$dms->beschreibung = $name;
			$dms->parent_kategorie_kurzbz = $this->kategorie_kurzbz;

			if(!$dms->saveKategorie(true))
			{
				throw new Sabre_DAV_Exception_MethodNotAllowed('Failed '.$dms->errormsg);
			}
		}
		else
		{
			throw new Sabre_DAV_Exception_MethodNotAllowed('Directory already exists');
		}
	}
}
?>
