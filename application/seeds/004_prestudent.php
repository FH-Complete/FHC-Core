<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Seed_Prestudent
{
		public function __construct()
		{
			$this->fhc =& get_instance();
		}
		
        public function seed($limit = 25)
        {
			echo "Seeding $limit prestudents ";
			
			$person = $this->fhc->db->query('SELECT person_id FROM public.tbl_person WHERE person_id>2 AND person_id%5!=0 LIMIT 100;');
			$studplan = $this->fhc->db->query('SELECT oe_kurzbz, studiengang_kz, studienplan_id from public.tbl_studiengang JOIN lehre.tbl_studienordnung USING (studiengang_kz) JOIN lehre.tbl_studienplan USING (studienordnung_id) LIMIT 100;');
			$studrows = $studplan->num_rows();
			$studplan = $studplan->result();

			$i = 0;
		    foreach ($person->result() as $p)
			{
				$studrow = $i % $studrows;

				// Prestudent
		        $data = array(
		            'person_id' => $p->person_id,
		            'aufmerksamdurch_kurzbz' => 'k.A.',
		            'studiengang_kz' => $studplan[$studrow]->studiengang_kz
		        );
		        $this->fhc->db->insert('public.tbl_prestudent',$data);
				$id = $this->fhc->db->insert_id();

				// Prestudentstatus
				$data = array(
		            'prestudent_id' => $id,
		            'status_kurzbz' => 'Interessent',
		            'studiensemester_kurzbz' => 'WS2016',
		            'datum' => 'now()',
					'studienplan_id' => $studplan[$studrow]->studienplan_id
		        );
		        $this->fhc->db->insert('public.tbl_prestudentstatus',$data);
				
				echo ".";
		        if (++$i>$limit)
					break;
		    }
	 
		    echo PHP_EOL;     
		}

        public function truncate()
        {
              $this->fhc->db->query('DELETE FROM public.tbl_prestudent;');
        }
}

