<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Seed_Message
{

        public function seed($limit = 25)
        {
			echo "Seeding $limit messages ";
			$this->fhc =& get_instance();
			$this->fhc->load->model('system/Recipient_model');
			$this->fhc->Recipient_model->setUid('admin');
			$this->fhc->load->model('system/Message_model');
 
		    for ($i = 0; $i < $limit; $i++)
			{
		        echo ".";
		        
				$data = array
				(
		            'subject' => $this->fhc->faker->sentence(4, true),
					'body' => $this->fhc->faker->text(400),
					'person_id' => $i%5+1
		        );
		        $message = $this->fhc->Message_model->insert($data);

				$data = array
				(
		            'message_id' => $message->retval,
					'person_id' => $i%5+2,
					'insertvon' => 'seed'
		        );
		        $recipient = $this->fhc->Recipient_model->insert($data);
				if ($recipient->error)
					show_error($recipient->retval);
				//for ($j=1; $j<10; $j++)
				//	$this->fhc->Message_model->addRecipient($thread->retval, $i+$j+5);
		    }
	 
		    echo PHP_EOL;
          
		}

        public function truncate()
        {
              //$this->db->query('EMPTY TABLE public.person;');
        }
}

