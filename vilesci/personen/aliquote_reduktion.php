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
				if (b.rt_gesamtpunkte == null || parseFloat(a.rt_gesamtpunkte) > parseFloat(b.rt_gesamtpunkte))
					return -1;
				else if (a.rt_gesamtpunkte == null || parseFloat(a.rt_gesamtpunkte) < parseFloat(b.rt_gesamtpunkte))
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
				aqr.selectedStudienplatz = "";
				aqr.choosenStuds = 0;
				aqr.studenten = [];
				aqr.studiengaenge = [];
				aqr.studiensemester = [];
				aqr.studienplaetze = [];
				aqr.zgvElems = [];
				aqr.actualSequence = 1;
				SERVICE_TARGET = "aliquote_reduktion.json.php";

				if(!aqr.studiensemester_kurzbz)
					die("Es wurde kein Studiensemester angegeben");

				if(!aqr.selectedStudiengang.studiengang_kz)
					die("Es wurde kein Studiengang angeben");






				//bei jeder änderung des studiensemesters, sollen die studienplaetze erneut geholt werden
				$scope.$watch('aqr.selectedStudiengang', function (){aqr.loadStudienPlaene();},true);
				$scope.$watch('aqr.selectedStudiensemester', function (){aqr.loadStudienPlaene();},true);

				$scope.$watch('aqr.selectedStudienplatz', function (){aqr.loadStudenten();},true);


				AJAXCall({action:"getStudiensemester",studiengang_kz:aqr.selectedStudiengang.studiengang_kz},function(res){aqr.studiensemester=res;aqr.setStudiensemester(aqr.studiensemester_kurzbz);$scope.$apply();});
				AJAXCall({action:"getStudiengaenge",studiengang_kz:aqr.selectedStudiengang.studiengang_kz},function(res){aqr.studiengaenge=res;aqr.studiengaenge.sort(sortStudiengaenge);aqr.setStudiengang(aqr.selectedStudiengang.studiengang_kz);$scope.$apply();});



				aqr.submit = function()
				{
					if(aqr.choosenStuds < aqr.selectedStudienplatz.apz)
					{
						if(!confirm("Es wurden zu wenig Studenten gewählt!"))
							return;
					}
					else if(aqr.choosenStuds > aqr.selectedStudienplatz.apz)
					{
						if(!confirm("Es wurden zu viel Studenten gewählt!"))
							return;
					}
					else
					{
						if(!confirm("Sind Sie sicher, dass Sie die ausgewählten Personen aufnehmen wollen?"))
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

					AJAXCall({action:"setAufgenommene",studiengang_kz:aqr.selectedStudiengang.studiengang_kz,prestudent_ids:JSON.stringify(prestudent_ids),studienplan_id:aqr.selectedStudienplatz.studienplan_id},function(res){aqr.loadStudenten();});
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
					var found = false;
					aqr.studiengaenge.forEach(function(i)
					{
						if(i.studiengang_kz == studiengang_kz)
						{
							aqr.selectedStudiengang = i;
							found = true;
						}
					});
					if(!found)
						alert("Studiengang nicht gefunden!");
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
						aqr.selectedStudienplatz = "";
						aqr.studienplaetze.clear;
						AJAXCall({action:"getStudienplaetze",studiengang_kz:aqr.selectedStudiengang.studiengang_kz,studiensemester_kurzbz:aqr.selectedStudiensemester.studiensemester_kurzbz},function(res)
						{
							aqr.studienplaetze=res;
							aqr.selectedStudienplatz=aqr.studienplaetze[0];
							$scope.$apply();
						});
					}
				}

				aqr.loadStudenten = function()
				{
					aqr.actualSequence = 1;
					aqr.studenten=[];
					if(aqr.selectedStudienplatz && aqr.selectedStudienplatz.studienplan_id)
						AJAXCall({action:"getStudenten",studiengang_kz:aqr.selectedStudiengang.studiengang_kz,studienplan_id:aqr.selectedStudienplatz.studienplan_id,studiensemester_kurzbz:aqr.selectedStudiensemester.studiensemester_kurzbz},function(res)
						{
							aqr.studenten=res;
							aqr.studenten.forEach(function(i)
							{
								if((i.laststatus=='Wartender'||i.laststatus=='Bewerber') && i.rt_gesamtpunkte !== null  && i.rt_gesamtpunkte > 0)
									i.applicant = true;
								else if((i.laststatus=='Student'||i.laststatus=='Aufgenommener') && aqr.selectedStudienplatz.studienplan_id == i.studienplan_id)
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

				aqr.getAcceptedCount = function(zgvGruppe)
				{
					var ret = 0;
					aqr.studenten.forEach(function(i)
					{
						if((i.laststatus=='Student'||i.laststatus=='Aufgenommener') && aqr.selectedStudienplatz.studienplan_id == i.studienplan_id && (zgvGruppe === undefined || zgvGruppe === i.bezeichnung))
							ret++;
					});
					return ret;
				}

				aqr.doPreselection = function()
				{
					aqr.zgvElems = [];
					if(parseInt(aqr.selectedStudienplatz.apz) >= 0)    // we only preselect, if we have an APZ
					{
						aqr.studenten.sort(sortStudentenRTP);

						var zgvs = aqr.getZGVArray();
						var neededStudentsCount = aqr.selectedStudienplatz.apz - aqr.getAcceptedCount();
						var perZGV = aqr.studenten.length;
						var zgvElems = [];
						var allCountedStudents = [];

						aqr.studenten.forEach(function(j)
						{
							if(j.rt_gesamtpunkte !== null  && j.rt_gesamtpunkte > 0)
								allCountedStudents.push(j);
						});

						var applicantCount = aqr.selectedStudienplatz.apz;
						if(applicantCount > allCountedStudents.length)
							applicantCount = allCountedStudents.length;

						zgvs.forEach(function(i)
						{
							var applicantsFromZGV = [];

							aqr.studenten.forEach(function(j)
							{
								if((j.rt_gesamtpunkte !== null  && j.rt_gesamtpunkte > 0) && j.bezeichnung === i)
									applicantsFromZGV.push(j);
							});

							// calculate the aliquote reduction for every ZGV
							var percent = applicantsFromZGV.length / allCountedStudents.length * 100;
							var neededFromZGV = (applicantCount / 100 * percent) - aqr.getAcceptedCount(i);

							if(neededFromZGV < 0)
								neededFromZGV = 0;

							zgvElems.push({name:i, needed:neededFromZGV, percent:percent, accepted: aqr.getAcceptedCount(i), overallNeeded: neededFromZGV + aqr.getAcceptedCount(i)});
						});

						// calculate the already distributed students
						var residual = 0;
						zgvElems.forEach(function(i)
						{
							residual += parseInt(i.needed);
						});

						// calculate the difference from needed to already distributed
						var resDiff = neededStudentsCount - residual;

						// distribute the remaining places on the present ZGVs
						while(resDiff > 0 && zgvElems.length > 0/*if there are no zgvs(to prevent a deadlock)*/)
						{
							zgvElems.forEach(function(i)
							{
								if(resDiff > 0)
								{
									i.overallNeeded ++;
									i.needed ++;
									resDiff --;
								}
							});
						}
						aqr.zgvElems = JSON.parse(JSON.stringify(zgvElems));
						aqr.recursiveChoose(neededStudentsCount, zgvElems);
					}
				}

				aqr.getActualSeq = function()
				{
					aqr.actualSequence ++;
					return aqr.actualSequence;
				}
				aqr.setSequence = function(elem)
				{
					if(!elem.seqPlace)
					{
						elem.seqPlace = aqr.actualSequence;
					}
					aqr.actualSequence ++;
				}


				aqr.recursiveChoose = function(needed, zgvElems)
				{
					var beginNeeded = needed;

					//distribute the applicants to the present ZGVs
					for(var i=0; i < zgvElems.length; i++)
					{
						for(var j in aqr.studenten)
						{
							if(
									 aqr.studenten[j].laststatus!='Abgewiesener'
								&& aqr.studenten[j].laststatus!='Abbrecher'
								&& parseInt(zgvElems[i].needed) > 0
								&& aqr.studenten[j].bezeichnung == zgvElems[i].name
								&& !aqr.studenten[j].seqPlace
								&& !aqr.studenten[j].selected
								&& aqr.studenten[j].applicant)
							{
								aqr.setSequence(aqr.studenten[j]);
								zgvElems[i].needed --;
								aqr.studenten[j].selected = true;
								needed --;
								break;
							}
						}
					}

					//if we are finished or the ZGVs are full
					if(needed < 1 || beginNeeded == needed)
					{
						//distribute the rest of the applicants, WITH a ZGV group
						for(var j in aqr.studenten)
						{
							if(!aqr.studenten[j].selected && aqr.studenten[j].bezeichnung && aqr.studenten[j].applicant)
							{
								aqr.setSequence(aqr.studenten[j]);
								if(needed > 0 && (aqr.studenten[j].laststatus=='Wartender'||aqr.studenten[j].laststatus=='Bewerber'))
								{
									aqr.studenten[j].selected = true;
									needed --;
								}
							}
						}
						//distribute the rest of the applicants, WITHOUT a ZGV group
						for(var j in aqr.studenten)
						{
							if(!aqr.studenten[j].selected && !aqr.studenten[j].bezeichnung && aqr.studenten[j].applicant)
							{
								aqr.setSequence(aqr.studenten[j]);
								if(needed > 0 && (aqr.studenten[j].laststatus=='Wartender'||aqr.studenten[j].laststatus=='Bewerber'))
								{
									aqr.studenten[j].selected = true;
									needed --;
								}
							}
						}
						if(needed > 0)
							alert("Es werden mehr Bewerber benötigt, als es gibt!");
						return;
					}
					else
					{
						aqr.recursiveChoose(needed, zgvElems);
					}
				}

				aqr.download = function()
				{
					var filteredStudents = [];


					aqr.studenten.forEach(function(i)
					{
						if(i.applicant)
						{
							filteredStudents.push(i);
						}
					});


					var form = document.createElement("form");
					form.setAttribute("method", "post");
					form.setAttribute("action", "aliquote_reduktion.json.php");
					form.setAttribute("target", "view");

					var hiddenField = document.createElement("input");
					hiddenField.setAttribute("type", "hidden");
					hiddenField.setAttribute("name", "action");
					hiddenField.setAttribute("value", "dlTable");
					form.appendChild(hiddenField);

					var hiddenField = document.createElement("input");
					hiddenField.setAttribute("type", "hidden");
					hiddenField.setAttribute("name", "studiengang_kz");
					hiddenField.setAttribute("value", aqr.selectedStudiengang.studiengang_kz);
					form.appendChild(hiddenField);

					var hiddenField = document.createElement("input");
					hiddenField.setAttribute("type", "hidden");
					hiddenField.setAttribute("name", "students");
					hiddenField.setAttribute("value", JSON.stringify(filteredStudents));
					form.appendChild(hiddenField);

					document.body.appendChild(form);


					form.submit();
				}
			});
		</script>
	</head>
	<body class="Background_main">
		<div ng-controller="aliqRedController as aqr" ng-app="aliqRed">
			<h2>{{aqr.name}} {{aqr.selectedStudiengang.studiengang_kz}}/{{aqr.selectedStudienplatz.studienplan_id}}</h2>

			<select data-ng-options="stg.kurzbzlang for stg in aqr.studiengaenge" data-ng-model="aqr.selectedStudiengang"></select>
			<select data-ng-options="stsem.studiensemester_kurzbz for stsem in aqr.studiensemester" data-ng-model="aqr.selectedStudiensemester"></select>
			<span ng-if="aqr.selectedStudienplatz"><select data-ng-options="stpl.bezeichnung for stpl in aqr.studienplaetze" data-ng-model="aqr.selectedStudienplatz"></select></span><span ng-if="!aqr.selectedStudienplatz" style="color:#A33;">Keinen Studienplan gefunden!</span>
			<span ng-if="aqr.selectedStudienplatz && aqr.studenten.length == 1">{{aqr.studenten.length}} Student</span>
			<span ng-if="aqr.selectedStudienplatz && aqr.studenten.length > 1">{{aqr.studenten.length}} Studenten</span>
			<span ng-if="aqr.selectedStudienplatz && aqr.studenten.length < 1">keine Studenten</span>

			<h3>Auswahl</h3>
			<table ts-wrapper>
				<thead>
					<tr>
						<th ts-criteria="prestudent_id">ID</th>
						<th ts-criteria="nachname">Nachname</th>
						<th ts-criteria="vorname">Vorname</th>
						<th ts-criteria="bezeichnung">ZGV Gruppe</th>
						<th ts-criteria="seqPlace|parseInt" ts-default="ascending">Reihung</th>
						<th ts-criteria="rt_punkte1|parseFloat">RT Punkte 1</th>
						<th ts-criteria="rt_punkte2|parseFloat">RT Punkte 2</th>
						<th ts-criteria="rt_gesamtpunkte|parseFloat" ts-default="ascending">RT Gesamt</th>
						<th ts-criteria="interviewbogen">Interviewbogen</th>
						<th ts-criteria="anmerkung">Anmerkung/Prio</th>
						<th ts-criteria="laststatus">Status</th>
						<th ng-if="aqr.selectedStudienplatz.apz">{{aqr.choosenStuds}}/{{aqr.selectedStudienplatz.apz}}</th>
						<th ng-if="!aqr.selectedStudienplatz.apz">{{aqr.choosenStuds}}/Keine APZ</th>
					</tr>
				</thead>
				<tbody>
					<tr ng-repeat="stud in aqr.studenten track by stud.prestudent_id" ng-if="stud.applicant" ng-click="aqr.countChoosen()" ts-repeat ts-hide-no-data ng-class="{true:'applicant', false:'no_applicant', undefined:'no_applicant'}[stud.applicant]"><!-- "{applicant, no_applicant : stud.applicant}">-->
						<td>{{stud.prestudent_id}}</td>
						<td>{{stud.nachname}}</td>
						<td>{{stud.vorname}}</td>
						<td ng-if="stud.bezeichnung">{{stud.bezeichnung}}</td>
						<td ng-if="!stud.bezeichnung" style="font-weight: bold;">Keine Angabe</td>
						<td>{{stud.seqPlace}}</td>
						<td>{{stud.rt_punkte1}}</td>
						<td>{{stud.rt_punkte2}}</td>
						<td>{{stud.rt_gesamtpunkte}}</td>
						<td>{{stud.interviewbogen?'vorhanden':'nicht vorhanden'}}</td>
						<td>{{stud.anmerkung}}</td>
						<td>{{stud.laststatus}}</td>
						<td>
							<input ng-if="stud.applicant" type="checkbox" ng-model="stud.selected"/>
							<input ng-if="!stud.applicant" type="checkbox" ng-model="stud.selected" disabled="disabled"/>
						</td>
					</tr>
				</tbody>
			</table>

			<input style="float:right;" type="button" value="Annehmen" ng-click="aqr.submit()"/>
			<input style="float:right;" type="button" value="Download" ng-click="aqr.download()"/>
			<h3>ZGV Informationen</h3>
			<table ts-wrapper>
				<thead>
					<tr>
						<th ts-criteria="name" ts-default="ascending">Name</th>
						<th ts-criteria="percent">Prozent</th>
						<th ts-criteria="overallNeeded">Insgesamt benötigt</th>
						<th ts-criteria="accepted">Bereits aufgenommen</th>
						<th ts-criteria="needed">Noch benötigte Personen</th>
					</tr>
				</thead>
				<tbody>
					<tr ng-repeat="zgv in aqr.zgvElems" ts-repeat ts-hide-no-data>
						<td>{{zgv.name}}</td>
						<td>{{zgv.percent | number: 2}}%</td>
						<td>{{zgv.overallNeeded | parseInt}}</td>
						<td>{{zgv.accepted}}</td>
						<td>{{zgv.needed | parseInt}}</td>
					</tr>
					<tr>
						<th>Gesamt</th>
						<th></th>
						<th>{{aqr.selectedStudienplatz.apz}}</th>
						<th>{{aqr.getAcceptedCount()}}</th>
						<th>{{aqr.selectedStudienplatz.apz - aqr.getAcceptedCount()}}</th>
					</tr>
				</tbody>
			</table>

			<h3>Restliche Studenten</h3>
			<table ts-wrapper>
				<thead>
					<tr>
						<th ts-criteria="prestudent_id">ID</th>
						<th ts-criteria="nachname">Nachname</th>
						<th ts-criteria="vorname">Vorname</th>
						<th ts-criteria="bezeichnung">ZGV Gruppe</th>
						<th ts-criteria="seqPlace|parseInt" ts-default="ascending">Reihung</th>
						<th ts-criteria="rt_punkte1|parseFloat">RT Punkte 1</th>
						<th ts-criteria="rt_punkte2|parseFloat">RT Punkte 2</th>
						<th ts-criteria="rt_gesamtpunkte|parseFloat">RT Gesamt</th>
						<th ts-criteria="interviewbogen">Interviewbogen</th>
						<th ts-criteria="anmerkung">Anmerkung/Prio</th>
						<th ts-criteria="laststatus">Status</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<tr ng-repeat="stud in aqr.studenten track by stud.prestudent_id" ng-if="!stud.applicant" ng-click="aqr.countChoosen()" ts-repeat ts-hide-no-data ng-class="{true:'applicant', false:'no_applicant', undefined:'no_applicant'}[stud.applicant]"><!-- "{applicant, no_applicant : stud.applicant}">-->
						<td>{{stud.prestudent_id}}</td>
						<td>{{stud.nachname}}</td>
						<td>{{stud.vorname}}</td>
						<td ng-if="stud.bezeichnung">{{stud.bezeichnung}}</td>
						<td ng-if="!stud.bezeichnung" style="font-weight: bold;">Keine Angabe</td>
						<td>{{stud.seqPlace}}</td>
						<td>{{stud.rt_punkte1}}</td>
						<td>{{stud.rt_punkte2}}</td>
						<td>{{stud.rt_gesamtpunkte}}</td>
						<td>{{stud.interviewbogen?'vorhanden':'nicht vorhanden'}}</td>
						<td>{{stud.anmerkung}}</td>
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
