<?php

class Vorlage_widget extends Widget
{
    public function display($data)
	{
		$this->load->model("system/Vorlage_model", "VorlageModel");
		$this->VorlageModel->addOrder("vorlage_kurzbz");
		$result = $this->VorlageModel->loadWhere(array("mimetype" => "text/html"));
		
		if (is_object($result) && $result->error == EXIT_SUCCESS)
		{
			// Adding an empty element
			$emptyVorlage = new stdClass();
			$emptyVorlage->vorlage_kurzbz = '-1';
			$emptyVorlage->bezeichnung = 'Select a template...';
			array_unshift($result->retval, $emptyVorlage);
			
			$data = array("vorlage" => $result->retval);
			$this->view("widgets/vorlage", $data);
		}
    }
}