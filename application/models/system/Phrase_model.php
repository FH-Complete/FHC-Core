<?php

class Phrase_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_phrase';
		$this->pk = 'phrase_id';
	}

	/**
	 * getPhrases
	 */
	public function getPhrases($app, $sprache, $phrase = null, $orgeinheit_kurzbz = null, $orgform_kurzbz = null)
	{
		$parametersArray = array('app' => $app, 'sprache' => $sprache);

		$query = 'SELECT phrase,
						 sprache,
						 orgeinheit_kurzbz,
						 orgform_kurzbz,
						 text
					FROM system.tbl_phrase JOIN system.tbl_phrasentext USING (phrase_id)
				   WHERE app = ? AND sprache = ?';

		if (isset($phrase))
		{
			$parametersArray['phrase'] = $phrase;

			if (is_array($phrase))
			{
				$query .= ' AND phrase IN ?';
			}
			else
			{
				$query .= ' AND phrase = ?';
			}
		}

		if (isset($orgeinheit_kurzbz))
		{
			$parametersArray['orgeinheit_kurzbz'] = $orgeinheit_kurzbz;
			$query .= ' AND orgeinheit_kurzbz = ?';
		}
		if (isset($orgform_kurzbz))
		{
			$parametersArray['orgform_kurzbz'] = $orgform_kurzbz;
			$query .= ' AND orgform_kurzbz = ?';
		}

		return $this->execQuery($query, $parametersArray);
	}

	/**
	 * Loads phrases using category(s) and language as keys
	 * The retrieved fields are category, phrase, orgeinheit_kurzbz, orgform_kurzbz and text
	 * They are ordered by p.category, p.phrase, pt.orgeinheit_kurzbz DESC and pt.orgform_kurzbz DESC'
	 */
	public function getPhrasesByCategoryAndLanguage($categories, $language)
	{
		$query = 'SELECT p.category, p.phrase, pt.orgeinheit_kurzbz, pt.orgform_kurzbz, pt.text
					FROM system.tbl_phrase p
			  INNER JOIN system.tbl_phrasentext pt USING(phrase_id)
				   WHERE p.category IN ?
				   	 AND pt.sprache = ?
				ORDER BY p.category, p.phrase, pt.orgeinheit_kurzbz DESC, pt.orgform_kurzbz DESC';

		return $this->execQuery($query, array($categories, $language));
	}

	/**
	 * Loads phrases using category(s) and language as keys using associative category array
	 * that contains also phrases for each category
	 */
	public function getPhrasesByCategoryAndPhrasesAndLanguage($phrasesParams, $language)
	{
		$query = '
			SELECT p.category, p.phrase, pt.orgeinheit_kurzbz, pt.orgform_kurzbz, pt.text
			  FROM system.tbl_phrase p
		INNER JOIN system.tbl_phrasentext pt USING(phrase_id)
			 WHERE pt.sprache = ? AND (';

		$parametersArray = array($language);

		foreach ($phrasesParams as $category => $phrases)
		{
			$query .= '(category = ? AND phrase IN ?) OR ';
			$parametersArray[] = $category;
			$parametersArray[] = $phrases;
		}

		$query = rtrim($query, ' OR ');


		$query .= ') ORDER BY p.category, p.phrase, pt.orgeinheit_kurzbz DESC, pt.orgform_kurzbz DESC';

		return $this->execQuery($query, $parametersArray);
	}
}
