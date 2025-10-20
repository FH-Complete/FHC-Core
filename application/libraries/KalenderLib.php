<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

class KalenderLib
{
	/**
	 * Loads model OrganisationseinheitModel
	 */
	public function __construct()
	{
		$this->ci =& get_instance();

		$this->ci->load->model('ressource/Kalender_model', 'KalenderModel');
		$this->ci->load->model('ressource/Kalender_Lehreinheit_model', 'KalenderLehreinheitModel');
		$this->ci->load->model('ressource/Kalender_Ort_model', 'KalenderOrtModel');
		$this->ci->load->model('education/Lehreinheit_model', 'LehreinheitModel');
		$this->ci->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->ci->load->model('education/LehreinheitMitarbeiter_model', 'LehreinheitMitarbeiterModel');

	}

	public function getRoomData($ort_kurzbz, $start_date, $end_date)
	{
		$data = $this->ci->KalenderModel->addJoin('lehre.tbl_kalender_ort', 'kalender_id');
		$data = $this->ci->KalenderModel->loadWhere(array(
			'von >=' => $start_date,
			'bis <= '=>$end_date,
			'ort_kurzbz'=>$ort_kurzbz
		));

		$stundenplan_data = array();
		if(isSuccess($data) && hasData($data))
		{
			$data = getData($data);
			foreach($data as $rowstpl)
			{
				$obj = new stdClass();
				$obj->type='lehreinheit';

				$von = new DateTime($rowstpl->von);
				$bis = new DateTime($rowstpl->bis);

				$obj->beginn = $von->format('H:i:s');
				$obj->ende = $bis->format('H:i:s');
				$obj->datum = $von->format('Y-m-d');
				$obj->topic = 'undefined';
				$obj->lektor = array();
				$obj->gruppe = array();
				$obj->isostart = $von->format('c');
				$obj->isoend = $bis->format('c');
				$obj->tooltip = 'tip';

				$obj->lehreinheit_id = array();

				$lehreinheiten = $this->ci->KalenderLehreinheitModel->loadWhere(array('kalender_id'=>$rowstpl->kalender_id));
				if(isSuccess($lehreinheiten) && hasData($lehreinheiten))
				{
					$lehreinheiten = getData($lehreinheiten);
					foreach($lehreinheiten as $le)
					{
						$obj->lehreinheit_id[] = $le->lehreinheit_id;

						$lehreinheitdata = $this->ci->LehreinheitModel->loadWhere(array('lehreinheit_id'=>$le->lehreinheit_id));

						if(isSuccess($lehreinheitdata) && hasData($lehreinheitdata))
						{
							$ledata = getData($lehreinheitdata)[0];


							$lvid = $ledata->lehrveranstaltung_id;
							$lehrfach_id = $ledata->lehrfach_id;
							$obj->lehrform = $ledata->lehrform_kurzbz;

							$lehreinheitmitarbeiterdata = $this->ci->LehreinheitMitarbeiterModel->loadWhere(array('lehreinheit_id'=>$le->lehreinheit_id));
							$lemitarbeiterdata = getData($lehreinheitmitarbeiterdata);

							foreach($lemitarbeiterdata as $rowma)
							{
								$obj->lektor[] = array(
									"mitarbeiter_uid"=> $rowma->mitarbeiter_uid,
									"vorname"=>$rowma->mitarbeiter_uid,
									"nachname"=>$rowma->mitarbeiter_uid,
									"kurzbz"=>$rowma->mitarbeiter_uid
								);
							}
						}
						else
						{
							// TODO
						}
					}
				}

				$lehrfachdata = $this->ci->LehrveranstaltungModel->loadWhere(array('lehrveranstaltung_id' => $lehrfach_id));
				$lfdata = getData($lehrfachdata)[0];

				$lehrveranstaltungdata = $this->ci->LehrveranstaltungModel->loadWhere(array('lehrveranstaltung_id' => $lvid));
				$lvdata = getData($lehrveranstaltungdata)[0];

				$obj->topic = $lfdata->kurzbz.' '.$obj->lehrform;

				$orte = $this->ci->KalenderOrtModel->loadWhere(array('kalender_id'=>$rowstpl->kalender_id));
				$obj->ort_kurzbz = '';
				if(isSuccess($orte) && hasData($orte))
				{
					$ortedata = getdata($orte);
					foreach($ortedata as $ort);
					{
						$obj->ort_kurzbz .= $ort->ort_kurzbz;
					}
				}
				$obj->titel = '';
				$obj->lehrfach = $lfdata->kurzbz;
				$obj->lehrfach_bez = $lfdata->bezeichnung;
				$obj->organisationseinheit = $lvdata->oe_kurzbz;
				$obj->farbe = $lfdata->farbe;
				$obj->lehrveranstaltung_id = $lvid;
				$obj->kalender_id = $rowstpl->kalender_id;

				$stundenplan_data[] = $obj;
			}
		}
		return $stundenplan_data;
	}

	public function addKalenderEvent($user, $ort_kurzbz, $start_date, $end_date, $lehreinheit_id)
	{
		$kalenderresult = $this->ci->KalenderModel->insert(array(
			'von' => $start_date,
			'bis' => $end_date,
			'typ' => 'lehreinheit',
			'status_kurzbz' => 'planning',
			'insertvon' => $user,
			'insertamum' => date('Y-m-d H:i:s')
		));

		if(isSuccess($kalenderresult) && hasData($kalenderresult))
		{
			$kalender_id = getData($kalenderresult);


			$kalenderlehreinheitresult = $this->ci->KalenderLehreinheitModel->insert(array(
				'kalender_id' => $kalender_id,
				'lehreinheit_id' => $lehreinheit_id
			));

			if(isSuccess($kalenderlehreinheitresult))
			{
				$kalenderOrtresult = $this->ci->KalenderOrtModel->insert(array(
					'kalender_id'=>$kalender_id,
					'ort_kurzbz'=>$ort_kurzbz
				));
			}

		}
	}

	public function updateKalenderEvent($user, $kalender_id, $ort_kurzbz, $start_date, $end_date)
	{
		/*TODO Checks:
		Von-Tag muss gleich dem Bis-Tag sein
		Bis darf nicht vor von liegen

		History erstellen
		Sync Status setzen
		*/
		$this->ci->KalenderModel->update($kalender_id,
			array(
			'von'=>$start_date,
			'updateamum'=>date('Y-m-d H:i:s'),
			'updatevon' => $user
			)
		);
		return success();
	}
}
