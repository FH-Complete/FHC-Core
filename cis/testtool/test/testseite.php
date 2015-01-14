<?php
/* Copyright (C) 2006 Technikum-Wien
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
 * Authors: Manfred Kindl <manfred.kindl@technikum-wien.at>
 */

echo '<?xml version="1.0" encoding="ISO-8859-1" ?>';
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Testsite MathML</title>
	<meta http-equiv="content-type" content="text/xhtml; charset=UTF-8"/>

<style>
body {
background-color: #FFFFFF;
margin:10px 10px 10px 90px;
padding-left:50px;
padding-top:50px;
}
body, td {
	background-color:transparent;
	font-family:"Tahoma";
	font-size:20px;
	line-height:30px;
	text-decoration:none;
	}
h1 {
	color:#459F8C;
	font-weight:bold;
	text-align: left;	
	padding: 0px;
	font-size:25px;
}

  </style></head>

<body>

<table cellpadding="0" cellspacing="0px">
  <tbody>
	<tr>
	  	<td><h1>MathML</h1></td>
	  	<td width="40px"></td>
	  	<td><h1>Picture</h1></td>
	</tr>
	<tr>
	  	<td>
			<math xmlns="http://www.w3.org/1998/Math/MathML">
			  <mfrac>
			    <mn> 5 </mn>
			    <mn> 3 </mn>
			  </mfrac>
			
			  <mo> + </mo>
			  <mfrac>
			    <mn> 7 </mn>
			    <mn> 6 </mn>
			  </mfrac>
			  <mo> = </mo>
			
			  <mfrac>
			    <mn> 10 </mn>
			    <mn> 6 </mn>
			  </mfrac>
			  <mo> + </mo>
			  <mfrac>
			
			    <mn> 7 </mn>
			    <mn> 6 </mn>  
			  </mfrac>
			  <mo> = </mo>
			  <mfrac>
			    <mn> 17 </mn>
			
			    <mn> 6 </mn>
			  </mfrac>
			</math><br/><br/>
			<math xmlns="http://www.w3.org/1998/Math/MathML">
			<mrow>
			  <munderover>
			     <mo movablelimits="false">â</mo>
			     <mn><mi>k</mi>=1</mn>
			     <mn>5</mn>
			  </munderover>
			<mrow>
			<msup>
			<mo>(-1)</mo>
			<mn><mi>k</mi>+1</mn>
			</msup>
			</mrow>
			<mfrac>
			      <mrow>
			       <msup>
			        <mi>x</mi>
			        <mn>2<mi>k</mi> + 1</mn>
			       </msup>
			      </mrow>
			      <mrow>
			       <mo>(2<mi>k</mi>+1)!</mo>
			      </mrow>
			</mfrac>
			</mrow>
			
			</math>
		</td>
		<td width="10">&nbsp;</td>
		<td style="width: 233px;" align="left">
			<img alt="Beispielbild" src="MathML_Beispiel.jpg" border="1" height="154" width="233"></img>
		</td>
	</tr>
    <tr>
    	<td align="center" colspan="3"><br>The formula on the left side should be equal <br/>to the picture on the right side.</td>
    </tr>
    <?php
    //Sound einbinden
	echo '	<tr>
	    		<td align="center" colspan="3"><hr><br/>Audio-Testfile<br/><br/>
					<script language="JavaScript" src="audio-player/audio-player.js"></script>
					<object type="application/x-shockwave-flash" data="audio-player/player.swf" id="audioplayer1" height="24" width="290">
					<param name="movie" value="audio_player/player.swf" />
					<param name="FlashVars" value="playerID=audioplayer1&amp;soundFile=audio_testfile.mp3" />
					<param name="quality" value="high" />
					<param name="menu" value="false" />
					<param name="wmode" value="transparent" />
					</object>
				</td>
	    	</tr>';
	?>
  </tbody>
</table>

</body>
</html> 

