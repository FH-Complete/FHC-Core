<?php
require_once __DIR__ . '/IValidation.php';
require_once __DIR__ . '/AbstractBestandteil.php';
require_once __DIR__ . '/Dienstverhaeltnis.php';
require_once __DIR__ . '/Vertragsbestandteil.php';
require_once __DIR__ . '/VertragsbestandteilStunden.php';
require_once __DIR__ . '/VertragsbestandteilFunktion.php';
require_once __DIR__ . '/VertragsbestandteilZeitaufzeichnung.php';
require_once __DIR__ . '/VertragsbestandteilKuendigungsfrist.php';
require_once __DIR__ . '/VertragsbestandteilUrlaubsanspruch.php';
require_once __DIR__ . '/VertragsbestandteilFreitext.php';
require_once __DIR__ . '/VertragsbestandteilKarenz.php';
require_once __DIR__ . '/VertragsbestandteilFactory.php';
require_once __DIR__ . '/OverlapChecker.php';

use vertragsbestandteil\Dienstverhaeltnis;
use vertragsbestandteil\Vertragsbestandteil;
use vertragsbestandteil\VertragsbestandteilFactory;

/**
 * Description of VertragsbestandteilLib
 *
 * @author bambi
 */
class VertragsbestandteilLib
{
	const INCLUDE_FUTURE = true;
	const DO_NOT_INCLUDE_FUTURE = false;

	protected $CI;
	/** @var Dienstverhaeltnis_model */
	protected $DienstverhaeltnisModel;
	/** @var Vertragsbestandteil_model */
	protected $VertragsbestandteilModel;
	/** @var Benutzer_model */
	protected $BenutzerModel;
	/**
	 * @var GehaltsbestandteilLib
	 */
	protected $GehaltsbestandteilLib;

	protected $loggedInUser;

	public function __construct()
	{
		$this->loggedInUser = getAuthUID();
		$this->CI = get_instance();
		$this->CI->load->model('vertragsbestandteil/Dienstverhaeltnis_model',
			'DienstverhaeltnisModel');
		$this->DienstverhaeltnisModel = $this->CI->DienstverhaeltnisModel;
		$this->CI->load->model('vertragsbestandteil/Vertragsbestandteil_model',
			'VertragsbestandteilModel');
		$this->VertragsbestandteilModel = $this->CI->VertragsbestandteilModel;
		$this->CI->load->model('person/benutzer_model',
			'BenutzerModel');
		$this->BenutzerModel = $this->CI->BenutzerModel;
		$this->CI->load->library('vertragsbestandteil/GehaltsbestandteilLib',
			null, 'GehaltsbestandteilLib');
		$this->GehaltsbestandteilLib = $this->CI->GehaltsbestandteilLib;
	}

	public function handleGUIData($guidata, $employeeUID, $userUID)
	{
		$guiHandler  = new GUIHandler($employeeUID, $userUID);
		$ret = false;
		try {
			$ret = $guiHandler->handle($guidata,  $employeeUID, $userUID);
		} catch (Exception $ex)
		{
			log_message('debug', "Error handling json data from GUI. " . $ex->getMessage());
		}

		return $ret;
	}

	public function fetchDienstverhaeltnisse($unternehmen, $stichtag=null, $mitarbeiteruid=null) {
		$dvs = $this->DienstverhaeltnisModel->fetchDienstverhaeltnisse($unternehmen, $stichtag, $mitarbeiteruid);
		return $dvs;
	}

	public function fetchDienstverhaeltnis($dienstverhaeltnis_id)
	{
		$result = $this->DienstverhaeltnisModel->load($dienstverhaeltnis_id);
		$dv = null;
		if(null !== ($row = getData($result)))
		{
			$dv = new Dienstverhaeltnis();
			$dv->hydrateByStdClass($row[0], true);
		}
		return $dv;
	}

	public function fetchVertragsbestandteile($dienstverhaeltnis_id, $stichtag=null, $includefuture=false)
	{
		$vbs = $this->VertragsbestandteilModel->getVertragsbestandteile($dienstverhaeltnis_id, $stichtag, $includefuture);
		$gbs = $this->GehaltsbestandteilLib->fetchGehaltsbestandteile($dienstverhaeltnis_id, $stichtag, $includefuture);

		$gbsByVBid = array();
		foreach( $gbs as $gb )
		{
			if( intval($gb->getVertragsbestandteil_id()) > 0 )
			{
				if( !isset($gbsByVBid[$gb->getVertragsbestandteil_id()])
					|| !is_array($gbsByVBid[$gb->getVertragsbestandteil_id()]) ) {
					$gbsByVBid[$gb->getVertragsbestandteil_id()] = array();
				}
				$gbsByVBid[$gb->getVertragsbestandteil_id()][] = $gb;
			}
		}

		foreach ($vbs as $vb)
		{
			if( isset($gbsByVBid[$vb->getVertragsbestandteil_id()]) )
			{
				$vb->setGehaltsbestandteile($gbsByVBid[$vb->getVertragsbestandteil_id()]);
			}
		}

		return $vbs;
	}

