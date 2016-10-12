<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . '/libraries/MigrationLib.php';

class Migration_Dokumentprestudent extends MigrationLib
{
	public function __construct()
	{
		parent::__construct();
	}
	
    public function up()
    {
		$this->startUP();
		
		// Change mitarbeiter_uid could be null
		$this->execQuery('ALTER TABLE public.tbl_dokumentprestudent ALTER mitarbeiter_uid DROP NOT NULL');
		
		$this->endUP();
	}

    public function down()
    {
		$this->startDown();
		
		// Change mitarbeiter_uid could not be null
		$this->execQuery('ALTER TABLE public.tbl_dokumentprestudent ALTER mitarbeiter_uid SET NOT NULL');
		
		$this->endDown();
    }
}