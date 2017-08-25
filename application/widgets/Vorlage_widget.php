<?php

class Vorlage_widget extends DropdownWidget
{
    public function display($widgetData)
	{
		// All organization units to which the user belongs
		$oe_kurzbz = $widgetData['oe_kurzbz'];
		$idAdmin = $widgetData['isAdmin'];
		
		$vorlage = null;
		
		// If the user is an admin
		if ($idAdmin === true)
		{
			 // Get all the vorlage with mimetype = text/html
			$vorlage = $this->_getAllHTMLVorlage();
		}
		else
		{
			// Get all the vorlage that belongs to the organisation units of the user
			// and the parents of those organisation units until the root of the
			// organisation unit tree
			$vorlage = $this->_getUserVorlage($oe_kurzbz);
		}
		
		$this->setElementsArray(
			$vorlage,
			true,
			'Select a vorlage...',
			'No vorlage found'
		);
		
		$this->loadDropDownView($widgetData);
    }
    
    /**
     * Get all the vorlage with mimetype = text/html
     */
    private function _getAllHTMLVorlage()
    {
		$this->load->model('system/Vorlage_model', 'VorlageModel');
		$this->VorlageModel->addOrder('vorlage_kurzbz');
		
		$this->addSelectToModel($this->VorlageModel, 'vorlage_kurzbz', 'bezeichnung');
		
		return $this->VorlageModel->loadWhere(array('mimetype' => 'text/html'));
    }
    
    /**
     * Get all the vorlage that belongs to the organisation units of the user
     * and the parents of those organisation units until the root of the
     * organisation unit tree
     */
    private function _getUserVorlage($oe_kurzbz)
    {
		// Loads library OrganisationseinheitLib
        $this->load->library('OrganisationseinheitLib');
        
		$vorlage = success(array()); // Default value
		
		$table = '(
					SELECT v.vorlage_kurzbz, v.bezeichnung, vs.version, vs.oe_kurzbz, vs.aktiv, vs.subject, vs.text, v.mimetype
					FROM tbl_vorlagestudiengang vs INNER JOIN tbl_vorlage v USING(vorlage_kurzbz)
				) templates';
		$alias = 'templates';
		$fields = array("templates.vorlage_kurzbz AS id", "UPPER(templates.oe_kurzbz) || ' - ' || templates.bezeichnung || ' - V' || templates.version AS description");
		$where = 'templates.aktiv = TRUE
				AND templates.subject IS NOT NULL
				AND templates.text IS NOT NULL
				AND templates.mimetype = \'text/html\'';
		$order_by = 'description ASC';
		
		if (!is_array($oe_kurzbz))
		{
			$vorlage = $this->organisationseinheitlib->treeSearchEntire(
				$table,
				$alias,
				$fields,
				$where,
				$order_by,
				$oe_kurzbz
			);
		}
		else // is an array
		{
			// Get the vorlage for each organisation unit
			foreach($oe_kurzbz as $val)
			{
				$tmpVorlage = $this->organisationseinheitlib->treeSearchEntire(
					$table,
					$alias,
					$fields,
					$where,
					$order_by,
					$val
				);
				
				// Everything is ok and data are inside
				if (hasData($tmpVorlage))
				{
					// If it's the first vorlage copy it
					if (count($vorlage->retval) == 0)
					{
						$vorlage->retval = $tmpVorlage->retval;
					}
					else // checks for duplicates, if it's not already present push it into the array $vorlage->retval
					{
						for ($i = 0; $i < count($vorlage->retval); $i++)
						{
							for ($j = 0; $j < count($tmpVorlage->retval); $j++)
							{
								if ($vorlage->retval[$i]->_pk != $tmpVorlage->retval[$j]->_pk
									&& $vorlage->retval[$i]->_ppk != $tmpVorlage->retval[$j]->_ppk
									&& $vorlage->retval[$i]->_jtpk != $tmpVorlage->retval[$j]->_jtpk)
								{
									array_push($vorlage->retval, $tmpVorlage->retval[$j]);
								}
							}
						}
					}
				}
			}
		}
		
		return $vorlage;
    }
}
