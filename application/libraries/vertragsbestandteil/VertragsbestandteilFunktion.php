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
		
		$tmp = array_filter($tmp, function($v) {
			return !is_null($v);
		});
		
		return (object) $tmp;
	}

	public function __toString()
	{
		$txt = <<<EOTXT
		benutzerfunktion_id: {$this->getBenutzerfunktion_id()}

EOTXT;
		return parent::__toString() . $txt;
	}

	public function hydrateByStdClass($data)
	{
		parent::hydrateByStdClass($data);
		isset($data->benutzerfunktionid) && $this->setBenutzerfunktion_id($data->benutzerfunktionid);
		isset($data->funktion) && isset($data->orget) 
			&& isset($data->mitarbeiter_uid) && $this->createBenutzerfunktionData($data);
		
	}
	
	public function getBenutzerfunktion_id()
	{
		return $this->benutzerfunktion_id;
	}
	
	public function setBenutzerfunktion_id($benutzerfunktion_id)
	{
		$this->benutzerfunktion_id = $benutzerfunktion_id;
		return $this;
	}
	
	protected function createBenutzerfunktionData($data)
	{
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
