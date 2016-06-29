<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Seed_Message
{

        public function __construct()
		{
			$this->fhc =& get_instance();
			$this->fhc->load->helper('fhc');
		}
		
        public function seed($limit = 50)
        {
			echo "Seeding $limit messages ";
			// fetch some persons
			$db = $this->fhc->db->query('SELECT person_id FROM public.tbl_person LIMIT 100;');
			$person = $db->result();
			$num_persons = $db->num_rows();
			
			for ($i = 0; $i < $limit; $i++)
			{
		        echo ".";
		        
				$data = array
				(
		            'subject' => $this->fhc->faker->sentence(4, true),
					'body' => $this->fhc->faker->text(400),
					'person_id' => $person[$i%$num_persons]->person_id
		        );
		        $this->fhc->db->insert('public.tbl_msg_message', $data);
				$message_id = $this->fhc->db->insert_id();

				$data = array
				(
		            'message_id' => $message_id,
					'person_id' => $person[$i%($num_persons-1)+1]->person_id,
					'token' => generateToken(),
					'insertvon' => 'seed'
		        );
		        $recipient = $this->fhc->db->insert('public.tbl_msg_recipient', $data);
				if (!$recipient)
					show_error($recipient);
		    }
	 
		    echo PHP_EOL;
          
		}

        public function truncate()
        {
              $this->fhc->db->query('DELETE FROM public.msg_message;');
        }
}

