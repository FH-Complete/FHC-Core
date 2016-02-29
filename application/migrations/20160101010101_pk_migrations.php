<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Pk_migrations extends CI_Migration {

        public function up()
        {
          
            if ($this->db->table_exists('ci_migrations'))
			{
				$this->db->query('ALTER TABLE ci_migrations ADD CONSTRAINT pk_migrations PRIMARY KEY(version);');
    		}
		}

        public function down()
        {
                $this->db->query('ALTER TABLE ci_migrations DROP CONSTRAINT pk_migrations;');
        }
}

