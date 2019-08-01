<?php

class Studiengang_widget extends DropdownWidget
{
    public function display($widgetData)
	{
		// Studiengaenge
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->StudiengangModel->addOrder('kurzbzlang');
		
		$this->addSelectToModel($this->StudiengangModel, 'studiengang_kz', '\'(\' || kurzbzlang || \') \' || tbl_studiengang.bezeichnung');


		// If a specific array of studiengaenge is privided, set the condition to retrieve them
		if (isset($widgetData['studiengang']))
        {
            $condition = '
                studiengang_kz IN ('. implode(',', $widgetData['studiengang']) . ') AND
                aktiv = true
            ';
        }
		// Default: retrieve all studiengaenge
		else
        {
            $condition = array('aktiv' => true);
        }

        $this->setElementsArray(
            $this->StudiengangModel->loadWhere($condition),
            true,
            'Select a studiengang...',
            'No studiengaenge found'
        );

		$this->loadDropDownView();
    }
}