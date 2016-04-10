<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Init extends CI_Migration {

        public function up()
        {        	
			$this->load->database('system');
			// Schemas
			$this->db->query('CREATE SCHEMA IF NOT EXISTS public;');
          	$this->db->query('CREATE SCHEMA IF NOT EXISTS addon;');
		}

        public function down()
        {
              /* $this->db->query('
				DROP SCHEMA IF EXISTS addon;
				DROP SCHEMA IF EXISTS bis;
				DROP SCHEMA IF EXISTS campus;
				DROP SCHEMA IF EXISTS fue;
				DROP SCHEMA IF EXISTS kommune;
				DROP SCHEMA IF EXISTS lehre;
				DROP SCHEMA IF EXISTS public;
				DROP SCHEMA IF EXISTS sync;
				DROP SCHEMA IF EXISTS system;
				DROP SCHEMA IF EXISTS testtool;
				DROP SCHEMA IF EXISTS wawi;
				');*/
        }
}

