<?php
require_once __DIR__ . '/IValidation.php';
require_once __DIR__ . '/AbstractBestandteil.php';
require_once __DIR__ . '/Gehaltsbestandteil.php';

use vertragsbestandteil\Gehaltsbestandteil;

/**
 * Description of GehaltsbestandteilLib
 *
 * @author bambi
 */
class GehaltsbestandteilLib
{		
	protected $CI;
	/** @var Gehaltsbestandteil_model */
	protected $GehaltsbestandteilModel;

	protected $loggedInUser;
	
	public function __construct()
	{
		$this->loggedInUser = getAuthUID();
		$this->CI = get_instance();
		$this->CI->load->model('vertragsbestandteil/Gehaltsbestandteil_model', 
			'GehaltsbestandteilModel');
		$this->CI->load->library('extensions/FHC-Core-Personalverwaltung/abrechnung/GehaltsLib');
		$this->GehaltsbestandteilModel = $this->CI->GehaltsbestandteilModel;
	}

	public function fetchGehaltsbestandteile($dienstverhaeltnis_id, $stichtag=null, $includefuture=false)
	{
		return $this->GehaltsbestandteilModel->getGehaltsbestandteile($dienstverhaeltnis_id, $stichtag, $includefuture);
	}

	public function fetchGehaltsbestandteil($gehaltsbestandteil_id)
	{
		return $this->GehaltsbestandteilModel->getGehaltsbestandteil($gehaltsbestandteil_id);
	}
	
	public function storeGehaltsbestandteile($gehaltsbestandteile) 
	{
		foreach( $gehaltsbestandteile as $gehaltsbestandteil ) 
		{
			$this->storeGehaltsbestandteil($gehaltsbestandteil);
		}
	}
	
	public function storeGehaltsbestandteil(Gehaltsbestandteil $gehaltsbestandteil) 
	{
		try
		{
			$this->setUIDtoPGSQL();
			if( intval($gehaltsbestandteil->getGehaltsbestandteil_id()) > 0 )
			{
				$this->updateGehaltsbestandteil($gehaltsbestandteil);
			}
			else
			{
				$this->insertGehaltsbestandteil($gehaltsbestandteil);
			}
		}
		catch (Exception $ex)
		{
			log_message('debug', "Storing Gehaltsbestandteil failed. " . $ex->getMessage());
			throw new Exception('Storing Gehaltsbestandteil failed.');
		}	
	}
	
	protected function insertGehaltsbestandteil(Gehaltsbestandteil $gehaltsbestandteil)
	{
		$gehaltsbestandteil->setInsertvon($this->loggedInUser)
			->setInsertamum(strftime('%Y-%m-%d %H:%M:%S'));
		$ret = $this->GehaltsbestandteilModel->insert($gehaltsbestandteil->toStdClass(),
			$this->GehaltsbestandteilModel->getEncryptedColumns());
		if( hasData($ret) ) 
		{
			$gehaltsbestandteil->setGehaltsbestandteil_id(getData($ret));
		}
		else
		{
			throw new Exception('error inserting gehaltsbestandteil');
		}		
	}
	
	protected function updateGehaltsbestandteil(Gehaltsbestandteil $gehaltsbestandteil)
	{
		if(!$gehaltsbestandteil->isDirty()) {
			return;
		}
		
		$gehaltsbestandteil->setUpdatevon($this->loggedInUser)
			->setUpdateamum(strftime('%Y-%m-%d %H:%M:%S'));
		$ret = $this->GehaltsbestandteilModel->update($gehaltsbestandteil->getGehaltsbestandteil_id(), 
			$gehaltsbestandteil->toStdClass(),
			$this->GehaltsbestandteilModel->getEncryptedColumns());
		
		if(isError($ret) )
		{
			throw new Exception('error updating gehaltsbestandteil');
		}
	}

	public function deleteGehaltsbestandteile($gehaltsbestandteile)
	{
		foreach( $gehaltsbestandteile as $gehaltsbestandteil )
		{
			$this->deleteGehaltsbestandteil($gehaltsbestandteil);
		}
	}

	public function deleteGehaltsbestandteil(Gehaltsbestandteil $gehaltsbestandteil)
	{
		$this->setUIDtoPGSQL();

		// delete Gehaltsabrechnung
		$ret = $this->CI->gehaltslib->deleteAbrechnung($gehaltsbestandteil);

		//
		$ret = $this->GehaltsbestandteilModel->delete($gehaltsbestandteil->getGehaltsbestandteil_id());
		
		if (isError($ret))
		{
			throw new Exception('error deleting gehaltsbestandteil');
		}
	}
	
	public function endGehaltsbestandteil(Gehaltsbestandteil $gehaltsbestandteil, $enddate)
	{
		$this->setUIDtoPGSQL();
		if( $gehaltsbestandteil->getBis() !== null && $gehaltsbestandteil->getBis() < $enddate ) 
		{
			return;
		}
		
		$gehaltsbestandteil->setBis($enddate);
		$this->updateGehaltsbestandteil($gehaltsbestandteil);
	}
		
	protected function setUIDtoPGSQL() {
		$ret = $this->GehaltsbestandteilModel
			->execReadOnlyQuery('SET LOCAL pv21.uid TO \'' 
				. $this->loggedInUser . '\'');
		if(isError($ret)) 
		{
			throw new Exception('error setting uid to pgsql');
		}
	}
}
