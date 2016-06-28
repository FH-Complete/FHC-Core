<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Seed_Prestudent
{

        public function seed($limit = 25)
        {
			echo "Seeding $limit prestudents ";
			$this->fhc =& get_instance();
			$this->fhc->load->model('crm/Prestudent_model');
 
		    for ($i = 0; $i < $limit; $i++)
			{
		        echo ".";
		        $data = array(
		            'person_id' => $i+3, // start with person_id 3
		            'aufmerksamdurch_kurzbz' => 'k.A.',
		            'studiengang_kz' => $this->fhc->faker->firstName,
		            'nachname' => $this->fhc->faker->lastName,
		            //'address' => $this->faker->streetAddress,
		            'gebort' => $this->fhc->faker->city,
		            //'state' => $this->faker->state,
		            //'country' => $this->faker->country,
		            //'postcode' => $this->faker->postcode,
		            //'email' => $this->faker->email,
		            //'email_verified' => mt_rand(0, 1) ? '0' : '1',
		            //'phone' => $this->faker->phoneNumber,
		            'gebdatum' => $this->fhc->faker->dateTimeThisCentury->format('Y-m-d H:i:s'),
		            //'registration_date' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
		            //'ip_address' => mt_rand(0, 1) ? $this->faker->ipv4 : $this->faker->ipv6,
		        );
	 
		        $this->fhc->Prestudent_model->insert($data);
		    }
	 
		    echo PHP_EOL;
          
		}

        public function truncate()
        {
              //$this->db->query('EMPTY TABLE public.person;');
        }
}

