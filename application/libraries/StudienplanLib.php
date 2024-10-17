<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

class StudienplanLib
{
	/**
	 * Loads model OrganisationseinheitModel
	 */
	public function __construct()
	{
		$this->ci =& get_instance();

		// Loads model Organisationseinheit_model
		$this->ci->load->model('organisation/Studienplan_model', 'StudienplanModel');
	}

	public function getLehrveranstaltungTree($studienplan_id, $semester, $studplan = null)
	{
		$tree = array();
		$data = $this->ci->StudienplanModel->getStudienplanLehrveranstaltung($studienplan_id, $semester);
		if(isSuccess($data) && hasData($data))
		{
			$this->lehrveranstaltungen = $data->retval;
			foreach($this->lehrveranstaltungen as $row)
			{
				if (!is_null($studplan) && $row->export != $studplan)
					continue;

				if (is_null($row->studienplan_lehrveranstaltung_id_parent))
				{
					$treeitem = array(
						'lehrveranstaltung_id' => $row->lehrveranstaltung_id,
						'lehrtyp_kurzbz' => $row->lehrtyp_kurzbz,
						'lehrform_kurzbz' => $row->lehrform_kurzbz,
						'sws' => $row->sws,
						'pflicht' => $row->pflicht,
						'zeugnis' => $row->zeugnis,
						'bezeichnung' => $row->bezeichnung,
						'kurzbz' => $row->kurzbz,
						'ects' => $row->ects,
						'semester' => $row->semester
					);
					$childs = $this->getChildElements($row->studienplan_lehrveranstaltung_id);
					if(is_array($childs) && count($childs) > 0)
						$treeitem['childs'] = $childs;
					$tree[] = $treeitem;
				}
			}
		}
		return $tree;
	}

	private function getChildElements($studienplan_lehrveranstaltung_id)
	{
		$subtree = array();

		foreach($this->lehrveranstaltungen as $row)
		{
			if($studienplan_lehrveranstaltung_id == $row->studienplan_lehrveranstaltung_id_parent)
			{
				$treeitem = array(
					'lehrveranstaltung_id' => $row->lehrveranstaltung_id,
					'lehrtyp_kurzbz' => $row->lehrtyp_kurzbz,
					'lehrform_kurzbz' => $row->lehrform_kurzbz,
					'sws' => $row->sws,
					'pflicht' => $row->pflicht,
					'zeugnis' => $row->zeugnis,
					'bezeichnung' => $row->bezeichnung,
					'kurzbz' => $row->kurzbz,
					'ects' => $row->ects,
					'semester' => $row->semester
				);
				$childs = $this->getChildElements($row->studienplan_lehrveranstaltung_id);
				if(is_array($childs))
					$treeitem['childs'] = $childs;
				$subtree[] = $treeitem;
			}
		}
		return $subtree;
	}
}