	public function fetchVertragsbestandteil($vertragsbestandteil_id)
	{
		return $this->VertragsbestandteilModel->getVertragsbestandteil($vertragsbestandteil_id);
	}

	public function storeDienstverhaeltnis(Dienstverhaeltnis $dv)
	{
		if( intval($dv->getDienstverhaeltnis_id()) > 0 )
		{
			$this->updateDienstverhaeltnis($dv);
		}
		else
		{
			$this->insertDienstverhaeltnis($dv);
		}
	}

	public function storeVertragsbestandteil(Vertragsbestandteil $vertragsbestandteil)
	{
		$this->CI->db->trans_begin();
		try
		{
			$this->setUIDtoPGSQL();
			if( intval($vertragsbestandteil->getVertragsbestandteil_id()) > 0 )
			{
				$this->updateVertragsbestandteil($vertragsbestandteil);
			}
			else
			{
				$this->insertVertragsbestandteil($vertragsbestandteil);
			}
			if( $this->CI->db->trans_status() === false )
			{
				log_message('debug', "Transaction failed");
				throw new Exception("Transaction failed");
			}
			$this->CI->db->trans_commit();
		}
		catch (Exception $ex)
		{
			log_message('debug', "Transaction rolled back. " . $ex->getMessage());
			$this->CI->db->trans_rollback();
			throw new Exception('Storing Vertragsbestandteil failed.');
		}
	}

	public function deleteDienstverhaeltnis(Dienstverhaeltnis $dv)
	{
		$this->CI->db->trans_begin();
		try
		{
			$this->setUIDtoPGSQL();
			if( intval($dv->getDienstverhaeltnis_id()) > 0 )
			{
				$vbs = $this->fetchVertragsbestandteile($dv->getDienstverhaeltnis_id());
				foreach ($vbs as $vb)
				{
					$this->deleteVertragsbestandteil($vb);
				}

				$ret = $this->DienstverhaeltnisModel->delete($dv->getDienstverhaeltnis_id());
				if(isError($ret) )
				{
					log_message('debug', "Delete DV failed");
					throw new Exception('error deleting dienstverhaeltnis '
						. $dv->getDienstverhaeltnis_id());
				}

				if( $this->CI->db->trans_status() === false )
				{
					log_message('debug', "Transaction failed");
					throw new Exception("Transaction failed");
				}
				$this->CI->db->trans_commit();
			}
		}
		catch (Exception $ex)
		{
			log_message('debug', "Transaction rolled back. " . $ex->getMessage());
			$this->CI->db->trans_rollback();
			return $ex->getMessage();
		}

		return true;

	}

	public function deleteVertragsbestandteil(Vertragsbestandteil $vertragsbestandteil)
	{
		$this->CI->db->trans_begin();
		try
		{
			$this->setUIDtoPGSQL();
			if( intval($vertragsbestandteil->getVertragsbestandteil_id()) > 0 )
			{
				$this->deleteVertragsbestandteilHelper($vertragsbestandteil);
			}
			if( $this->CI->db->trans_status() === false )
			{
				log_message('debug', "Transaction failed");
				throw new Exception("Transaction failed");
			}
			$this->CI->db->trans_commit();
		}
		catch (Exception $ex)
		{
			log_message('debug', "Transaction rolled back. " . $ex->getMessage());
			$this->CI->db->trans_rollback();
			throw new Exception('Delete Vertragsbestandteil failed.');
		}
	}

	protected function insertDienstverhaeltnis(Dienstverhaeltnis $dv)
	{
		$dv->setInsertvon($this->loggedInUser)
			->setInsertamum(strftime('%Y-%m-%d %H:%M:%S'));
		$ret = $this->DienstverhaeltnisModel->insert($dv->toStdClass());
		if( hasData($ret) )
		{
			$dv->setDienstverhaeltnis_id(getData($ret));
		}
		else
		{
			throw new Exception('error inserting dienstverhaeltnis');
		}
	}

