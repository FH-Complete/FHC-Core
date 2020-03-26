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
		if ($idAdmin)
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
			$this->p->t('ui', 'vorlageWaehlen')
		);

		$this->loadDropDownView($widgetData);
    }

    /**
     * Get all the vorlage with mimetype = text/html
     */
    private function _getAllHTMLVorlage()
    {
		$this->load->model('system/Vorlage_model', 'VorlageModel');
		$this->VorlageModel->addOrder('bezeichnung');

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
					SELECT v.vorlage_kurzbz,
							v.bezeichnung,
							vs.version,
							vs.oe_kurzbz,
							vs.aktiv,
							vs.subject,
							vs.text,
							v.mimetype
					FROM tbl_vorlagestudiengang vs
					JOIN tbl_vorlage v USING(vorlage_kurzbz)
				) templates';

		$alias = 'templates';

	    $fields = array(
			'templates.vorlage_kurzbz AS id',
			'templates.bezeichnung || \' (\' || UPPER(templates.oe_kurzbz) || \')\' AS description'
		);

		$where = 'templates.aktiv = TRUE
					AND templates.subject IS NOT NULL
					AND templates.text IS NOT NULL
					AND templates.mimetype = \'text/html\'
			   GROUP BY 1, 2, 3';

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
					if (!hasData($vorlage))
					{
						for ($j = 0; $j < count(getData($tmpVorlage)); $j++)
						{
							if (getData($tmpVorlage)[$j]->id != '')
							{
								array_push($vorlage->retval, getData($tmpVorlage)[$j]);
							}
						}
					}
					else // checks for duplicates, if it's not already present push it into the array getData($vorlage)
					{
						for ($j = 0; $j < count(getData($tmpVorlage)); $j++)
						{
							$found = false;
							$currentTmpVorlageData = null;

							for ($i = 0; $i < count(getData($vorlage)); $i++)
							{
								$currentTmpVorlageData = getData($tmpVorlage)[$j];

								if (getData($vorlage)[$i]->id == getData($tmpVorlage)[$j]->id
									&& getData($vorlage)[$i]->_pk == getData($tmpVorlage)[$j]->_pk
									&& getData($vorlage)[$i]->_ppk == getData($tmpVorlage)[$j]->_ppk
									&& getData($vorlage)[$i]->_jtpk == getData($tmpVorlage)[$j]->_jtpk)
								{
									$found = true;
									break;
								}
							}

							if (!$found && $currentTmpVorlageData->id != '')
							{
								array_push($vorlage->retval, $currentTmpVorlageData);
							}
						}
					}
				}
			}
		}

		return $vorlage;
	}
}
