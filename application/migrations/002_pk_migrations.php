<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

require_once APPPATH . "/libraries/MigrationLib.php";

class Migration_Pk_migrations extends MigrationLib
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function up()
	{
		$this->startUP();
		
		$this->addPrimaryKey(
			"public",
			"ci_migrations",
			"pk_migrations",
			array("version")
		);
		
		$this->endUP();
	}

	public function down()
	{
		$this->startDown();
		
		$this->execQuery('ALTER TABLE ci_migrations DROP CONSTRAINT pk_migrations');
		
		$this->endDown();
	}
}