	protected function insertVertragsbestandteil(Vertragsbestandteil $vertragsbestandteil)
	{
		$vertragsbestandteil->setInsertvon($this->loggedInUser)
			->setInsertamum(strftime('%Y-%m-%d %H:%M:%S'));
		$vertragsbestandteil->beforePersist();
		$ret = $this->VertragsbestandteilModel->insert($vertragsbestandteil->baseToStdClass());
		if( hasData($ret) )
		{
			$vertragsbestandteil->setVertragsbestandteil_id(getData($ret));
		}
		else
		{
			throw new Exception('error inserting vertragsbestandteil');
		}

		$specialisedModel = VertragsbestandteilFactory::getVertragsbestandteilDBModel(
			$vertragsbestandteil->getVertragsbestandteiltyp_kurzbz());
		$retspecial = $specialisedModel->insert($vertragsbestandteil->toStdClass());

		if(isError($retspecial) )
		{
			throw new Exception('error updating vertragsbestandteil '
				. $vertragsbestandteil->getVertragsbestandteiltyp_kurzbz());
		}

		try
		{
			$gehaltsbestandteile = $vertragsbestandteil->getGehaltsbestandteile();
			$this->GehaltsbestandteilLib->storeGehaltsbestandteile($gehaltsbestandteile);
		}
		catch(Exception $ex)
		{
			throw new Exception('VertragsbestandteilLib insertVertragsbestandteil '
				. 'failed to store Gehaltsbestandteile. ' . $ex->getMessage());
		}
	}

	protected function updateDienstverhaeltnis(Dienstverhaeltnis $dv)
	{
		if(!$dv->isDirty()) {
			return;
		}

		$dv->setUpdatevon($this->loggedInUser)
			->setUpdateamum(strftime('%Y-%m-%d %H:%M:%S'));
		$ret = $this->DienstverhaeltnisModel->update($dv->getDienstverhaeltnis_id(),
			$dv->toStdClass());
		if(isError($ret) )
		{
			throw new Exception('error updating dienstverhaeltnis');
		}
	}

	private function deleteVertragsbestandteilHelper(Vertragsbestandteil $vertragsbestandteil)
	{

		$specialisedModel = VertragsbestandteilFactory::getVertragsbestandteilDBModel(
			$vertragsbestandteil->getVertragsbestandteiltyp_kurzbz());
		$retspecial = $specialisedModel->delete($vertragsbestandteil->getVertragsbestandteil_id());

		if(isError($retspecial) )
		{
			throw new Exception('error deleting vertragsbestandteil '
				. $vertragsbestandteil->getVertragsbestandteiltyp_kurzbz());
		}

		try
		{
			$gehaltsbestandteile = $vertragsbestandteil->getGehaltsbestandteile();
			$this->GehaltsbestandteilLib->deleteGehaltsbestandteile($gehaltsbestandteile);
		}
		catch(Exception $ex)
		{
			throw new Exception('VertragsbestandteilLib deleteVertragsbestandteil '
				. 'failed to delete Gehaltsbestandteile. ' . $ex->getMessage());
		}


		$ret = $this->VertragsbestandteilModel->delete($vertragsbestandteil->getVertragsbestandteil_id());

		if(isError($ret) )
		{
			throw new Exception('error deleting vertragsbestandteil');
		}

		$vertragsbestandteil->afterDelete();
	}

	protected function updateVertragsbestandteil(Vertragsbestandteil $vertragsbestandteil)
	{
		if($vertragsbestandteil->isDirty()) {
			$vertragsbestandteil->setUpdatevon($this->loggedInUser)
				->setUpdateamum(strftime('%Y-%m-%d %H:%M:%S'));
			$vertragsbestandteil->beforePersist();
			$basedata = $vertragsbestandteil->baseToStdClass();
			if( count((array) $basedata) > 0 )
			{
				$ret = $this->VertragsbestandteilModel->update(
					$vertragsbestandteil->getVertragsbestandteil_id(),
					$basedata);

				if(isError($ret) )
				{
					throw new Exception('error updating vertragsbestandteil');
				}
			}

			$specialisedData = $vertragsbestandteil->toStdClass();
			if( count((array) $specialisedData) > 0 )
			{
				$specialisedModel = VertragsbestandteilFactory::getVertragsbestandteilDBModel(
					$vertragsbestandteil->getVertragsbestandteiltyp_kurzbz());
				$retspecial = $specialisedModel->update(
					$vertragsbestandteil->getVertragsbestandteil_id(),
					$specialisedData);

				if(isError($retspecial) )
				{
					throw new Exception('error updating vertragsbestandteil '
						. $vertragsbestandteil->getVertragsbestandteiltyp_kurzbz());
				}
			}
		}

		try
		{
			$gehaltsbestandteile = $vertragsbestandteil->getGehaltsbestandteile();
			$this->GehaltsbestandteilLib->storeGehaltsbestandteile($gehaltsbestandteile);
		}
		catch(Exception $ex)
		{
			throw new Exception('VertragsbestandteilLib updateVertragsbestandteil '
				. 'failed to store Gehaltsbestandteile. ' . $ex->getMessage());
		}
	}

