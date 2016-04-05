<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_fhc30 extends CI_Migration {

        public function up()
        {			
			//$this->load->helper('file');
			require_once(FCPATH.'include/basis_db.class.php');
			$db = new basis_db();
	        //$db = $this->db;
			//$db->db_query = $this->db->simple_query;
			require_once('./system/dbupdate_3.0.php');
        }

        public function down()
        {
                /*$this->db->simple_query('DROP SCHEMA bis;');
				$this->db->simple_query('DROP SCHEMA campus;');
				$this->db->simple_query('DROP SCHEMA fue;');
				$this->db->simple_query('DROP SCHEMA kommune;');
				$this->db->simple_query('DROP SCHEMA lehre;');
				$this->db->simple_query('DROP SCHEMA sync;');
				$this->db->simple_query('DROP SCHEMA system;');
				$this->db->simple_query('DROP SCHEMA testtool;');
				$this->db->simple_query('DROP SCHEMA wawi;');*/
        }
}

