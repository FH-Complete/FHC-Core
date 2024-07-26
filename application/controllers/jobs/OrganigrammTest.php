<?php
if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Description of OrganigrammTest
 *
 * @author bambi
 */
class OrganigrammTest extends JOB_Controller
{
    const DEFAULT_XOFFSET = 40;
    const DEFAULT_YOFFSET = 30;
    const DEFAULT_WIDTH	  = 160;
    const DEFAULT_HEIGHT  = 50;
    const DEFAULT_SPACING = 40;
    const DEFAULT_FONTSIZE = 8;
    const RENDER_CHILDS_HORIZONTAL  = 1;
    const RENDER_CHILDS_VERTICAL    = 2;
    
    protected $maxxoffset;
    protected $donotrenderchildsoftype;

    public function __construct()
	{
	    parent::__construct();
	    
	    $this->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
	    
	    $this->load->library('DrawIoLib', null, 'DrawIoLib');
	    
	    $this->maxxoffset = self::DEFAULT_XOFFSET;
	    $this->donotrenderchildsoftype = array();
	}
	
	public function getAllActiveOes()
	{
	    $sql = <<<EOSQL
		SELECT 
		  oe.oe_kurzbz, 
		  CASE 
		      WHEN oe.oe_parent_kurzbz = 'atw' THEN 'gst'
		      WHEN oe.oe_parent_kurzbz = 'etw' THEN 'gst'
		      ELSE oe.oe_parent_kurzbz
		  END AS oe_parent_kurzbz, 
		  oe.bezeichnung, 
		  oe.organisationseinheittyp_kurzbz, 
		  STRING_AGG(
		    DISTINCT p.vorname || ' ' || p.nachname, 
		    ', '
		  ) AS leitung_uid 
		FROM 
		  "public"."tbl_organisationseinheit" oe 
		  LEFT JOIN public.tbl_benutzerfunktion bf ON bf.oe_kurzbz = oe.oe_kurzbz 
		  AND (
		    bf.datum_bis IS NULL 
		    OR bf.datum_bis > NOW() :: date
		  ) 
		  AND (
		    bf.funktion_kurzbz IS NULL 
		    OR bf.funktion_kurzbz = 'Leitung'
		  ) 
		  LEFT JOIN public.tbl_benutzer b USING(uid) 
		  LEFT JOIN public.tbl_person p USING(person_id) 
		WHERE 
		  oe.aktiv = true
		  AND oe.oe_kurzbz NOT IN ('betriebsrat', 'oeh', 'FUEWahl', 'atw', 'etw', 'gf20')
		GROUP BY 
		  oe.oe_kurzbz, 
		  oe.oe_parent_kurzbz, 
		  oe.bezeichnung, 
		  oe.organisationseinheittyp_kurzbz 
		ORDER BY 
		  oe.oe_parent_kurzbz ASC NULLS FIRST, 
		  oe.oe_kurzbz ASC;
EOSQL;
	    
	    $result = $this->OrganisationseinheitModel->execReadOnlyQuery($sql);
	    $oes = getData($result);

	    $errors = array();
	    $assocoes = array();
	    $roots = array();
	    if($oes)
	    {
		foreach($oes as &$oe) 
		{
		    $oe->parent = NULL;
		    $oe->childs = array();
		    $oe->renderchilds = self::RENDER_CHILDS_HORIZONTAL;
		    $oe->childstorender = array();
		    $oe->donotrenderchildsoftype = array();
		    $assocoes[$oe->oe_kurzbz] = $oe;
		}
		
		foreach($assocoes as &$assocoe)
		{
		    if( $assocoe->oe_parent_kurzbz === NULL )
		    {
			$roots[$assocoe->oe_kurzbz] = $assocoe;
			$assocoe->donotrenderchildsoftype = array(
			    'Team',
			    'Lehrgang',
			    'Studiengang',
			    'Kompetenzfeld',
			    'Forschungsfeld',
			    'Container'
			);
		    }
		    else
		    {						
			if( isset($assocoes[$assocoe->oe_parent_kurzbz]) ) 
			{
			    $assocoe->parent = $assocoes[$assocoe->oe_parent_kurzbz];
			    $assocoes[$assocoe->oe_parent_kurzbz]->childs[] = $assocoe;
			    
			    if( $assocoe->organisationseinheittyp_kurzbz === 'Fakultaet' )
			    {
				$roots[$assocoe->oe_kurzbz] = $assocoe;
			    }
			}
			else
			{
			    $errors[] = "ERROR: Missing parent oe " . $assocoe->oe_parent_kurzbz . "\n";
			}			
		    }
		}
		
		if(count($errors) === 0) {
		    $pages = count($roots);
		    $this->DrawIoLib->renderFileStart($pages);

		    foreach($roots AS &$root)
		    {
			$this->maxxoffset = self::DEFAULT_XOFFSET;
			$this->donotrenderchildsoftype = $root->donotrenderchildsoftype;
			$this->prepareChildsToRender($root);
			$this->DrawIoLib->renderDiagramStart($root->oe_kurzbz, $root->bezeichnung);
			$this->renderOE($root, 0, 0);
			$this->DrawIoLib->renderDiagramEnd();
		    }
		    $this->DrawIoLib->renderFileEnd();
		}
		else 
		{
		    foreach ($errors as $error)
		    {
			echo $error;
		    }
		}
	    }
	    else 
	    {
		echo "Keine Oes gefunden.\n";
	    }
	}

