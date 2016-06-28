<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Message extends CI_Migration {

        public function up()
        {  
			if (! $this->db->table_exists('public.tbl_msg_message'))
			{
				$this->db->insert('system.tbl_berechtigung', array(
					'berechtigung_kurzbz' => 'basis/message',
					'beschreibung' => 'Nachrichtensystem von FH-Complete'));
				$this->db->insert('system.tbl_rolleberechtigung', array(
					'berechtigung_kurzbz' => 'basis/message',
					'rolle_kurzbz' => 'admin',
					'art' => 'suid'));

				$query= "
					CREATE TABLE public.tbl_msg_message (
					  message_id serial,
					  person_id bigint NOT NULL references public.tbl_person(person_id),
					  subject varchar(256) NOT NULL,
					  body text NOT NULL,
					  priority smallint NOT NULL DEFAULT 0,
					  relationmessage_id bigint references public.tbl_msg_message(message_id),
					  oe_kurzbz varchar(32) references public.tbl_organisationseinheit(oe_kurzbz),
					  insertamum timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					  insertvon varchar(32),
					  PRIMARY KEY (message_id)
					);
					COMMENT ON COLUMN public.tbl_msg_message.person_id IS 'Sender';
					COMMENT ON COLUMN public.tbl_msg_message.priority IS 'Codex in config/message.php';
					GRANT SELECT ON TABLE public.tbl_msg_message TO web;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_msg_message TO admin;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_msg_message TO vilesci;
					GRANT SELECT, UPDATE ON SEQUENCE public.tbl_msg_message_message_id_seq TO web;
					GRANT SELECT, UPDATE ON SEQUENCE public.tbl_msg_message_message_id_seq TO admin;
					GRANT SELECT, UPDATE ON SEQUENCE public.tbl_msg_message_message_id_seq TO vilesci;

					CREATE TABLE public.tbl_msg_recipient (
					  person_id bigint NOT NULL references public.tbl_person(person_id),
					  message_id bigint NOT NULL references public.tbl_msg_message(message_id),
						token varchar(128) UNIQUE,
					  insertamum timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					  insertvon varchar(32),
					  PRIMARY KEY (person_id,message_id)
					);
					GRANT SELECT ON TABLE public.tbl_msg_recipient TO web;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_msg_recipient TO admin;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_msg_recipient TO vilesci;

					CREATE TABLE public.tbl_msg_status (
					  message_id bigint NOT NULL references public.tbl_msg_message(message_id),
					  person_id bigint NOT NULL references public.tbl_person(person_id),
					  status smallint NOT NULL,
					  statusinfo text,
					  insertamum timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					  insertvon varchar(32),
					  updateamum timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					  updatevon varchar(32),
					  PRIMARY KEY (message_id,person_id, status)
					);
					GRANT SELECT ON TABLE public.tbl_msg_status TO web;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_msg_status TO admin;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_msg_status TO vilesci;

					CREATE TABLE public.tbl_msg_attachment (
					attachment_id serial,
					message_id bigint NOT NULL references public.tbl_msg_message(message_id),
					name text,
					filename text,
					PRIMARY KEY (attachment_id)
				);
					GRANT SELECT ON TABLE public.tbl_msg_attachment TO web;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_msg_attachment TO admin;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_msg_attachment TO vilesci;
					GRANT SELECT, UPDATE ON SEQUENCE public.tbl_msg_attachment_attachment_id_seq TO web;
					GRANT SELECT, UPDATE ON SEQUENCE public.tbl_msg_attachment_attachment_id_seq TO admin;
					GRANT SELECT, UPDATE ON SEQUENCE public.tbl_msg_attachment_attachment_id_seq TO vilesci;
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
				$this->db->delete('system.tbl_rolleberechtigung', array('berechtigung_kurzbz' => 'basis/message'));
				$this->db->delete('system.tbl_berechtigung', array('berechtigung_kurzbz' => 'basis/message'));
				$this->dbforge->drop_table('public.tbl_msg_recipient');
	            $this->dbforge->drop_table('public.tbl_msg_status');
				$this->dbforge->drop_table('public.tbl_msg_attachment');
				$this->dbforge->drop_table('public.tbl_msg_message');
            	echo "Table public.tbl_msg_message, public.tbl_msg_status, public.tbl_msg_attachment and public.tbl_msg_recipient dropped!";
			}
			catch(Exception $e)
			{
				echo 'Exception abgefangen: ',  $e->getMessage(), "\n";
				echo $this->db->error();
			}
        }
}

