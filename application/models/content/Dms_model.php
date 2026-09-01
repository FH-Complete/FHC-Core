<?php

class Dms_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_dms';
		$this->pk = 'dms_id';
	}
	
	/**
	 * 
	 */
	public function filterFields($dms)
	{
		$fieldsArray = array('oe_kurzbz', 'dokument_kurzbz', 'kategorie_kurzbz');
		$returnArray = array();
		
		foreach ($fieldsArray as $value)
		{
			if (isset($dms[$value]))
			{
				$returnArray[$value] = $dms[$value];
			}
		}
		
		return $returnArray;
	}

	/**
	 * Reads a DMS category and the access facts a listing needs.
	 *
	 * Mirrors the rule of cms/dms.php and dms.class.php at category level:
	 * a category is locked when any category in its parent chain grants a group or
	 * carries a berechtigung_kurzbz. The caller decides, this method only reports.
	 *
	 * @param string $kategorie_kurzbz
	 * gesperrt and in_gruppe come back as 0 or 1, not as a boolean. A PostgreSQL boolean
	 * reaches PHP as the string 'f' when the driver conversion does not run, and 'f' is
	 * truthy. The int cast is correct in both cases.
	 *
	 * @param string $uid
	 * @return object one row, or an empty result when the category does not exist
	 */
	public function getKategorieZugriff($kategorie_kurzbz, $uid)
	{
		// tiefe guards against a cyclic parent chain.
		$query = '
			WITH RECURSIVE chain(kategorie_kurzbz, parent_kategorie_kurzbz, berechtigung_kurzbz, tiefe) AS (
				SELECT kategorie_kurzbz, parent_kategorie_kurzbz, berechtigung_kurzbz, 1
				FROM campus.tbl_dms_kategorie
				WHERE kategorie_kurzbz = ?
				UNION ALL
				SELECT k.kategorie_kurzbz, k.parent_kategorie_kurzbz, k.berechtigung_kurzbz, c.tiefe + 1
				FROM campus.tbl_dms_kategorie k
					JOIN chain c ON k.kategorie_kurzbz = c.parent_kategorie_kurzbz
				WHERE c.tiefe < 32
			)
			SELECT
				kat.kategorie_kurzbz,
				kat.bezeichnung,
				kat.beschreibung,
				kat.berechtigung_kurzbz,
				(
					EXISTS (SELECT 1 FROM chain WHERE chain.berechtigung_kurzbz IS NOT NULL)
					OR EXISTS (
						SELECT 1
						FROM campus.tbl_dms_kategorie_gruppe g
							JOIN chain ON chain.kategorie_kurzbz = g.kategorie_kurzbz
					)
				)::int AS gesperrt,
				(EXISTS (
					SELECT 1
					FROM campus.tbl_dms_kategorie_gruppe g
						JOIN chain ON chain.kategorie_kurzbz = g.kategorie_kurzbz
						JOIN public.tbl_benutzergruppe b ON b.gruppe_kurzbz = g.gruppe_kurzbz
					WHERE b.uid = ?
				))::int AS in_gruppe
			FROM campus.tbl_dms_kategorie kat
			WHERE kat.kategorie_kurzbz = ?
		';

		return $this->execReadOnlyQuery($query, array($kategorie_kurzbz, $uid, $kategorie_kurzbz));
	}

	/**
	 * Lists the newest version of every document in one DMS category.
	 *
	 * filename stays out of the result. The reader gets the document through
	 * cms/dms.php?id=<dms_id>, never through the name on disk.
	 *
	 * @param string $kategorie_kurzbz
	 * @return object
	 */
	public function getKategorieDokumente($kategorie_kurzbz)
	{
		$query = '
			SELECT
				d.dms_id,
				v.version,
				v.name,
				v.beschreibung,
				v.mimetype,
				COALESCE(v.updateamum, v.insertamum) AS geaendert
			FROM campus.tbl_dms d
				JOIN campus.tbl_dms_version v ON v.dms_id = d.dms_id
			WHERE d.kategorie_kurzbz = ?
				-- cis_suche marks a document as published to CIS readers. A category also
				-- holds working files, and those must not appear on a content page.
				AND v.cis_suche
				AND v.version = (
					SELECT MAX(version) FROM campus.tbl_dms_version WHERE dms_id = d.dms_id
				)
				-- A project document carries its own rule in dms.class.php and does not
				-- belong in a CMS category listing.
				AND NOT EXISTS (
					SELECT 1 FROM fue.tbl_projekt_dokument pd WHERE pd.dms_id = d.dms_id
				)
			ORDER BY LOWER(v.name)
		';

		return $this->execReadOnlyQuery($query, array($kategorie_kurzbz));
	}

	/**
	 * Newest version of the named documents, in no particular order.
	 *
	 * Unlike getKategorieDokumente() this does NOT filter on cis_suche. An editor who names
	 * a dms_id has chosen that document on purpose, and the flag only marks a document for
	 * the CIS search. The category rule still applies, and the caller enforces it.
	 *
	 * @param array $ids dms_id values
	 * @return object
	 */
	public function getDokumenteByIds($ids)
	{
		if (empty($ids))
			return success(array());

		$platzhalter = implode(', ', array_fill(0, count($ids), '?'));

		$query = '
			SELECT
				d.dms_id,
				d.kategorie_kurzbz,
				v.version,
				v.name,
				v.beschreibung,
				v.mimetype,
				COALESCE(v.updateamum, v.insertamum) AS geaendert
			FROM campus.tbl_dms d
				JOIN campus.tbl_dms_version v ON v.dms_id = d.dms_id
			WHERE d.dms_id IN (' . $platzhalter . ')
				AND v.version = (
					SELECT MAX(version) FROM campus.tbl_dms_version WHERE dms_id = d.dms_id
				)
				AND NOT EXISTS (
					SELECT 1 FROM fue.tbl_projekt_dokument pd WHERE pd.dms_id = d.dms_id
				)
		';

		return $this->execReadOnlyQuery($query, array_values($ids));
	}

	/**
	 * Every document of one category, for the editor picker.
	 *
	 * Unlike getKategorieDokumente() this does NOT filter on cis_suche, because
	 * dms-dokumente renders named documents regardless of that flag. The flag travels with
	 * the row so the picker can mark a document that no reader would find by search.
	 *
	 * @param string $kategorie_kurzbz
	 * @return object
	 */
	public function getKategorieDokumenteAlle($kategorie_kurzbz)
	{
		$query = '
			SELECT
				d.dms_id,
				v.name,
				v.cis_suche::int AS cis_suche
			FROM campus.tbl_dms d
				JOIN LATERAL (
					SELECT v.name, v.cis_suche
					FROM campus.tbl_dms_version v
					WHERE v.dms_id = d.dms_id
					ORDER BY v.version DESC
					LIMIT 1
				) v ON TRUE
			WHERE d.kategorie_kurzbz = ?
				AND NOT EXISTS (
					SELECT 1 FROM fue.tbl_projekt_dokument pd WHERE pd.dms_id = d.dms_id
				)
			ORDER BY LOWER(v.name)
		';

		return $this->execReadOnlyQuery($query, array($kategorie_kurzbz));
	}

	/**
	 * Lists every DMS category with the number of documents it holds.
	 * Used by the insert dialog of the CMS content component "dms-liste".
	 *
	 * @return object
	 */
	public function getKategorien()
	{
		$query = '
			SELECT
				k.kategorie_kurzbz,
				k.bezeichnung,
				k.oe_kurzbz,
				COALESCE(a.anzahl, 0) AS anzahl
			FROM campus.tbl_dms_kategorie k
				LEFT JOIN (
					SELECT d.kategorie_kurzbz, COUNT(*) AS anzahl
					FROM campus.tbl_dms d
						JOIN (
							SELECT DISTINCT ON (dms_id) dms_id, cis_suche
							FROM campus.tbl_dms_version
							ORDER BY dms_id, version DESC
						) v ON v.dms_id = d.dms_id
					WHERE v.cis_suche
					GROUP BY d.kategorie_kurzbz
				) a ON a.kategorie_kurzbz = k.kategorie_kurzbz
			ORDER BY LOWER(COALESCE(k.bezeichnung, k.kategorie_kurzbz))
		';

		return $this->execReadOnlyQuery($query);
	}
}