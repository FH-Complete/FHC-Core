<?php
namespace vertragsbestandteil;

use vertragsbestandteil\Vertragsbestandteil;
use vertragsbestandteil\VertragsbestandteilFactory;

/**
 * Description of VertragsbestandteilFunktion
 *
 * @author bambi
 */
class VertragsbestandteilFunktion extends Vertragsbestandteil
{
	protected $benutzerfunktion_id;
	protected $benutzerfunktiondata;

	protected $CI;
	
	public function __construct()
	{
		parent::__construct();
		$this->benutzerfunktiondata = null;
		
		$this->setVertragsbestandteiltyp_kurzbz(
			VertragsbestandteilFactory::VERTRAGSBESTANDTEIL_FUNKTION);
		
		$this->CI = get_instance();
		$this->CI->load->model('person/Benutzerfunktion_model', 
			'BenutzerfunktionModel');
	}
	
	public function beforePersist()
	{
		if( isset($this->benutzerfunktion_id) && intval($this->benutzerfunktion_id) > 0 ) 
		{
			$this->beforePersitExisting();
		} 
		else 
		{
			$this->beforePersitNew();
		}
	}
	
	protected function beforePersitExisting() {
		$data = (object) array(
			'datum_bis' => $this->getBis(),
			'updateamum' => strftime('%Y-%m-%d %H:%M:%S'),
			'updatevon' => getAuthUID()
		);
		$ret = $this->CI->BenutzerfunktionModel->update($this->getBenutzerfunktion_id(), $data);
		
		if(isError($ret) )
		{
			throw new Exception('failed to update Benutzerfunktion');
		}
	}

	protected function beforePersitNew() {
		if( $this->benutzerfunktiondata === null) 
		{
			return;
		}
		
		$ret = $this->CI->BenutzerfunktionModel->insert($this->benutzerfunktiondata);
		
		if(isError($ret) )
		{
			throw new Exception('failed to create Benutzerfunktion');
		}
		
		$this->setBenutzerfunktion_id(getData($ret));		
	}

	public function toStdClass()
	{
		$tmp = array(
			'vertragsbestandteil_id' => $this->getVertragsbestandteil_id(),
			'benutzerfunktion_id' => $this->getBenutzerfunktion_id()
		);
		
		$tmp = array_filter($tmp, function($k) {
			return in_array($k, $this->modifiedcolumns);
		},  ARRAY_FILTER_USE_KEY);
		
		return (object) $tmp;
	}

	public function __toString()
	{
		$txt = <<<EOTXT
		benutzerfunktion_id: {$this->getBenutzerfunktion_id()}

EOTXT;
		return parent::__toString() . $txt;
	}

	public function hydrateByStdClass($data, $fromdb=false)
	{
		parent::hydrateByStdClass($data, $fromdb);
		$this->fromdb = $fromdb;
		isset($data->benutzerfunktionid) && $this->setBenutzerfunktion_id($data->benutzerfunktionid);
		isset($data->benutzerfunktion_id) && $this->setBenutzerfunktion_id($data->benutzerfunktion_id);
		isset($data->funktion) && isset($data->orget) 
			&& isset($data->mitarbeiter_uid) && $this->createBenutzerfunktionData($data);
		isset($data->funktion_bezeichnung) && isset($data->oe_bezeichnung) 
			&& $this->createBenutzerfunktionData4Display($data);
		$this->fromdb = false;
		
	}
	
	public function getBenutzerfunktion_id()
	{
		return $this->benutzerfunktion_id;
	}
	
	public function setBenutzerfunktion_id($benutzerfunktion_id)
	{
		$this->markDirty('benutzerfunktion_id', $this->benutzerfunktion_id, $benutzerfunktion_id);
		$this->benutzerfunktion_id = $benutzerfunktion_id;
		return $this;
	}
	
	protected function createBenutzerfunktionData($data)
	{
		if( empty($data->funktion) || empty($data->orget) ) 
		{
			return;
		}
		
		$this->benutzerfunktiondata = (object) array(
			'funktion_kurzbz' => $data->funktion,
			'oe_kurzbz' => $data->orget,
			'uid' => $data->mitarbeiter_uid, 
			'datum_von' => $this->getVon(), 
			'datum_bis' => $this->getBis(),
			'insertamum' => strftime('%Y-%m-%d %H:%M:%S'),
			'insertvon' => getAuthUID()
		);
	}

	protected function createBenutzerfunktionData4Display($data)
	{
		if( empty($data->funktion_bezeichnung) || empty($data->oe_bezeichnung) ) 
		{
			return;
		}
		
		$this->benutzerfunktiondata = (object) array(
			'funktion_kurzbz' => $data->funktion_kurzbz,
			'funktion_bezeichnung' => $data->funktion_bezeichnung,
			'oe_kurzbz' => $data->oe_kurzbz,
			'oe_bezeichnung' => $data->oe_bezeichnung,
			'oe_kurzbz_sap' => $data->oe_kurzbz_sap
		);
	}
	
	public function validate()
	{
		if( (intval($this->benutzerfunktion_id) < 1) 
			&& ($this->benutzerfunktiondata === NULL) ) {
			$this->validationerrors[] = 'Eine bestehende Funktion oder eine '
				. 'Funktion und eine Organisationseinheit müssen ausgewählt sein.';
		}
		
		return parent::validate();
	}
}
