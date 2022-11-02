<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Studiensemester controller for listing, editing and removing a Studiensemester
 */
class Studiensemester extends Auth_Controller
{

	/**
	 * Studiensemester constructor.
	 * loads model for Studiensemester and Studienjahr (Studienjahr needed for dropdown)
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'listStudiensemester' => 'basis/studiensemester:r',
				'editStudiensemester' => 'basis/studiensemester:rw',
				'newStudiensemester' => 'basis/studiensemester:rw',
				'insStudiensemester' => 'basis/studiensemester:rw',
				'updateStudiensemester' => 'basis/studiensemester:rw',
				'deleteStudiensemester' => 'basis/studiensemester:rw'
			)
		);

		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->load->model('organisation/Studienjahr_model', 'StudienjahrModel');
	}

	/**
	 * by default, Studiensemesters are listed by calling the listStudiensemester function
	 */
	/*	public function index()
		{
			$this->listStudiensemester();
		}*/

	/**
	 * lists all Studiensemesters
	 */
	public function listStudiensemester()
	{
		$semester = $this->StudiensemesterModel->load();
		if ($semester->error)
		{
			show_error(getError($semester));
		}

		$data = array(
			"semester" => $semester->retval
		);
		$this->load->view("organisation/studiensemester.php", $data);
	}

	/**
	 * shows view for editing a Studiensemester with a given Kurzbezeichnung
	 * retrieves Studienjahre for showing in a dropdown in descending order
	 * @param $semester_kurzbez Semesterkurzbezeichnung, e.g. SS2017
	 */
	public function editStudiensemester($semester_kurzbez)
	{
		$semester = $this->StudiensemesterModel->load($semester_kurzbez);
		if ($semester->error)
		{
			show_error(getError($semester));
		}
		$this->StudienjahrModel->addOrder('studienjahr_kurzbz', "DESC");
		$allstudienjahre = $this->StudienjahrModel->load();
		if ($allstudienjahre->error)
		{
			show_error(getError($allstudienjahre));
		}
		$data = array(
			"semester" => $semester->retval,
			"allstudienjahre" => $allstudienjahre->retval
		);

		$this->load->view("organisation/studiensemesterEdit.php", $data);
	}

	/**
	 * shows view for adding a Studiensemester
	 * retrieves Studienjahre for showing in a dropdown in descending order
	 */
	public function newStudiensemester()
	{
		$this->StudienjahrModel->addOrder('studienjahr_kurzbz', "DESC");
		$allstudienjahre = $this->StudienjahrModel->load();
		if ($allstudienjahre->error)
		{
			show_error(getError($allstudienjahre));
		}

		$data = array(
			"allstudienjahre" => $allstudienjahre->retval
		);

		$this->load->view("organisation/studiensemesterNew.php", $data);
	}

	/**
	 * inserts a Studiensemester
	 * redirects to edit page after inserting.
	 * saved=true is a GET parameter passed for showing save message
	 */
	public function insStudiensemester()
	{
		$data = $this->__retrieveStudiensemesterData();

		$studiensemester_exists = $this->StudiensemesterModel->load($data['studiensemester_kurzbz']);
		if (hasData($studiensemester_exists))
			show_error("Studiensemester existiert bereits");

		$semester = $this->StudiensemesterModel->insert($data);

		if ($semester->error)
		{
			show_error(getError($semester));
		}

		redirect("/organisation/studiensemester/editStudiensemester/".$data['studiensemester_kurzbz']."?saved=true");
	}


	/**
	 * gets Studiensemester data from input fields (POST request)
	 * formats Studiensemester begin and end date as required by the database (english format)
	 * escapes html characters for all texts coming from text input fields
	 * validates the Studiensemester data before returning it or throwing an error
	 * @return array contains all data for a Studiensemester
	 */
	private function __retrieveStudiensemesterData()
	{
		$studiensemester_kurzbz = $this->input->post("semkurzbz");
		$bezeichnung = $this->input->post("sembz");
		$start = $this->input->post("semstart");
		$ende = $this->input->post("semende");
		$studienjahr_kurzbz = $this->input->post("studienjahrkurzbz");
		$beschreibung = isEmptyString($this->input->post("beschreibung")) ? null : $this->input->post("beschreibung");
		$onlinebewerbung = $this->input->post("onlinebewerbung");
		$onlinebewerbung = isset($onlinebewerbung);

		$data = array(
			"studiensemester_kurzbz" => $studiensemester_kurzbz,
			"bezeichnung" => html_escape($bezeichnung),
			"start" => $start,
			"ende" => $ende,
			"studienjahr_kurzbz" => $studienjahr_kurzbz,
			"beschreibung" => html_escape($beschreibung),
			"onlinebewerbung" => $onlinebewerbung
		);

		$validation = $this->_validate($data);
		if (isSuccess($validation))
		{
			//dateconversion
			$data["start"] = date_format(date_create($start), "Y-m-d");
			$data["ende"] = date_format(date_create($ende), "Y-m-d");
			return $data;
		} else
		{
			show_error(getError($validation));
		}
	}

	/**
	 * runs checks on Studiensemester data
	 * checks if Studiensemester Kurzbezeichnung has the correct form e.g. SS2017
	 * checks if date was given in the correct format dd.mm.yyyy (german format)
	 * @param $data contains all data for a Studiensemester
	 * @return array errorarray with error text if a check failed or success-array if all checks succeeded
	 */
	private function _validate($data)
	{
		$datepattern = "/^\d{2}.\d{2}.\d{4}$/";

		if (!preg_match("/^(WS|SS)\d{4}$/", $data['studiensemester_kurzbz']))
			return error("Semesterkurzbezeichnung muss mit WS oder SS beginnen und mit einer Jahreszahl enden, z.B. SS2017");
		if (!preg_match($datepattern, $data['start']))
			return error("Falsches Startdatumsformat. Richtiges Format: dd.mm.yyyy");
		if (!preg_match($datepattern, $data['ende']))
			return error("Falsches Enddatumsformat. Richtiges Format: dd.mm.yyyy");
		return success("Semesterdaten sind valide");
	}

	/**
	 * updates a Studiensemester
	 * redirects to edit page after inserting
	 * saved=true is a GET parameter passed for showing save message
	 */
	public function updateStudiensemester()
	{
		$data = $this->__retrieveStudiensemesterData();
		$semester = $this->StudiensemesterModel->update($data['studiensemester_kurzbz'], $data);

		if ($semester->error)
		{
			show_error(getError($semester));
		}

		redirect("/organisation/studiensemester/editStudiensemester/".$data['studiensemester_kurzbz']."?saved=true");
	}

	/**
	 * deletes a Studiensemester
	 * redirects to list Studiensemester view after deleting
	 * @param $semester_kurzbez Semesterkurzbezeichnung, e.g. SS2017
	 */
	public function deleteStudiensemester($semester_kurzbez)
	{
		$semester = $this->StudiensemesterModel->delete($semester_kurzbez);

		if ($semester->error)
		{
			show_error(getError($semester));
		}

		redirect("/organisation/studiensemester/listStudiensemester");
	}
}
