<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class RoomRequest
{
    protected $_ci;

    public function __construct()
    {
        $this->_ci =& get_instance();
        $this->_ci->load->library('CustomFormValidationLib');
    }

     public function validate($method = 'create')
    {
       if ($method === 'create') {
			$this->_ci->customformvalidationlib->set_rules('ort_kurzbz', 'kurzbezeichnung', 'required|is_unique[tbl_ort.ort_kurzbz]|max_length[16]|regex_match[/^[a-zA-Z0-9_.]+$/]', [
				'required' => $this->_ci->p->t('ui', 'error_fieldRequired', ['field' =>  $this->_ci->p->t('gruppenmanagement', 'kurzbezeichnung')]),
				'is_unique' => $this->_ci->p->t('ui', 'error_fieldUnique', ['field' =>  $this->_ci->p->t('gruppenmanagement', 'kurzbezeichnung')]),
				'max_length' => $this->_ci->p->t('ui', 'error_fieldMaxLength', ['field' =>  $this->_ci->p->t('gruppenmanagement', 'kurzbezeichnung'), 'max' => 16]),
				'regex_match' => $this->_ci->p->t('ui', 'error_fieldInvalidFormat', ['field' =>  $this->_ci->p->t('gruppenmanagement', 'kurzbezeichnung')])
			]);
		}
		
		$this->_ci->customformvalidationlib->set_rules('parent_ort_kurzbz', 'parent_ort_kurzbz', 'does_exist[public.tbl_ort.ort_kurzbz]|max_length[16]', [
			'does_exist' => $this->_ci->p->t('ui', 'error_entryDoesExists', ['entry' =>  $this->_ci->p->t('ui', 'parentRoom')]),
			'max_length' => $this->_ci->p->t('ui', 'error_fieldMaxLength', ['field' =>  $this->_ci->p->t('ui', 'parentRoom'), 'max' => 16])
		]);
		$this->_ci->customformvalidationlib->set_rules('oe_kurzbz', 'oe_kurzbz', 'does_exist[public.tbl_organisationseinheit.oe_kurzbz]|max_length[32]', [
			'does_exist' => $this->_ci->p->t('ui', 'error_entryDoesExists', ['entry' =>  $this->_ci->p->t('lehre', 'organisationseinheit')]),
			'max_length' => $this->_ci->p->t('ui', 'error_fieldMaxLength', ['field' =>  $this->_ci->p->t('lehre', 'organisationseinheit'), 'max' => 32])
		]);
		$this->_ci->customformvalidationlib->set_rules('standort_id', 'standort_id', 'explicit_integer|does_exist[public.tbl_standort.standort_id]', [
			'does_exist' => $this->_ci->p->t('ui', 'error_entryDoesExists', ['entry' =>  $this->_ci->p->t('person', 'standort')]),
		]);
		$this->_ci->customformvalidationlib->set_rules('content_id', 'content_id', 'explicit_integer|does_exist[campus.tbl_content.content_id]', [
			'explicit_integer' => $this->_ci->p->t('ui', 'error_fieldInteger', ['field' =>  $this->_ci->p->t('ui', 'contentId')]),
			'does_exist' => $this->_ci->p->t('ui', 'error_entryDoesExists', ['entry' =>  $this->_ci->p->t('ui', 'contentId')])
		]);

		$this->_ci->customformvalidationlib->set_rules('lehre', 'lehre', 'explicit_boolean', [
			'explicit_boolean' => $this->_ci->p->t('ui', 'error_fieldBoolean', ['field' =>  $this->_ci->p->t('ui', 'lehre')])
		]);
		$this->_ci->customformvalidationlib->set_rules('reservieren', 'reservieren', 'explicit_boolean', [
			'explicit_boolean' => $this->_ci->p->t('ui', 'error_fieldBoolean', ['field' =>  $this->_ci->p->t('ui', 'reservieren')])
		]);
		$this->_ci->customformvalidationlib->set_rules('aktiv', 'aktiv', 'explicit_boolean', [
			'explicit_boolean' => $this->_ci->p->t('ui', 'error_fieldBoolean', ['field' =>  $this->_ci->p->t('person', 'aktiv')])
		]);
		
		$this->_ci->customformvalidationlib->set_rules('bezeichnung', 'bezeichnung', 'max_length[64]', [
			'max_length' => $this->_ci->p->t('ui', 'error_fieldMaxLength', ['field' =>  $this->_ci->p->t('ui', 'bezeichnung'), 'max' => 64])
		]);
		$this->_ci->customformvalidationlib->set_rules('planbezeichnung', 'planbezeichnung', 'max_length[8]', [
			'max_length' => $this->_ci->p->t('ui', 'error_fieldMaxLength', ['field' =>  $this->_ci->p->t('ui', 'planbezeichnung'), 'max' => 8])
		]);
		$this->_ci->customformvalidationlib->set_rules('max_person', 'maxPerson', 'explicit_integer', [
			'explicit_integer' => $this->_ci->p->t('ui', 'error_fieldInteger', ['field' =>  $this->_ci->p->t('ui', 'maxPersons')])
		]);
		$this->_ci->customformvalidationlib->set_rules('stockwerk', 'stockwerk', 'explicit_integer', [
			'explicit_integer' => $this->_ci->p->t('ui', 'error_fieldInteger', ['field' =>  $this->_ci->p->t('ui', 'stockwerk')])
		]);
		$this->_ci->customformvalidationlib->set_rules('m2', 'm2', 'explicit_numeric', [
			'explicit_numeric' => $this->_ci->p->t('ui', 'error_fieldNumeric', ['field' =>  $this->_ci->p->t('ui', 'quadratmeter')])
		]);
		$this->_ci->customformvalidationlib->set_rules('dislozierung', 'dislozierung', 'explicit_numeric', [
			'explicit_numeric' => $this->_ci->p->t('ui', 'error_fieldNumeric', ['field' =>  $this->_ci->p->t('ui', 'dislozierung')])
		]);
		$this->_ci->customformvalidationlib->set_rules('kosten', 'kosten', 'explicit_numeric', [
			'explicit_numeric' => $this->_ci->p->t('ui', 'error_fieldNumeric', ['field' =>  $this->_ci->p->t('ui', 'kosten')])
		]);
		$this->_ci->customformvalidationlib->set_rules('telefonklappe', 'telefonklappe', 'max_length[8]', [
			'max_length' => $this->_ci->p->t('ui', 'error_fieldMaxLength', ['field' =>  $this->_ci->p->t('person', 'telefonklappe'), 'max' => 8])
		]);
		$this->_ci->customformvalidationlib->set_rules('gebteil', 'gebteil', 'max_length[32]', [
			'max_length' => $this->_ci->p->t('ui', 'error_fieldMaxLength', ['field' =>  $this->_ci->p->t('ui', 'gebaudeteil'), 'max' => 32])
		]);
		$this->_ci->customformvalidationlib->set_rules('arbeitsplaetze', 'arbeitsplaetze', 'explicit_integer', [
			'explicit_integer' => $this->_ci->p->t('ui', 'error_fieldInteger', ['field' =>  $this->_ci->p->t('ui', 'arbeitsplaetze')])
		]);

        return $this->_ci->customformvalidationlib->run();
    }

    public function errors()
    {
        return $this->_ci->customformvalidationlib->error_array();
    }

     
}