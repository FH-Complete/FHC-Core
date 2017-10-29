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
		$oe = $this->fhc->db->query($query)->result();

		// ************** Mail Registration Conf ******************
		// Insert Template MailRegistrationConfirmation
		$query= "INSERT INTO public.tbl_vorlage(vorlage_kurzbz, bezeichnung, anmerkung, mimetype, attribute) VALUES ('MailRegistrationConfirmation', 'eMail zur Bestätigung der Registrierung', NULL, 'text/html', '{  \"\$schema\": \"http://json-schema.org/draft-03/schema#\",  \"title\": \"Person\",  \"type\": \"object\",  \"properties\": {    \"anrede\": {      \"type\": \"string\",      \"enum\": [        \"Herr\",        \"Frau\"      ],      \"default\": \"Herr\"    },    \"vorname\": {      \"type\": \"string\",      \"description\": \"Firstname\",      \"minLength\": 2,      \"default\": \"Vorname\"    },    \"nachname\": {      \"type\": \"string\",      \"description\": \"Surename\",      \"minLength\": 2,      \"default\": \"Nachname\"    },    \"code\": {      \"type\": \"string\",      \"description\": \"Accesscode\",      \"minLength\": 6,      \"default\": \"1q2w3e4r5t6z7u8i9o0\"    },    \"link\": {      \"type\": \"string\",      \"description\": \"LoginURL\",      \"minLength\": 6,      \"default\": \"https://demo.fhcomplete.org/addons/aufnahme/cis/index.php/\"    }  }}');
		";
		if (! $this->fhc->db->simple_query($query))
			echo "Error adding Template MailRegistrationConfirmation!";

		// Insert Vorlagetext for MailRegistration
		foreach ($oe as $o)
		{
			$query = "INSERT INTO public.tbl_vorlagestudiengang(vorlage_kurzbz, studiengang_kz, version, text, oe_kurzbz, sprache, subject) VALUES ('MailRegistrationConfirmation', 0, 1, '<p>Vielen Dank für Ihre Anmeldung!</p>
<p>Bitte klicken Sie folgenden Link, um Ihre Anmeldung zu bestätigen:<br /><a title=\"LoginLink\" href=\"{link}\">{link}</a></p>
<p>Ihre Anmeldedaten:<br/>
Code: <code>{code}</code><br/>
eMail: <code>{eMailAdresse}</code></p>
<p>Mit freundlichen Grüßen,<br/>FH Complete</p>', '$o->oe_kurzbz','German','Registration');
	";
			if ($this->fhc->db->simple_query($query))
				echo 'Added Tamplate MailRegistrationConfirmation for OE:'.$o->oe_kurzbz.' ';
		}

		// ************** Mail Application Conf ******************
		// Insert Template MailApplicationConfirmation
		$query= "INSERT INTO public.tbl_vorlage(vorlage_kurzbz, bezeichnung, anmerkung, mimetype, attribute) VALUES ('MailApplicationConfirmation', 'eMail zur Bestätigung der Bewerbung', NULL, 'text/html', '{  \"\$schema\": \"http://json-schema.org/draft-03/schema#\",  \"title\": \"Person\",  \"type\": \"object\",  \"properties\": {    \"anrede\": {      \"type\": \"string\",      \"enum\": [        \"Herr\",        \"Frau\"      ],      \"default\": \"Herr\"    },    \"vorname\": {      \"type\": \"string\",      \"description\": \"Firstname\",      \"minLength\": 2,      \"default\": \"Max\"    },    \"nachname\": {      \"type\": \"string\",      \"description\": \"Surename\",      \"minLength\": 2,      \"default\": \"Mustermann\"    },    \"typ\": {      \"type\": \"string\",      \"description\": \"Studiengangstyp\",      \"minLength\": 1,      \"default\": \"Bachelor\"    },    \"studiengang\": {      \"type\": \"string\",      \"description\": \"Studiengangsbezeichnung\",      \"minLength\": 3,      \"default\": \"Medientechnik\"    }  }}');
		";
		if (! $this->fhc->db->simple_query($query))
			echo "Error adding Template MailApplicationConfirmation!";

		// Insert Vorlagetext for MailApplicationConfirmation
		foreach ($oe as $o)
		{
			$query = "INSERT INTO public.tbl_vorlagestudiengang(vorlage_kurzbz, studiengang_kz, version, text, oe_kurzbz, sprache, subject) VALUES ('MailApplicationConfirmation', 0, 1, '<p>Sehr geehrter Herr {vorname} {nachname}!</p>
	<p>Wir freuen uns über Ihre Bewerbung für das Studium {typ} {studiengang} an unserer Fachhochschule und bestätigen den Erhalt Ihrer Bewerbungsunterlagen. Ihre Bewerbung wird von uns bearbeitet und die Zugangsvoraussetzungen geprüft. Im Falle von Unklarheiten melden wir uns bei Ihnen.</p>
	<p>Über die endgültige Aufnahme wird nach Abschluss des Aufnahmeverfahrens entschieden.
	Bitte melden Sie sich für einen Aufnahmetermin an, sofern Sie das noch nicht erledigt haben.</p>
	<p>Bei Fragen stehen wir Ihnen gerne zur Verfügung!</p>
	<p>Mit freundlichen Grüßen<br/>
	FH Complete</p>
	<code>
	Fachhochschule FHComplete<br/>
	Demostrasse 15,<br/>
	1234 Ortsname<br/>
	T: +43/555/123 456 - 200<br/>
	F: +43/555/123 456 - 339<br/>
	M: bewerbung@example.com<br/>
	I: www.example.com<br/>
	</code>
	<br/>
	________________________
	<br/>
	<p>Dear {vorname} {nachname}!</p>
	<p>Thank you for your application for  the degree program {typ} {studiengang} at our University, which we hereby confirm. Your application is currently being processed and the admission requirements checked. We will contact you if something is not clear.
	Decisions concerning admission will be made after the application process has been finalized. We would ask you to register for an admission date if you have not already done so.</p>
	<p>In the event of any questions, please do not hesitate to contact:
	<p>Yours sincerely,<br/>
	FH Complete</p>
	<code>
	Fachhochschule FHComplete<br/>
	Demostrasse 15,<br/>
	1234 Ortsname<br/>
	T: +43/555/123 456 - 200<br/>
	F: +43/555/123 456 - 339<br/>
	M: bewerbung@example.com<br/>
	I: www.example.com<br/>
	</code>', '$o->oe_kurzbz','German','Registrierung');
		";
			if ($this->fhc->db->simple_query($query))
				echo 'Added Tamplate MailApplicationConfirmation for OE:'.$o->oe_kurzbz.' ';
		}

		// ************** Mail Appointment Conf ******************
		// Insert Template MailAppointmentConfirmation
		$query= "INSERT INTO public.tbl_vorlage(vorlage_kurzbz, bezeichnung, anmerkung, mimetype, attribute) VALUES ('MailAppointmentConfirmation', 'eMail zur Bestätigung des Aufnahmetermins', NULL, 'text/html', '{  \"\$schema\": \"http://json-schema.org/draft-03/schema#\",  \"title\": \"Person\",  \"type\": \"object\",  \"properties\": {    \"anrede\": {      \"type\": \"string\",      \"enum\": [        \"Herr\",        \"Frau\"      ],      \"default\": \"Herr\"    },    \"vorname\": {      \"type\": \"string\",      \"description\": \"Firstname\",      \"minLength\": 2,      \"default\": \"Vorname\"    },    \"nachname\": {      \"type\": \"string\",      \"description\": \"Surename\",      \"minLength\": 2,      \"default\": \"Nachname\"    },    \"typ\": {      \"type\": \"string\",      \"description\": \"Studiengangstyp\",      \"minLength\": 1,      \"default\": \"Bachelor\"    },    \"studiengang\": {      \"type\": \"string\",      \"description\": \"Studiengangsbezeichnung\",      \"minLength\": 3,      \"default\": \"Studiengang\"    },    \"orgform\": {      \"type\": \"string\",      \"description\": \"Organisationsform\",      \"minLength\": 2,      \"default\": \"Vollzeit\"    },    \"termin\": {      \"type\": \"string\",      \"description\": \"Aufnahmetermin\",      \"minLength\": 1,      \"default\": \"Dummytermin:11.22.3333, 44:55\"    }  }}');
			";
		if (! $this->fhc->db->simple_query($query))
			echo "Error adding Template MailAppointmentConfirmation!";

		// Insert Vorlagetext for MailAppointmentConfirmation
		foreach ($oe as $o)
		{
			$query = "INSERT INTO public.tbl_vorlagestudiengang(vorlage_kurzbz, studiengang_kz, version, text, oe_kurzbz, sprache, subject) VALUES ('MailAppointmentConfirmation', 0, 1, '<p>Vielen Dank für Ihre Anmeldung zum Aufnahmetermin des {typ}-Studiengangs {studiengang} ({orgform})!</p>
	<p>Sie haben folgenden Aufnahmetermin ausgewählt:
	{termin}</p>
	<p>Unser Aufnahmeverfahren besteht aus einem computergestützten, bildungsneutralen Test sowie einem Aufnahmegespräch. Informationen zum genauen Ablauf des Aufnahmetages erhalten Sie ca. eine Woche vor dem Termin per E-Mail. Bitte reservieren Sie sich aber vorerst den ganzen Tag. Die Einteilung ist abhängig von der Anzahl der BewerberInnen.</p>
	<p>Wir bitten Sie, sich rechtzeitig zu Ihrem ausgewählten Termin am Fachhochschulstandort am Informationstreffpunkt im Erdgeschoß einzufinden. Die Registrierung zur Testteilnahme erfolgt jeweils 45 Min. vor Testbeginn.
	Zur persönlichen Identifikation ist es erforderlich, einen aktuellen Lichtbildausweis (Führerschein oder Pass) vorzulegen.</p>
	<p>Die Ergebnisse werden erst nach Abschluss des gesamten Aufnahmeverfahrens innerhalb von 14 Tagen nach dem letzten Aufnahmetermin bekannt gegeben.</p>
	<p>Wir wünschen Ihnen viel Erfolg für das Aufnahmeverfahren!</p>

	<p>Bei Fragen stehen wir Ihnen gerne zur Verfügung!<br/>
	Mit freundlichen Grüßen,<br/>
	FH Complete</p>
	<code>
	Fachhochschule FHComplete<br/>
	Demostrasse 15,<br/>
	1234 Ortsname<br/>
	T: +43/555/123 456 - 200<br/>
	F: +43/555/123 456 - 339<br/>
	M: bewerbung@example.com<br/>
	I: www.example.com<br/></code>
	<p>Achtung!<br/>
	Antworten Sie nicht direkt auf diese Mail, da diese Mailadresse nur dem Versenden von Mails dient.<br/>
	Bitte wenden Sie sich direkt an das zuständige Studiengangssekretariat.', '$o->oe_kurzbz','German','Bestätigung Aufnahmetermin');
		";
			if ($this->fhc->db->simple_query($query))
				echo 'Added Tamplate MailAppointmentConfirmation for OE:'.$o->oe_kurzbz.' ';
		}


		// ************** Mail Status Confirmation Interessent ******************
		// Insert Template MailStatConfirmInteressent
		$query= "INSERT INTO public.tbl_vorlage(vorlage_kurzbz, bezeichnung, anmerkung, mimetype, attribute) VALUES ('MailStatConfirmInteressent', 'Zulassung zum Aufnahmeverfahren', NULL, 'text/html', '{  \"\$schema\": \"http://json-schema.org/draft-03/schema#\",  \"title\": \"Person\",  \"type\": \"object\",  \"properties\": {    \"anrede\": {      \"type\": \"string\",      \"enum\": [        \"Herr\",        \"Frau\"      ],      \"default\": \"Herr\"    },    \"vorname\": {      \"type\": \"string\",      \"description\": \"Firstname\",      \"minLength\": 2,      \"default\": \"Vorname\"    },    \"nachname\": {      \"type\": \"string\",      \"description\": \"Surename\",      \"minLength\": 2,      \"default\": \"Nachname\"    },    \"typ\": {      \"type\": \"string\",      \"description\": \"Studiengangstyp\",      \"minLength\": 1,      \"default\": \"Bachelor\"    },    \"studiengang\": {      \"type\": \"string\",      \"description\": \"Studiengangsbezeichnung\",      \"minLength\": 3,      \"default\": \"Studiengang\"    },    \"orgform\": {      \"type\": \"string\",      \"description\": \"Organisationsform\",      \"minLength\": 2,      \"default\": \"Vollzeit\"    },    \"stgMail\": {      \"type\": \"string\",      \"description\": \"StudiengangsMailAdresse\",      \"minLength\": 1,      \"default\": \"xxx@example.com\"    }  }}');
			";
		if (! $this->fhc->db->simple_query($query))
			echo "Error adding Template MailAppointmentConfirmation!";

		// Insert Vorlagetext for MailAppointmentConfirmation
		foreach ($oe as $o)
		{
			$query = "INSERT INTO public.tbl_vorlagestudiengang(vorlage_kurzbz, studiengang_kz, version, text, oe_kurzbz, sprache, subject) VALUES ('MailStatConfirmInteressent', 0, 1, 'Sehr geehrte/r {anrede} {vorname} {nachname},

	Sie haben die notwendigen Zugangsvoraussetzungen zum {typ}-Studiengang {studiengang} ({orgform}) erfüllt und sind nun für das Aufnahmeverfahren zugelassen.
	Bitte wählen Sie Ihren gewünschten Termin in der Online Bewerbung unter dem Menüpunkt „Aufnahmetermine“ aus.
	Bei Fragen stehen wir Ihnen gerne unter {stgMail} zur Verfügung!

	Mit freundlichen Grüßen,
	Studiengang {studiengang}
	<code>
	Fachhochschule FHComplete<br/>
	Demostrasse 15,<br/>
	1234 Ortsname<br/>
	T: +43/555/123 456 - 200<br/>
	F: +43/555/123 456 - 339<br/>
	M: bewerbung@example.com<br/>
	I: www.example.com<br/>
	</code>
	<p>Achtung!<br/>
	Antworten Sie nicht direkt auf diese Mail, da diese Mailadresse nur dem Versenden von Mails dient.<br/>
	Bitte wenden Sie sich direkt an das zuständige Studiengangssekretariat. ({stgMail})', '$o->oe_kurzbz','German','Zulassung zum Aufnahmeverfahren');
		";
			if ($this->fhc->db->simple_query($query))
				echo 'Added Tamplate MailAppointmentConfirmation for OE:'.$o->oe_kurzbz.' ';
		}
		echo PHP_EOL;
	}

	public function truncate()
	{
		//$this->fhc->db->query("DELETE FROM public.tbl_vorlage WHERE mimetype='text/html';");
	}
}
