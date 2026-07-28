<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Provides the authenticated user's LVPlan events as JSON.
 */
class LvPlanCalendarEvents extends FHCAPI_Controller
{
	/**
	 * Object initialization.
	 */
	public function __construct()
	{
		parent::__construct([
			'getCalendarData' => self::PERM_LOGGED
		]);
	}

	/**
	 * Returns the authenticated user's mapped calendar events.
	 */
	public function getCalendarData()
	{
		$this->output->set_header('Cache-Control: no-store');

		if ($this->input->method() !== 'get')
		{
			$this->output->set_header('Allow: GET');
			$this->terminateWithError(
				'Method not allowed',
				self::ERROR_TYPE_GENERAL,
				REST_Controller::HTTP_METHOD_NOT_ALLOWED
			);
		}

		$startDate = $this->input->get('start_date', true);
		$endDate = $this->input->get('end_date', true);
		$validationErrors = array();

		if (!is_null($startDate) && !$this->isValidDate($startDate))
			$validationErrors['start_date'] = 'The start_date field must use the YYYY-MM-DD format.';

		if (!is_null($endDate) && !$this->isValidDate($endDate))
			$validationErrors['end_date'] = 'The end_date field must use the YYYY-MM-DD format.';

		if (!empty($validationErrors))
			$this->terminateWithValidationErrors($validationErrors);

		$today = new DateTimeImmutable('today', new DateTimeZone('Europe/Vienna'));

		if (is_null($startDate))
			$startDate = $today->modify('-14 days')->format('Y-m-d');

		if (is_null($endDate))
			$endDate = $today->modify('+6 months')->format('Y-m-d');

		if ($startDate > $endDate)
		{
			$this->terminateWithValidationErrors(array(
				'end_date' => 'The end_date field must be on or after start_date.'
			));
		}

		$userUID = getAuthUID();
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
		$this->load->library('KalenderLib', ['uid' => $userUID]);

		$isUserEmployeeResult = $this->MitarbeiterModel->isMitarbeiter($userUID);
		$isUserEmployee = $this->getDataOrTerminateWithError($isUserEmployeeResult);

		if ($isUserEmployee)
		{
			$events = $this->kalenderlib->getPlanForLecturerByLecturer(
				$startDate,
				$endDate,
				$userUID
			);
		}
		else
		{
			$events = $this->kalenderlib->getPlanForStudentByStudent(
				$startDate,
				$endDate,
				$userUID
			);
		}

		if (!is_array($events))
			$events = array();

		$this->terminateWithSuccess($events);
	}

	/**
	 * Checks whether a date uses the exact YYYY-MM-DD format.
	 *
	 * @param mixed $date
	 * @return bool
	 */
	private function isValidDate($date)
	{
		if (!is_string($date) || $date === '')
			return false;

		$parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
		$errors = DateTimeImmutable::getLastErrors();

		return $parsedDate !== false
			&& ($errors === false
				|| ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
			&& $parsedDate->format('Y-m-d') === $date;
	}
}
