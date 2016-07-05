<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Seed_Person
{

        public function __construct()
		{
			$this->fhc =& get_instance();
		}
		
        public function seed($limit = 200)
        {
			echo "Seeding $limit persons ";
			
		    for ($i = 0; $i < $limit; $i++)
			{
		        echo ".";
		        $data = array(
		            // 'username' => $this->faker->unique()->userName, // get a unique nickname
		            'vorname' => $this->fhc->faker->firstName,
		            'vornamen' => $this->fhc->faker->firstName,
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
	 
		        $this->fhc->db->insert('public.tbl_person', $data);
		    }
	 
		    echo PHP_EOL;
          
		}

        public function truncate()
        {
              $this->fhc->db->query('DELETE FROM public.tbl_person WHERE person_id>2;');
        }
}

