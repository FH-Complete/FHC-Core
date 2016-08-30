<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

require_once APPPATH . "/libraries/MigrationLib.php";

class Migration_Person_svnr extends MigrationLib
{
	public function __construct()
	{
		parent::__construct();
	}
	
    public function up()
    {
		$this->startUP();

		$this->execQuery("ALTER TABLE public.tbl_person DROP CONSTRAINT chk_person_svnr");
		$this->execQuery(
			"ALTER TABLE public.tbl_person
		  ADD CONSTRAINT chk_person_svnr
				   CHECK (
						char_length(svnr::text) = 10 OR
						char_length(svnr::text) = 12 OR
						char_length(svnr::text) = 16 OR
						svnr IS NULL
					)"
		);
		
		$this->endUP();
	}

    public function down()
    {
		$this->startDown();

		$this->execQuery("ALTER TABLE public.tbl_person DROP CONSTRAINT chk_person_svnr");
		$this->execQuery(
			"ALTER TABLE public.tbl_person
		  ADD CONSTRAINT chk_person_svnr
				   CHECK (
						char_length(svnr::text) = 10 OR
						char_length(svnr::text) = 16 OR
						svnr IS NULL
					)"
		);

		$this->endDown();
    }
}