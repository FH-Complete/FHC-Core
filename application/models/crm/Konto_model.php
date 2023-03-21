<?php
class Konto_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_konto';
		$this->pk = 'buchungsnr';
	}

	/**
	 * Sets a Payment as paid
	 */
	public function setPaid($buchungsnr)
	{
		// get payment
		$buchungResult = $this->loadWhere(array('buchungsnr' => $buchungsnr));

		if (isSuccess($buchungResult) && hasData($buchungResult))
		{
			$buchung = getData($buchungResult)[0];

			// get already paid amount
			$this->addSelect('sum(betrag) as bezahlt');
			$this->addGroupBy('buchungsnr_verweis');
			$buchungVerweisResult = $this->loadWhere(array('buchungsnr_verweis' => $buchungsnr));

			if (isSuccess($buchungVerweisResult))
			{
				$betragBezahlt = 0;

				if (hasData($buchungVerweisResult))
				{
					$betragBezahlt = getData($buchungVerweisResult)[0]->bezahlt;
				}

				// calculate open amount
				$betragOffen = $betragBezahlt - $buchung->betrag * (-1);

				if ($betragOffen != 0)
				{
					$data = array(
						'person_id' => $buchung->person_id,
						'studiengang_kz' => $buchung->studiengang_kz,
						'studiensemester_kurzbz' => $buchung->studiensemester_kurzbz,
						'buchungsnr_verweis' => $buchungsnr,
						'betrag' => str_replace(',', '.', $betragOffen * (-1)),
						'buchungsdatum' => date('Y-m-d'),
						'buchungstext' => $buchung->buchungstext,
						'insertamum' => date('Y-m-d H:i:s'),
						'insertvon' => '',
						'buchungstyp_kurzbz' => $buchung->buchungstyp_kurzbz,
					);

					return $this->insert($data);
				}
				else
				{
					return success();
				}
			}
			else
			{
				return error('Failed to load Payment');
			}
		}
		else
		{
			return error('Failed to load Payment');
		}
	}

	public function getStudienbeitraege($uid, $buchungstypen)
	{
		$query = 'SELECT konto.studiensemester_kurzbz
					FROM public.tbl_konto konto
							JOIN public.tbl_studiensemester studiensemester ON konto.studiensemester_kurzbz = studiensemester.studiensemester_kurzbz,
						public.tbl_benutzer,
						public.tbl_student
					WHERE tbl_benutzer.uid = \'' . $uid . '\'
					AND tbl_benutzer.uid = tbl_student.student_uid
					AND tbl_benutzer.person_id = konto.person_id
					AND konto.studiengang_kz = tbl_student.studiengang_kz
					AND konto.buchungstyp_kurzbz IN (\'' . $buchungstypen . '\')
					AND 0 = (
						SELECT sum(betrag)
						FROM public.tbl_konto skonto
						WHERE skonto.buchungsnr = konto.buchungsnr_verweis
						OR skonto.buchungsnr_verweis = konto.buchungsnr_verweis
						)
					ORDER BY studiensemester.start DESC;
					';

		return $this->execQuery($query);
	}

	public function checkStudienbeitrag($uid, $stsem, $buchungstypen)
	{
		$query = 'SELECT tbl_konto.buchungsnr, 
       				tbl_konto.buchungsdatum 
					FROM public.tbl_konto,
					public.tbl_benutzer,
					public.tbl_student
					WHERE
						tbl_konto.studiensemester_kurzbz = \'' . $stsem . '\'
						AND tbl_benutzer.uid = \'' . $uid . '\'
						AND tbl_benutzer.uid = tbl_student.student_uid
						AND tbl_benutzer.person_id = tbl_konto.person_id
						AND tbl_konto.studiengang_kz=tbl_student.studiengang_kz
						AND tbl_konto.buchungstyp_kurzbz IN (\'' . $buchungstypen . '\')
						AND 0 >= (
							SELECT sum(betrag)
							FROM public.tbl_konto skonto
							WHERE skonto.buchungsnr = tbl_konto.buchungsnr_verweis
							OR skonto.buchungsnr_verweis = tbl_konto.buchungsnr_verweis
						)	
					ORDER BY buchungsnr DESC LIMIT 1
					';

		return $this->execQuery($query);
	}
}
