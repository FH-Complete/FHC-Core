<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_fhc31 extends CI_Migration {

        public function up()
        {			
			echo '<br/><h1>Update to FHC 3.1</h1><br/>';
			$this->db=$this->load->database('system', true); 	
			$this->load->helper('fhcdb');
			$db = new basis_db($this);
			require_once('./system/dbupdate_3.1.php');
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

