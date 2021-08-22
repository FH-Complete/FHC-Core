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
	<link rel="stylesheet" href="../../../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" type="text/css"/>
<!--	<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">-->
<!--	<link rel="stylesheet" href="../../vendor/components/jqueryui/themes/base/jquery-ui.min.css" type="text/css"/>-->
	<script type="text/javascript" src="../../../vendor/components/jquery/jquery.min.js"></script>
<!--	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>-->
	<script type="text/javascript" src="../../../vendor/components/jqueryui/ui/i18n/datepicker-de.js"></script>
<!--	<script type="text/javascript" src="../../vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>-->

<style>
/*body {*/
/*background-color: #FFFFFF;*/
/*margin:10px 10px 10px 90px;*/
/*padding-left:50px;*/
/*padding-top:50px;*/
/*}*/
/*body, td {*/
/*	background-color:transparent;*/
/*	font-family:"Tahoma";*/
/*	font-size:20px;*/
/*	line-height:30px;*/
/*	text-decoration:none;*/
/*	}*/
/*h1 {*/
/*	color:#459F8C;*/
/*	font-weight:bold;*/
/*	text-align: left;	*/
/*	padding: 0px;*/
/*	font-size:25px;*/
/*}*/

  </style></head>

<body>

<div class="text-center">
	<h1>Testseite zur Darstellung des Reihungstests an der FHTW</h1>
</div>
<div class="container">

	<noscript>
		<div class="row alert alert-danger">
			<div class="col-sm-12 ">
				Javascript ist in diesem Browser nicht aktiviert.<br>
				Bitte aktivieren Sie Javascript, um den Reihungstest durchführen zu können.<br>
				<br>
				Javascript is not activated in this browser.<br>
				Please activate Javascript before starting the test.
			</div>
		</div>
	</noscript>
	<div class="row well">
		<div class="col-sm-12">
			<h2>Formeln</h2>
			<p>Die Formeln auf der linken Seite sollten möglichst gleich dargestellt werden, wie in dem Bild auf der rechten Seite.<br>
			Abweichungen in Schriftgröße und -art können vorkommen.</p>
			<p>The formula on the left side should be equal to the picture on the right side.<br>
			Differences in font size and font styling are acceptable.</p>
			<div class="row">
				<div class="col-sm-6 text-right">
					<h3>Formel / Formula</h3>
					<div style="font-size: large; padding-top: 15px">
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
									<mo movablelimits="false">&sum;</mo>
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
					</div>
				</div>
				<div class="col-sm-6">
					<h3>Bild / Picture</h3>
					<img alt="Beispielbild" src="MathML_Beispiel.jpg" border="1" height="154" width="233"></img>
				</div>
			</div>
		</div>
	</div>
	<div class="row well">
		<div class="col-sm-12">
			<h2>Audiofiles</h2>
			<p>Manche Module beinhalten Hörbeispiele</p>
			<p>Testen Sie hier, ob Sie den Ton hören können.</p>
			<audio controls>
				<source src="audio_testfile.ogg" type="audio/ogg">
				<source src="audio_testfile.mp3" type="audio/mpeg">
				<p>Ihr Browser unterstützt dieses Audioelement leider nicht.</p>
			</audio>
		</div>
	</div>
</div>

</body>
</html>
