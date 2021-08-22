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
		$buchungResult =  $this->loadWhere(array('buchungsnr' => $buchungsnr));

		if(isSuccess($buchungResult) && hasData($buchungResult))
		{
			// get already paid amount
			$this->addSelect('sum(betrag) as bezahlt');
			$this->addGroupBy('buchungsnr_verweis');
			$buchungVerweisResult =  $this->loadWhere(array('buchungsnr_verweis' => $buchungsnr));

			if(isSuccess($buchungVerweisResult))
			{
				if(hasData($buchungVerweisResult))
				{
					$betragBezahltResult = getData($buchungVerweisResult);
					$betragBezahlt = $betragBezahltResult->bezahlt;
				}
				else
					$betragBezahlt = 0;

				$buchung = getData($buchungResult);
				$buchung = $buchung[0];

				// calculate open amount
				$betragOffen = $betragBezahlt - $buchung->betrag*(-1);

				$data = array(
					'person_id' => $buchung->person_id,
					'studiengang_kz' => $buchung->studiengang_kz,
					'studiensemester_kurzbz' => $buchung->studiensemester_kurzbz,
					'buchungsnr_verweis' => $buchungsnr,
					'betrag' => str_replace(',','.',$betragOffen*(-1)),
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
				return error('Failed to load Payment');
			}
		}
		else
		{
			return error('Failed to load Payment');
		}
	}
}
