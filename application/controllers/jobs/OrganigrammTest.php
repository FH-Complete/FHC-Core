<?php
if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Description of OrganigrammTest
 *
 * @author bambi
 */
class OrganigrammTest extends JOB_Controller
{
    const DEFAULT_XOFFSET = 100;
    const DEFAULT_YOFFSET = 180;
    const RENDER_CHILDS_HORIZONTAL  = 1;
    const RENDER_CHILDS_VERTICAL    = 2;
    
    protected $maxxoffset;

    public function __construct()
	{
	    parent::__construct();
	    
	    $this->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
	    
	    $this->maxxoffset = self::DEFAULT_XOFFSET;
	}
	
	public function getAllActiveOes()
	{
	    $sql = <<<EOSQL
		SELECT 
		  oe.oe_kurzbz, 
		  oe.oe_parent_kurzbz, 
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
		    $assocoes[$oe->oe_kurzbz] = $oe;
		}
		
		foreach($assocoes as &$assocoe)
		{
		    if( $assocoe->oe_parent_kurzbz === NULL )
		    {
			$roots[$assocoe->oe_kurzbz] = $assocoe;
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
		    $modified = (new DateTime('now', new DateTimeZone('UTC')))->format(DateTime::ATOM);
		    $agent = 'FH-Complete';
		    echo <<<HEADER
<mxfile modified="{$modified}" host="Electron" agent="{$agent}" type="device" pages="{$pages}">

HEADER;

		    foreach($roots AS &$root)
		    {
			$this->resetChildsOffset($root);

			echo <<<STARTDIAGRAMM
  <diagram id="diagram_{$root->oe_kurzbz}" name="{$root->bezeichnung}">
    <mxGraphModel dx="1177" dy="687" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="827" pageHeight="1169" math="0" shadow="0">
      <root>
        <mxCell id="0" />
        <mxCell id="1" parent="0" />

STARTDIAGRAMM;

			$this->renderOE($root, 0, 0);
  
			echo <<<ENDDIAGRAMM
        </root>
    </mxGraphModel>
  </diagram>

ENDDIAGRAMM;
  
		    }
		
		    echo <<<FOOTER
</mxfile>

FOOTER;
	
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

    protected function resetChildsOffset($oe) 
    {
	if( isset($oe->childsxoffset) )
	{
	    unset($oe->childsxoffset);
	}
	
	foreach($oe->childs AS $oechild )
	{
	    $this->resetChildsOffset($oechild);
	}
    }

    protected function renderOE($oe, $level, $parentrenderedchildcount) 
    {	
	$width	   = 200;
	$height	   = 100;
	$spacing   = 80;
	$nextlevel = $level + 1;
	$renderedchildcount = 0;
	
	$curxoffset = $this->maxxoffset;
	
	if( count($oe->childs) > 0 )
	{
	    if( !isset($oe->childsxoffset) )
	    {
		$oe->childsxoffset = (object) array(
		    'min' => $curxoffset,
		    'max' => $curxoffset
		);
	    }

	    $nurLehrgaengeOderStudiengaengeOhneKinder = true;
	    foreach($oe->childs AS $oechild )
	    {
/*		
		if( !(($oechild->organisationseinheittyp_kurzbz == 'Studiengang' 
		    || $oechild->organisationseinheittyp_kurzbz == 'Lehrgang')
		    && count($oechild->childs) === 0) )
 */
		if( !(count($oechild->childs) === 0) )
		{
		    $nurLehrgaengeOderStudiengaengeOhneKinder = false;
		    break;
		}
	    }
	    if( $nurLehrgaengeOderStudiengaengeOhneKinder ) 
	    {
		$oe->renderchilds = self::RENDER_CHILDS_VERTICAL;
	    }
	    
	    foreach($oe->childs AS $child) 
	    {	
		$oe->childsxoffset->max = $this->renderOE($child, $nextlevel, $renderedchildcount);
		$renderedchildcount++;
	    }

	    if(  count($oe->childs) === $renderedchildcount )
	    {
		$curxoffset = $oe->childsxoffset->min + 
		    floor((($oe->childsxoffset->max - $oe->childsxoffset->min) / 2)) - 
		    floor((($width + $spacing) / 2));
	    }
	}


	if( $oe->parent !== null && $oe->parent->renderchilds === self::RENDER_CHILDS_VERTICAL ) 
	{
/*	    
	    if($parentrenderedchildcount === 0)
	    {
		$curxoffset += ($width + $spacing);
		$this->maxxoffset = $curxoffset;
	    }
 */
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
	    'Team'	    => '#ffe6cc',
	    'Abteilung'	    => '#e6ffcc',
	    'Studiengang'   => '#cce6ff',
	    'Lehrgang'	    => '#e6ccff'
	);
	$fillcolor = (isset($fillcolors[$oe->organisationseinheittyp_kurzbz])) ? $fillcolors[$oe->organisationseinheittyp_kurzbz] : '#ffffff';
	echo <<<OE
	    <mxCell id="{$oe->oe_kurzbz}" value="&lt;div style=&quot;&quot;&gt;&lt;font style=&quot;font-size: 12px;&quot;&gt;[{$oe->organisationseinheittyp_kurzbz}]&lt;/font&gt;&lt;/div&gt;&lt;b style=&quot;&quot;&gt;{$bezeichnung}&lt;/b&gt;&lt;div&gt;({$leitung})&lt;/div&gt;" style="whiteSpace=wrap;html=1;align=center;verticalAlign=middle;treeFolding=1;treeMoving=1;newEdgeStyle={&quot;edgeStyle&quot;:&quot;elbowEdgeStyle&quot;,&quot;startArrow&quot;:&quot;none&quot;,&quot;endArrow&quot;:&quot;none&quot;};fillColor={$fillcolor};" parent="1" vertex="1">
	      <mxGeometry x="{$x}" y="{$y}" width="{$width}" height="{$height}" as="geometry" />
	    </mxCell>

OE;

	if( $oe->oe_parent_kurzbz !== NULL ) 
	{
	    $exitX = '0.5';
	    $exitY = '1';
	    $entryX = '0.5';
	    $entryY = '0';
	    $edgegeom = '<mxGeometry relative="1" as="geometry" />';
	    if( $oe->parent !== null && $oe->parent->renderchilds === self::RENDER_CHILDS_VERTICAL) 
	    {
		$exitX = '0';
		$exitY = '0.5';
		$entryX = '0';
		$entryY = '0.5';
		$pointx = $x - floor($spacing / 2);
		$pointy = $y + floor($height / 2);
		$edgegeom = <<<EDGEPOINTS
	      <mxGeometry relative="1" as="geometry">
		<Array as="points">
		  <mxPoint x="{$pointx}" y="{$pointy}" />
		</Array>
	      </mxGeometry>
EDGEPOINTS;
	    }
	    echo <<<EDGE
	    <mxCell id="edge_{$oe->oe_parent_kurzbz}_{$oe->oe_kurzbz}" value="" style="edgeStyle=elbowEdgeStyle;elbow=vertical;sourcePerimeterSpacing=0;targetPerimeterSpacing=0;startArrow=none;endArrow=none;rounded=0;curved=0;exitX={$exitX};exitY={$exitY};exitDx=0;exitDy=0;entryX={$entryX};entryY={$entryY};entryDx=0;entryDy=0;dashed=1;dashPattern=12 12;" parent="1" source="{$oe->oe_parent_kurzbz}" target="{$oe->oe_kurzbz}" edge="1">
	      {$edgegeom}
	    </mxCell>

EDGE;
	}

	if( $oe->parent !== null && $oe->parent->renderchilds === self::RENDER_CHILDS_VERTICAL ) 
	{
	    if( count($oe->parent->childs) === ($parentrenderedchildcount + 1) ) 
	    {
		if( $this->maxxoffset <  ($x + $width + $spacing) )
		{
		    $this->maxxoffset = ($x + $width + $spacing);
		}
		return ($x + $width + $spacing);
	    }
	    return $x;
	}
	
	if( $this->maxxoffset <  ($x + $width + $spacing) )
	{
	    $this->maxxoffset = ($x + $width + $spacing);
	}
	
	return ($x + $width + $spacing);
    }
}
