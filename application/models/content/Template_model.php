<?php
class Template_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_template';
		$this->pk = 'template_kurzbz';
	}

	/**
	 * True if the template carries a CIS4 stylesheet of its own, which is not true for every template.
	 *
	 * @param string $template_kurzbz
	 * @return stdClass success(bool) or error
	 */
	public function hasCis4Stylesheet($template_kurzbz)
	{
		$query = '
			SELECT (xslt_xhtml_c4 IS NOT NULL
					AND xslt_xhtml_c4::text IS DISTINCT FROM xslt_xhtml::text) AS eigen
			FROM campus.tbl_template
			WHERE template_kurzbz = ?
		';

		$result = $this->execReadOnlyQuery($query, [$template_kurzbz]);
		if (isError($result))
			return $result;

		$data = getData($result);
		if (empty($data))
			return success(false);

		return success($data[0]->eigen === 't' || $data[0]->eigen === true);
	}
}
