<?php

class Anrechnungszeitraum_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_anrechnungszeitraum';
		$this->pk = 'anrechnungszeitraum_id';
	}

    /**
     * Save new Anrechnungszeitraum.
     *
     * @param $studiensemester_kurzbz
     * @param $anrechnungstart
     * @param $anrechnungende
     * @return array|stdClass
     */
    public function insertAzr($studiensemester_kurzbz, $anrechnungstart, $anrechnungende)
    {
        $result = $this->insert(array(
            'studiensemester_kurzbz' => $studiensemester_kurzbz,
            'anrechnungstart' => $anrechnungstart,
            'anrechnungende' => $anrechnungende,
            'insertvon' => getAuthUID()
        ));

        if (isError($result))
        {
            return error('Fehler bei Anrechnungszeitraum speichern.');
        }

        // Return new anrechnungszeitraum_id
        return success($result->retval);
    }

    /**
     * Delete Anrechnungszeitraum.
     *
     * @param $anrechnungszeitraum_id
     * @return array|stdClass
     */
    public function deleteAzr($anrechnungszeitraum_id)
    {
        $result = $this->delete(array('anrechnungszeitraum_id' => $anrechnungszeitraum_id));

        if (isError($result))
        {
            return error('Fehler bei Anrechnungszeitraum löschen.');
        }

        return success($result->retval);
    }

    /**
     * Update existing Anrechnungszeitraum.
     *
     * @param $anrechnungszeitraum_id
     * @param $studiensemester_kurzbz
     * @param $anrechnungstart
     * @param $anrechnungende
     * @return array|stdClass
     */
    public function updateAzr($anrechnungszeitraum_id, $studiensemester_kurzbz, $anrechnungstart, $anrechnungende)
    {
        $result = $this->update(
            $anrechnungszeitraum_id,
            array(
                'studiensemester_kurzbz' => $studiensemester_kurzbz,
                'anrechnungstart' => $anrechnungstart,
                'anrechnungende' => $anrechnungende
            )
        );

        if (isError($result))
        {
            return error('Fehler bei Anrechnungszeitraum update.');
        }

        return success($result->retval);
    }

}
