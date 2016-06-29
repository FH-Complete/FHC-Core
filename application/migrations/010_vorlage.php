<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Vorlage extends CI_Migration {

    public function up()
    {
		if (! @$this->db->simple_query('SELECT attribute FROM public.tbl_vorlage'))
		{
			$query= "ALTER TABLE public.tbl_vorlage 
				ADD COLUMN attribute json;
                ";
  			if ($this->db->simple_query($query))
			{
				echo 'Column public.tbl_vorlage.attribute added!';
				// Insert Demo Data
				$query= "INSERT INTO public.tbl_vorlage VALUES ('MailRegistration', 'eMail zur Registrierung', NULL, 'text/html', '{  \"\$schema\": \"http://json-schema.org/draft-03/schema#\",  \"title\": \"Person\",  \"type\": \"object\",  \"properties\": {    \"anrede\": {      \"type\": \"string\",      \"enum\": [        \"Herr\",        \"Frau\"      ],      \"default\": \"Herr\"    },    \"vorname\": {      \"type\": \"string\",      \"description\": \"Firstname\",      \"minLength\": 2,      \"default\": \"Vorname\"    },    \"nachname\": {      \"type\": \"string\",      \"description\": \"Surename\",      \"minLength\": 2,      \"default\": \"Nachname\"    },    \"code\": {      \"type\": \"string\",      \"description\": \"Accesscode\",      \"minLength\": 6,      \"default\": \"1q2w3e4r5t6z7u8i9o0\"    },    \"link\": {      \"type\": \"string\",      \"description\": \"LoginURL\",      \"minLength\": 6,      \"default\": \"https://cis.fhcomplete.org/addon/aufnahme/cis/login/\"    }  }}');
		            ";
	  			$this->db->simple_query($query);
			}
			else
				echo "Error adding public.tbl_vorlage.attribute!";
		}

		// tbl_vorlagestudiengang->Subject
		if (! @$this->db->simple_query('SELECT subject FROM public.tbl_vorlagestudiengang'))
		{
			$query= "ALTER TABLE public.tbl_vorlagestudiengang
				ADD COLUMN subject text;
                ";
  			if ($this->db->simple_query($query))
				echo 'Column public.tbl_vorlagestudiengang.subject added!';
			else
				echo "Error adding public.tbl_vorlagestudiengang.subject!";
		}

		// tbl_vorlagestudiengang->OrgForm
		if (! @$this->db->simple_query('SELECT orgform_kurzbz FROM public.tbl_vorlagestudiengang'))
		{
			$query= "ALTER TABLE public.tbl_vorlagestudiengang
				ADD COLUMN orgform_kurzbz varchar(3) references bis.tbl_orgform(orgform_kurzbz);
                ";
  			if ($this->db->simple_query($query))
			{
				echo 'Column public.tbl_vorlagestudiengang.orgform_kurzbz added!';
				// Insert Demo Data
				$query = "SELECT setval('seq_vorlagestudiengang_vorlagestudiengang_id', (SELECT MAX(vorlagestudiengang_id) FROM public.tbl_vorlagestudiengang));";
				$query .= "INSERT INTO public.tbl_vorlagestudiengang VALUES ('MailRegistration', 0, 1, '<p>Sehr geehrte/r {anrede} <strong>{vorname} {nachname}</strong>,</p>
<p>vielen Dank für Ihre Registrierung an unserer Hochschule. Im Anhang senden wir ihnen den Zugangscode.</p>
<p>Code: <code>{code}</code></p>
<p>Unter folgenden Link können sie sich direkt für unser Service einloggen: <a title=\"LoginLink\" href=\"{link}{code}\">{link}{code}</a></p>
<p>Mit freundlichen Grüßen,<br>FH Technikum Wien</p>', 'etw');
		            ";
	  			$this->db->simple_query($query);
			}
			else
				echo "Error adding public.tbl_vorlagestudiengang.orgform_kurzbz!";
		}
	}

    public function down()
    {
		try
		{
			$this->db->delete('public.tbl_vorlagestudiengang', array('vorlage_kurzbz' => 'MailRegistration'));
			$this->db->delete('public.tbl_vorlage', array('vorlage_kurzbz' => 'MailRegistration'));
			$this->dbforge->drop_column('public.tbl_vorlage', 'attribute');
			$this->dbforge->drop_column('public.tbl_vorlagestudiengang', 'subject');
			$this->dbforge->drop_column('public.tbl_vorlagestudiengang', 'orgform_kurzbz');
            echo "Column public.tbl_vorlage.attribute, public.tbl_vorlagestudiengang.subject, public.tbl_vorlagestudiengang.orgform_kurzbz dropped!";
		}
		catch(Exception $e)
		{
			echo 'Exception abgefangen: ',  $e->getMessage(), "\n";
			echo $this->db->error();
		}
    }
}
