<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Seed_Vorlage
{

        public function __construct()
		{
			$this->fhc =& get_instance();
		}
		
        public function seed()
        {
			echo "Seeding Standard templates (Vorlage) ";
			// OEen ohne Eltern holen
			$query = 'SELECT oe_kurzbz FROM public.tbl_organisationseinheit WHERE oe_parent_kurzbz IS NULL;';
			$oe = $this->db->query($query)->result();

			// Insert Template MailRegistration
			$query= "INSERT INTO public.tbl_vorlage VALUES ('MailRegistration', 'eMail zur Registrierung', NULL, 'text/html', '{  \"\$schema\": \"http://json-schema.org/draft-03/schema#\",  \"title\": \"Person\",  \"type\": \"object\",  \"properties\": {    \"anrede\": {      \"type\": \"string\",      \"enum\": [        \"Herr\",        \"Frau\"      ],      \"default\": \"Herr\"    },    \"vorname\": {      \"type\": \"string\",      \"description\": \"Firstname\",      \"minLength\": 2,      \"default\": \"Vorname\"    },    \"nachname\": {      \"type\": \"string\",      \"description\": \"Surename\",      \"minLength\": 2,      \"default\": \"Nachname\"    },    \"code\": {      \"type\": \"string\",      \"description\": \"Accesscode\",      \"minLength\": 6,      \"default\": \"1q2w3e4r5t6z7u8i9o0\"    },    \"link\": {      \"type\": \"string\",      \"description\": \"LoginURL\",      \"minLength\": 6,      \"default\": \"https://cis.fhcomplete.org/addon/aufnahme/cis/login/\"    }  }}');
		            ";
	  		if (! $this->db->simple_query($query)
				echo "Error adding Template MailRegistration!";

			// Insert Vorlagetext
			foreach ($oe as $o)
			{
				$query = "INSERT INTO public.tbl_vorlagestudiengang VALUES ('MailRegistration', 0, 1, '<p>Sehr geehrte/r {anrede} <strong>{vorname} {nachname}</strong>,</p>
<p>vielen Dank für Ihre Registrierung an unserer Hochschule. Im Anhang senden wir ihnen den Zugangscode.</p>
<p>Code: <code>{code}</code></p>
<p>Unter folgenden Link können sie sich direkt für unser Service einloggen: <a title=\"LoginLink\" href=\"{link}{code}\">{link}{code}</a></p>
<p>Mit freundlichen Grüßen,<br>FH Technikum Wien</p>', '$o->oe_kurzbz');
	            ";
  				if ($this->db->simple_query($query))
					echo 'Added Tamplate MailRegistration for OE:'.$o->oe_kurzbz.' ';
			}
			
		    echo PHP_EOL;
          
		}

        public function truncate()
        {
              //$this->fhc->db->query('DELETE FROM public.tbl_vorlage;');
        }
}

