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
 * Webdav File fuer DMS
 */
require_once(dirname(__FILE__).'/../include/dms.class.php');
require_once(dirname(__FILE__).'/../include/datum.class.php');
require_once(dirname(__FILE__).'/../include/benutzerberechtigung.class.php');

class DMSFile extends Sabre_DAV_File
{
	private $myPath;
	private $kategorie_kurzbz;
	private $name;
	private $dms_id;
	private $auth;

	function __construct($myPath, $auth=null) 
	{
		$this->myPath = $myPath;
		//Dateinamen ermitteln
		$this->name = mb_substr($myPath, mb_strrpos($myPath,"/")+1);
		//Kategorie ermitteln
		$katpath = mb_substr($myPath,0,mb_strrpos($myPath,"/"));
		$this->kategorie_kurzbz = $this->getKategorie($katpath);

		//DMS ID ermitteln
		$dms = new dms();
		$dms->getDocumentFromName($this->name, $this->kategorie_kurzbz);
		if(isset($dms->result[0]))
			$this->dms_id = $dms->result[0]->dms_id;
		//else
		//	error_log("fileNotFound $this->name in kat $this->kategorie_kurzbz");

		$this->auth = $auth;
	}

	/**
	 * Liefert den eingeloggten User
	 */
	function getUser()
	{
		return $this->auth->getCurrentUser();
	}

	/**
	 * Parst die Kategorie aus dem Pfad
	 */
	function getKategorie($path)
	{
		$parts = explode($path, "/");

		$parent = null;
		foreach($parts as $kat)
		{
			$dms = new dms();
			$dms->getKategorieFromBezeichnung($kat, $parent);
			if(isset($dms->result[0]))
				$parent = $dms->result[0]->kategorie_kurzbz;
		}
		return $parent;
	}

	/**
	 * Liefert den Dateinamen
	 */
	function getName() 
	{
		return $this->name;
	}

	/**
	 * Liefert die Datei
	 */
	function get() 
	{
		$dms = new dms();
		$dms->load($this->dms_id);
		return fopen(DMS_PATH.$dms->filename,'r');
	}

	/**
	 * Liefert die Dateigroesse
	 */
	function getSize() 
	{
		$dms = new dms();
		$dms->load($this->dms_id);
		return filesize(DMS_PATH.$dms->filename);
	}

	/**
	 * Liefert einen eindeutige Identifier fuer die Datei
	 */
	function getETag() 
	{
		$dms = new dms();
		$dms->load($this->dms_id);
		return '"' . md5_file(DMS_PATH.$dms->filename) . '"';
	}

	/**
	 * Entfernt eine Datei
	 */
	function delete()
	{
		$dms = new dms();
		$rechte = new benutzerberechtigung();
		$rechte->getBerechtigungen($this->getUser());

		if($rechte->isBerechtigt('basis/dms',null,'suid'))
		{
			$dms->load($this->dms_id);
			if(!$dms->deleteDms($this->dms_id))
			{
				throw new Sabre_DAV_Exception_FileNotFound('Failed '.$dms->errormsg);
			}
			else
			{
				unlink(DMS_PATH.$dms->filename);
			}
		}
		else
		{
			throw new Sabre_DAV_Exception_MethodNotAllowed('Keine Berechtigung');
		}
	}
	
	/**
	 * Benennt eine Datei um
	 */
	function setName($newName)
	{
		$dms = new dms();
		if($dms->load($this->dms_id))
		{
			$dms->updateamum = date('Y-m-d H:i:s');
			$dms->updatevon = $this->getUser();
			$dms->name = $newName;
			if(!$dms->save(false))
			{
				throw new Sabre_DAV_Exception_FileNotFound('Failed '.$dms->errormsg);
			}
			else
				$this->name = $newName;
		}
		else
		{
			throw new Sabre_DAV_Exception_FileNotFound('Failed '.$dms->errormsg);
		}
	}
	
	/**
	 * Liefert Timestamp der letzten Aenderung
	 */
	function getLastModified()
	{
		$dms = new dms();
		$dms->load($this->dms_id);
		$datum = new datum();
		if($dms->updateamum!='')
			$ts = $datum->mktime_fromtimestamp($dms->updateamum);
		else
			$ts = $datum->mktime_fromtimestamp($dms->insertamum);
		return $ts;
	}
	
	/**
	 * Speichert Daten in eine Datei
	 * @param $data
	 */
	function put($data)
	{
		$dms = new dms();
		if($dms->load($this->dms_id))
		{
			$dms->version = $dms->version++;

			$pos = mb_strrpos($dms->name,'.')+1;
			if($pos>1)
				$ext = '.'.mb_substr($dms->name, $pos);
			else
				$ext ='';
			$filename=uniqid().$ext; 
	   		$dms->version++;
		    $dms->insertamum=date('Y-m-d H:i:s');
	    	$dms->insertvon = $this->getUser();
	    	$dms->filename = $filename;
    	    	
			if($dms->save(true))
			{
				if(file_put_contents(DMS_PATH.$filename, $data))
				{
					if(!chgrp(DMS_PATH.$filename,'dms'))
						echo 'CHGRP failed';
					if(!chmod(DMS_PATH.$filename, 0774))
						echo 'CHMOD failed';
					exec('sudo chown wwwrun '.$filename);	

					$dms->save(false);
				}
				else
					throw new Sabre_DAV_Exception_FileNotFound('Failed to Write File');
			}
			else
			{
				throw new Sabre_DAV_Exception_FileNotFound('Failed '.$dms->errormsg);
			}
		}
	}
}
?>