    protected function prepareChildsToRender(&$oe)
    {
	$oe->childstorender = array();
	$oe->renderchilds = self::RENDER_CHILDS_HORIZONTAL;
	if( isset($oe->childsxoffset) )
	{
	    unset($oe->childsxoffset);
	}
	
	foreach($oe->childs AS &$oechild )
	{
	    $this->prepareChildsToRender($oechild);
	    if( !in_array($oechild->organisationseinheittyp_kurzbz, $this->donotrenderchildsoftype) )
	    {
		$oe->childstorender[] = $oechild;
	    }
	}
	
	$nurLehrgaengeOderStudiengaengeOhneKinder = true;
	foreach($oe->childstorender AS &$oechildtorender )
	{
/*		
	    if( !(($oechild->organisationseinheittyp_kurzbz == 'Studiengang' 
		|| $oechild->organisationseinheittyp_kurzbz == 'Lehrgang')
		&& count($oechild->childs) === 0) )
*/
	    if( !(count($oechildtorender->childstorender) === 0) )
	    {
		$nurLehrgaengeOderStudiengaengeOhneKinder = false;
	    }
	}
	
	if( $nurLehrgaengeOderStudiengaengeOhneKinder && $oe->parent !== NULL ) 
	{
	    $oe->renderchilds = self::RENDER_CHILDS_VERTICAL;
	}
    }
    
