<?php
namespace vertragsbestandteil;

use Exception;
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
		$this->CI->load->model('vertragsbestandteil/VertragsbestandteilFunktion_model', 
			'VertragsbestandteilFunktionModel');
		$this->CI->load->library('vertragsbestandteil/VertragsbestandteilLib', 
			null, 'VertragsbestandteilLib');
	}
	
	public function isDirty()
	{
		$isdirty = parent::isDirty();
		if( !$isdirty ) {
			$bf = $this->loadBenutzerfunktion($this->getBenutzerfunktion_id());
			if( !$this->areVbAndBfInSync($bf) )
			{
				$isdirty = true;
			}
		}
		return $isdirty;
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
	
	protected function loadBenutzerfunktion($bfid)
	{
		$bfres = $this->CI->BenutzerfunktionModel->load($bfid);
		if(!hasData($bfres))
		{
			throw new Exception('failed to load existing Benutzerfunktion');
		}
		return (getData($bfres))[0];
	}
	
	protected function loadPersitedVB($vbid)
	{
		$vb = $this->CI->VertragsbestandteilLib->fetchVertragsbestandteil($vbid);
		if( $vb === null )
		{
			throw new Exception('failed to load persited Vertragsbestandteil');
		}
		return $vb;
	}
	
	protected function areVbAndBfInSync($bf)
	{		
		$vbvon = $this->getVon();
		$vbbis = $this->getBis();
		if( intval($this->getVertragsbestandteil_id()) > 0 )
		{
			$vb = $this->loadPersitedVB($this->getVertragsbestandteil_id());
			$vbvon = $vb->getVon();
			$vbbis = $vb->getBis();
		}
		
		if( ($bf->datum_von === $vbvon) && ($bf->datum_bis === $vbbis) )
		{
			return true;
		}
		return false;
	}
	
	protected function isBefore($a, $b)
	{
		if($a === null) {
			return false;
		}
		elseif($b === null) {
			return true;
		}
		else {
			return $a < $b;
		}
	}

	protected function isAfter($a, $b)
	{
		if($b === null) {
			return false;
		}
		elseif($a === null) {
			return true;
		}
		else {
			return $a > $b;
		}
	}
	
	protected function beforePersitExisting() 
	{
		$bf = $this->loadBenutzerfunktion($this->getBenutzerfunktion_id());
		if( $this->areVbAndBfInSync($bf) )
		{
			// vb or stored vb von bis is in sync so update benutzerfunktion
			$this->updateBenutzerfunktion($bf, $this->getVon(), $this->getBis());
		}
		else
		{
			$daybeforevon = \DateTime::createFromFormat('Y-m-d', $this->getVon(), 
				new \DateTimeZone('Europe/Vienna'));
			$daybeforevon->sub(new \DateInterval('P1D'));
			
			if( $this->isBefore($bf->datum_von, $this->getVon()) && 
				$this->isBefore($bf->datum_von, $this->getBis()) )
			{
				$data = (object) array(
					'mitarbeiter_uid' => $bf->uid,
					'funktion' => $bf->funktion_kurzbz,
					'orget' => $bf->oe_kurzbz
				);
				$this->createBenutzerfunktionData($data);
				$bfid = $this->insertBenutzerfunktion($this->getBenutzerfunktionData4Insert());
				$this->setBenutzerfunktion_id($bfid);
			}
			elseif( $this->isBefore($bf->datum_von, $this->getVon()) && 
					$this->isAfter($this->getBis(), $bf->datum_von) )
			{
				$this->updateBenutzerfunktion($bf, $bf->datum_von, $daybeforevon->format('Y-m-d'));
				$data = (object) array(
					'mitarbeiter_uid' => $bf->uid,
					'funktion' => $bf->funktion_kurzbz,
					'orget' => $bf->oe_kurzbz
				);
				$this->createBenutzerfunktionData($data);
				$bfid = $this->insertBenutzerfunktion($this->getBenutzerfunktionData4Insert());
				$this->setBenutzerfunktion_id($bfid);
			}
			else
			{
				$this->updateBenutzerfunktion($bf, $this->getVon(), $this->getBis());
			}
		}
	}
	
	protected function updateBenutzerfunktion($bf, $von, $bis)
	{
		$data = array();
		
		if($von !== $bf->datum_von) 
		{
			$data['datum_von'] = $von;
		}
		if($bis !== $bf->datum_bis) 
		{
			$data['datum_bis'] = $bis;
		}			

		if( count($data) === 0 ) 
		{
			return;
		}
		
		$data['updateamum'] = strftime('%Y-%m-%d %H:%M:%S');
		$data['updatevon'] = getAuthUID();		
		
		$ret = $this->CI->BenutzerfunktionModel->update($bf->benutzerfunktion_id, $data);
		
		if(isError($ret) )
		{
			throw new Exception('failed to update Benutzerfunktion');
		}
	}

	protected function insertBenutzerfunktion($benutzerfunktiondata)
	{
		$ret = $this->CI->BenutzerfunktionModel->insert($benutzerfunktiondata);
		
		if(isError($ret) )
		{
			throw new Exception('failed to create Benutzerfunktion');
		}
		
		return getData($ret);
	}

	protected function deleteBenutzerfunktion($benutzerfunktion_id)
	{
		$ret = $this->CI->BenutzerfunktionModel->delete($benutzerfunktion_id);
		
		if(isError($ret) )
		{
			throw new Exception('failed to delete Benutzerfunktion');
		}
	}
	
	protected function beforePersitNew() {
		if( $this->benutzerfunktiondata === null) 
		{
			return;
		}
		
		$bfid = $this->insertBenutzerfunktion($this->getBenutzerfunktionData4Insert());
		
		$this->setBenutzerfunktion_id($bfid);
	}
	
	public function afterDelete()
	{
		if( !(intval($this->getBenutzerfunktion_id()) > 0) )
		{
			return;
		}
		
		$this->deleteBenutzerfunktion($this->getBenutzerfunktion_id());
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
	
	protected function getBenutzerfunktionData4Insert()
	{
		if( null === $this->benutzerfunktiondata ) {
			return null;
		}
		
		$benutzerfunktiondata = (object) array(
			'funktion_kurzbz' => $this->benutzerfunktiondata->funktion_kurzbz,
			'oe_kurzbz' => $this->benutzerfunktiondata->oe_kurzbz,
			'uid' => $this->benutzerfunktiondata->uid,			
			'datum_von' => $this->getVon(), 
			'datum_bis' => $this->getBis(),
			'insertamum' => strftime('%Y-%m-%d %H:%M:%S'),
			'insertvon' => getAuthUID()
		);
		
		return $benutzerfunktiondata;
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
			'uid' => $data->mitarbeiter_uid
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
			'oe_kurzbz_sap' => $data->oe_kurzbz_sap,
			'oe_typ_kurzbz' => $data->oe_typ_kurzbz,
			'oe_typ_bezeichnung' => $data->oe_typ_bezeichnung, 
			'uid' => $data->mitarbeiter_uid
		);
	}
	
	public function validate()
	{
		if( (intval($this->benutzerfunktion_id) < 1) 
			&& ($this->benutzerfunktiondata === NULL) ) {
			$this->validationerrors[] = 'Eine bestehende Funktion oder eine '
				. 'Funktion und eine Organisationseinheit müssen ausgewählt sein.';
		}
		
		// TODO check if Benutzerfunktion is assigned to another vb
		if( intval($this->benutzerfunktion_id) > 0 ) 
		{
			if ( $this->CI->VertragsbestandteilFunktionModel
					->isBenutzerfunktionAlreadyAttachedToAnotherVB(
						$this->benutzerfunktion_id, 
						$this->getVertragsbestandteil_id()) )
			{
				$this->validationerrors[] = 'Die Benutzerfunktion ist bereits '
					. 'mit einem anderen Vertragsbestandteil verknüpft und kann '
					. 'nicht mehrfach verknüft werden.';
			}
		}
		
		return parent::validate();
	}
}
