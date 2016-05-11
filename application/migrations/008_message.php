<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Message extends CI_Migration {

        public function up()
        {  
			if (! $this->db->table_exists('public.tbl_msg_message'))
			{
				$query= "
					CREATE TABLE public.tbl_msg_message (
					  message_id serial,
					  thread_id bigint NOT NULL,
					  body text NOT NULL,
					  priority smallint NOT NULL DEFAULT 0,
					  person_id bigint NOT NULL,
					  cdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
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

					CREATE TABLE public.tbl_msg_participant (
					  person_id bigint NOT NULL,
					  thread_id bigint NOT NULL,
					  cdate timestamp NOT NULL DEFAULT now(),
					  PRIMARY KEY (person_id,thread_id)
					);
					GRANT SELECT ON TABLE public.tbl_msg_participant TO web;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_msg_participant TO admin;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_msg_participant TO vilesci;

					CREATE TABLE public.tbl_msg_status (
					  message_id bigint NOT NULL,
					  person_id bigint NOT NULL,
					  status smallint NOT NULL,
					  PRIMARY KEY (message_id,person_id)
					);
					GRANT SELECT ON TABLE public.tbl_msg_status TO web;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_msg_status TO admin;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_msg_status TO vilesci;

					CREATE TABLE public.tbl_msg_thread (
					thread_id serial,
					subject text,
					PRIMARY KEY (thread_id)
				);
					GRANT SELECT ON TABLE public.tbl_msg_thread TO web;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_msg_thread TO admin;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.tbl_msg_thread TO vilesci;
					GRANT SELECT, UPDATE ON SEQUENCE public.tbl_msg_thread_thread_id_seq TO web;
					GRANT SELECT, UPDATE ON SEQUENCE public.tbl_msg_thread_thread_id_seq TO admin;
					GRANT SELECT, UPDATE ON SEQUENCE public.tbl_msg_thread_thread_id_seq TO vilesci;
";
      			if (!$this->db->simple_query($query))
				{
						echo "Error creating Basis DB-Schema!";
				}
			}         
		}

        public function down()
        {
              $this->dbforge->drop_table('public.tbl_msg_message');
              $this->dbforge->drop_table('public.tbl_msg_participant');
              $this->dbforge->drop_table('public.tbl_msg_status');
              $this->dbforge->drop_table('public.tbl_msg_thread');
        }
}