    protected function renderOE($oe, $level, $parentrenderedchildcount) 
    {	
	$width	   = self::DEFAULT_WIDTH;
	$height	   = self::DEFAULT_HEIGHT;
	$spacing   = self::DEFAULT_SPACING;
	$fontsize  = self::DEFAULT_FONTSIZE;
	$nextlevel = $level + 1;
	$renderedchildcount = 0;
	
	$curxoffset = $this->maxxoffset;
	
	if( count($oe->childstorender) > 0 )
	{
	    if( !isset($oe->childsxoffset) )
	    {
		$oe->childsxoffset = (object) array(
		    'min' => $curxoffset,
		    'max' => $curxoffset
		);
	    }
	    
	    foreach($oe->childstorender AS $child) 
	    {	
		$ret = $this->renderOE($child, $nextlevel, $renderedchildcount);
		$renderedchildcount++;
		if( $renderedchildcount === 1 ) 
		{
		    $oe->childsxoffset->min = $ret->minx;
		}
		$oe->childsxoffset->max = $ret->maxx;
	    }

	    if( count($oe->childstorender) === $renderedchildcount )
	    {
		$curxoffset = $oe->childsxoffset->min + 
		    floor((($oe->childsxoffset->max - $oe->childsxoffset->min) / 2)) - 
		    floor((($width + $spacing) / 2));
	    }
	}


	if( $oe->parent !== null && $oe->parent->renderchilds === self::RENDER_CHILDS_VERTICAL ) 
	{
	    $x = $curxoffset;
	    $y = self::DEFAULT_YOFFSET
		+ (($level - 1) * ($height + $spacing))
		+ (($parentrenderedchildcount + 1) * ($height + floor($spacing / 2)));
	}
	else
	{
	    $x = $curxoffset;
	    $y = self::DEFAULT_YOFFSET + ($level * ($height + $spacing));
	}
	$bezeichnung = htmlspecialchars($oe->bezeichnung);
	$leitung = ($oe->leitung_uid !== null) ? htmlspecialchars($oe->leitung_uid) : 'N.N.';
	$fillcolors = array(
	    'Team'	     => '#ffe6cc',
	    'Abteilung'	     => '#e6ffcc',
	    'Studiengang'    => '#cce6ff',
	    'Lehrgang'	     => '#e6ccff',
	    'Fakultaet'	     => '#f8cecc',
	    'Department'     => '#fff2cc',
	    'Forschungsfeld' => '#e1d5e7',
	    'Kompetenzfeld'  => '#f5f5f5'
	);
	$fillcolor = (isset($fillcolors[$oe->organisationseinheittyp_kurzbz])) ? $fillcolors[$oe->organisationseinheittyp_kurzbz] : '#ffffff';
	$value = <<<EOVAL
&lt;font style=&quot;font-size: {$fontsize}px;&quot;&gt;&lt;div style=&quot;&quot;&gt;[{$oe->organisationseinheittyp_kurzbz}]&lt;/div&gt;&lt;b style=&quot;&quot;&gt;{$bezeichnung}&lt;/b&gt;&lt;div&gt;({$leitung})&lt;/div&gt;&lt;/font&gt;" style="whiteSpace=wrap;html=1;align=center;verticalAlign=middle;treeFolding=1;treeMoving=1;newEdgeStyle={&quot;edgeStyle&quot;:&quot;elbowEdgeStyle&quot;,&quot;startArrow&quot;:&quot;none&quot;,&quot;endArrow&quot;:&quot;none&quot;};fillColor={$fillcolor};
EOVAL;

	$this->DrawIoLib->renderCell($oe->oe_kurzbz, $value, $x, $y, $width, $height);

	if( $oe->oe_parent_kurzbz !== NULL ) 
	{
	    $exitX = '0.5';
	    $exitY = '1';
	    $entryX = '0.5';
	    $entryY = '0';
	    $points = array();
	    if( $oe->parent !== null && $oe->parent->renderchilds === self::RENDER_CHILDS_VERTICAL) 
	    {
		$exitX = '0';
		$exitY = '0.5';
		$entryX = '0';
		$entryY = '0.5';
		$points = array(
		    (object) array(
			'x' => ($x - floor($spacing / 2)),
			'y' => ($y + floor($height  / 2))
		    )
		);
	    }
	    $this->DrawIoLib->renderEdge($oe->oe_parent_kurzbz, $oe->oe_kurzbz, $exitX, $exitY, $entryX, $entryY, $points);
	}

	if( $oe->parent !== null && $oe->parent->renderchilds === self::RENDER_CHILDS_VERTICAL ) 
	{
	    if( count($oe->parent->childstorender) === ($parentrenderedchildcount + 1) ) 
	    {
		if( $this->maxxoffset <  ($x + $width + $spacing) )
		{
		    $this->maxxoffset = ($x + $width + $spacing);
		}
		return (object) array(
		    'minx' => $curxoffset,
		    'maxx' => ($x + $width + $spacing)
		);
	    }
	    return (object) array( 
		'minx' => $curxoffset,
		'maxx' => $x
	    );
	}
	
	if( $this->maxxoffset <  ($x + $width + $spacing) )
	{
	    $this->maxxoffset = ($x + $width + $spacing);
	}
	
	return (object) array(
	    'minx' => $curxoffset,
	    'maxx' => ($x + $width + $spacing)
	);
    }
}
