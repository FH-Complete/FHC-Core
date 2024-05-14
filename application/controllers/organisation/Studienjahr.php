<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Studienjahr controller for listing, editing and removing a Studienjahr
 */
class Studienjahr extends Auth_Controller
{

	/**
	 * Studienjahr constructor.
	 * loads model for Studienjahr
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'listStudienjahr' => 'basis/studiensemester:r',
				'editStudienjahr' => 'basis/studiensemester:rw',
				'newStudienjahr' => 'basis/studiensemester:rw',
				'insStudienjahr' => 'basis/studiensemester:rw',
				'updateStudienjahr' => 'basis/studiensemester:rw',
				'deleteStudienjahr' => 'basis/studiensemester:rw'
			)
		);

		$this->load->model('organisation/Studienjahr_model', 'StudienjahrModel');
	}

	/**
	 * by default, Studienjahre are listed by calling the listStudienjahr function
	 */
	/*	public function index()
		{
			$this->listStudienjahr();
		}*/

	/**
	 * lists all Studienjahre
	 */
	public function listStudienjahr()
	{
		$studienjahr = $this->StudienjahrModel->load();
		if ($studienjahr->error)
		{
			show_error(getError($studienjahr));
		}

		$data = array(
			"studienjahr" => $studienjahr->retval
		);
		$this->load->view("organisation/studienjahr.php", $data);
	}

	/**
	 * shows view for editing a Studienjahr with a given Kurzbezeichnung
	 * replaces slash in Kurzbezeichnung with underscore,
	 * otherwise the Kurzbezeichnung is treated as part of url navigation
	 * e.g. organisation/studienjahr/editStudienjahr/2017/18
	 * @param $studienjahr_kurzbez Studienjahrkurzbezeichnung, e.g. 2017/18
	 */
	public function editStudienjahr($studienjahr_kurzbez)
	{
		$studienjahr_kurzbez = str_replace("_", "/", $studienjahr_kurzbez);
		$studienjahr = $this->StudienjahrModel->load($studienjahr_kurzbez);
		if ($studienjahr->error)
		{
			show_error(getError($studienjahr));
		}
		$data = array(
			"studienjahr" => $studienjahr->retval
		);
		$this->load->view("organisation/studienjahrEdit.php", $data);
	}

	/**
	 * shows view for adding a Studienjahr
	 * retrieves all Studienjahre, increases last Studienjahr in database by 1 to get current Studienjahr
	 * sends current Studienjahrkurzbezeichnung to view
	 * So view can prefill fields with current Studienjahr
	 */
	public function newStudienjahr()
	{
		$this->StudienjahrModel->addOrder('studienjahr_kurzbz', "DESC");
		$allstudienjahrkurzbz = $this->StudienjahrModel->load();
		if ($allstudienjahrkurzbz->error)
		{
			show_error(getError($allstudienjahrkurzbz));
		}
		$studienjahrkurzbz = $allstudienjahrkurzbz->retval[0]->studienjahr_kurzbz;
		$years = $this->__getYearsFromStudienjahr($studienjahrkurzbz);
		$data = array(
			"studienjahrkurzbz" => ($years[0] + 1)."/".($years[1] + 1)
		);
		$this->load->view("organisation/studienjahrNew.php", $data);
	}

	/**
	 * helper function for extracting the two years from Studienjahrkurzbezeichnung
	 * @param $studienjahr_kurzbez Studienjahrkurzbezeichnung, e.g. 2017/18
	 * @return array contains the two years, e.g. [0] - 2017, [1] - 18
	 */
	private function __getYearsFromStudienjahr($studienjahr_kurzbez)
	{
		$firstyear = intval(substr($studienjahr_kurzbez, 0, 4));
		$secondyear = intval(substr($studienjahr_kurzbez, 5, 2));
		return array($firstyear, $secondyear);
	}

	/**
	 * inserts a Studienjahr
	 * replaces slash in Kurzbezeichnung with underscore,
	 * redirects to edit page after inserting.
	 * saved=true is a GET parameter passed for showing save message
	 */
	public function insStudienjahr()
	{
		$data = $this->__retrieveStudienjahrData();
		$studienjahr_exists = $this->StudienjahrModel->load($data['studienjahr_kurzbz']);
		if (hasData($studienjahr_exists))
			show_error("Studienjahr existiert bereits");

		$studienjahr = $this->StudienjahrModel->insert($data);

		if ($studienjahr->error)
		{
			show_error(getError($studienjahr));
		}

		redirect("/organisation/studienjahr/editStudienjahr/".str_replace("/", "_", $data['studienjahr_kurzbz']."?saved=true"));
	}

	/**
	 * gets Studienjahr data from input fields (POST request)
	 * escapes html characters for all texts coming from text input fields
	 * validates the Studienjahr data before returning it or throwing an error
	 * @return array contains all data for a Studienjahr
	 */
	private function __retrieveStudienjahrData()
	{
		$studienjahr_kurzbz = $this->input->post("studienjahrkurzbz");
		$bezeichnung = $this->input->post("studienjahrbz");

		$data = array(
			"studienjahr_kurzbz" => $studienjahr_kurzbz,
			"bezeichnung" => html_escape($bezeichnung)
		);

		$validation = $this->_validate($data);
		if (isSuccess($validation))
		{
			return $data;
		} else
		{
			show_error(getError($validation));
		}
	}

	/**
	 * runs checks on Studienjahr data
	 * checks if Studienjahr Kurzbezeichnung has the correct form e.g. 2017/18
	 * checks if second year in Studienjahr is exactly one year after first
	 * @param $data contains all data for a Studienjahr
	 * @return array errorarray with error text if a check failed or success-array if all checks succeeded
	 */
	private function _validate($data)
	{
		$studienjahr_kurzbz = $data['studienjahr_kurzbz'];
		$years = $this->__getYearsFromStudienjahr($studienjahr_kurzbz);
		//if wrong form or second year comes not right after the first
		$correctyears = $years[0] % 100 == $years[1] - 1;
		if (!preg_match("/^\d{4}\/\d{2}$/", $studienjahr_kurzbz) || !$correctyears)
			return error("Studienjahrbezeichnung muss folgende Form haben: Jahreszahl/letzeZweiZahlenDesNächstenJahres, z.B. 2017/18");
		return success("Studienjahrdaten sind valide");
	}

	/**
	 * updates a Studienjahr
	 * redirects to edit page after inserting
	 * replaces slash in Kurzbezeichnung with underscore
	 * saved=true is a GET parameter passed for showing save message
	 */
	public function updateStudienjahr()
	{
		$data = $this->__retrieveStudienjahrData();
		$studienjahr = $this->StudienjahrModel->update($data['studienjahr_kurzbz'], $data);

		if ($studienjahr->error)
		{
			show_error(getError($studienjahr));
		}

		redirect("/organisation/studienjahr/editStudienjahr/".str_replace("/", "_", $data['studienjahr_kurzbz']."?saved=true"));
	}

	/**
	 * deletes a Studienjahr
	 * redirects to list Studienjahr view after deleting
	 * replaces slash in Kurzbezeichnung with underscore
	 * @param $studienjahr_kurzbez Studienjahrkurzbezeichnung, e.g. SS2017
	 */
	public function deleteStudienjahr($studienjahr_kurzbez)
	{
		$studienjahr_kurzbez = str_replace("_", "/", $studienjahr_kurzbez);
		$studienjahr = $this->StudienjahrModel->delete($studienjahr_kurzbez);

		if ($studienjahr->error)
		{
			show_error(getError($studienjahr));
		}

		redirect("/organisation/studienjahr/listStudienjahr");
	}


}