	public function isOverlappingExistingDV(Dienstverhaeltnis $dv)
	{
		return $this->DienstverhaeltnisModel->isOverlappingExistingDV(
			$dv->getMitarbeiter_uid(),
			$dv->getOe_kurzbz(),
			$dv->getVon(),
			$dv->getBis(),
			$dv->getDienstverhaeltnis_id()
		);
	}

	protected function hasOtherActiveDV(Dienstverhaeltnis $dv, $duedate)
	{
	    $hasotheractivedv = false;
	    $result = $this->DienstverhaeltnisModel->getDVByPersonUID($dv->getMitarbeiter_uid(), null, $duedate);
	    $dvs = getData($result);
	    foreach ($dvs as $tmpdv)
	    {
		if(intval($tmpdv->dienstverhaeltnis_id) !== intval($dv->getDienstverhaeltnis_id()))
		{
		    $hasotheractivedv = true;
		    break;
		}
	    }
	    return $hasotheractivedv;
	}

	/**
	 * like endDienstverhaeltnis, but also sets aktiv flag to false
	 */
	public function deactivateDienstverhaeltnis(Dienstverhaeltnis $dv, $enddate, $deactivate)
	{
	    $result = $this->endDienstverhaeltnis($dv, $enddate);
	    if ( $result === true)
	    {
		if (!$deactivate) return $result;

		if(!$this->hasOtherActiveDV($dv, $enddate))
		{
		    $result = $this->BenutzerModel->update(
			array('uid' => $dv->getMitarbeiter_uid()),
			array(
			    'aktiv' => false,
			    'updateaktivam' => date('Y-m-d'),
			    'updateaktivvon' => $this->loggedInUser
			)
		    );
		}
	    }

	    return $result;
	}

	public function endDienstverhaeltnis(Dienstverhaeltnis $dv, $enddate, $dvendegrund_kurzbz=null, $dvendegrund_anmerkung=null)
	{
		if( $dv->getBis() !== null && $dv->getBis() < $enddate )
		{
			return 'Dienstverhältnis ist bereits beendet.';
		}

		$this->CI->db->trans_begin();
		try
		{
			$this->setUIDtoPGSQL();
			if( intval($dv->getDienstverhaeltnis_id()) > 0 )
			{
				$gbs = $this->GehaltsbestandteilLib->fetchGehaltsbestandteile($dv->getDienstverhaeltnis_id());
				foreach ($gbs as $gb)
				{
					$this->GehaltsbestandteilLib->endGehaltsbestandteil($gb, $enddate);
				}

				$vbs = $this->fetchVertragsbestandteile($dv->getDienstverhaeltnis_id());
				foreach ($vbs as $vb)
				{
					$this->endVertragsbestandteil($vb, $enddate);
				}

				if( $dvendegrund_kurzbz !== null )
				{
				    $dv->setDvendegrund_kurzbz($dvendegrund_kurzbz);
				}
				if( $dvendegrund_anmerkung !== null )
				{
				    $dv->setDvendegrund_anmerkung($dvendegrund_anmerkung);
				}
				$dv->setBis($enddate);
				$this->updateDienstverhaeltnis($dv);

				if( $this->CI->db->trans_status() === false )
				{
					log_message('debug', "Transaction failed");
					throw new Exception("Transaction failed");
				}
				$this->CI->db->trans_commit();
			}
		}
		catch (Exception $ex)
		{
			log_message('debug', "end DV failed " . $dv->getDienstverhaeltnis_id());
			log_message('debug', "Transaction rolled back. " . $ex->getMessage());
			$this->CI->db->trans_rollback();
			return $ex->getMessage();
		}
		return true;
	}

	public function endVertragsbestandteil(Vertragsbestandteil $vertragsbestandteil, $enddate)
	{
		if( $vertragsbestandteil->getBis() !== null && $vertragsbestandteil->getBis() < $enddate )
		{
			return;
		}

		$vertragsbestandteil->setBis($enddate);
		$this->updateVertragsbestandteil($vertragsbestandteil);
	}

	protected function setUIDtoPGSQL() {
		$ret = $this->VertragsbestandteilModel
			->execReadOnlyQuery('SET LOCAL pv21.uid TO \''
				. $this->loggedInUser . '\'');
		if(isError($ret))
		{
			throw new Exception('error setting uid to pgsql');
		}
	}
}
