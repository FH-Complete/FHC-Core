<?php
class Studierendenantragstatus_model extends DB_Model
{

	const STATUS_CREATED = 'Erstellt';
	const STATUS_APPROVED = 'Genehmigt';
	const STATUS_REJECTED = 'Abgelehnt';
	const STATUS_PASS = 'Verzichtet';
	const STATUS_REOPENED = 'Offen';
	const STATUS_CANCELLED = 'Zurueckgezogen';
	const STATUS_LVSASSIGNED = 'Lvszugewiesen';
	const STATUS_REMINDERSENT = 'EmailVersandt';
	const STATUS_REQUESTSENT_1 = 'ErsteAufforderungVersandt';
	const STATUS_REQUESTSENT_2 = 'ZweiteAufforderungVersandt';
	const STATUS_OBJECTED = 'Beeinsprucht';
	const STATUS_OBJECTION_DENIED = 'EinspruchAbgelehnt';
	const STATUS_DEREGISTERED = 'Abgemeldet';
	const STATUS_PAUSE = 'Pause';

	const INSERTVON_ABMELDUNGSTGL = "AbmeldungStgl";
	const INSERTVON_DEREGISTERED = "Studienabbruch";

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_studierendenantrag_status';
		$this->pk = 'studierendenantrag_status_id';
	}

	public function loadWithTyp($studierendenantrag_status_id)
	{
		$lang = 'SELECT index FROM public.tbl_sprache WHERE sprache=' . $this->escape(getUserLanguage());

		$this->addSelect($this->dbTable . '.*');
		$this->addSelect('bezeichnung[(' . $lang . ')] AS typ');

		$this->addJoin('campus.tbl_studierendenantrag_statustyp', 'studierendenantrag_statustyp_kurzbz');

		return $this->load($studierendenantrag_status_id);
	}

	public function loadWithTypWhere($where)
	{
		$lang = 'SELECT index FROM public.tbl_sprache WHERE sprache=' . $this->escape(getUserLanguage());

		$this->addSelect($this->dbTable . '.*');
		$this->addSelect('bezeichnung[(' . $lang . ')] AS typ');

		$this->addJoin('campus.tbl_studierendenantrag_statustyp', 'studierendenantrag_statustyp_kurzbz');

		return $this->loadWhere($where);
	}

	public function stopAntraegeForAbmeldungStgl($antrag_id)
	{
		$sql = 'INSERT INTO campus.tbl_studierendenantrag_status 
		(studierendenantrag_id, studierendenantrag_statustyp_kurzbz, insertvon, insertamum)
		SELECT studierendenantrag_id, ?, ?, (
			SELECT insertamum 
			FROM campus.tbl_studierendenantrag_status 
			WHERE studierendenantrag_status_id = campus.get_status_id_studierendenantrag(?)
		) 
		FROM campus.tbl_studierendenantrag 
		WHERE prestudent_id = (
			SELECT prestudent_id 
			FROM campus.tbl_studierendenantrag 
			WHERE studierendenantrag_id = ?
		)
		AND studierendenantrag_id <> ? 
		AND  (
			(
				typ = ?
				AND campus.get_status_studierendenantrag(studierendenantrag_id) IN ?
			) OR (
				typ = ?
				AND campus.get_status_studierendenantrag(studierendenantrag_id) IN ?
			) OR (
				typ = ?
				AND campus.get_status_studierendenantrag(studierendenantrag_id) IN ?
			)
		)';
		
		return $this->execQuery($sql, [
			self::STATUS_PAUSE,
			self::INSERTVON_ABMELDUNGSTGL,
			$antrag_id,
			$antrag_id,
			$antrag_id,
			Studierendenantrag_model::TYP_ABMELDUNG,
			[
				Studierendenantragstatus_model::STATUS_CREATED
			],
			Studierendenantrag_model::TYP_UNTERBRECHUNG,
			[
				Studierendenantragstatus_model::STATUS_CREATED
			],
			Studierendenantrag_model::TYP_WIEDERHOLUNG,
			[
				Studierendenantragstatus_model::STATUS_REQUESTSENT_1,
				Studierendenantragstatus_model::STATUS_REQUESTSENT_2,
				Studierendenantragstatus_model::STATUS_CREATED,
				Studierendenantragstatus_model::STATUS_LVSASSIGNED,
				Studierendenantragstatus_model::STATUS_PAUSE
			],
		]);
	}

	public function resumeAntraegeForAbmeldungStgl($antrag_id)
	{
		$sql = 'INSERT INTO campus.tbl_studierendenantrag_status 
		(studierendenantrag_id, studierendenantrag_statustyp_kurzbz, insertvon, insertamum)
		SELECT studierendenantrag_id, (
			SELECT studierendenantrag_statustyp_kurzbz
			FROM campus.tbl_studierendenantrag_status s 
			WHERE s.studierendenantrag_id=a.studierendenantrag_id 
			AND campus.get_status_id_studierendenantrag(a.studierendenantrag_id) <> studierendenantrag_status_id 
			ORDER BY insertamum DESC 
			LIMIT 1
		), ?, (
			SELECT insertamum 
			FROM campus.tbl_studierendenantrag_status 
			WHERE studierendenantrag_status_id = campus.get_status_id_studierendenantrag(?)
		)
		FROM campus.tbl_studierendenantrag a
		WHERE prestudent_id = (
			SELECT prestudent_id 
			FROM campus.tbl_studierendenantrag 
			WHERE studierendenantrag_id = ?
		)
		AND typ <> ?
		AND campus.get_status_studierendenantrag(studierendenantrag_id) = ?
		';
		
		return $this->execQuery($sql, [
			self::INSERTVON_ABMELDUNGSTGL,
			$antrag_id,
			$antrag_id,
			Studierendenantrag_model::TYP_ABMELDUNG_STGL,
			Studierendenantragstatus_model::STATUS_PAUSE
		]);
	}

	public function stopAntraegeForAbbruchBy($antrag_id)
	{
		$sql = 'INSERT INTO campus.tbl_studierendenantrag_status 
		(studierendenantrag_id, studierendenantrag_statustyp_kurzbz, insertvon, insertamum)
		SELECT studierendenantrag_id, ?, ?, (
			SELECT insertamum 
			FROM campus.tbl_studierendenantrag_status 
			WHERE studierendenantrag_status_id = campus.get_status_id_studierendenantrag(?)
		)
		FROM campus.tbl_studierendenantrag 
		WHERE prestudent_id = (
			SELECT prestudent_id 
			FROM campus.tbl_studierendenantrag 
			WHERE studierendenantrag_id = ?
		)
		AND studierendenantrag_id <> ? 
		AND (
			(
				typ = ?
				AND campus.get_status_studierendenantrag(studierendenantrag_id) NOT IN ?
			) OR (
				typ = ?
				AND campus.get_status_studierendenantrag(studierendenantrag_id) NOT IN ?
			) OR (
				typ = ?
				AND campus.get_status_studierendenantrag(studierendenantrag_id) NOT IN ?
			) OR (
				typ = ?
				AND campus.get_status_studierendenantrag(studierendenantrag_id) NOT IN ?
			)
		)';
		
		return $this->execQuery($sql, [
			self::STATUS_PAUSE,
			self::INSERTVON_DEREGISTERED,
			$antrag_id,
			$antrag_id,
			$antrag_id,
			Studierendenantrag_model::TYP_ABMELDUNG,
			[
				Studierendenantragstatus_model::STATUS_APPROVED,
				Studierendenantragstatus_model::STATUS_CANCELLED
			],
			Studierendenantrag_model::TYP_UNTERBRECHUNG,
			[
				Studierendenantragstatus_model::STATUS_APPROVED,
				Studierendenantragstatus_model::STATUS_CANCELLED,
				Studierendenantragstatus_model::STATUS_REMINDERSENT,
				Studierendenantragstatus_model::STATUS_REJECTED
			],
			Studierendenantrag_model::TYP_ABMELDUNG_STGL,
			[
				Studierendenantragstatus_model::STATUS_CANCELLED,
				Studierendenantragstatus_model::STATUS_DEREGISTERED,
				Studierendenantragstatus_model::STATUS_OBJECTION_DENIED
			],
			Studierendenantrag_model::TYP_WIEDERHOLUNG,
			[
				Studierendenantragstatus_model::STATUS_DEREGISTERED,
				Studierendenantragstatus_model::STATUS_APPROVED
			],
		]);
	}
}
