<?php

/**
 * FH-Complete
 *
 * @package             FHC-Helper
 * @author              FHC-Team
 * @copyright           Copyright (c) 2022 fhcomplete.net
 * @license             GPLv3
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Description of DrawIoLib
 *
 * @author bambi
 */
class DrawIoLib
{
    public function renderFileStart($pages=1, $agent='FH-Complete', $timemodified='now')
    {
	$modified = (new DateTime($timemodified, new DateTimeZone('UTC')))->format(DateTime::ATOM);
	echo <<<HEADER
<mxfile modified="{$modified}" host="Electron" agent="{$agent}" type="device" pages="{$pages}">

HEADER;

    }
    
    public function renderFileEnd()
    {
	echo <<<FOOTER
</mxfile>

FOOTER;

    }
    
    public function renderDiagramStart($diagram_id, $diagram_bezeichnung)
    {
	$bezeichnung = htmlspecialchars($diagram_bezeichnung);
	echo <<<STARTDIAGRAMM
  <diagram id="diagram_{$diagram_id}" name="{$bezeichnung}">
    <mxGraphModel dx="1177" dy="687" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="827" pageHeight="1169" math="0" shadow="0">
      <root>
        <mxCell id="0" />
        <mxCell id="1" parent="0" />

STARTDIAGRAMM;

    }
    
    public function renderDiagramEnd()
    {
	echo <<<ENDDIAGRAMM
      </root>
    </mxGraphModel>
  </diagram>

ENDDIAGRAMM;

    }
    
    public function renderCell($id, $value, $x, $y, $width, $height)
    {	    
	echo <<<OE
	<mxCell id="{$id}" value="{$value}" parent="1" vertex="1">
	    <mxGeometry x="{$x}" y="{$y}" width="{$width}" height="{$height}" as="geometry" />
	</mxCell>

OE;

    }
    
    public function renderEdge($source, $target, $exitX, $exitY, $entryX, $entryY, $points=array())
    {
	if( count($points) > 0 )
	{
	    $pointsxml = '';
	    foreach($points as $point)
	    {
		$pointsxml .= <<<EOPOINT
	      <mxPoint x="{$point->x}" y="{$point->y}" />		    
EOPOINT;

	    }
	    
	    $edgegeom = <<<EDGEPOINTS
	  <mxGeometry relative="1" as="geometry">
	    <Array as="points">
{$pointsxml}
	    </Array>
	  </mxGeometry>
EDGEPOINTS;

	}
	else
	{
	    $edgegeom = '	  <mxGeometry relative="1" as="geometry" />';
	}
	
	echo <<<EDGE
	<mxCell id="edge_{$source}_{$target}" value="" style="edgeStyle=elbowEdgeStyle;elbow=vertical;sourcePerimeterSpacing=0;targetPerimeterSpacing=0;startArrow=none;endArrow=none;rounded=0;curved=0;exitX={$exitX};exitY={$exitY};exitDx=0;exitDy=0;entryX={$entryX};entryY={$entryY};entryDx=0;entryDy=0;" parent="1" source="{$source}" target="{$target}" edge="1">
{$edgegeom}
	</mxCell>

EDGE;

    }
}
