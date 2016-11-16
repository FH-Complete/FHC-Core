<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . '/libraries/MigrationLib.php';

class Migration_Dokumentstudiengang extends MigrationLib
{
	public function __construct()
	{
		parent::__construct();
	}
	
    public function up()
    {
		$this->startUP();
		
		// Add nachreichbar to public.tbl_dokumentstudiengang
		$columns = array(
			'nachreichbar' => array(
				'type' => 'boolean DEFAULT FALSE',
				'null' => false
			)
		);
		$this->addColumn('public', 'tbl_dokumentstudiengang', $columns);
		
		$this->endUP();
	}

    public function down()
    {
		$this->startDown();
		
		$this->dropColumn('public', 'tbl_dokumentstudiengang', 'nachreichbar');
		
		$this->endDown();
    }
}