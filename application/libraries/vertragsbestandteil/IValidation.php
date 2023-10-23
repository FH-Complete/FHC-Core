<?php
namespace vertragsbestandteil;

/**
 * Description of IValidation
 *
 * @author bambi
 */
interface IValidation
{
	public function isValid();

	public function getValidationErrors();
	
	public function validate();
	
	public function addValidationError($errormsg);
}
