<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Seed_Studienordnung
{
		public function __construct()
		{
			$this->fhc =& get_instance();
		}

		public function seed($limit = 25)
        {
			//$this->fhc =& get_instance();
			$this->fhc->load->model('organisation/Studienordnung_model', 'StudienordnungModel');
			$this->fhc->load->model('organisation/Studienplan_model', 'StudienplanModel');

			// Studienordnung
			echo "Seeding about $limit Studienordnungen ";
			$data = array('studiengang_kz' =>3, 'bezeichnung' => 'BUM-WS2016-VZ');
			$studienordnung_id = $this->fhc->StudienordnungModel->insert($data)->retval;
			$data = array('studiengang_kz' =>4, 'bezeichnung' => 'BDI-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>5, 'bezeichnung' => 'BPT-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>6, 'bezeichnung' => 'MBM-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>7, 'bezeichnung' => 'BMK-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>8, 'bezeichnung' => 'BMT-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>9, 'bezeichnung' => 'DSO-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>10, 'bezeichnung' => 'MDH-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>11, 'bezeichnung' => 'BDB-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>12, 'bezeichnung' => 'DTM-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>13, 'bezeichnung' => 'DMM-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>14, 'bezeichnung' => 'DCS-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>15, 'bezeichnung' => 'BID-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>16, 'bezeichnung' => 'MID-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>17, 'bezeichnung' => 'MIS-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>18, 'bezeichnung' => 'BMM-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>19, 'bezeichnung' => 'BSO-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>20, 'bezeichnung' => 'MMM-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>21, 'bezeichnung' => 'MSA-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>22, 'bezeichnung' => 'MSO-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>23, 'bezeichnung' => 'BBM-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>24, 'bezeichnung' => 'MDM-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>25, 'bezeichnung' => 'MMK-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>26, 'bezeichnung' => 'BSE-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>27, 'bezeichnung' => 'BIS-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			$data = array('studiengang_kz' =>28, 'bezeichnung' => 'BGK-WS2016-VZ');
			$this->fhc->StudienordnungModel->insert($data);
			
			//Studienplan
			echo "Seeding about $limit Studienplaene ";
			$data = array('studienordnung_id' => $studienordnung_id, 'bezeichnung' => 'BUM-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' => $studienordnung_id+1, 'bezeichnung' => 'BDI-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' => $studienordnung_id+2, 'bezeichnung' => 'BPT-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' => $studienordnung_id+3, 'bezeichnung' => 'MBM-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' => $studienordnung_id+4, 'bezeichnung' => 'BMK-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' => $studienordnung_id+5, 'bezeichnung' => 'BMT-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' => $studienordnung_id+6, 'bezeichnung' => 'DSO-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' => $studienordnung_id+7, 'bezeichnung' => 'MDH-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' => $studienordnung_id+8, 'bezeichnung' => 'BDB-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' => $studienordnung_id+9, 'bezeichnung' => 'DTM-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' => $studienordnung_id+10, 'bezeichnung' => 'DMM-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' =>$studienordnung_id+11, 'bezeichnung' => 'DCS-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' =>$studienordnung_id+12, 'bezeichnung' => 'BID-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' =>$studienordnung_id+13, 'bezeichnung' => 'MID-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' =>$studienordnung_id+14, 'bezeichnung' => 'MIS-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' =>$studienordnung_id+15, 'bezeichnung' => 'BMM-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' =>$studienordnung_id+16, 'bezeichnung' => 'BSO-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' =>$studienordnung_id+17, 'bezeichnung' => 'MMM-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' =>$studienordnung_id+18, 'bezeichnung' => 'MSA-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' =>$studienordnung_id+19, 'bezeichnung' => 'MSO-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' =>$studienordnung_id+20, 'bezeichnung' => 'BBM-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' =>$studienordnung_id+21, 'bezeichnung' => 'MDM-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' =>$studienordnung_id+22, 'bezeichnung' => 'MMK-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' =>$studienordnung_id+23, 'bezeichnung' => 'BSE-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' =>$studienordnung_id+24, 'bezeichnung' => 'BIS-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);
			$data = array('studienordnung_id' =>$studienordnung_id+25, 'bezeichnung' => 'BGK-WS2016-VZ');
			$this->fhc->StudienplanModel->insert($data);

			// Studienplaene

			
		    echo PHP_EOL;
          
		}

        public function truncate()
        {
            echo "Truncating Studienordnungen and Studienplaene!";
			$this->fhc->db->query('DELETE FROM lehre.tbl_studienplan WHERE studienordnung_id>1;');
			$this->fhc->db->query('DELETE FROM lehre.tbl_studienordnung WHERE studienordnung_id>1;');
        }
}

