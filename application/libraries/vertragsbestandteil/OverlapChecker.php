<?php
namespace vertragsbestandteil;

use vertragsbestandteil\Vertragsbestandteil;
use vertragsbestandteil\VertragsbestandteilFreitext;

/**
 * Description of OverlapChecker
 *
 * @author bambi
 */
class OverlapChecker
{
	protected $CI;
	/**
	 * @var Vertragsbestandteil_model
	 */
	protected $VertragsbestandteilModel;
	/**
	 * @var VertragsbestandteilFreitext_model
	 */
	protected $VertragsbestandteilFreitextModel;
	/**
	 * @var VertragsbestandteilTyp_model
	 */
	protected $VertragsbestandteilTypModel;
	/**
	 * @var VertragsbestandteilFreitexttyp_model
	 */
	protected $VertragsbestandteilFreitexttypModel;
	
	protected static $instance = null;
	
	public static function getInstance()
	{
		if( null === self::$instance )
		{
			self::$instance = new OverlapChecker();
		}
		return self::$instance;
	}
	
	private function __construct()
	{
		$this->CI = get_instance();
		$this->CI->load->model('vertragsbestandteil/Vertragsbestandteil_model', 
			'VertragsbestandteilModel');
		$this->VertragsbestandteilModel = $this->CI->VertragsbestandteilModel;
		$this->CI->load->model('vertragsbestandteil/VertragsbestandteilFreitext_model', 
			'VertragsbestandteilFreitextModel');
		$this->VertragsbestandteilFreitextModel = $this->CI->VertragsbestandteilFreitextModel;
		$this->CI->load->model('vertragsbestandteil/Vertragsbestandteiltyp_model', 
			'VertragsbestandteilTypModel');
		$this->VertragsbestandteilTypModel = $this->CI->VertragsbestandteilTypModel;
		$this->CI->load->model('vertragsbestandteil/VertragsbestandteilFreitexttyp_model', 
			'VertragsbestandteilFreitexttypModel');
		$this->VertragsbestandteilFreitexttypModel = $this->CI->VertragsbestandteilFreitexttypModel;
	}
	
	public function overlapsVB(Vertragsbestandteil $vb)
	{
		$result = $this->VertragsbestandteilTypModel->load($vb->getVertragsbestandteiltyp_kurzbz());
		if( null === ($vertragsbestandteiltyp = getData($result)) )
		{
			throw new Exception('vertragsbestandteiltyp: ' 
				. $vb->getVertragsbestandteiltyp_kurzbz() . ' not found.');
		}
		
		if( true === $vertragsbestandteiltyp[0]->ueberlappend )
		{
			// vertragsbestandteiltyp can overlap
			return false;
		}
		
		if( $this->VertragsbestandteilModel->countOverlappingVBsOfSameType($vb) === 0 )
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	public function overlapsFreitext(VertragsbestandteilFreitext $vbft)
	{
		$result = $this->VertragsbestandteilFreitexttypModel->load($vbft->getFreitexttypKurzbz());
		if( null === ($vertragsbestandteilfreitexttyp = getData($result)) )
		{
			throw new Exception('vertragsbestandteilfreitexttyp: ' 
				. $vbft->getFreitexttypKurzbz() . ' not found.');
		}
		
		if( true === $vertragsbestandteilfreitexttyp[0]->ueberlappend )
		{
			// freitexttyp can overlap
			return false;
		}
		
		if( $this->VertragsbestandteilFreitextModel->countOverlappingVBFreitextsOfSameType($vbft) === 0 )
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	private function __clone() {}
}
