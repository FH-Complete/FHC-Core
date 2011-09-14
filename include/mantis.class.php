<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * 			Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */
/**
 * @class 			Mantis
 * @author	 		Christian Paminger
 * @date	 		2011/8/22
 * @version			$Revision: 1.3 $
 * Update: 			22.08.2011 von Christian Paminger
 * @brief  			Klasse fuer die Schnittstelle zum Mantis BTS
 * Abhaengig:	 	von basis_db.class.php
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class mantis extends basis_db
{
	public $issue_id;					//ID
	public $issue_view_state;			//Anzeigestatus
	public $issue_last_updated;			//Letzte Aktualisierung
	public $issue_project;				//Projekt
	public $issue_category;				//Kategorie
	public $issue_priority;				//Prioritaet
	public $issue_severity;				//Auswirkung
	public $issue_status;				//Status
	public $issue_reporter;				//Reporter
	public $issue_summary;				//Zusammendassung
	public $issue_reproducibility;		//Reproduzierbar
	public $issue_date_submitted;		//Meldungsdatum
	public $issue_sponsorship_total;	
	public $issue_projection;			//Projektion
	public $issue_eta;					//Aufwand
	public $issue_resolution;			//Lösung
	public $issue_description;			//Beschreibung
	public $issue_attachments;			//Anhang
	public $issue_due_date;
	public $issue_steps_to_reproduce;
	public $issue_additional_information;
	
	public $soapClient;
	public $errormsg;

	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->initSoapClient();
	}

	/**
	 * Initialsiert den Soap Client
	 */
	public function initSoapClient()
	{
		try 
		{ 
			$this->soapClient = new SoapClient(MANTIS_PFAD); 
		}
		catch (Exception $e)
		{ 
    			echo $e->getMessage();
		}
	}

	/**
	 * Ticket Update
	 */
	public function updateIssue()
	{
		$issue = array('summary'=>$this->issue_summary,
					'project'=>array('id'=>$this->issue_project->id),
					'category'=>$this->issue_category,
					'description'=>$this->issue_description,
					'steps_to_reproduce'=>$this->issue_steps_to_reproduce,
					'additional_information'=>$this->issue_additional_information,
				);				
				
		$params=array('username' => MANTIS_USERNAME, 'password' => MANTIS_PASSWORT,'issueId' => $this->issue_id, $issue);
		$result = $this->soapClient->__soapCall('mc_issue_update',$params);
		return $result;
	}
	
	/**
	 * Neues Ticket anlegen
	 */
	public function insertIssue()
	{
		$result = $this->soapClient->__soapCall('mc_version',array());
		return $result;
	}
	
	/**
	 * Ticket holen
	 */
	public function getIssue($issue_id=1)
	{
		$params=array('username' => MANTIS_USERNAME, 'password' => MANTIS_PASSWORT,'issue_id' => $issue_id);
		$result = $this->soapClient->__soapCall('mc_issue_get',$params);
		
		$this->issue_id = $result->id;			
		$this->issue_view_state->id = $result->view_state->id;	
		$this->issue_view_state->name = $result->view_state->name;		
		$this->issue_last_updated = $result->last_updated; 		
		$this->issue_project->id = $result->project->id;			
		$this->issue_project->name = $result->project->name;		
		$this->issue_category = $result->category;		
		$this->issue_priority->id = $result->priority->id;
		$this->issue_priority->name = $result->priority->name;		
		$this->issue_severity->id = $result->severity->id;		
		$this->issue_severity->name = $result->severity->name;		
		$this->issue_status->id = $result->status->id;			
		$this->issue_status->name = $result->status->name;			
		$this->issue_reporter->id = $result->reporter->id;			
		$this->issue_reporter->name = $result->reporter->name;			
		$this->issue_reporter->real_name = $result->reporter->real_name;			
		$this->issue_reporter->email = $result->reporter->email;		
		$this->issue_summary = $result->summary;		
		$this->issue_reproducibility->id = $result->reproducibility->id;
		$this->issue_reproducibility->name = $result->reproducibility->name;	
		$this->issue_date_submitted = $result->date_submitted;		
		$this->issue_sponsorship_total = $result->sponsorship_total;	
		$this->issue_projection->id = $result->projection->id;		
		$this->issue_projection->name = $result->projection->name;		
		$this->issue_eta->id = $result->eta->id;		
		$this->issue_eta->name = $result->eta->name;
		$this->issue_resolution->id = $result->resolution->id;	
		$this->issue_resolution->name = $result->resolution->name;	
		$this->issue_description = $result->description;	
		$this->issue_attachments = $result->attachments;	
		$this->issue_due_date = $result->due_date;	
		$this->issue_steps_to_reproduce = $result->steps_to_reproduce;
		$this->issue_additional_information = $result->additional_information;
		
		return true;
	}
}
