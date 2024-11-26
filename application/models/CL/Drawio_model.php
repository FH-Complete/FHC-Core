<?php
/* Copyright (C) 2024 fhcomplete.net
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 */

class Drawio_model extends CI_Model
{
	/**
	 *
	 */
	public function renderFileStart($pages=1, $agent='FH-Complete', $timemodified='now')
	{
		$modified = (new DateTime($timemodified, new DateTimeZone('UTC')))->format(DateTime::ATOM);

		echo <<<HEADER
	<mxfile modified="{$modified}" host="Electron" agent="{$agent}" type="device" pages="{$pages}">
HEADER;
	}

	/**
	 *
	 */
	public function renderFileEnd()
	{
		echo <<<FOOTER
</mxfile>
FOOTER;
	}

	/**
	 *
	 */
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

	/**
	 *
	 */
	public function renderDiagramEnd()
	{
		echo <<<ENDDIAGRAMM
	  </root>
	</mxGraphModel>
  </diagram>
ENDDIAGRAMM;
	}

	/**
	 *
	 */
	public function renderCell($id, $value, $x, $y, $width, $height)
	{		
		echo <<<OE
	<mxCell id="{$id}" value="{$value}" parent="1" vertex="1">
		<mxGeometry x="{$x}" y="{$y}" width="{$width}" height="{$height}" as="geometry" />
	</mxCell>
OE;
	}

	/**
	 *
	 */
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

	/**
	 *
	 */
	public function renderSemesterLabel($id, $sem, $ects, $x, $y, $width, $height) 
	{
		echo <<<RENDERSEM
			<mxCell id="{$id}" value="&lt;b&gt;{$sem}. Semester&lt;br&gt;&lt;/b&gt;&lt;div style=&quot;text-align: left;&quot;&gt;&lt;span style=&quot;background-color: initial;&quot;&gt;{$ects} ECTS&lt;/span&gt;&lt;/div&gt;" style="text;html=1;align=center;verticalAlign=middle;whiteSpace=wrap;rounded=0;" vertex="1" parent="1">
	  <mxGeometry x="{$x}" y="{$y}" width="{$width}" height="{$height}" as="geometry" />
		</mxCell>
RENDERSEM;
	}

	/**
	 *
	 */
	public function renderModulList($listid, $mod, $x, $y, $ects_width, $lv_height)
	{	
		$width = ceil($mod->ects * $ects_width);
		$height = (count($mod->childs) + 1) * $lv_height;
		$modul_ects = (int) $mod->ects;

		echo <<<RENDERMOD
		<mxCell id="{$listid}" value="{$mod->bezeichnung} ({$modul_ects})" style="swimlane;fontStyle=0;childLayout=stackLayout;horizontal=1;startSize={$lv_height};horizontalStack=0;resizeParent=1;resizeParentMax=0;resizeLast=0;collapsible=0;marginBottom=0;whiteSpace=wrap;html=1;" vertex="1" parent="1">
		  <mxGeometry x="{$x}" y="{$y}" width="{$width}" height="{$height}" as="geometry">
			<mxRectangle x="{$x}" y="{$y}" width="{$width}" height="{$height}" as="alternateBounds" />
		  </mxGeometry>
		</mxCell>
RENDERMOD;

		$childnumber = 0;

		foreach ($mod->childs as $child)
		{
			$childnumber++;
			$childid = uniqid();
			$childheight = $childnumber * $lv_height;
			$child_ects = (int) $child->ects;

			echo <<<RENDERMODFOR
			<mxCell id="{$childid}" value="{$child->bezeichnung} ({$child_ects})" style="text;strokeColor=none;fillColor=none;align=left;verticalAlign=middle;spacingLeft=4;spacingRight=4;overflow=hidden;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;rotatable=0;whiteSpace=wrap;html=1;" vertex="1" parent="{$listid}">
		  <mxGeometry y="{$childheight}" width="{$width}" height="{$lv_height}" as="geometry" />
	  </mxCell>
RENDERMODFOR;
		}

		return (object) array(
			'width'	=> $width,
			'height' => $height
		);
	}
}

