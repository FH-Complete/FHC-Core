<?php

class Akte_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_akte';
		$this->pk = 'akte_id';
	}

	/**
	 * getAkten
	 */
	public function getAkten($person_id, $dokument_kurzbz = null, $stg_kz = null, $prestudent_id = null)
	{
		$query = 'SELECT akte_id,
						 person_id,
						 dokument_kurzbz,
						 mimetype,
						 erstelltam,
						 gedruckt,
						 titel_intern,
						 anmerkung_intern,
						 titel, bezeichnung,
						 updateamum,
						 insertamum,
						 updatevon,
						 insertvon,
						 uid,
						 dms_id,
						 anmerkung,
						 nachgereicht,
						 nachgereicht_am,
						 CASE WHEN inhalt is not null THEN true ELSE false END as inhalt_vorhanden
					FROM public.tbl_akte
				   WHERE person_id = ?';

		$parametersArray = array($person_id);

		if (!is_null($dokument_kurzbz))
		{
			$query .= ' AND dokument_kurzbz = ?';
			array_push($parametersArray, $dokument_kurzbz);
		}

		if (!is_null($stg_kz) && !is_null($prestudent_id))
		{
			$query .= ' AND dokument_kurzbz NOT IN (
							SELECT dokument_kurzbz
							  FROM public.tbl_dokument JOIN public.tbl_dokumentstudiengang USING (dokument_kurzbz)
							 WHERE studiengang_kz = ?
						)
						AND dokument_kurzbz NOT IN (\'Zeugnis\')
						AND dokument_kurzbz NOT IN (
							SELECT dokument_kurzbz
							  FROM public.tbl_dokumentprestudent JOIN public.tbl_dokument USING (dokument_kurzbz)
							 WHERE prestudent_id = ?
						)';
			array_push($parametersArray, $stg_kz, $prestudent_id);
		}

		$query .= ' ORDER BY erstelltam';

		return $this->execQuery($query, $parametersArray);
	}

	/**
	 * getAktenAccepted
	 */
	public function getAktenAccepted($person_id, $dokument_kurzbz = null)
	{
		$query = 'SELECT a.akte_id,
						 a.person_id,
						 a.dokument_kurzbz,
						 a.mimetype,
						 a.erstelltam,
						 a.gedruckt,
						 a.titel_intern,
						 a.anmerkung_intern,
						 a.titel,
						 a.bezeichnung,
						 a.updateamum,
						 a.insertamum,
						 a.updatevon,
						 a.insertvon,
						 a.uid,
						 a.dms_id,
						 a.anmerkung,
						 a.nachgereicht,
						 a.nachgereicht_am,
						 CASE WHEN MAX(dp.dokument_kurzbz) IS NOT NULL THEN TRUE ELSE FALSE END AS accepted
					FROM public.tbl_akte a
			  INNER JOIN public.tbl_prestudent p USING(person_id)
			   LEFT JOIN public.tbl_dokumentprestudent dp USING(prestudent_id, dokument_kurzbz)
				   WHERE a.person_id = ?';

		$parametersArray = array($person_id);

		if (!isEmptyString($dokument_kurzbz))
		{
			$query .= ' AND a.dokument_kurzbz = ?';
			array_push($parametersArray, $dokument_kurzbz);
		}

		$query .= ' GROUP BY a.akte_id ORDER BY a.erstelltam';

		return $this->execQuery($query, $parametersArray);
	}

	/**
	 * getAktenAcceptedDms
	 */
	public function getAktenAcceptedDms($person_id, $dokument_kurzbz = null)
	{
		$query = 'SELECT a.akte_id,
						 a.person_id,
						 a.dokument_kurzbz,
						 a.mimetype,
						 a.erstelltam,
						 a.gedruckt,
						 a.titel_intern,
						 a.anmerkung_intern,
						 a.titel,
						 a.bezeichnung,
						 a.updateamum,
						 a.insertamum,
						 a.updatevon,
						 a.insertvon,
						 a.uid,
						 a.dms_id,
						 a.anmerkung,
						 a.nachgereicht,
						 a.nachgereicht_am,
						 CASE WHEN MAX(dp.dokument_kurzbz) IS NOT NULL THEN TRUE ELSE FALSE END AS accepted,
						 d.oe_kurzbz,
						 d.kategorie_kurzbz,
						 dv.version,
						 dv.filename,
						 dv.mimetype,
						 dv.name,
						 dv.beschreibung
					FROM public.tbl_akte a
			  INNER JOIN public.tbl_prestudent p USING(person_id)
			   LEFT JOIN public.tbl_dokumentprestudent dp ON(p.prestudent_id = dp.prestudent_id AND a.dokument_kurzbz = dp.dokument_kurzbz)
			  INNER JOIN campus.tbl_dms d ON (a.dms_id = d.dms_id AND a.dokument_kurzbz = d.dokument_kurzbz)
			  INNER JOIN (SELECT dms_id, MAX(version) AS version FROM campus.tbl_dms_version GROUP BY dms_id) dvv ON (d.dms_id = dvv.dms_id)
			  INNER JOIN campus.tbl_dms_version dv ON (dv.dms_id = dvv.dms_id AND dv.version = dvv.version)
				   WHERE a.person_id = ?';

		$parametersArray = array($person_id);

		if (!isEmptyString($dokument_kurzbz))
		{
			$query .= ' AND a.dokument_kurzbz = ?';
			array_push($parametersArray, $dokument_kurzbz);
		}

		$query .= ' GROUP BY a.akte_id, d.dms_id, dv.dms_id, dv.version ORDER BY a.erstelltam';

		return $this->execQuery($query, $parametersArray);
	}

	/**
	 * gets Akten together with documenttype info, mainly bezeichnung fields
	 * @param $person_id
	 * @param null $dokument_kurzbz
	 * @param bool $nachgereicht if true, retrieves only nachgereichte Dokumente. if false, only not nachgereichte. default: null, all Dokumente
	 * @return array
	 */
	public function getAktenWithDokInfo($person_id, $dokument_kurzbz = null, $nachgereicht = null, $archiv = null)
	{
		$this->addSelect('public.tbl_akte.*, bezeichnung_mehrsprachig, dokumentbeschreibung_mehrsprachig, public.tbl_dokument.bezeichnung as dokument_bezeichnung, bis.tbl_nation.*, ausstellungsdetails');
		$this->addJoin('public.tbl_dokument', 'dokument_kurzbz');
		$this->addJoin('bis.tbl_nation', 'ausstellungsnation = nation_code', 'LEFT');

		$where = array();
		$where['person_id'] = $person_id;
		if(isset($dokument_kurzbz))
			$where['dokument_kurzbz'] = $dokument_kurzbz;
		if(is_bool($nachgereicht))
			$where['nachgereicht'] = $nachgereicht;

		if (is_bool($archiv))
			$where['archiv'] = $archiv;

		$dokumente = $this->loadWhere($where);

		if($dokumente->error) return $dokumente;

		return success($dokumente->retval);
	}

	/**
	 * Liefert die Archivdokumente einer Person
	 *
	 * @param integer				$person_id
	 * @param boolean|null			$signiert			Wenn true werden nur Dokumente geliefert die digital signiert wurden.
	 * @param boolean|null			$stud_selfservice	Wenn true werden nur Dokumente geliefert die Studierende selbst herunterladen duerfen.
	 *
	 * @return stdClass
	 */
	public function getArchiv($person_id, $signiert = null, $stud_selfservice = null)
	{
		$this->addSelect('akte_id');
		$this->addSelect('person_id');
		$this->addSelect('dokument_kurzbz');
		$this->addSelect('mimetype');
		$this->addSelect('erstelltam');
		$this->addSelect('gedruckt');
		$this->addSelect('titel_intern');
		$this->addSelect('anmerkung_intern');
		$this->addSelect('titel');
		$this->addSelect('bezeichnung');
		$this->addSelect('updateamum');
		$this->addSelect('insertamum');
		$this->addSelect('updatevon');
		$this->addSelect('insertvon');
		$this->addSelect('uid');
		$this->addSelect('dms_id');
		$this->addSelect('anmerkung');
		$this->addSelect('nachgereicht');
		$this->addSelect('CASE WHEN inhalt is not null THEN true ELSE false END as inhalt_vorhanden', false);
		$this->addSelect('nachgereicht_am');
		$this->addSelect('ausstellungsnation');
		$this->addSelect('formal_geprueft_amum');
		$this->addSelect('archiv');
		$this->addSelect('signiert');
		$this->addSelect('stud_selfservice');
		$this->addSelect('akzeptiertamum');

		if ($signiert !== null)
			$this->db->where('signiert', (boolean)$signiert);
		if ($stud_selfservice !== null)
			$this->db->where('stud_selfservice', (boolean)$stud_selfservice);

		$this->addOrder('erstelltam', 'DESC');

		return $this->loadWhere([
			'person_id' => $person_id,
			'archiv' => true
		]);
	}
}
