<?php

class Ausbildungssemester_widget extends DropdownWidget
{
    public function display($widgetData)
	{
        $ausbildungssemester_arr = array();

        // Set max number of ausbildungssemester
        if (isset($widgetData['studiengang']) && is_numeric($widgetData['studiengang']))  // max semester for given studiengang
        {
            // to be done
        }
        elseif (isset($widgetData['number_semester']) && is_numeric($widgetData['number_semester']))  // custom number of semester
        {
            $number_semester = $widgetData['number_semester'];   // max semester for bachelor
        }
        else
        {
            $number_semester = 10;  // default
        }


        // Generate number series
        for ($i = 1; $i <= $number_semester; $i++)
        {
            $ausbildungssemester_obj = new StdClass();
            $ausbildungssemester_obj->id = $i;
            $ausbildungssemester_obj->description = $i;

            $ausbildungssemester_arr []= $ausbildungssemester_obj;
        }

		$this->setElementsArray(
            success($ausbildungssemester_arr),
			true,
			$this->p->t('lehre', 'ausbildungssemester'),
			'No Ausbildungssemester found'
		);

		$this->loadDropDownView($widgetData);
    }
}