<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

require_once APPPATH . "/libraries/MigrationLib.php";

class Migration_Reihungstest extends MigrationLib
{
	public function __construct()
	{
		parent::__construct();
	}
	
    public function up()
    {
		$this->startUP();

		// Add stufe and anmeldefrist to public.tbl_reihungstest
		$columns = array(
			"stufe" => array("type" => "smallint"),
			"anmeldefrist" => array("type" => "date")
		);
		$this->addColumn("public", "tbl_reihungstest", $columns);

		// Add rt_stufe and punkte to public.tbl_prestudentstatus
		$columns = array(
			"rt_stufe" => array("type" => "smallint DEFAULT NULL")
		);
		$this->addColumn("public", "tbl_prestudentstatus", $columns);

		// Create table public.tbl_rt_studienplan
		$fields = array(
			"reihungstest_id" => array(
				"type" => "integer"
			),
			"stundenplan_id" => array(
				"type" => "integer"
			)
		);
		$this->createTable("public", "tbl_rt_studienplan", $fields);
		$this->addPrimaryKey(
			"public",
			"tbl_rt_studienplan",
			"pk_tbl_rt_studienplan",
			array("reihungstest_id", "stundenplan_id")
		);
		$this->addForeingKey(
			"public",
			"tbl_rt_studienplan",
			"fk_rt_studienplan_reihungstest_id",
			"reihungstest_id",
			"public",
			"tbl_reihungstest",
			"reihungstest_id",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->addForeingKey(
			"public",
			"tbl_rt_studienplan",
			"fk_rt_studienplan_stundenplan_id",
			"stundenplan_id",
			"lehre",
			"tbl_stundenplan",
			"stundenplan_id",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->grantTable("SELECT", "public", "tbl_rt_studienplan", "web");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_rt_studienplan", "admin");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_rt_studienplan", "vilesci");

		// Create table public.tbl_rt_person
		$fields = array(
			"person_id" => array(
				"type" => "integer"
			),
			"rt_id" => array(
				"type" => "integer"
			),
			"anmeldedatum" => array(
				"type" => "date"
			),
			"teilgenommen" => array(
				"type" => "boolean DEFAULT FALSE"
			),
			"ort_kurzbz" => array(
				"type" => "varchar(16)"
			),
			"punkte" => array(
				"type" => "numeric(8,4) DEFAULT NULL"
			)
		);
		$this->createTable("public", "tbl_rt_person", $fields);
		$this->addPrimaryKey(
			"public",
			"tbl_rt_person",
			"pk_tbl_rt_person",
			array("person_id", "rt_id")
		);
		$this->addForeingKey(
			"public",
			"tbl_rt_person",
			"fk_rt_person_ort_kurzbz",
			"ort_kurzbz",
			"public",
			"tbl_ort",
			"ort_kurzbz",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->addForeingKey(
			"public",
			"tbl_rt_person",
			"fk_rt_person_reihungstest_id",
			"rt_id",
			"public",
			"tbl_reihungstest",
			"reihungstest_id",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->grantTable("SELECT", "public", "tbl_rt_person", "web");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_rt_person", "admin");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_rt_person", "vilesci");
		
		// Create table public.tbl_rt_ort
		$fields = array(
			"rt_id" => array(
				"type" => "integer"
			),
			"ort_kurzbz" => array(
				"type" => "varchar(16)"
			),
			"uid" => array(
				"type" => "varchar(32)"
			)
		);
		$this->createTable("public", "tbl_rt_ort", $fields);
		$this->addPrimaryKey(
			"public",
			"tbl_rt_ort",
			"pk_tbl_rt_ort",
			array("rt_id", "ort_kurzbz")
		);
		$this->addForeingKey(
			"public",
			"tbl_rt_ort",
			"fk_rt_ort_reihungstest_id",
			"rt_id",
			"public",
			"tbl_reihungstest",
			"reihungstest_id",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->addForeingKey(
			"public",
			"tbl_rt_ort",
			"fk_rt_ort_ort_kurzbz",
			"ort_kurzbz",
			"public",
			"tbl_ort",
			"ort_kurzbz",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->addForeingKey(
			"public",
			"tbl_rt_ort",
			"fk_rt_ort_uid",
			"uid",
			"public",
			"tbl_benutzer",
			"uid",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->grantTable("SELECT", "public", "tbl_rt_ort", "web");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_rt_ort", "admin");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_rt_ort", "vilesci");
		
		$this->endUP();
	}

    public function down()
    {
		try
		{
			$this->dbforge->drop_column("public.tbl_reihungstest", "stufe");
			$this->dbforge->drop_column("public.tbl_reihungstest", "anmeldefrist");
			$this->dbforge->drop_column("public.tbl_prestudentstatus", "rt_stufe");

            echo "Columns public.tbl_reihungstest.stufe, public.tbl_reihungstest.anmeldefrist, public.tbl_prestudentstatus.rt_stufe dropped!";

			$this->dbforge->drop_table("public.tbl_rt_studienplan");
			$this->dbforge->drop_table("public.tbl_rt_person");
			$this->dbforge->drop_table("public.tbl_rt_ort");

			echo "Tables public.tbl_rt_studienplan, public.tbl_rt_person, public.tbl_rt_ort dropped!";
		}
		catch(Exception $e)
		{
			echo "Exception: ",  $e->getMessage(), "\n";
			echo $this->db->error();
		}
    }
}