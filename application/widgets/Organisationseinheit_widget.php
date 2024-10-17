<?php

class Organisationseinheit_widget extends DropdownWidget
{
    public function display($widgetData)
	{
		$this->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');

		if (isset($widgetData['typ']))
        {
            $this->setElementsArray($this->OrganisationseinheitModel->getRecursiveList($widgetData['typ']));
        }
		// If 'organisationseinheit' (array of specific oe_kurzbz) is given, retrieve these organisational units only
        elseif (isset($widgetData['organisationseinheit']) && !empty($widgetData['organisationseinheit']))
        {
            $condition = '
                oe_kurzbz IN (\''. implode('\',\'', $widgetData['organisationseinheit']) . '\') AND
                aktiv = TRUE
            ';
            $this->addSelectToModel($this->OrganisationseinheitModel, 'oe_kurzbz', 'organisationseinheittyp_kurzbz  || \' \' || bezeichnung');
            $this->OrganisationseinheitModel->addOrder('organisationseinheittyp_kurzbz', 'ASC');
            $this->setElementsArray(
                $this->OrganisationseinheitModel->loadWhere($condition),
                true,
                $this->p->t('lehre', 'organisationseinheit'),
                'No organisational units found'
            );
        }
		// Default: retrieve tree of all organisational units
		else
        {
            // NOTE: no need to call addSelectToModel because getRecursiveList already returns
	        // the correct names of the fields
		    $this->setElementsArray($this->OrganisationseinheitModel->getRecursiveList(),
			    true,
			    $this->p->t('lehre', 'organisationseinheit'),
			    'No organisational unit found');
        }
		
		$this->loadDropDownView($widgetData);
    }
}