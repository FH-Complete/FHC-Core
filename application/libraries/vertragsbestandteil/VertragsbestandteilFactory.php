<?php
namespace vertragsbestandteil;

use Exception;
use vertragsbestandteil\VertragsbestandteilStunden;

/**
 * Description of VertragsbestandteilFactory
 *
 * @author bambi
 */
class VertragsbestandteilFactory
{
	const VERTRAGSBESTANDTEIL_STUNDEN	= 'stunden';
	const VERTRAGSBESTANDTEIL_FUNKTION	= 'funktion';
	const VERTRAGSBESTANDTEIL_GEHALT	= 'gehalt';
	const VERTRAGSBESTANDTEIL_FREITEXT = 'freitext';
	const VERTRAGSBESTANDTEIL_KARENZ = 'karenz';
	const VERTRAGSBESTANDTEIL_BEFRISTUNG = 'befristung';
	const VERTRAGSBESTANDTEIL_KUENDIGUNGSFRIST = 'kuendigungsfrist';
	const VERTRAGSBESTANDTEIL_KV = 'kv';
	const VERTRAGSBESTANDTEIL_URLAUBSANSPRUCH = 'urlaubsanspruch';
	const VERTRAGSBESTANDTEIL_ZEITAUFZEICHNUNG = 'zeitaufzeichnung';
	const VERTRAGSBESTANDTEIL_LEHRE = 'lehre';
	
	public static function getVertragsbestandteil($data, $fromdb=false)
	{
		$vertragsbestandteiltyp_kurzbz = isset($data->vertragsbestandteiltyp_kurzbz)
			? $data->vertragsbestandteiltyp_kurzbz : false;
		if( false === $vertragsbestandteiltyp_kurzbz )
		{
			throw new Exception('Missing Parameter vertragsbestandteiltyp_kurzbz');
		}
		
		$vertragsbestandteil = null;
		switch ($vertragsbestandteiltyp_kurzbz)
		{
			case self::VERTRAGSBESTANDTEIL_FREITEXT:
				$vertragsbestandteil = new VertragsbestandteilFreitext();
				$vertragsbestandteil->hydrateByStdClass($data, $fromdb);
				break;
			
			case self::VERTRAGSBESTANDTEIL_FUNKTION:
				$vertragsbestandteil = new VertragsbestandteilFunktion();
				$vertragsbestandteil->hydrateByStdClass($data, $fromdb);
				break;

			case self::VERTRAGSBESTANDTEIL_KARENZ:
				$vertragsbestandteil = new VertragsbestandteilKarenz();
				$vertragsbestandteil->hydrateByStdClass($data, $fromdb);
				break;

			case self::VERTRAGSBESTANDTEIL_KUENDIGUNGSFRIST:
				$vertragsbestandteil = new VertragsbestandteilKuendigungsfrist();
				$vertragsbestandteil->hydrateByStdClass($data, $fromdb);
				break;
			
			case self::VERTRAGSBESTANDTEIL_STUNDEN:
				$vertragsbestandteil = new VertragsbestandteilStunden();
				$vertragsbestandteil->hydrateByStdClass($data, $fromdb);
				break;
			
			case self::VERTRAGSBESTANDTEIL_URLAUBSANSPRUCH:
				$vertragsbestandteil = new VertragsbestandteilUrlaubsanspruch();
				$vertragsbestandteil->hydrateByStdClass($data, $fromdb);
				break;
			
			case self::VERTRAGSBESTANDTEIL_ZEITAUFZEICHNUNG:
				$vertragsbestandteil = new VertragsbestandteilZeitaufzeichnung();
				$vertragsbestandteil->hydrateByStdClass($data, $fromdb);
				break;
			
			default:
				throw new Exception('Unknown vertragsbestandteiltyp_kurzbz ' 
					. $vertragsbestandteiltyp_kurzbz);			
		}
		
		return $vertragsbestandteil;
	}
	
	public static function getVertragsbestandteilDBModel($vertragsbestandteil_kurzbz): \DB_model
	{
		$CI = get_instance();
		
		$vertragsbestandteildbmodel = null;
		switch ($vertragsbestandteil_kurzbz)
		{
			case self::VERTRAGSBESTANDTEIL_FREITEXT:
				$CI->load->model('vertragsbestandteil/VertragsbestandteilFreitext_model',
					'VertragsbestandteilFreitext_model');
				$vertragsbestandteildbmodel = $CI->VertragsbestandteilFreitext_model;
				break;
			
			case self::VERTRAGSBESTANDTEIL_FUNKTION:
				$CI->load->model('vertragsbestandteil/VertragsbestandteilFunktion_model',
					'VertragsbestandteilFunktion_model');
				$vertragsbestandteildbmodel = $CI->VertragsbestandteilFunktion_model;
				break;
			
			case self::VERTRAGSBESTANDTEIL_KARENZ:
				$CI->load->model('vertragsbestandteil/VertragsbestandteilKarenz_model',
					'VertragsbestandteilKarenz_model');
				$vertragsbestandteildbmodel = $CI->VertragsbestandteilKarenz_model;
				break;

			case self::VERTRAGSBESTANDTEIL_KUENDIGUNGSFRIST:
				$CI->load->model('vertragsbestandteil/VertragsbestandteilKuendigungsfrist_model',
					'VertragsbestandteilKuendigungsfrist_model');
				$vertragsbestandteildbmodel = $CI->VertragsbestandteilKuendigungsfrist_model;
				break;
			
			case self::VERTRAGSBESTANDTEIL_STUNDEN:
				$CI->load->model('vertragsbestandteil/VertragsbestandteilStunden_model', 
					'VertragsbestandteilStunden_model');
				$vertragsbestandteildbmodel = $CI->VertragsbestandteilStunden_model;
				break;
			
			case self::VERTRAGSBESTANDTEIL_URLAUBSANSPRUCH:
				$CI->load->model('vertragsbestandteil/VertragsbestandteilUrlaubsanspruch_model',
					'VertragsbestandteilUrlaubsanspruch_model');
				$vertragsbestandteildbmodel = $CI->VertragsbestandteilUrlaubsanspruch_model;
				break;
			
			case self::VERTRAGSBESTANDTEIL_ZEITAUFZEICHNUNG:
				$CI->load->model('vertragsbestandteil/VertragsbestandteilZeitaufzeichnung_model',
					'VertragsbestandteilZeitaufzeichnung_model');
				$vertragsbestandteildbmodel = $CI->VertragsbestandteilZeitaufzeichnung_model;
				break;

			default:
				throw new Exception('Unknown vertragsbestandteil_kurzbz '
					. $vertragsbestandteil_kurzbz);
		}
		
		return $vertragsbestandteildbmodel;
	}
}
