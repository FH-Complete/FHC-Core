<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Seed_Message
{

        public function __construct()
		{
			$this->fhc =& get_instance();
		}
		
        public function seed($limit = 25)
        {
			echo "Seeding $limit messages ";
			for ($i = 0; $i < $limit; $i++)
			{
		        echo ".";
		        
				$data = array
				(
		            'subject' => $this->fhc->faker->sentence(4, true),
					'body' => $this->fhc->faker->text(400),
					'person_id' => $i%5+1
		        );
		        $this->fhc->db->insert('public.tbl_msg_message', $data);
				$message_id = $this->fhc->db->insert_id();

				$data = array
				(
		            'message_id' => $message_id,
					'person_id' => $i%5+2,
					'insertvon' => 'seed'
		        );
		        $recipient = $this->fhc->db->insert('public.tbl_msg_recipient', $data);
				if (!$recipient)
					show_error($recipient);
				//for ($j=1; $j<10; $j++)
				//	$this->fhc->Message_model->addRecipient($thread->retval, $i+$j+5);
		    }
	 
		    echo PHP_EOL;
          
		}

        public function truncate()
        {
              $this->fhc->db->query('DELETE FROM public.msg_message;');
        }
}

