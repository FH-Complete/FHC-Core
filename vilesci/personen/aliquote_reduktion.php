<?php
/* Copyright (C) 2016 FH Technikum-Wien
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
 * Authors: Andreas Moik <moik@technikum-wien.at>
 */
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Aliquote Reduktion</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<?php require_once(dirname(__FILE__).'/../../config/vilesci.config.inc.php'); ?>
		<?php require_once(dirname(__FILE__)."/../../include/meta/angular.php"); ?>
		<?php require_once(dirname(__FILE__)."/../../include/meta/angular-tablesorter.php"); ?>
		<?php require_once(dirname(__FILE__)."/../../include/meta/js_utils.php"); ?>
		<style>
			.applicant
			{
			}
			.no_applicant
			{
				color:#999;
			}
		</style>

		<script>
			function sortStudiengaenge(a,b)
			{
				if (a.kurzbzlang < b.kurzbzlang)
					return -1;
				else if (a.kurzbzlang > b.kurzbzlang)
					return 1;
				else
					return 0;
			}
			function sortStudentenRTP(a,b)
			{
				if (a.rt_gesamtpunkte < b.rt_gesamtpunkte)
					return -1;
				else if (a.rt_gesamtpunkte > b.rt_gesamtpunkte)
					return 1;
				else
					return 0;
			}


			var aliquoteReduktion = angular.module('aliqRed',['tableSort']).controller('aliqRedController',function($scope)
			{
				var aqr = this;
				aqr.name = "Aliquote Reduktion";
				aqr.studiensemester_kurzbz = _GET()["studiensemester_kurzbz"];
				aqr.selectedStudiengang = Object();
				aqr.selectedStudiengang.studiengang_kz = _GET()["studiengang_kz"];
				aqr.selectedStudiensemester = "";
				aqr.selectedStudienplan = "";
				aqr.choosenStuds = 0;
				aqr.studenten = [];
				aqr.studiengaenge = [];
				aqr.studiensemester = [];
				aqr.studienplaene = [];
				SERVICE_TARGET = "aliquote_reduktion.json.php"

				if(!aqr.studiensemester_kurzbz)
					die("Es wurde kein Studiensemester angegeben");

				if(!aqr.selectedStudiengang.studiengang_kz)
					die("Es wurde kein Studiengang angeben");






				//bei jeder änderung des studiensemesters, sollen die studienplaene erneut geholt werden
				$scope.$watch('aqr.selectedStudiengang', function (){aqr.loadStudienPlaene();},true);
				$scope.$watch('aqr.selectedStudiensemester', function (){aqr.loadStudienPlaene();},true);

				$scope.$watch('aqr.selectedStudienplan', function (){aqr.loadStudenten();},true);


				AJAXCall({action:"getStudiensemester",studiengang_kz:aqr.selectedStudiengang.studiengang_kz},function(res){aqr.studiensemester=res;aqr.setStudiensemester(aqr.studiensemester_kurzbz);$scope.$apply();});
				AJAXCall({action:"getStudiengaenge",studiengang_kz:aqr.selectedStudiengang.studiengang_kz},function(res){aqr.studiengaenge=res;aqr.studiengaenge.sort(sortStudiengaenge);aqr.setStudiengang(aqr.selectedStudiengang.studiengang_kz);$scope.$apply();});



				aqr.submit = function()
				{
					if(aqr.choosenStuds < aqr.selectedStudienplan.apz)
					{
						if(!confirm("Es wurden zu wenig Studenten gewählt!"))
							return;
					}
					else if(aqr.choosenStuds > aqr.selectedStudienplan.apz)
					{
						if(!confirm("Es wurden zu viel Studenten gewählt!"))
							return;
					}
					var prestudent_ids = [];

					aqr.studenten.forEach(function(i)
					{
						if(i.selected)
						{
							prestudent_ids.push(i.prestudent_id);
						}
					});

					AJAXCall({action:"setAufgenommene",studiengang_kz:aqr.selectedStudiengang.studiengang_kz,prestudent_ids:JSON.stringify(prestudent_ids)},function(res){aqr.loadStudenten();});
				}
				aqr.countChoosen = function()
				{
					var buf = 0;
					aqr.studenten.forEach(function(i)
					{
						if(i.selected)
						{
							buf ++;
						}
					});
					aqr.choosenStuds = buf;
				}

				aqr.setStudiengang = function(studiengang_kz)
				{
					aqr.studiengaenge.forEach(function(i)
					{
						if(i.studiengang_kz == studiengang_kz)
						{
							aqr.selectedStudiengang = i;
							return;
						}
					});
				}

				aqr.setStudiensemester = function(studiensemester_kurzbz)
				{
					aqr.studiensemester.forEach(function(i)
					{
						if(i.studiensemester_kurzbz == studiensemester_kurzbz)
						{
							aqr.selectedStudiensemester = i;
							return;
						}
					});
				}

				aqr.loadStudienPlaene = function()
				{
					if(aqr.selectedStudiensemester != "")
					{
						aqr.selectedStudienplan = "";
						aqr.studienplaene.clear;
						AJAXCall({action:"getStudienplaene",studiengang_kz:aqr.selectedStudiengang.studiengang_kz,studiensemester_kurzbz:aqr.selectedStudiensemester.studiensemester_kurzbz},function(res)
						{
							aqr.studienplaene=res;
							aqr.selectedStudienplan=aqr.studienplaene[0];
							$scope.$apply();
						});
					}
				}

				aqr.loadStudenten = function()
				{
					aqr.studenten=[];
					if(aqr.selectedStudienplan && aqr.selectedStudienplan.studienplan_id)
						AJAXCall({action:"getStudenten",studiengang_kz:aqr.selectedStudiengang.studiengang_kz,studienplan_id:aqr.selectedStudienplan.studienplan_id,studiensemester_kurzbz:aqr.selectedStudiensemester.studiensemester_kurzbz},function(res)
						{
							aqr.studenten=res;
							aqr.studenten.forEach(function(i)
							{
								if(i.laststatus=='Wartender'||i.laststatus=='Bewerber')
									i.applicant = true;
								else if(i.laststatus=='Student'||i.laststatus=='Aufgenommener')
									i.selected=true;
							});
							aqr.doPreselection();
							aqr.countChoosen();
							$scope.$apply();
						});
				}

				aqr.getZGVArray = function()
				{
					var ret = [];
					aqr.studenten.forEach(function(i)
					{
						if(i.bezeichnung != null && ret.indexOf(i.bezeichnung) < 0)
							ret.push(i.bezeichnung);
					});
					return ret;
				}

				aqr.getAcceptedCount = function()
				{
					var ret = 0;
					aqr.studenten.forEach(function(i)
					{
						if(i.laststatus=='Student'||i.laststatus=='Aufgenommener')
							ret++;
					});
					return ret;
				}

				aqr.doPreselection = function()
				{
					if(parseInt(aqr.selectedStudienplan.apz) >= 0)
					{
						aqr.studenten.sort(sortStudentenRTP);
						var zgvs = aqr.getZGVArray();
						var neededStudentsCount = aqr.selectedStudienplan.apz - aqr.getAcceptedCount();
						var perZGV = parseInt(neededStudentsCount / zgvs.length);
						var zgvElems = [];

						zgvs.forEach(function(i)
						{
							zgvElems.push({name:i,needed:perZGV});
						});

						aqr.studenten.forEach(function(i)
						{
							if(i.laststatus=='Wartender'||i.laststatus=='Bewerber')
							{
								zgvElems.forEach(function(j)
								{
									if(j.needed > 0 && !i.selected && i.bezeichnung == j.name)
									{
										i.selected = true;
										j.needed --;
									}
								});
							}
						});
					}
				}
			});
		</script>
	</head>
	<body class="Background_main">
		<div ng-controller="aliqRedController as aqr" ng-app="aliqRed">
			<h2>{{aqr.name}} {{aqr.selectedStudiengang.studiengang_kz}}</h2>

			<select data-ng-options="stg.kurzbzlang for stg in aqr.studiengaenge" data-ng-model="aqr.selectedStudiengang"></select>
			<select data-ng-options="stsem.studiensemester_kurzbz for stsem in aqr.studiensemester" data-ng-model="aqr.selectedStudiensemester"></select>
			<span ng-if="aqr.selectedStudienplan"><select data-ng-options="stpl.bezeichnung for stpl in aqr.studienplaene" data-ng-model="aqr.selectedStudienplan"></select></span><span ng-if="!aqr.selectedStudienplan" style="color:#A33;">Keinen Studienplan gefunden!</span>
			<span ng-if="aqr.studenten.length == 1">{{aqr.studenten.length}} Student</span>
			<span ng-if="aqr.studenten.length > 1">{{aqr.studenten.length}} Studenten</span>
			<span ng-if="aqr.studenten.length < 1">keine Student</span>

			<h3>Auswahl</h3>
			<table ts-wrapper>
				<thead>
					<tr>
						<th ts-criteria="prestudent_id">ID</th>
						<th ts-criteria="vorname">Vorname</th>
						<th ts-criteria="nachname">Nachname</th>
						<th ts-criteria="bezeichnung" ts-default="descending">ZGV Gruppe</th>
						<th ts-criteria="rt_gesamtpunkte|parseFloat" ts-default="descending">RT Gesamt</th>
						<th ts-criteria="laststatus">Status</th>
						<th ng-if="aqr.selectedStudienplan.apz">{{aqr.choosenStuds}}/{{aqr.selectedStudienplan.apz}}</th>
						<th ng-if="!aqr.selectedStudienplan.apz">{{aqr.choosenStuds}}/Keine APZ</th>
					</tr>
				</thead>
				<tbody>
					<tr ng-repeat="stud in aqr.studenten track by stud.prestudent_id" ng-if="stud.applicant" ng-click="aqr.countChoosen()" ts-repeat ts-hide-no-data ng-class="{true:'applicant', false:'no_applicant', undefined:'no_applicant'}[stud.applicant]"><!-- "{applicant, no_applicant : stud.applicant}">-->
						<td>{{stud.prestudent_id}}</td>
						<td>{{stud.vorname}}</td>
						<td>{{stud.nachname}}</td>
						<td ng-if="stud.bezeichnung">{{stud.bezeichnung}}</td>
						<td ng-if="!stud.bezeichnung" style="font-weight: bold;">Keine Angabe</td>
						<td>{{stud.rt_gesamtpunkte}}</td>
						<td>{{stud.laststatus}}</td>
						<td>
							<input ng-if="stud.applicant" type="checkbox" ng-model="stud.selected"/>
							<input ng-if="!stud.applicant" type="checkbox" ng-model="stud.selected" disabled="disabled"/>
						</td>
					</tr>
				</tbody>
			</table>

			<input style="float:right;" type="button" value="Annehmen" ng-click="aqr.submit()"/>

			<h3>Bereits aufgenommene</h3>
			<table ts-wrapper>
				<thead>
					<tr>
						<th ts-criteria="prestudent_id">ID</th>
						<th ts-criteria="vorname">Vorname</th>
						<th ts-criteria="nachname">Nachname</th>
						<th ts-criteria="bezeichnung" ts-default="descending">ZGV Gruppe</th>
						<th ts-criteria="rt_gesamtpunkte|parseFloat" ts-default="descending">RT Gesamt</th>
						<th ts-criteria="laststatus">Status</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<tr ng-repeat="stud in aqr.studenten track by stud.prestudent_id" ng-if="!stud.applicant" ng-click="aqr.countChoosen()" ts-repeat ts-hide-no-data ng-class="{true:'applicant', false:'no_applicant', undefined:'no_applicant'}[stud.applicant]"><!-- "{applicant, no_applicant : stud.applicant}">-->
						<td>{{stud.prestudent_id}}</td>
						<td>{{stud.vorname}}</td>
						<td>{{stud.nachname}}</td>
						<td ng-if="stud.bezeichnung">{{stud.bezeichnung}}</td>
						<td ng-if="!stud.bezeichnung" style="font-weight: bold;">Keine Angabe</td>
						<td>{{stud.rt_gesamtpunkte}}</td>
						<td>{{stud.laststatus}}</td>
						<td>
							<input ng-if="stud.applicant" type="checkbox" ng-model="stud.selected"/>
							<input ng-if="!stud.applicant" type="checkbox" ng-model="stud.selected" disabled="disabled"/>
						</td>
					</tr>
				</tbody>
			</table>

		</div>
	</body>
</html>
