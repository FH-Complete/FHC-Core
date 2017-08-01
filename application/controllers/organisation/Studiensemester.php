<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Description of Semester
 *
 * @author root
 */
class Studiensemester extends VileSci_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model("organisation/Studiensemester_model", "StudiensemesterModel");
		$this->load->model("organisation/Studienjahr_model", "StudienjahrModel");
	}

	public function index()
	{
		$this->listStudiensemester();
	}

	public function listStudiensemester()
	{
		$semester = $this->StudiensemesterModel->load();
		if ($semester->error)
		{
			show_error($semester->retval);
		}

		$data = array(
			"semester" => $semester->retval
		);
		$this->load->view("organisation/studiensemester.php", $data);
	}

	public function editStudiensemester($semester_kurzbez)
	{
		$semester = $this->StudiensemesterModel->load($semester_kurzbez);
		if ($semester->error)
		{
			show_error($semester->retval);
		}
		$this->StudienjahrModel->addOrder('studienjahr_kurzbz', "DESC");
		$allstudienjahre = $this->StudienjahrModel->load();
		if ($allstudienjahre->error)
		{
			show_error($allstudienjahre->retval);
		}
		$data = array(
			"semester" => $semester->retval,
			"allstudienjahre" => $allstudienjahre->retval
		);

		$this->load->view("organisation/studiensemesterEdit.php", $data);
	}

	public function newStudiensemester()
	{
		$this->StudienjahrModel->addOrder('studienjahr_kurzbz', "DESC");
		$allstudienjahre = $this->StudienjahrModel->load();
		if ($allstudienjahre->error)
		{
			show_error($allstudienjahre->retval);
		}

		$data = array(
			"allstudienjahre" => $allstudienjahre->retval
		);

		$this->load->view("organisation/studiensemesterNew.php", $data);
	}

	/**
	 * inserts a Studiensemester
	 * formats dates in english as required by database
	 */
	public function insStudiensemester()
	{
		$data = $this->__retrieveStudiensemesterData();
		$semester = $this->StudiensemesterModel->insert($data);

		if ($semester->error)
		{
			show_error($semester->retval);
		}

		redirect("/organisation/studiensemester/editStudiensemester/".$data['studiensemester_kurzbz']."?saved=true");
	}


	private function __retrieveStudiensemesterData()
	{
		$studiensemester_kurzbz = $this->input->post("semkurzbz");
		$bezeichnung = $this->input->post("sembz");
		$start = $this->input->post("semstart");
		$ende = $this->input->post("semende");
		$studienjahr_kurzbz = $this->input->post("studienjahrkurzbz");
		$beschreibung = $this->input->post("beschreibung");
		$onlinebewerbung = $this->input->post("onlinebewerbung");
		$onlinebewerbung = isset($onlinebewerbung);

		$data = array(
			"studiensemester_kurzbz" => $studiensemester_kurzbz,
			"bezeichnung" => $bezeichnung,
			"start" => $start,
			"ende" => $ende,
			"studienjahr_kurzbz" => $studienjahr_kurzbz,
			"beschreibung" => $beschreibung,
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
			show_error($validation->retval);
		}
	}

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

	public function saveStudiensemester()
	{
		$data = $this->__retrieveStudiensemesterData();
		$semester = $this->StudiensemesterModel->update($data['studiensemester_kurzbz'], $data);

		if ($semester->error)
		{
			show_error($semester->retval);
		}

		redirect("/organisation/studiensemester/editStudiensemester/".$data['studiensemester_kurzbz']."?saved=true");
	}

	public function deleteStudiensemester($semester_kurzbez)
	{
		$semester = $this->StudiensemesterModel->delete($semester_kurzbez);

		if ($semester->error)
		{
			show_error($semester->retval);
		}

		redirect("/organisation/studiensemester/listStudiensemester");
	}

}
