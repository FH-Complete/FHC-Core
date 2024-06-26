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
    
    protected $xoffsetperlevel;

    public function __construct()
	{
	    parent::__construct();
	    
	    $this->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
	    
	    $this->xoffsetperlevel = array();
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

			echo <<<STARTDIAGRAMM
  <diagram id="diagram_{$root->oe_kurzbz}" name="{$root->bezeichnung}">
    <mxGraphModel dx="1177" dy="687" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="827" pageHeight="1169" math="0" shadow="0">
      <root>
        <mxCell id="0" />
        <mxCell id="1" parent="0" />

STARTDIAGRAMM;

			$this->renderOE($root, 0);
  
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

    protected function renderOE($oe, $level) 
    {	
	$width	    = 200;
	$height	    = 100;
	$spacing    = 80;
	$nextlevel = $level + 1;
	$ownchildcount = 0;
	$firstelementinlevel = false;
	
	if( !isset($this->xoffsetperlevel[$level]) )
	{
	    $this->xoffsetperlevel[$level] = (object) array(
		'min' => self::DEFAULT_XOFFSET, 
		'max' => self::DEFAULT_XOFFSET
	    );
	    $firstelementinlevel = true;
	}
	
	fwrite(STDERR, print_r($this->xoffsetperlevel, true) . PHP_EOL);
	
	foreach($oe->childs AS $child) 
	{	
	    $this->renderOE($child, $nextlevel);
	    $ownchildcount++;
	}

	if( count($oe->childs) === 0 )
	{
	    if( !isset($this->xoffsetperlevel[$nextlevel]) )
	    {
		$this->xoffsetperlevel[$nextlevel] = (object) array(
		    'min' => $this->xoffsetperlevel[$level]->max + $width + $spacing, 
		    'max' => $this->xoffsetperlevel[$level]->max + $width + $spacing
		);
	    }
	}

	if( count($oe->childs) > 0 && count($oe->childs) === $ownchildcount )
	{
	    $this->xoffsetperlevel[$level]->max = 
		floor((($this->xoffsetperlevel[$nextlevel]->max - $this->xoffsetperlevel[$nextlevel]->min) / 2)) - floor(($width / 2)); 
	    if( $firstelementinlevel )
	    {
		$this->xoffsetperlevel[$level]->min = $this->xoffsetperlevel[$level]->max;
	    }
	}		

	$x		= $this->xoffsetperlevel[$level]->max;
	$y		= 180 + ($level * ($height + $spacing));
	$bezeichnung = htmlspecialchars($oe->bezeichnung);
	$leitung = ($oe->leitung_uid !== null) ? htmlspecialchars($oe->leitung_uid) : 'N.N.';
	$fillcolors = array(
	    'Team'		=> '#ffe6cc',
	    'Abteilung'	=> '#e6ffcc',
	    'Studiengang'	=> '#cce6ff',
	    'Lehrgang'	=> '#e6ccff'
	);
	$fillcolor = (isset($fillcolors[$oe->organisationseinheittyp_kurzbz])) ? $fillcolors[$oe->organisationseinheittyp_kurzbz] : '#ffffff';
	echo <<<OE
	    <mxCell id="{$oe->oe_kurzbz}" value="&lt;div style=&quot;&quot;&gt;&lt;font style=&quot;font-size: 12px;&quot;&gt;[{$oe->organisationseinheittyp_kurzbz}]&lt;/font&gt;&lt;/div&gt;&lt;b style=&quot;&quot;&gt;{$bezeichnung}&lt;/b&gt;&lt;div&gt;({$leitung})&lt;/div&gt;" style="whiteSpace=wrap;html=1;align=center;verticalAlign=middle;treeFolding=1;treeMoving=1;newEdgeStyle={&quot;edgeStyle&quot;:&quot;elbowEdgeStyle&quot;,&quot;startArrow&quot;:&quot;none&quot;,&quot;endArrow&quot;:&quot;none&quot;};fillColor={$fillcolor};" parent="1" vertex="1">
	      <mxGeometry x="{$x}" y="{$y}" width="{$width}" height="{$height}" as="geometry" />
	    </mxCell>

OE;

	if( $oe->oe_parent_kurzbz !== NULL ) 
	{
	    echo <<<EDGE
	    <mxCell id="edge_{$oe->oe_parent_kurzbz}_{$oe->oe_kurzbz}" value="" style="edgeStyle=elbowEdgeStyle;elbow=vertical;sourcePerimeterSpacing=0;targetPerimeterSpacing=0;startArrow=none;endArrow=none;rounded=0;curved=0;exitX=0.5;exitY=1;exitDx=0;exitDy=0;entryX=0.5;entryY=0;entryDx=0;entryDy=0;dashed=1;dashPattern=12 12;" parent="1" source="{$oe->oe_parent_kurzbz}" target="{$oe->oe_kurzbz}" edge="1">
	      <mxGeometry relative="1" as="geometry" />
	    </mxCell>

EDGE;
	}

	$this->xoffsetperlevel[$level]->max = ($x + $width + $spacing);
    }	
}
