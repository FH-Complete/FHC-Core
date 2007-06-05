<?php
/**
 * Klasse email (FAS-Online)
 * @create 14-03-2006
 */
class email
{
	var $conn;    // @var resource DB-Handle
	var $new;      // @var boolean
	var $errormsg; // @var string
	var $result = array(); // @var email Objekt

	//Tabellenspalten
	var $email_id;       // @var integer
	var $person_id;      // @var integer
	var $email;          // @var string
	var $name;	        // @var string
	var $typ;            // @var integer
	var $zustelladresse; // @var boolean
	var $updateamum;     // @var timestamp
	var $updatevon=0;      // @var string


	/**
	 * Konstruktor
	 * @param conn    Connection zur Datenbank
	 *        mail_id ID des zu ladenden Datensatzes
	 */
	function email($conn, $mail_id=null)
	{
		$this->conn = $conn;
		$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
			return false;
		}
		if($mail_id != null)
			$this->load($mail_id);
	}

	/**
	 * Laedt einen Datensatz
	 * @param mail_id ID des zu ladenden Datensatzes
	 */
	function load($mail_id)
	{
		//mail_id auf gueltigkeit pruefen
		if(!is_numeric($mail_id) || $mail_id == '')
		{
			$this->errormsg = 'mail_id muss eine gueltige Zahl sein';
			return false;
		}

		//laden des Datensatzes
		$qry = "SELECT * FROM email WHERE email_pk='$mail_id';";

		if(!$res = pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}

		if($row=pg_fetch_object($res))
		{
			$this->email_id       = $row->email_pk;
			$this->person_id      = $row->person_fk;
			$this->email          = $row->email;
			$this->name           = $row->name;
			$this->typ            = $row->typ;
			$this->zustelladresse = ($row->zustelladresse=='J'?true:false);
			$this->updateamum     = $row->creationdate;
			$this->updatevon      = $row->creationuser;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}

		return true;
	}

	/**
	 * Laedt alle Datensaetze
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		/* Frisst zu viel Speicher und wird beendet

		$qry = "SELECT * FROM email;";

		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while($row = pg_fetch_object($res))
		{
			$mail_obj = new email($this->conn);

			$mail_obj->email_id       = $row->email_pk;
			$mail_obj->person_id      = $row->person_fk;
			$mail_obj->email          = $row->email;
			$mail_obj->name           = $row->name;
			$mail_obj->typ            = $row->typ;
			$mail_obj->zustelladresse = ($row->zustelladresse=='J'?true:false);
			$mail_obj->updateamum     = $row->creationdate;
			$mail_obj->updatevon      = $row->creationuser;

			$this->result[] = $mail_obj;
		}

		return true;
		*/
		return false;
	}

	/**
	 * Laedt alle Datensaetze zu einer person
	 * @param  pers_id ID der Person zu der die Mails geladen werden sollen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load_pers($pers_id)
	{
		//pers_id auf gueltigkeit pruefen
		if(!is_numeric($pers_id) || $pers_id == '')
		{
			$this->errormsg = 'pers_id muss eine gueltige Zahl sein';
			return false;
		}

		//Datensaetze laden
		$qry = "SELECT * FROM email WHERE person_fk='$pers_id';";

		if(!$result = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while($row = pg_fetch_object($result))
		{
			$mail_obj = new email($this->conn);

			$mail_obj->email_id       = $row->email_pk;
			$mail_obj->person_id      = $row->person_fk;
			$mail_obj->email          = $row->email;
			$mail_obj->name           = $row->name;
			$mail_obj->typ            = $row->typ;
			$mail_obj->zustelladresse = ($row->zustelladresse=='J'?true:false);
			$mail_obj->updateamum     = $row->creationdate;
			$mail_obj->updatevon      = $row->creationuser;

			$this->result[] = $mail_obj;
		}

		return true;
	}

	/**
	 * Loescht einen Datensatz
	 * @param mail_id ID des zu leoschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($mail_id)
	{
		//mail_id auf gueltigkeit pruefen
		if(!is_numeric($mail_id) || $mail_id == '')
		{
			$this->errormsg = 'mail_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "DELETE FROM email WHERE email_pk = '$mail_id';";

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			$sql = $qry;
			$qry = "SELECT nextval('log_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Fehler beim Auslesen der Log-Sequence';
				return false;
			}

			$qry = "INSERT INTO log(log_pk, creationdate, creationuser, sql) VALUES('$row->id', now(), '$this->updatevon', '".addslashes($sql)."')";
			if(pg_query($this->conn, $qry))
				return true;
			else
			{
				$this->errormsg = 'Fehler beim Speichern des Log-Eintrages';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim loeschen';
			return false;
		}
	}

	/**
	 * Prueft die variablen auf gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{
		//Gesamtlaenge pruefen
		if(strlen($this->name)>255)
		{
			$this->errormsg = 'Name darf nicht mehr als 255 Zeichen lang sein';
			return false;
		}
		if(strlen($this->email)>255)
		{
			$this->errormsg = 'EMail darf nicht mehr als 255 Zeichen lang sein';
			return false;
		}

		//Zahlenfelder pruefen
		if(!is_numeric($this->person_id))
		{
			$this->errormsg = 'Person_id muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->typ))
		{
			$this->errormsg = 'Typ muss eine gueltige Zahl sein';
			return false;
		}

		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $email_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		if(!$this->checkvars())
			return false;

		if($this->new)
		{
			//Neuen Datensatz anlegen

			//Naechste ID aus Sequence holen
			$qry = "SELECT nextval('email_seq') as id;";
			if(!$row=pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Fehler beim auslesen der ID aus der Sequence';
				return false;
			}
			$this->email_id = $row->id;

			$qry = "INSERT INTO email (email_pk, person_fk, name, email, typ, creationdate, creationuser, zustelladresse)".
			       " VALUES ('$this->email_id', '$this->person_id', '$this->name', '$this->email', '$this->typ', now(),".
			       " $this->updatevon, '".($this->zustelladresse?'J':'N')."');";
		}
		else
		{
			//Bestehenden Datensatz aktualisieren

			//email_id auf gueltigkeit pruefen
			if(!is_numeric($this->email_id) || $this->email_id == '')
			{
				$this->errormsg = 'email_id muss eine gueltige Zahl sein';
				return false;
			}

			$qry = "UPDATE email SET person_fk = '$this->person_id', name = '$this->name', email = '$this->email',".
			       " typ = '$this->typ', zustelladresse = '".($this->zustelladresse?'J':'N')."' WHERE email_pk = '$this->email_id';";
		}

		if(pg_query($this->conn, $qry))
		{
			//Log schreiben
			$sql = $qry;
			$qry = "SELECT nextval('log_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Fehler beim Auslesen der Log-Sequence';
				return false;
			}

			$qry = "INSERT INTO log(log_pk, creationdate, creationuser, sql) VALUES('$row->id', now(), '$this->updatevon', '".addslashes($sql)."')";
			if(pg_query($this->conn, $qry))
				return true;
			else
			{
				$this->errormsg = 'Fehler beim Speichern des Log-Eintrages';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim speichern des Datensatzes';
			return false;
		}
	}
}
?>