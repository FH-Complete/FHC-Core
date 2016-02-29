<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Init extends CI_Migration {

        public function up()
        {
        	// Schemas
			//$this->db->query('CREATE SCHEMA IF NOT EXISTS gis;');
          
		}

        public function down()
        {
              //$this->db->query('DROP SCHEMA IF EXISTS gis;');
        }
}

