<?php

class Reihungstest_widget extends DropdownWidget
{
    public function display($widgetData)
	{
		$this->load->model('crm/Reihungstest_model', 'ReihungstestModel');
		$this->ReihungstestModel->addOrder('datum', 'DESC');

		$this->addSelectToModel($this->ReihungstestModel, 'reihungstest_id', 'CONCAT(datum, \' \',  uhrzeit, \' \', anmerkung)');

		$parametersArray = array();
		// If the parameters studiengang or studiensemester are given and are not empty
		if (isset($widgetData) && is_array($widgetData)
			&& ((isset($widgetData['studiengang']) && !isEmptyString($widgetData['studiengang']))
			|| (isset($widgetData['studiensemester']) && !isEmptyString($widgetData['studiensemester']))))
		{
			if ($widgetData['studiengang'] != null)
			{
				$parametersArray['studiengang_kz'] = $widgetData['studiengang'];
			}
			if ($widgetData['studiensemester'] != null)
			{
				$parametersArray['studiensemester_kurzbz'] = $widgetData['studiensemester'];
			}
		}
		else
		{
			// To NOT select anything
			// Set 0 = 1 in the where clause of the query
			$parametersArray['0'] = '1';
		}

		$this->setElementsArray(
			$this->ReihungstestModel->loadWhere($parametersArray),
			true,
			'Select a reihungstest...',
			'No reihungstest found'
		);

		$this->loadDropDownView($widgetData);
    }
}
