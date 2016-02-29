<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_apikey extends CI_Migration {

        public function up()
        {
            $this->dbforge->add_field(array(
                    'apikey_id' => array(
                            'type' => 'INT',
                            'constraint' => 5,
                            'unsigned' => TRUE,
                            'auto_increment' => TRUE
                    ),
                    'key' => array(
                            'type' => 'VARCHAR',
                            'constraint' => '100',
                    ),
                    'level' => array(
                            'type' => 'INT',
                            'null' => TRUE,
                    ),
                    'ignore_limits' => array(
                            'type' => 'INT',
                            'null' => TRUE,
                    ),
                    'date_created' => array(
                            'type' => 'DATE',
                            'null' => TRUE,
							'default' => 'now()'
                    )
            ));
            $this->dbforge->add_key('apikey_id', TRUE);
            if (!$this->db->table_exists('ci_apikey'))
			{
            	$this->dbforge->create_table('ci_apikey');
    		}

			if (!$this->db->simple_query("INSERT INTO ci_apikey (key) VALUES ('aufnahme@fhcomplete.org');"))
			{
					echo "Error DB-Insert!";
			}
        }

        public function down()
        {
                $this->dbforge->drop_table('ci_apikey');
        }
}

