<?php

class Notiz_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_notiz';
		$this->pk = 'notiz_id';
	}
	
	// ------------------------------------------------------------------------------------------------------
	/**
	 * Get a specialization for a prestudent
	 */
	public function getSpecialization($prestudent_id, $titel)
	{
		// Join with the table public.tbl_notizzuordnung using notiz_id
		$this->addJoin('public.tbl_notizzuordnung', 'notiz_id');
		
		return $this->NotizModel->loadWhere(array('prestudent_id' => $prestudent_id, 'titel' => $titel));
	}
	
	/**
	 * Remove a specialization
	 */
	public function rmSpecialization($notiz_id)
	{
		// Loads model Notizzuordnung_model
		$this->load->model('person/Notizzuordnung_model', 'NotizzuordnungModel');
		
		// Start DB transaction
		$this->db->trans_start(false);
		
		$result = $this->delete(array('notiz_id' => $notiz_id));
		if (isSuccess($result))
		{
			$result = $this->NotizzuordnungModel->delete(array('notiz_id' => $notiz_id));
		}
		
		// Transaction complete!
		$this->db->trans_complete();
		
		// Check if everything went ok during the transaction
		if ($this->db->trans_status() === false || isError($result))
		{
			$this->db->trans_rollback();
			$result = error($result->msg, EXIT_ERROR);
		}
		else
		{
			$this->db->trans_commit();
			$result = success('Specialization successfully removed');
		}
		
		return $result;
	}

	/**
	 * Add a specialization for a prestudent
	 */
	public function addSpecialization($prestudent_id, $titel, $text)
	{
		// Loads model Notizzuordnung_model
		$this->load->model('person/Notizzuordnung_model', 'NotizzuordnungModel');
		
		// Start DB transaction
		$this->db->trans_start(false);
		
		$result = $this->insert(array('titel' => $titel, 'text' => $text, 'erledigt' => true));
		$notiz_id = $result->retval;
		if (isSuccess($result))
		{
			$result = $this->NotizzuordnungModel->insert(array('notiz_id' => $notiz_id, 'prestudent_id' => $prestudent_id));
		}
		
		// Transaction complete!
		$this->db->trans_complete();
		
		// Check if everything went ok during the transaction
		if ($this->db->trans_status() === false || isError($result))
		{
			$this->db->trans_rollback();
			$result = error($result->msg, EXIT_ERROR);
		}
		else
		{
			$this->db->trans_commit();
			$result = success($notiz_id);
		}
		
		return $result;
	}

	/**
	 * Add a Notiz for a given person
	 */
	public function addNotizForPerson($person_id, $titel, $text, $erledigt, $verfasser_uid)
	{
		// Loads model Notizzuordnung_model
		$this->load->model('person/Notizzuordnung_model', 'NotizzuordnungModel');

		// Start DB transaction
		$this->db->trans_start(false);

		$result = $this->insert(array('titel' => $titel, 'text' => $text, 'erledigt' => $erledigt, 'verfasser_uid' => $verfasser_uid,
			"insertvon" => $verfasser_uid));

		if (isSuccess($result))
		{
			$notiz_id = $result->retval;
			$result = $this->NotizzuordnungModel->insert(array('notiz_id' => $notiz_id, 'person_id' => $person_id));
		}

		// Transaction complete!
		$this->db->trans_complete();

		// Check if everything went ok during the transaction
		if ($this->db->trans_status() === false || isError($result))
		{
			$this->db->trans_rollback();
			$result = error($result->msg, EXIT_ERROR);
		}
		else
		{
			$this->db->trans_commit();
			$result = success($notiz_id);
		}

		return $result;
	}

	/**
	 * gets all Notizen for a person
	 * @param $person_id
	 */
	public function getNotiz($person_id)
	{
		$this->addSelect('public.tbl_notiz.*');
		$this->addJoin('public.tbl_notizzuordnung', 'notiz_id');

		return $this->loadWhere(array('person_id' => $person_id));
	}

	/**
	 * gets all Notizen with Documententries for a certain type and type_id
	 * @param String type of id eg. person_id, prestudent_id, mitarbeiter_uid, projekt_kurzbz, projektphase_id, projekttask_id,
	 *         bestellung_id, lehreinheit_id, anrechnung_id, uid)
	 * @param $id the corresponding id, part of public.tbl_notizzuordnung
	 */
	public function getNotizWithDocEntries($id, $type)
	{
			$qry = "
				SELECT 
						n.*, count(dms_id) as countDoc, z.notizzuordnung_id,
						TO_CHAR (CASE 
							WHEN n.updateamum >= n.insertamum THEN n.updateamum 
							ELSE n.insertamum
						END::timestamp, 'DD.MM.YYYY HH24:MI:SS') AS lastUpdate,
						regexp_replace(n.text, '<[^>]*>', '', 'g') as text_stripped,
						TO_CHAR(n.start::timestamp, 'DD.MM.YYYY') AS start_format,
						TO_CHAR(n.ende::timestamp, 'DD.MM.YYYY') AS ende_format,
						z.notiz_id, z.person_id as id, ? as type_id
						
				FROM
						public.tbl_notiz n
				JOIN 
							public.tbl_notizzuordnung z USING (notiz_id)
				LEFT JOIN 
							public.tbl_notiz_dokument dok USING (notiz_id)
				LEFT JOIN 
							campus.tbl_dms_version USING (dms_id)
				WHERE 
				   z.$type  = ?
				GROUP BY 
					notiz_id, z.notizzuordnung_id
			";

		return $this->execQuery($qry, array($type, $id));

	}


	/**
	 * gets all Notizen for a person with a specific title
	 * @param $person_id
	 * @param $titel
	 */
	public function getNotizByTitel($person_id, $titel)
	{
		$this->addSelect('public.tbl_notiz.insertamum as insertnotiz, *');
		// Join with the table public.tbl_notizzuordnung using notiz_id
		$this->addJoin('public.tbl_notizzuordnung', 'notiz_id');
		$this->addJoin('public.tbl_prestudent', 'prestudent_id', 'LEFT');
		$this->addJoin('public.tbl_studiengang', 'studiengang_kz', 'LEFT');
		$this->addOrder('public.tbl_notiz.insertamum', 'DESC');

		return $this->loadWhere(array('public.tbl_notizzuordnung.person_id' => $person_id, 'titel LIKE' => $titel));
	}
	
	/**
	 * Add a Notiz for a given Anrechnung
	 * @param $anrechnung_id
	 * @param $titel
	 * @param $text
	 * @param $verfasser_uid
	 * @return array
	 */
	public function addNotizForAnrechnung($anrechnung_id, $titel, $text, $verfasser_uid)
	{
		// Loads model Notizzuordnung_model
		$this->load->model('person/Notizzuordnung_model', 'NotizzuordnungModel');
		
		// Start DB transaction
		$this->db->trans_start(false);
		
		$result = $this->insert(array(
			'titel' => $titel,
			'text' => $text,
			'erledigt' => true,
			'verfasser_uid' => $verfasser_uid,
			"insertvon" => $verfasser_uid
		));
		
		if (isSuccess($result))
		{
			$notiz_id = $result->retval;
			$result = $this->NotizzuordnungModel->insert(array('notiz_id' => $notiz_id, 'anrechnung_id' => $anrechnung_id));
		}
		
		// Transaction complete!
		$this->db->trans_complete();
		
		// Check if everything went ok during the transaction
		if ($this->db->trans_status() === false || isError($result))
		{
			$this->db->trans_rollback();
			$result = error($result->msg, EXIT_ERROR);
		}
		else
		{
			$this->db->trans_commit();
			$result = success($notiz_id);
		}
		
		return $result;
	}
	
	/**
	 * Get Notizen by Anrechnung and title ordered last first
	 *
	 * @param $anrechnung_id
	 * @return array
	 */
	public function getNotizByAnrechnung($anrechnung_id, $titel = null)
	{
		$this->addJoin('public.tbl_notizzuordnung', 'notiz_id');
		$this->addOrder('insertamum', 'DESC');
		
		if (is_string($titel))
		{
			return $this->loadWhere(array(
				'anrechnung_id' => $anrechnung_id,
				'titel'         => $titel
			));
		}
		
		return $this->loadWhere(array('anrechnung_id' => $anrechnung_id));
	}
}
