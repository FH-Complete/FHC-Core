<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . '/libraries/MigrationLib.php';

class Migration_Reihungstest extends MigrationLib
{
    public function up()
    {
		$this->startUP();
		
		// Add stufe to public.tbl_reihungstest
		$this->addColumn('public', 'tbl_reihungstest', 'stufe', 'smallint');
		
		// Add anmeldefrist to public.tbl_reihungstest
		$this->addColumn('public', 'tbl_reihungstest', 'anmeldefrist', 'date');
		
		// Add rt_stufe and punkte to public.tbl_prestudentstatus
		$this->addColumn('public', 'tbl_prestudentstatus', 'rt_stufe', 'smallint DEFAULT NULL');
		
		// Create table public.tbl_rt_studienplan
		$this->createTable('public', 'tbl_rt_studienplan',
			'reihungstest_id integer,
			stundenplan_id integer,
			CONSTRAINT pk_tbl_rt_studienplan PRIMARY KEY (reihungstest_id, stundenplan_id),
			CONSTRAINT fk_rt_studienplan_reihungstest_id FOREIGN KEY (reihungstest_id) REFERENCES public.tbl_reihungstest(reihungstest_id) ON UPDATE CASCADE ON DELETE RESTRICT,
			CONSTRAINT fk_rt_studienplan_stundenplan_id FOREIGN KEY (stundenplan_id) REFERENCES lehre.tbl_stundenplan(stundenplan_id) ON UPDATE CASCADE ON DELETE RESTRICT'
		);
		$this->grantTable('SELECT', 'public', 'tbl_rt_studienplan', 'web');
		$this->grantTable(array('SELECT', 'INSERT', 'DELETE', 'UPDATE'), 'public', 'tbl_rt_studienplan', 'admin');
		$this->grantTable(array('SELECT', 'INSERT', 'DELETE', 'UPDATE'), 'public', 'tbl_rt_studienplan', 'vilesci');
		
		// Create table public.tbl_rt_person
		$this->createTable('public', 'tbl_rt_person',
			'person_id integer,
			rt_id integer,
			anmeldedatum date,
			teilgenommen boolean DEFAULT FALSE,
			ort_kurzbz varchar(16) NOT NULL,
			punkte numeric(8,4) DEFAULT NULL,
			CONSTRAINT pk_tbl_rt_person PRIMARY KEY (person_id, rt_id),
			CONSTRAINT fk_rt_person_ort_kurzbz FOREIGN KEY (ort_kurzbz) REFERENCES public.tbl_ort(ort_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT,
			CONSTRAINT fk_rt_person_reihungstest_id FOREIGN KEY (rt_id) REFERENCES public.tbl_reihungstest(reihungstest_id) ON UPDATE CASCADE ON DELETE RESTRICT'
		);
		$this->grantTable('SELECT', 'public', 'tbl_rt_person', 'web');
		$this->grantTable(array('SELECT', 'INSERT', 'DELETE', 'UPDATE'), 'public', 'tbl_rt_person', 'admin');
		$this->grantTable(array('SELECT', 'INSERT', 'DELETE', 'UPDATE'), 'public', 'tbl_rt_person', 'vilesci');
		
		// Create table public.tbl_rt_ort
		$this->createTable('public', 'tbl_rt_ort',
			'rt_id integer,
			ort_kurzbz varchar(16),
			uid varchar(32),
			CONSTRAINT pk_tbl_rt_ort PRIMARY KEY (rt_id, ort_kurzbz),
			CONSTRAINT fk_rt_ort_reihungstest_id FOREIGN KEY (rt_id) REFERENCES public.tbl_reihungstest(reihungstest_id) ON UPDATE CASCADE ON DELETE RESTRICT,
			CONSTRAINT fk_rt_ort_ort_kurzbz FOREIGN KEY (ort_kurzbz) REFERENCES public.tbl_ort(ort_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT'
		);
		$this->grantTable('SELECT', 'public', 'tbl_rt_ort', 'web');
		$this->grantTable(array('SELECT', 'INSERT', 'DELETE', 'UPDATE'), 'public', 'tbl_rt_ort', 'admin');
		$this->grantTable(array('SELECT', 'INSERT', 'DELETE', 'UPDATE'), 'public', 'tbl_rt_ort', 'vilesci');
		
		$this->endUP();
	}

    public function down()
    {
		try
		{
			$this->dbforge->drop_column('public.tbl_reihungstest', 'stufe');
			$this->dbforge->drop_column('public.tbl_reihungstest', 'anmeldefrist');
			$this->dbforge->drop_column('public.tbl_prestudentstatus', 'rt_stufe');
			
            echo "Columns public.tbl_reihungstest.stufe, public.tbl_reihungstest.anmeldefrist, public.tbl_prestudentstatus.rt_stufe dropped!";
			
			$this->dbforge->drop_table('public.tbl_rt_studienplan');
			$this->dbforge->drop_table('public.tbl_rt_person');
			$this->dbforge->drop_table('public.tbl_rt_ort');
			
			echo "Tables public.tbl_rt_studienplan, public.tbl_rt_person, public.tbl_rt_ort dropped!";
		}
		catch(Exception $e)
		{
			echo 'Exception: ',  $e->getMessage(), "\n";
			echo $this->db->error();
		}
    }
}