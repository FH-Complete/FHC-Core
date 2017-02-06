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

		$this->load->view("crm/statusList.php", $data);
	}

	public function listGrund($status_kurzbz)
	{
		$statusGrund = $this->StatusgrundModel->loadWhere(array("status_kurzbz" => $status_kurzbz));
		if ($statusGrund->error)
		{
			show_error($statusGrund->retval);
		}

		$data = array (
			"statusGrund" => $statusGrund->retval,
			"status_kurzbz" => $status_kurzbz
		);

		$this->load->view("crm/statusGrundList.php", $data);
	}

	public function editGrund($statusgrund_id, $update = null)
	{
		$statusGrund = $this->StatusgrundModel->load($statusgrund_id);
		if ($statusGrund->error)
		{
			show_error($statusGrund->retval);
		}

		$sprache = $this->SpracheModel->loadWhere(array('content' => true));
		if ($sprache->error)
		{
			show_error($sprache->retval);
		}

		$data = array (
			"statusgrund" => $statusGrund->retval[0],
			"sprache" => $sprache->retval,
			"update" => $update
		);

		$this->load->view("crm/statusgrundEdit.php", $data);
	}

	public function editStatus($status_id, $update = null)
	{
		$status = $this->StatusModel->load($status_id);
		if ($status->error)
		{
			show_error($status->retval);
		}

		$sprache = $this->SpracheModel->loadWhere(array('content' => true));
		if ($sprache->error)
		{
			show_error($sprache->retval);
		}

		$data = array (
			"status" => $status->retval[0],
			"sprache" => $sprache->retval,
			"update" => $update
		);

		$this->load->view("crm/statusEdit.php", $data);
	}

	public function newGrund($status_kurzbz)
	{
		$sprache = $this->SpracheModel->loadWhere(array('content' => true));
		if ($sprache->error)
		{
			show_error($sprache->retval);
		}

		$data = array (
			"status_kurzbz" => $status_kurzbz,
			"sprache" => $sprache->retval
		);

		$this->load->view("crm/statusgrundNew.php", $data);
	}

	public function saveGrund()
	{
		$statusgrund_id = $this->input->post("statusgrund_id");
		$aktiv = $this->input->post("aktiv") != null && $this->input->post("aktiv") == "on" ? true : false;
		$bezeichnung_mehrsprachig = $this->input->post("bezeichnung_mehrsprachig");
		$beschreibung = $this->input->post("beschreibung");

		for ($i = 0; $i < count($bezeichnung_mehrsprachig); $i++)
		{
			if ($i == 0) $tmp = "{";

			if (trim($bezeichnung_mehrsprachig[$i]) == "")
			{
				$bezeichnung_mehrsprachig[$i] = "\"\"";
			}

			$bezeichnung_mehrsprachig[$i] = str_replace(",", "|", $bezeichnung_mehrsprachig[$i]);
			if ($i < count($bezeichnung_mehrsprachig) - 1)
			{
				$tmp .= $bezeichnung_mehrsprachig[$i] . ",";
			}
			else
			{
				$tmp .= $bezeichnung_mehrsprachig[$i];
			}

			if ($i == count($bezeichnung_mehrsprachig) - 1) $bezeichnung_mehrsprachig = $tmp . "}";
		}

		for ($i = 0; $i < count($beschreibung); $i++)
		{
			if ($i == 0) $tmp = "{";

			if (trim($beschreibung[$i]) == "")
			{
				$beschreibung[$i] = "\"\"";
			}

			$beschreibung[$i] = str_replace(",", "|", $beschreibung[$i]);
			if ($i < count($beschreibung) - 1)
			{
				$tmp .= $beschreibung[$i] . ",";
			}
			else
			{
				$tmp .= $beschreibung[$i];
			}

			if ($i == count($beschreibung) - 1) $beschreibung = $tmp . "}";
		}

		$data = array(
			"aktiv" => $aktiv,
			"bezeichnung_mehrsprachig" => $bezeichnung_mehrsprachig,
			"beschreibung" => $beschreibung
		);

		$statusgrund = $this->StatusgrundModel->update($statusgrund_id, $data);

		if ($statusgrund->error)
		{
			show_error($statusgrund->retval);
		}

		redirect("/crm/Statusgrund/editGrund/" . $statusgrund_id . "/" . true);
	}

	public function insGrund()
	{
		$aktiv = $this->input->post("aktiv") != null && $this->input->post("aktiv") == "on" ? true : false;
		$bezeichnung_mehrsprachig = $this->input->post("bezeichnung_mehrsprachig");
		$beschreibung = $this->input->post("beschreibung");
		$status_kurzbz = $this->input->post("status_kurzbz");

		for ($i = 0; $i < count($bezeichnung_mehrsprachig); $i++)
		{
			if ($i == 0) $tmp = "{";

			if (trim($bezeichnung_mehrsprachig[$i]) == "")
			{
				$bezeichnung_mehrsprachig[$i] = "\"\"";
			}

			$bezeichnung_mehrsprachig[$i] = str_replace(",", "|", $bezeichnung_mehrsprachig[$i]);
			if ($i < count($bezeichnung_mehrsprachig) - 1)
			{
				$tmp .= $bezeichnung_mehrsprachig[$i] . ",";
			}
			else
			{
				$tmp .= $bezeichnung_mehrsprachig[$i];
			}

			if ($i == count($bezeichnung_mehrsprachig) - 1) $bezeichnung_mehrsprachig = $tmp . "}";
		}

		for ($i = 0; $i < count($beschreibung); $i++)
		{
			if ($i == 0) $tmp = "{";

			if (trim($beschreibung[$i]) == "")
			{
				$beschreibung[$i] = "\"\"";
			}

			$beschreibung[$i] = str_replace(",", "|", $beschreibung[$i]);
			if ($i < count($beschreibung) - 1)
			{
				$tmp .= $beschreibung[$i] . ",";
			}
			else
			{
				$tmp .= $beschreibung[$i];
			}

			if ($i == count($beschreibung) - 1) $beschreibung = $tmp . "}";
		}

		$data = array(
			"status_kurzbz" => $status_kurzbz,
			"aktiv" => $aktiv,
			"bezeichnung_mehrsprachig" => $bezeichnung_mehrsprachig,
			"beschreibung" => $beschreibung
		);

		$statusgrund = $this->StatusgrundModel->insert($data);

		if ($statusgrund->error)
		{
			show_error($statusgrund->retval);
		}

		redirect("/crm/Statusgrund/editGrund/" . $statusgrund->retval . "/" . true);
	}

	public function saveStatus()
	{
		$status_kurzbz = $this->input->post("status_kurzbz");
		$anmerkung = $this->input->post("anmerkung");
		$bezeichnung_mehrsprachig = $this->input->post("bezeichnung_mehrsprachig");
		$beschreibung = $this->input->post("beschreibung");

		for ($i = 0; $i < count($bezeichnung_mehrsprachig); $i++)
		{
			if ($i == 0) $tmp = "{";

			if (trim($bezeichnung_mehrsprachig[$i]) == "")
			{
				$bezeichnung_mehrsprachig[$i] = "\"\"";
			}

			$bezeichnung_mehrsprachig[$i] = str_replace(",", "|", $bezeichnung_mehrsprachig[$i]);
			if ($i < count($bezeichnung_mehrsprachig) - 1)
			{
				$tmp .= $bezeichnung_mehrsprachig[$i] . ",";
			}
			else
			{
				$tmp .= $bezeichnung_mehrsprachig[$i];
			}

			if ($i == count($bezeichnung_mehrsprachig) - 1) $bezeichnung_mehrsprachig = $tmp . "}";
		}

		$data = array(
			"anmerkung" => $anmerkung,
			"bezeichnung_mehrsprachig" => $bezeichnung_mehrsprachig,
			"beschreibung" => $beschreibung
		);

		$status = $this->StatusModel->update($status_kurzbz, $data);

		if ($status->error)
		{
			show_error($status->retval);
		}

		redirect("/crm/Statusgrund/editStatus/" . $status_kurzbz . "/" . true);
	}
}
