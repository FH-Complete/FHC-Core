<?php
use CI3_Events as Events;
/**
 * FH-Complete
 *
 * @package		FHC-Helper
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016 fhcomplete.org
 * @license		GPLv3
 * @since		Version 1.0.0
 */

/**
 * Form validation Helper
 *
 * @subpackage	Helpers
 * @category	Helpers
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

// -------------------------------------------------------------------------------------------------------
// Helper for triggering events for form validation (events for checking input data)
// -------------------------------------------------------------------------------------------------------

/**
 * Trigger event for form validation.
 */
function triggerValidationEvent($eventName, $validationObj, $form_validation)
{
	// trigger event
	Events::trigger($eventName, $validationObj->params, $validationObj);

	// add validation rules to validation object
	if (isset($validationObj->validations))
	{
		foreach ($validationObj->validations as $validation)
		{
			if (!isset($validation['field'])) continue;
			if (!isset($validation['result']) || !$validation['result'])
			{
				$error_message = isset($validation['error_message']) ? $validation['error_message'] : 'Unknown error';
				$form_validation->set_message('callable', $error_message);
				$form_validation->set_rules(
					$validation['field'],
					$validation['fieldDisplay'] ?? $validation['field'],
					array(
						array(
								'callable',
								function()
								{
									return false;
								}
						)
					)
				);
				return;
			}
		}
	}
}
