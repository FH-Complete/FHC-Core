<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

class KalenderNotificationLib
{
	private $_ci;
	public function __construct()
	{
		$this->_ci =& get_instance();
		$this->_ci->load->library('MailLib');
		$this->_ci->load->config('tempus');

	}

	public function sendMails($mail_infos)
	{
		if (!$this->_ci->config->item('send_update_mails'))
			return true;

		$lektor_added = array();
		$lektor_changed = array();
		$lektor_deleted = array();

		$student_added = array();
		$student_changed = array();
		$student_deleted = array();

		foreach ($mail_infos as $info)
		{
			$entry = $info['entry'];
			$new_status = $info['new_status'];
			$notify = $info['notify'];

			$old_entry = null;
			if ($entry->vorgaenger_kalender_id)
			{
				$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_ort', 'tbl_kalender.kalender_id = tbl_kalender_ort.kalender_id', 'LEFT');
				$vorgaenger = $this->_ci->KalenderModel->load(array('tbl_kalender.kalender_id' => $entry->vorgaenger_kalender_id));
				if (hasData($vorgaenger))
					$old_entry = getData($vorgaenger)[0];
			}

			if ($new_status === 'deleted')
				$row = $this->_buildMailDeleted($entry);
			else if ($old_entry)
				$row = $this->_buildMailChanged($old_entry, $entry);
			else
				$row = $this->_buildMailNew($entry);

			if (in_array('lektor', $notify))
			{
				if ($new_status === 'deleted')
					$lektor_deleted[] = $row;
				else if ($old_entry)
					$lektor_changed[] = $row;
				else
					$lektor_added[] = $row;
			}

			if (in_array('student', $notify))
			{
				if ($new_status === 'deleted')
					$student_deleted[] = $row;
				else if ($old_entry)
					$student_changed[] = $row;
				else
					$student_added[] = $row;
			}
		}

		$lektor_entries = '';
		$student_entries = '';

		if (!empty($lektor_added))
			$lektor_entries .= $this->_addToList($lektor_added, 'hinzugefügt') . '<hr/>';

		if (!empty($lektor_changed))
			$lektor_entries .= $this->_addToList($lektor_changed, 'geändert') . '<hr/>';

		if (!empty($lektor_deleted))
			$lektor_entries .= $this->_addToList($lektor_deleted, 'gelöscht') . '<hr/>';

		if (!empty($student_added))
			$student_entries .= $this->_addToList($student_added, 'hinzugefügt') . '<hr/>';

		if (!empty($student_changed))
			$student_entries .= $this->_addToList($student_changed, 'geändert') . '<hr/>';

		if (!empty($student_deleted))
			$student_entries .= $this->_addToList($student_deleted, 'gelöscht') . '<hr/>';

		if (!empty($lektor_entries))
			$this->_ci->maillib->send('', 'ma0048@technikum-wien.at', 'Lektor Tempus Update', $lektor_entries);
		if (!empty($student_entries))
			$this->_ci->maillib->send('', 'ma0048@technikum-wien.at', 'Student Tempus Update', $student_entries);
	}

	private function _addToList($entries, $status)
	{
		return 'Folgende Einträge wurden <b>'. $status .'</b>: <ul>' . implode('', $entries) . '</ul>';

	}
	private function _buildMailNew($entry)
	{
		$von = date('d.m.Y H:i', strtotime($entry->von));
		$bis = date('H:i', strtotime($entry->bis));

		return '<li>
					<b>Kalender ID ' . ($entry->kalender_id ?? '-') . '</b>
					<ul>
						<li><b>Uhrzeit:</b> ' . $von . ' - ' . $bis . '</li>
						<li><b>Ort:</b> ' . ($entry->ort_kurzbz ?? '-') . '</li>
					</ul>
				</li>';
	}

	private function _buildMailChanged($old_entry, $new_entry)
	{
		$old_von = date('d.m.Y H:i', strtotime($old_entry->von));
		$old_bis = date('H:i', strtotime($old_entry->bis));
		$new_von = date('d.m.Y H:i', strtotime($new_entry->von));
		$new_bis = date('H:i', strtotime($new_entry->bis));

		$old_ort = $old_entry->ort_kurzbz ?? '-';
		$new_ort = $new_entry->ort_kurzbz ?? '-';

		$uhrzeit_changed = ($old_von . $old_bis) !== ($new_von . $new_bis);
		$ort_changed = $old_ort !== $new_ort;

		$changes = '';

		if ($uhrzeit_changed)
		{
			$changes .= '<li>
							<b>Uhrzeit:</b>
							<s style="color:red;">' . $old_von . ' - ' . $old_bis . '</s>
							<span style="color:green;">' . $new_von . ' - ' . $new_bis . '</span>
						</li>';
		}

		if ($ort_changed)
		{
			$changes .= '<li>
				<b>Ort:</b>
				<s style="color:red;">' . $old_ort . '</s>
				<span style="color:green;">' . $new_ort . '</span>
			</li>';
		}

		return '<li>
					<b>Kalender ID ' . ($new_entry->kalender_id ?? '-') . '</b>
					<ul>' . $changes . '</ul>
				</li>';
	}

	private function _buildMailDeleted($entry)
	{
		$von = date('d.m.Y H:i', strtotime($entry->von));
		$bis = date('H:i', strtotime($entry->bis));

		return '<li style="color:red;">
					<b><s>Kalender ID ' . ($entry->kalender_id ?? '-') . '</s></b>
					<ul>
						<li><s>' . $von . ' - ' . $bis . '</s></li>
						<li><s>' . ($entry->ort_kurzbz ?? '-') . '</s></li>
					</ul>
				</li>';
	}
}