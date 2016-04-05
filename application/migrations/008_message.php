<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Message extends CI_Migration {

        public function up()
        {  
			if (!$this->db->table_exists('msg_messages'))
			{
				$query= '
					CREATE TABLE msg_messages (
					  id serial,
					  thread_id bigint NOT NULL,
					  body text NOT NULL,
					  priority smallint NOT NULL DEFAULT 0,
					  sender_id bigint NOT NULL,
					  cdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					  PRIMARY KEY (id)
					);
					GRANT SELECT ON TABLE msg_messages TO web;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE msg_messages TO admin;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE msg_messages TO vilesci;
					GRANT SELECT, UPDATE ON SEQUENCE msg_messages_id_seq TO web;
					GRANT SELECT, UPDATE ON SEQUENCE msg_messages_id_seq TO admin;
					GRANT SELECT, UPDATE ON SEQUENCE msg_messages_id_seq TO vilesci;

					CREATE TABLE msg_participants (
					  user_id bigint NOT NULL,
					  thread_id bigint NOT NULL,
					  cdate timestamp NOT NULL DEFAULT now(),
					  PRIMARY KEY (user_id,thread_id)
					);
					GRANT SELECT ON TABLE msg_participants TO web;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE msg_participants TO admin;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE msg_participants TO vilesci;

					CREATE TABLE msg_status (
					  message_id bigint NOT NULL,
					  user_id bigint NOT NULL,
					  status smallint NOT NULL,
					  PRIMARY KEY (message_id,user_id)
					);
					GRANT SELECT ON TABLE msg_status TO web;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE msg_status TO admin;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE msg_status TO vilesci;

					CREATE TABLE msg_threads (
					id serial,
					subject text,
					PRIMARY KEY (id)
				);
					GRANT SELECT ON TABLE msg_threads TO web;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE msg_threads TO admin;
					GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE msg_threads TO vilesci;
					GRANT SELECT, UPDATE ON SEQUENCE msg_threads_id_seq TO web;
					GRANT SELECT, UPDATE ON SEQUENCE msg_threads_id_seq TO admin;
					GRANT SELECT, UPDATE ON SEQUENCE msg_threads_id_seq TO vilesci;
';
      			if (!$this->db->simple_query($query))
				{
						echo "Error creating Basis DB-Schema!";
				}
			}         
		}

        public function down()
        {
              $this->dbforge->drop_table('msg_messages');
              $this->dbforge->drop_table('msg_participants');
              $this->dbforge->drop_table('msg_status');
              $this->dbforge->drop_table('msg_threads');
        }
}

