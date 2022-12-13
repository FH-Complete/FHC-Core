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
	
	public static function getVertragsbestandteil($data) 
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

			case self::VERTRAGSBESTANDTEIL_STUNDEN:
				$vertragsbestandteil = new VertragsbestandteilStunden();
				$vertragsbestandteil->hydrateByStdClass($data);
				break;

			case self::VERTRAGSBESTANDTEIL_FUNKTION:
				$vertragsbestandteil = new VertragsbestandteilFunktion();
				$vertragsbestandteil->hydrateByStdClass($data);
				break;
			
			default:
				throw new Exception('Unknown vertragsbestandteiltyp_kurzbz ' 
					. $vertragsbestandteiltyp_kurzbz);			
		}
		
		return $vertragsbestandteil;
	}
	
	public static function getVertragsbestandteilDBModel($vertragsbestandteil_kurzbz)
	{
		$CI = get_instance();
		
		$vertragsbestandteildbmodel = null;
		switch ($vertragsbestandteil_kurzbz)
		{
			case self::VERTRAGSBESTANDTEIL_STUNDEN:
				$CI->load->model('vertragsbestandteil/VertragsbestandteilStunden_model', 
					'VertragsbestandteilStunden_model');
				$vertragsbestandteildbmodel = $CI->VertragsbestandteilStunden_model;
				break;

			case self::VERTRAGSBESTANDTEIL_FUNKTION:
				$CI->load->model('vertragsbestandteil/VertragsbestandteilFunktion_model', 
					'VertragsbestandteilFunktion_model');
				$vertragsbestandteildbmodel = $CI->VertragsbestandteilFunktion_model;
				break;
			
			default:
				throw new Exception('Unknown vertragsbestandteil_kurzbz ' 
					. $vertragsbestandteil_kurzbz);
		}
		
		return $vertragsbestandteildbmodel;
	}
}
