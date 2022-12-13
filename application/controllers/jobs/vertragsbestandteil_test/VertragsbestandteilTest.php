<?php
if (!defined("BASEPATH")) exit("No direct script access allowed");

use vertragsbestandteil\VertragsbestandteilFactory;

/**
 * Description of VertragsbestandteilTest
 *
 * @author bambi
 */
class VertragsbestandteilTest extends JOB_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->library('vertragsbestandteil/VertragsbestandteilLib', 
			null, 'VertragsbestandteilLib');
	}
	
	public function testFetch() 
	{
		$dienstverhaeltnis_id = 1;
		$stichtag = null;
		
		foreach($this->VertragsbestandteilLib->fetchVertragsbestandteile(
			$dienstverhaeltnis_id, $stichtag) as $vertragsbestandteil) 
		{
			//print_r($vertragsbestandteil);
			echo $vertragsbestandteil . "\n";
		}
	}
	
	public function testUpdate() 
	{
		$now = new DateTime();
		
		$data = new stdClass();
		$data->vertragsbestandteil_id = 32;
		$data->von = '2022-12-05';
		
		$data->wochenstunden = 45.0;
		$data->vertragsbestandteiltyp_kurzbz = VertragsbestandteilFactory::VERTRAGSBESTANDTEIL_STUNDEN;
		
		$vb = VertragsbestandteilFactory::getVertragsbestandteil($data);
		
		try
		{
			$this->VertragsbestandteilLib->storeVertragsbestandteil($vb);
			echo "Update successful.\n";
		}
		catch( Exception $ex ) 
		{
			echo "Update failed.\n";
		}
	}

	
	public function testInsert()
	{
		$now = new DateTime();
		
		$data = new stdClass();
		$data->dienstverhaeltnis_id = 1;
		$data->von = '2022-12-01';
		$data->insertamum = $now->format(DateTime::ATOM);
		$data->insertvon = 'ma0080';
		$data->vertragsbestandteiltyp_kurzbz = VertragsbestandteilFactory::VERTRAGSBESTANDTEIL_FUNKTION;
		
		$data->benutzerfunktion_id = 112667;
		$data->anmerkung = 'test funkton';
		$data->kuendigungsrelevant = false;
		
		$vb = VertragsbestandteilFactory::getVertragsbestandteil($data);
		
		try
		{
			$this->VertragsbestandteilLib->storeVertragsbestandteil($vb);
			echo "Insert successful.\n";
		}
		catch( Exception $ex ) 
		{
			echo "Insert failed.\n";
		}
	}
}
