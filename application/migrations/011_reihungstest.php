<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Reihungstest extends CI_Migration {

    public function up()
    {
		// Add stufe to public.tbl_reihungstest
		if (! @$this->db->simple_query('SELECT stufe FROM public.tbl_reihungstest'))
		{
			$query = "ALTER TABLE public.tbl_reihungstest ADD COLUMN stufe smallint;";
  			if ($this->db->simple_query($query))
				echo 'Column public.tbl_reihungstest.stufe added!';
			else
				echo "Error adding public.tbl_reihungstest.stufe!";
		}
		
		// Add anmeldefrist to public.tbl_reihungstest
		if (! @$this->db->simple_query('SELECT anmeldefrist FROM public.tbl_reihungstest'))
		{
			$query = "ALTER TABLE public.tbl_reihungstest ADD COLUMN anmeldefrist date;";
  			if ($this->db->simple_query($query))
				echo 'Column public.tbl_reihungstest.anmeldefrist added!';
			else
				echo "Error adding public.tbl_reihungstest.anmeldefrist!";
		}
		
		// Add rt_stufe to public.tbl_prestudentstatus
		if (! @$this->db->simple_query('SELECT rt_stufe FROM public.tbl_prestudentstatus'))
		{
			$query = "ALTER TABLE public.tbl_prestudentstatus ADD COLUMN rt_stufe smallint DEFAULT 1;";
  			if ($this->db->simple_query($query))
				echo 'Column public.tbl_prestudentstatus.rt_stufe added!';
			else
				echo "Error adding public.tbl_prestudentstatus.rt_stufe!";
		}
		
		// Create table public.tbl_rt_studienplan
		if (! $this->db->table_exists('public.tbl_rt_studienplan'))
		{
			$query= "CREATE TABLE public.tbl_rt_studienplan (
						reihungstest_id integer,
						stundenplan_id integer,
						CONSTRAINT pk_tbl_rt_studienplan PRIMARY KEY (reihungstest_id, stundenplan_id),
						CONSTRAINT fk_rt_studienplan_reihungstest_id FOREIGN KEY (reihungstest_id) REFERENCES public.tbl_reihungstest(reihungstest_id) ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT fk_rt_studienplan_stundenplan_id FOREIGN KEY (stundenplan_id) REFERENCES lehre.tbl_stundenplan(stundenplan_id) ON UPDATE CASCADE ON DELETE RESTRICT
					);
					GRANT SELECT ON TABLE public.tbl_rt_studienplan TO web;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_rt_studienplan TO admin;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_rt_studienplan TO vilesci;";
			
  			if (!$this->db->simple_query($query))
			{
				echo "Error creating table public.tbl_rt_studienplan!";
			}
		}
		
		// Create table public.tbl_rt_person
		if (! $this->db->table_exists('public.tbl_rt_person'))
		{
			$query= "CREATE TABLE public.tbl_rt_person (
						person_id integer,
						rt_id integer,
						anmeldedatum date,
						teilgenommen boolean DEFAULT FALSE,
						ort_kurzbz varchar(16) NOT NULL,
						CONSTRAINT pk_tbl_rt_person PRIMARY KEY (person_id, rt_id),
						CONSTRAINT fk_rt_person_ort_kurzbz FOREIGN KEY (ort_kurzbz) REFERENCES public.tbl_ort(ort_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT fk_rt_person_reihungstest_id FOREIGN KEY (rt_id) REFERENCES public.tbl_reihungstest(reihungstest_id) ON UPDATE CASCADE ON DELETE RESTRICT
					);
					GRANT SELECT ON TABLE public.tbl_rt_person TO web;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_rt_person TO admin;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_rt_person TO vilesci;";
			
  			if (!$this->db->simple_query($query))
			{
				echo "Error creating table public.tbl_rt_person!";
			}
		}
		
		// Create table public.tbl_rt_ort
		if (! $this->db->table_exists('public.tbl_rt_ort'))
		{
			$query= "CREATE TABLE public.tbl_rt_ort (
						rt_id integer,
						ort_kurzbz varchar(16),
						uid varchar(32),
						CONSTRAINT pk_tbl_rt_ort PRIMARY KEY (rt_id, ort_kurzbz),
						CONSTRAINT fk_rt_ort_reihungstest_id FOREIGN KEY (rt_id) REFERENCES public.tbl_reihungstest(reihungstest_id) ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT fk_rt_ort_ort_kurzbz FOREIGN KEY (ort_kurzbz) REFERENCES public.tbl_ort(ort_kurzbz) ON UPDATE CASCADE ON DELETE RESTRICT
					);
					GRANT SELECT ON TABLE public.tbl_rt_ort TO web;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_rt_ort TO admin;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_rt_ort TO vilesci;";
			
  			if (!$this->db->simple_query($query))
			{
				echo "Error creating table public.tbl_rt_ort!";
			}
		}
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