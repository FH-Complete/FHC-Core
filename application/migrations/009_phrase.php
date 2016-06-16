<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Phrase extends CI_Migration {

    public function up()
    {
		if (! $this->db->table_exists('public.tbl_phrase'))
		{
			$this->db->insert('system.tbl_berechtigung', array(
				'berechtigung_kurzbz' => 'basis/phrase',
				'beschreibung' => 'Phrase System'));
			$this->db->insert('system.tbl_rolleberechtigung', array(
				'berechtigung_kurzbz' => 'basis/phrase',
				'rolle_kurzbz' => 'admin',
				'art' => 'suid'));

			$query= "
                CREATE TABLE public.tbl_app (
                  app varchar(32),
                  PRIMARY KEY (app)
                );
                GRANT SELECT ON TABLE public.tbl_app TO web;
                GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_app TO admin;
                GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_app TO vilesci;

				CREATE TABLE public.tbl_phrase (
				  phrase_id serial,
				  app varchar(32) NOT NULL,
				  phrase varchar(64) NOT NULL,
				  insertamum timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  insertvon varchar(32),
				  PRIMARY KEY (phrase_id)
				);
				GRANT SELECT ON TABLE public.tbl_phrase TO web;
				GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_phrase TO admin;
				GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_phrase TO vilesci;
				GRANT SELECT, UPDATE ON SEQUENCE public.tbl_phrase_phrase_id_seq TO web;
				GRANT SELECT, UPDATE ON SEQUENCE public.tbl_phrase_phrase_id_seq TO admin;
				GRANT SELECT, UPDATE ON SEQUENCE public.tbl_phrase_phrase_id_seq TO vilesci;

				CREATE TABLE public.tbl_phrase_inhalt (
				  phrase_inhalt_id serial,
				  phrase_id bigint NOT NULL,
                  sprache varchar(32) NOT NULL,
                  orgeinheit_kurzbz varchar(32) NOT NULL,
                  orgform_kurzbz varchar(32) NOT NULL,
                  text text,
				  insertamum timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  insertvon varchar(32),
				  PRIMARY KEY (phrase_inhalt_id)
				);
				GRANT SELECT ON TABLE public.tbl_phrase_inhalt TO web;
				GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_phrase_inhalt TO admin;
				GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_phrase_inhalt TO vilesci;
                GRANT SELECT, UPDATE ON SEQUENCE public.tbl_phrase_inhalt_phrase_inhalt_id_seq TO web;
                GRANT SELECT, UPDATE ON SEQUENCE public.tbl_phrase_inhalt_phrase_inhalt_id_seq TO admin;
                GRANT SELECT, UPDATE ON SEQUENCE public.tbl_phrase_inhalt_phrase_inhalt_id_seq TO vilesci;
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
			$this->db->delete('system.tbl_rolleberechtigung', array('berechtigung_kurzbz' => 'basis/phrase'));
			$this->db->delete('system.tbl_berechtigung', array('berechtigung_kurzbz' => 'basis/phrase'));
			$this->dbforge->drop_table('public.tbl_phrase_inhalt');
            $this->dbforge->drop_table('public.tbl_phrase');
			$this->dbforge->drop_table('public.tbl_app');
            echo "Table public.tbl_phrase_inhalt, public.tbl_phrase and public.tbl_app dropped!";
		}
		catch(Exception $e)
		{
			echo 'Exception abgefangen: ',  $e->getMessage(), "\n";
			echo $this->db->error();
		}
    }
}
