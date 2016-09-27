<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

class Statusgrund extends VileSci_Controller
{
	public function __construct()
    {
        parent::__construct();
        $this->load->model("crm/Status_model", "StatusModel");
        $this->load->model("crm/Statusgrund_model", "StatusgrundModel");
        $this->load->model("system/Sprache_model", "SpracheModel");
    }

	public function index()
	{
		$this->load->view("crm/statusgrund.php");
	}
	
	public function listStatus()
	{
		$status = $this->StatusModel->load();
		if ($status->error)
		{
			show_error($status->retval);
		}
		
		$data = array (
			"status" => $status->retval
		);
		
		$this->load->view("crm/statusgrundList.php", $data);
	}
	
	public function editGrund($status_kurzbz)
	{
		$statusGrund = $this->StatusgrundModel->loadWhere(array("status_kurzbz" => $status_kurzbz));
		if ($statusGrund->error)
		{
			show_error($statusGrund->retval);
		}
		
		if (count($statusGrund->retval) == 0)
		{
			$statusGrund->retval[0] = new stdClass();
			$statusGrund->retval[0]->status_kurzbz = $status_kurzbz;
		}
		
		$sprache = $this->SpracheModel->load();
		if ($sprache->error)
		{
			show_error($sprache->retval);
		}
		
		$data = array (
			"statusgrund" => $statusGrund->retval[0],
			"sprache" => $sprache->retval
		);
		
		$this->load->view("crm/statusgrundEdit.php", $data);
	}
	
	public function saveGrund()
	{
		$statusgrund_kurzbz = $this->input->post("statusgrund_kurzbz");
		$aktiv = $this->input->post("aktiv") != null && $this->input->post("aktiv") == "on" ? true : false;
		$bezeichnung_mehrsprachig = $this->input->post("bezeichnung_mehrsprachig");
		$beschreibung = $this->input->post("beschreibung");
		$status_kurzbz = $this->input->post("status_kurzbz");
		
		for ($i = 0; $i < count($bezeichnung_mehrsprachig); $i++)
		{
			if ($i == 0) $tmp = "{";
			
			if (trim($bezeichnung_mehrsprachig[$i]) != "")
			{
				$bezeichnung_mehrsprachig[$i] = str_replace(",", "|", $bezeichnung_mehrsprachig[$i]);
				if ($i < count($bezeichnung_mehrsprachig) - 1)
				{
					$tmp .= $bezeichnung_mehrsprachig[$i] . ",";
				}
				else
				{
					$tmp .= $bezeichnung_mehrsprachig[$i];
				}
			}
			
			if ($i == count($bezeichnung_mehrsprachig) - 1) $bezeichnung_mehrsprachig = $tmp . "}";
		}
		
		for ($i = 0; $i < count($beschreibung); $i++)
		{
			if ($i == 0) $tmp = "{";
			
			if (trim($beschreibung[$i]) != "")
			{
				$beschreibung[$i] = str_replace(",", "|", $beschreibung[$i]);
				if ($i < count($beschreibung) - 1)
				{
					$tmp .= $beschreibung[$i] . ",";
				}
				else
				{
					$tmp .= $beschreibung[$i];
				}
			}
			
			if ($i == count($beschreibung) - 1) $beschreibung = $tmp . "}";
		}
		
		$data = array(
			"aktiv" => $aktiv,
			"bezeichnung_mehrsprachig" => $bezeichnung_mehrsprachig,
			"beschreibung" => $beschreibung,
			"status_kurzbz" => $status_kurzbz
		);
		
		if (is_numeric($statusgrund_kurzbz))
		{
			$statusgrund = $this->StatusgrundModel->update($statusgrund_kurzbz, $data);
		}
		else
		{
			$statusgrund = $this->StatusgrundModel->insert($data);
		}
		
		if ($statusgrund->error)
		{
			show_error($tatusgrund->retval);
		}
		
		redirect("/crm/Statusgrund/editGrund/" . $status_kurzbz);
	}
}
