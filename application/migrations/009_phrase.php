<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Phrase extends CI_Migration {

    public function up()
    {
		if (! $this->db->table_exists('system.tbl_app'))
		{
			$query= "
                CREATE TABLE system.tbl_app (
                  app varchar(32),
                  PRIMARY KEY (app)
                );
                GRANT SELECT ON TABLE system.tbl_app TO web;
                GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE system.tbl_app TO admin;
                GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE system.tbl_app TO vilesci;
			";
			if (!$this->db->simple_query($query))
			{
					echo "Error creating Basis DB-Schema!";
			}
		}

		if (! $this->db->table_exists('system.tbl_phrase'))
		{
			$query= "
				CREATE TABLE system.tbl_phrase (
				  phrase_id serial,
				  app varchar(32) NOT NULL,
				  phrase varchar(64) NOT NULL,
				  insertamum timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  insertvon varchar(32),
				  PRIMARY KEY (phrase_id)
				);
				GRANT SELECT ON TABLE system.tbl_phrase TO web;
				GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE system.tbl_phrase TO admin;
				GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE system.tbl_phrase TO vilesci;
				GRANT SELECT, UPDATE ON SEQUENCE system.tbl_phrase_phrase_id_seq TO web;
				GRANT SELECT, UPDATE ON SEQUENCE system.tbl_phrase_phrase_id_seq TO admin;
				GRANT SELECT, UPDATE ON SEQUENCE system.tbl_phrase_phrase_id_seq TO vilesci;

				CREATE TABLE system.tbl_phrasentext (
				  phrasentext_id serial,
				  phrase_id bigint NOT NULL,
                  sprache varchar(32) NOT NULL,
                  orgeinheit_kurzbz varchar(32),
                  orgform_kurzbz varchar(32),
                  text text,
				  description text,
				  insertamum timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  insertvon varchar(32),
				  PRIMARY KEY (phrasentext_id)
				);
				GRANT SELECT ON TABLE system.tbl_phrasentext TO web;
				GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE system.tbl_phrasentext TO admin;
				GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE system.tbl_phrasentext TO vilesci;
                GRANT SELECT, UPDATE ON SEQUENCE system.tbl_phrasentext_phrasentext_id_seq TO web;
                GRANT SELECT, UPDATE ON SEQUENCE system.tbl_phrasentext_phrasentext_id_seq TO admin;
                GRANT SELECT, UPDATE ON SEQUENCE system.tbl_phrasentext_phrasentext_id_seq TO vilesci;
                ";
  			if (!$this->db->simple_query($query))
			{
					echo "Error creating Basis DB-Schema!";
			}
		}
	}
    
    public function down()
    {
		try
		{
			$this->dbforge->drop_table('system.tbl_phrasentext');
            $this->dbforge->drop_table('system.tbl_phrase');
			$this->dbforge->drop_table('system.tbl_app');
            echo "Table system.tbl_phrasentext, system.tbl_phrase and system.tbl_app dropped!";
		}
		catch(Exception $e)
		{
			echo 'Exception abgefangen: ',  $e->getMessage(), "\n";
			echo $this->db->error();
		}
    }
}
