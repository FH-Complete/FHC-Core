<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

class Studienjahr extends VileSci_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model("organisation/Studienjahr_model", "StudienjahrModel");
	}

	public function index()
	{
		$this->listStudienjahr();
	}

	public function listStudienjahr()
	{
		$studienjahr = $this->StudienjahrModel->load();
		if ($studienjahr->error)
		{
			show_error($studienjahr->retval);
		}

		$data = array(
			"studienjahr" => $studienjahr->retval
		);
		$this->load->view("organisation/studienjahr.php", $data);
	}

	public function editStudienjahr($studienjahr_kurzbez)
	{
		$studienjahr_kurzbez = str_replace("_", "/", $studienjahr_kurzbez);
		$studienjahr = $this->StudienjahrModel->load($studienjahr_kurzbez);
		if ($studienjahr->error)
		{
			show_error($studienjahr->retval);
		}
		$data = array(
			"studienjahr" => $studienjahr->retval
		);
		$this->load->view("organisation/studienjahrEdit.php", $data);
	}

	public function newStudienjahr()
	{
		$this->StudienjahrModel->addOrder('studienjahr_kurzbz', "DESC");
		$allstudienjahrkurzbz = $this->StudienjahrModel->load();
		if ($allstudienjahrkurzbz->error)
		{
			show_error($allstudienjahrkurzbz->retval);
		}
		$studienjahrkurzbz = $allstudienjahrkurzbz->retval[0]->studienjahr_kurzbz;
		$years = $this->__getYearsFromStudienjahr($studienjahrkurzbz);
		$data = array(
			"studienjahrkurzbz" => ($years[0] + 1)."/".($years[1] + 1)
		);
		$this->load->view("organisation/studienjahrNew.php", $data);
	}

	private function __getYearsFromStudienjahr($studienjahr_kurzbez)
	{
		$firstyear = intval(substr($studienjahr_kurzbez, 0, 4));
		$secondyear = intval(substr($studienjahr_kurzbez, 5, 2));
		return array($firstyear, $secondyear);
	}

	public function insStudienjahr()
	{
		$data = $this->__retrieveStudienjahrData();
		$studienjahr = $this->StudienjahrModel->insert($data);

		if ($studienjahr->error)
		{
			show_error($studienjahr->retval);
		}

		redirect("/organisation/studienjahr/editStudienjahr/".str_replace("/", "_", $data['studienjahr_kurzbz']."?saved=true"));
	}

	private function __retrieveStudienjahrData(){
		$studienjahr_kurzbz = $this->input->post("studienjahrkurzbz");
		$bezeichnung = $this->input->post("studienjahrbz");

		$data = array(
			"studienjahr_kurzbz" => $studienjahr_kurzbz,
			"bezeichnung" => $bezeichnung,
		);

		$validation = $this->_validate($data);
		if (isSuccess($validation))
		{
			return $data;
		} else
		{
			show_error($validation->retval);
		}
	}

	private function _validate($data)
	{
		$studienjahr_kurzbz = $data['studienjahr_kurzbz'];
		$years = $this->__getYearsFromStudienjahr($studienjahr_kurzbz);
		//if not desired form or second year comes not right after the first
		$correctyears = $years[0] % 100 == $years[1] - 1;
		if (!preg_match("/^\d{4}\/\d{2}$/", $studienjahr_kurzbz) || !$correctyears)
			return error("Studienjahrbezeichnung muss folgende Form haben: Jahreszahl/letzeZweiZahlenDesNächstenJahres, z.B. 2017/18");
		return success("Semesterdaten sind valide");
	}

	public function saveStudienjahr()
	{
		$data = $this->__retrieveStudienjahrData();
		$studienjahr = $this->StudienjahrModel->update($data['studienjahr_kurzbz'], $data);

		if ($studienjahr->error)
		{
			show_error($studienjahr->retval);
		}

		redirect("/organisation/studienjahr/editStudienjahr/".str_replace("/", "_", $data['studienjahr_kurzbz']."?saved=true"));
	}

	public function deleteStudienjahr($studienjahr_kurzbez)
	{
		$studienjahr_kurzbez = str_replace("_", "/", $studienjahr_kurzbez);
		$studienjahr = $this->StudienjahrModel->delete($studienjahr_kurzbez);

		if ($studienjahr->error)
		{
			show_error($studienjahr->retval);
		}

		redirect("/organisation/studienjahr/listStudienjahr");
	}


}