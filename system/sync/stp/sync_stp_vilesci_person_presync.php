<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Kopiert personen TAbelle von FH-DB StPoelten
//*
//*

require_once('sync_config.inc.php');

$starttime=time();
$conn=pg_connect(CONN_STRING)
	or die("Connection zur FH-Complete Datenbank fehlgeschlagen");
if (!$conn_ext=mssql_connect (STPDB_SERVER, STPDB_USER, STPDB_PASSWD))
	die('Fehler beim Verbindungsaufbau!');
mssql_select_db(STPDB_DB, $conn_ext);



// Sync-Tabelle fuer Personen checken
if (!@pg_query($conn,'SELECT * FROM sync.stp_person LIMIT 1;'))
{
	$sql='CREATE TABLE sync.stp_person
		(
			__Person	integer NOT NULL,
			_cxGeschlecht	integer,
			_cxPersonTyp	integer,
			boHabilitation	boolean,
			boPraesenzdienst	boolean,
			boAnmeldeGebuehrBez	boolean,
			boDeutschSehrGut	boolean,
			boPraxisVollzeit	boolean,
			boMitgliedEntwicklung	boolean,
			boHauptberuf	boolean,
			boQualNachweis	boolean,
			chGattin	Varchar(256),
			boFormAlleinverdiener	boolean,
			boFormAlleinerhalter	boolean,
			boFormFreibetragsbescheid	boolean,
			boFormPendlerpauschale	boolean,
			boStdGeblockt	boolean,
			boMoVMGew	boolean,
			boMoNMGew	boolean,
			boDiVMGew	boolean,
			boDiNMGew	boolean,
			boMiVMGew	boolean,
			boMiNMGew	boolean,
			boDoVMGew	boolean,
			boDoNMGew	boolean,
			boFrVMGew	boolean,
			boFrNMGew	boolean,
			meGewZeit	text,
			chBerufstitel	Varchar(256),
			chParkberechtigung	Varchar(256),
			chHomepage	Varchar(256),
			_cxBundesland	integer,
			chStrasse	Varchar(256),
			chHausNr	Varchar(256),
			chPLZ	Varchar(256),
			chOrt	Varchar(256),
			_Staat	integer,
			chAdrBemerkung	Varchar(256),
			chVorwahl	Varchar(256),
			chNummer	Varchar(256),
			chKlappe	Varchar(256),
			chTelBemerkung	Varchar(256),
			chEMailAdresse	Varchar(256),
			chEMailBemerkung	Varchar(256),
			boEMailFHWeb	boolean,
			_PersonPraxisFirma	integer,
			_PersonPraxisBetreuer	integer,
			daPraxisBeginnDat	date,
			daPraxisEndeDat	date,
			mePraxisBeschreibung	text,
			inPraxisWochenStd	integer,
			flPraxisEntgelt	float,
			meAusbildung	text,
			meBerufstaetigkeit	text,
			mePublikationen	text,
			chVenia	Varchar(256),
			_cxBesQual	integer,
			inAusmassBesch	integer,
			_cxBesch1Code	integer,
			_cxBesch2Code	integer,
			meKinder	text,
			chTitel	Varchar(256),
			chVorname	Varchar(256),
			chNachname	Varchar(256),
			chFirma	Varchar(256),
			_cxFamilienstand	integer,
			daGebDat	date,
			chGebOrt	Varchar(256),
			chAutoKennzeichen	Varchar(256),
			NIU_daParkenBis	date,
			meBemerkung	text,
			chKtoNr	Varchar(256),
			chBankBezeichnung	Varchar(256),
			chBLZ	Varchar(256),
			daEintrittDat	date,
			inPIN	integer,
			inChipTyp	integer,
			inChipSerNr	integer,
			chSpindNr	Varchar(256),
			chUserName	Varchar(256),
			inKinder	integer,
			chSVNr	Varchar(256),
			chIdentifikationsDokument	Varchar(256),
			chMatrikelNr	Varchar(256),
			daMaturaDat	date,
			_cxZugang	integer,
			_cxBerufstaetigkeit	integer,
			_cxStudStatus	integer,
			chKalenderSemStatAend	Varchar(256),
			inStudienSemester	integer,
			_StgVertiefung	integer,
			_StgOrgForm	integer,
			chLfdNr	Varchar(256),
			_cxThemenQuelle	integer,
			NIU_chThema	integer,
			_cxDiplomarbeitMotiv	integer,
			daPruefungsDat	date,
			meBeschreibung	text,
			_PersonLB	integer,
			_cxBeurteilungsStufeDiplArbeit	integer,
			meErstbeurteilung	text,
			meZweitbeurteilung	text,
			daArbeitsVergabeDat	date,
			_LVFachStud	integer,
			_LVFachLeitung	integer,
			_cxBeurteilungsStufeGesamt	integer,
			_cxBeurteilungsStufeLV1	integer,
			_cxBeurteilungsStufeLV2	integer,
			daAnmeldeDat	date,
			daStudienberechtPruefDat	date,
			chStudienberechtPruefFach	Varchar(256),
			meZusatzQualifikation	text,
			daTerminAufneVerf	Timestamp,
			chBemerkungTerminAufnVerf	Varchar(256),
			inGrp	integer,
			chGrp	Varchar(256),
			daSVAnmeldeDat	date,
			daSVAbmeldeDat	date,
			chThema	Varchar(256),
			daPruefTeil1dat	date,
			_cxGebBundesland	integer,
			_GebLand	integer,
			_Staatsbuerger	integer,
			chErsatzKZ	Varchar(256),
			_cxZugangOld	integer,
			_cxZugangFHMag	integer,
			daZugangFHMagDat	date,
			inFachbereich	integer,
			_PersonLB2	integer,
			_Vorsitzender	integer,
			_PrueferTech	integer,
			_PrueferNichtTech	integer,
			_GegenstandTech	integer,
			_GegenstandNichtTech	integer,
			AggStg	integer,
			PersonalNr	integer,
			HoechsteAusbildung	integer,
			Position	Varchar(256),
			Briefanrede	Varchar(256),
			OriginalID	integer,
			_PersonSchule	integer,
			chThemaEngl	Varchar(256),
			mePraxisBeschreibungEngl	text,
			inPraxisStudienSemester	integer,
			chPraxisKalenderSemester	Varchar(256),
			chPraxisOrtEngl	Varchar(256),
			chPraxisFirmaText	Varchar(256),
			_cxBeurteilungsStufeKommiPruef	integer,
			datenquelle	integer,
			chVertiefungZusatz	Varchar(256),
			constraint "pk_tbl_sync_stp_person" primary key ("__person"));
		Grant select on sync.stp_person to group "admin";
		Grant update on sync.stp_person to group "admin";
		Grant delete on sync.stp_person to group "admin";
		Grant insert on sync.stp_person to group "admin";';
	if (!@pg_query($conn,$sql))
		echo '<strong>sync.stp_person: '.pg_last_error($conn).' </strong><BR>';
	else
		echo 'sync.stp_person wurde angelegt!<BR>';

}

/*************************
 * FAS-PORTAL - Synchronisation
 */
?>
<html>
<head>
<title>Synchro - FHDB -> FH-Complete - PreSyncPerson</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>

<?php
if (!@pg_query($conn,'DELETE FROM sync.stp_person;'))
	echo '<strong>sync.stp_person: '.pg_last_error($conn).' </strong><BR>';
else
	echo 'sync.stp_person wurde geleert!<BR>';

echo 'Daten werden geholt!<BR>';

$i=0;

$qry="SELECT bomitgliedentwicklung,boqualnachweis,__person,_cxberufstaetigkeit,_cxbesch1code,_cxbesch2code,_cxbesqual,_cxbundesland,_cxfamilienstand,_cxgebbundesland,_cxgeschlecht,_cxstudstatus,_cxzugang,_cxzugangfhmag,_gebland,_personpraxisbetreuer,_personpraxisfirma,_staat,_staatsbuerger,_stgorgform,_stgvertiefung,bohabilitation,bohauptberuf,briefanrede,chadrbemerkung,chbankbezeichnung,chblz,chemailadresse,chemailbemerkung,chersatzkz,chfirma,chgebort,chhausnr,chhomepage,chkalendersemstataend,chklappe,chktonr,chmatrikelnr,chnachname,chnummer,chort,chplz,chstrasse,chsvnr,chtelbemerkung,chtitel,chusername,chvorname,chvorwahl,convert(varchar(10),daeintrittdat,121) AS daeintrittdat,convert(varchar(10),dagebdat,121) AS dagebdat,convert(varchar(10),damaturadat,121) AS damaturadat,convert(varchar(10),dapraxisbeginndat,121) AS dapraxisbeginndat,convert(varchar(10),dapraxisendedat,121) AS dapraxisendedat,convert(varchar(10),datenquelle,121) AS datenquelle,convert(varchar(10),dazugangfhmagdat,121) AS dazugangfhmagdat,flpraxisentgelt,hoechsteausbildung,inausmassbesch,inkinder,inpraxiswochenstd,instudiensemester,mepraxisbeschreibung,personalnr,_cxpersontyp,_cxzugangold,_personschule,aggstg,boanmeldegebuehrbez,bodeutschsehrgut,bodinmgew,bodivmgew,bodonmgew,bodovmgew,boemailfhweb,boformalleinerhalter,boformalleinverdiener,boformfreibetragsbescheid,boformpendlerpauschale,bofrnmgew,bofrvmgew,bominmgew,bomivmgew,bomonmgew,bomovmgew,bopraesenzdienst,bopraxisvollzeit,bostdgeblockt,chautokennzeichen,chberufstitel,chgattin,chidentifikationsdokument,chparkberechtigung,chspindnr,chvenia,chvertiefungzusatz,inchipsernr,inchiptyp,infachbereich,inpin,meausbildung,mebemerkung,meberufstaetigkeit,megewzeit,mekinder,mepublikationen,convert(varchar(10),niu_daparkenbis,121) AS niu_daparkenbis,olfoto,originalid,position,_cxbeurteilungsstufediplarbeit,_cxbeurteilungsstufegesamt,_cxbeurteilungsstufekommipruef,_cxbeurteilungsstufelv1,_cxbeurteilungsstufelv2,_cxdiplomarbeitmotiv,_cxthemenquelle,_gegenstandnichttech,_gegenstandtech,_lvfachleitung,_lvfachstud,_personlb,_personlb2,_pruefernichttech,_pruefertech,_vorsitzender,chbemerkungterminaufnverf,chgrp,chlfdnr,chpraxisfirmatext,chpraxiskalendersemester,chpraxisortengl,chstudienberechtprueffach,chthema,chthemaengl,convert(varchar(10),daanmeldedat,121) AS daanmeldedat,convert(varchar(10),daarbeitsvergabedat,121) AS daarbeitsvergabedat,convert(varchar(10),dapruefteil1dat,121) AS dapruefteil1dat,convert(varchar(10),dapruefungsdat,121) AS dapruefungsdat,convert(varchar(10),dastudienberechtpruefdat,121) AS dastudienberechtpruefdat,convert(varchar(10),dasvabmeldedat,121) AS dasvabmeldedat,convert(varchar(10),dasvanmeldedat,121) AS dasvanmeldedat,convert(varchar(23),daterminaufneverf,121) AS daterminaufneverf,ingrp,inpraxisstudiensemester,mebeschreibung,meerstbeurteilung,mepraxisbeschreibungengl,mezusatzqualifikation,mezweitbeurteilung,niu_chthema
		FROM person;";
if($result_ext = mssql_query($qry,$conn_ext))
{
	while($row=mssql_fetch_object($result_ext))
	{
		$row->chemailadresse	=trim($row->chemailadresse," \t\n\r\0\x0B'");

		$row->chnachname	=trim(str_replace("'","\'",$row->chnachname));
		$row->mepraxisbeschreibung	=trim(str_replace("'","\'",$row->mepraxisbeschreibung));
		$row->mepraxisbeschreibungengl	=trim(str_replace("'","\'",$row->mepraxisbeschreibungengl));
		$row->chpraxisfirmatext	=trim(str_replace("'","\'",$row->chpraxisfirmatext));
		$row->chthema		=trim(str_replace("'","\'",$row->chthema));
		$row->chthemaengl	=trim(str_replace("'","\'",$row->chthemaengl));
		$row->chstrasse		=trim(str_replace("'","\'",$row->chstrasse));
		$row->chfirma		=trim(str_replace("'","\'",$row->chfirma));
		$row->chgebort		=trim(str_replace("'","\'",$row->chgebort));

		$qry="INSERT INTO sync.stp_person (bomitgliedentwicklung,boqualnachweis,__person,_cxberufstaetigkeit,_cxbesch1code,_cxbesch2code,_cxbesqual,_cxbundesland,_cxfamilienstand,_cxgebbundesland,_cxgeschlecht,_cxstudstatus,_cxzugang,_cxzugangfhmag,_gebland,_personpraxisbetreuer,_personpraxisfirma,_staat,_staatsbuerger,_stgorgform,_stgvertiefung,bohabilitation,bohauptberuf,briefanrede,chadrbemerkung,chbankbezeichnung,chblz,chemailadresse,chemailbemerkung,chersatzkz,chfirma,chgebort,chhausnr,chhomepage,chkalendersemstataend,chklappe,chktonr,chmatrikelnr,chnachname,chnummer,chort,chplz,chstrasse,chsvnr,chtelbemerkung,chtitel,chusername,chvorname,chvorwahl,daeintrittdat,dagebdat,damaturadat,dapraxisbeginndat,dapraxisendedat,datenquelle,dazugangfhmagdat,flpraxisentgelt,hoechsteausbildung,inausmassbesch,inkinder,inpraxiswochenstd,instudiensemester,mepraxisbeschreibung,personalnr,_cxpersontyp,_cxzugangold,_personschule,aggstg,boanmeldegebuehrbez,bodeutschsehrgut,bodinmgew,bodivmgew,bodonmgew,bodovmgew,boemailfhweb,boformalleinerhalter,boformalleinverdiener,boformfreibetragsbescheid,boformpendlerpauschale,bofrnmgew,bofrvmgew,bominmgew,bomivmgew,bomonmgew,bomovmgew,bopraesenzdienst,bopraxisvollzeit,bostdgeblockt,chautokennzeichen,chberufstitel,chgattin,chidentifikationsdokument,chparkberechtigung,chspindnr,chvenia,chvertiefungzusatz,inchipsernr,inchiptyp,infachbereich,inpin,meausbildung,mebemerkung,meberufstaetigkeit,megewzeit,mekinder,mepublikationen,niu_daparkenbis,originalid,position,_cxbeurteilungsstufediplarbeit,_cxbeurteilungsstufegesamt,_cxbeurteilungsstufekommipruef,_cxbeurteilungsstufelv1,_cxbeurteilungsstufelv2,_cxdiplomarbeitmotiv,_cxthemenquelle,_gegenstandnichttech,_gegenstandtech,_lvfachleitung,_lvfachstud,_personlb,_personlb2,_pruefernichttech,_pruefertech,_vorsitzender,chbemerkungterminaufnverf,chgrp,chlfdnr,chpraxisfirmatext,chpraxiskalendersemester,chpraxisortengl,chstudienberechtprueffach,chthema,chthemaengl,daanmeldedat,daarbeitsvergabedat,dapruefteil1dat,dapruefungsdat,dastudienberechtpruefdat,dasvabmeldedat,dasvanmeldedat,daterminaufneverf,ingrp,inpraxisstudiensemester,mebeschreibung,meerstbeurteilung,mepraxisbeschreibungengl,mezusatzqualifikation,mezweitbeurteilung,niu_chthema)
				VALUES ('$row->bomitgliedentwicklung','$row->boqualnachweis',".($row->__person==''?'NULL':$row->__person).",".($row->_cxberufstaetigkeit==''?'NULL':$row->_cxberufstaetigkeit).",".($row->_cxbesch1code==''?'NULL':$row->_cxbesch1code).",".($row->_cxbesch2code==''?'NULL':$row->_cxbesch2code).",".($row->_cxbesqual==''?'NULL':$row->_cxbesqual).",".($row->_cxbundesland==''?'NULL':$row->_cxbundesland).",".($row->_cxfamilienstand==''?'NULL':$row->_cxfamilienstand).",".($row->_cxgebbundesland==''?'NULL':$row->_cxgebbundesland).",".($row->_cxgeschlecht==''?'NULL':$row->_cxgeschlecht).",".($row->_cxstudstatus==''?'NULL':$row->_cxstudstatus).",".($row->_cxzugang==''?'NULL':$row->_cxzugang).",".($row->_cxzugangfhmag==''?'NULL':$row->_cxzugangfhmag).",".($row->_gebland==''?'NULL':$row->_gebland).",".($row->_personpraxisbetreuer==''?'NULL':$row->_personpraxisbetreuer).",".($row->_personpraxisfirma==''?'NULL':$row->_personpraxisfirma).",".($row->_staat==''?'NULL':$row->_staat).",".($row->_staatsbuerger==''?'NULL':$row->_staatsbuerger).",".($row->_stgorgform==''?'NULL':$row->_stgorgform).",".($row->_stgvertiefung==''?'NULL':$row->_stgvertiefung).",'$row->bohabilitation','$row->bohauptberuf','$row->briefanrede','$row->chadrbemerkung','$row->chbankbezeichnung','$row->chblz','$row->chemailadresse','$row->chemailbemerkung','$row->chersatzkz','$row->chfirma','$row->chgebort','$row->chhausnr','$row->chhomepage','$row->chkalendersemstataend','$row->chklappe','$row->chktonr','$row->chmatrikelnr','$row->chnachname','$row->chnummer','$row->chort','$row->chplz','$row->chstrasse','$row->chsvnr','$row->chtelbemerkung','$row->chtitel','$row->chusername','$row->chvorname','$row->chvorwahl',".($row->daeintrittdat==''?'NULL':"'$row->daeintrittdat'").",".($row->dagebdat==''?'NULL':"'$row->dagebdat'").",".($row->damaturadat==''?'NULL':"'$row->damaturadat'").",".($row->dapraxisbeginndat==''?'NULL':"'$row->dapraxisbeginndat'").",".($row->dapraxisendedat==''?'NULL':"'$row->dapraxisendedat'").",".($row->datenquelle==''?'NULL':"'$row->datenquelle'").",".($row->dazugangfhmagdat==''?'NULL':"'$row->dazugangfhmagdat'").",".($row->flpraxisentgelt==''?'NULL':$row->flpraxisentgelt).",".($row->hoechsteausbildung==''?'NULL':$row->hoechsteausbildung).",".($row->inausmassbesch==''?'NULL':$row->inausmassbesch).",".($row->inkinder==''?'NULL':$row->inkinder).",".($row->inpraxiswochenstd==''?'NULL':$row->inpraxiswochenstd).",".($row->instudiensemester==''?'NULL':$row->instudiensemester).",'$row->mepraxisbeschreibung',".($row->personalnr==''?'NULL':$row->personalnr).",".($row->_cxpersontyp==''?'NULL':$row->_cxpersontyp).",".($row->_cxzugangold==''?'NULL':$row->_cxzugangold).",".($row->_personschule==''?'NULL':$row->_personschule).",".($row->aggstg==''?'NULL':$row->aggstg).",'$row->boanmeldegebuehrbez','$row->bodeutschsehrgut','$row->bodinmgew','$row->bodivmgew','$row->bodonmgew','$row->bodovmgew','$row->boemailfhweb','$row->boformalleinerhalter','$row->boformalleinverdiener','$row->boformfreibetragsbescheid','$row->boformpendlerpauschale','$row->bofrnmgew','$row->bofrvmgew','$row->bominmgew','$row->bomivmgew','$row->bomonmgew','$row->bomovmgew','$row->bopraesenzdienst','$row->bopraxisvollzeit','$row->bostdgeblockt','$row->chautokennzeichen','$row->chberufstitel','$row->chgattin','$row->chidentifikationsdokument','$row->chparkberechtigung','$row->chspindnr','$row->chvenia','$row->chvertiefungzusatz',".($row->inchipsernr==''?'NULL':$row->inchipsernr).",".($row->inchiptyp==''?'NULL':$row->inchiptyp).",".($row->infachbereich==''?'NULL':$row->infachbereich).",".($row->inpin==''?'NULL':$row->inpin).",'$row->meausbildung','$row->mebemerkung','$row->meberufstaetigkeit','$row->megewzeit','$row->mekinder','$row->mepublikationen',".($row->niu_daparkenbis==''?'NULL':"'$row->niu_daparkenbis'").",".($row->originalid==''?'NULL':$row->originalid).",'$row->position',".($row->_cxbeurteilungsstufediplarbeit==''?'NULL':$row->_cxbeurteilungsstufediplarbeit).",".($row->_cxbeurteilungsstufegesamt==''?'NULL':$row->_cxbeurteilungsstufegesamt).",".($row->_cxbeurteilungsstufekommipruef==''?'NULL':$row->_cxbeurteilungsstufekommipruef).",".($row->_cxbeurteilungsstufelv1==''?'NULL':$row->_cxbeurteilungsstufelv1).",".($row->_cxbeurteilungsstufelv2==''?'NULL':$row->_cxbeurteilungsstufelv2).",".($row->_cxdiplomarbeitmotiv==''?'NULL':$row->_cxdiplomarbeitmotiv).",".($row->_cxthemenquelle==''?'NULL':$row->_cxdiplomarbeitmotiv).",".($row->_gegenstandnichttech==''?'NULL':$row->_gegenstandnichttech).",".($row->_gegenstandtech==''?'NULL':$row->_gegenstandtech).",".($row->_lvfachleitung==''?'NULL':$row->_lvfachleitung).",".($row->_lvfachstud==''?'NULL':$row->_lvfachstud).",".($row->_personlb==''?'NULL':$row->_personlb).",".($row->_personlb2==''?'NULL':$row->_personlb2).",".($row->_pruefernichttech==''?'NULL':$row->_pruefernichttech).",".($row->_pruefertech==''?'NULL':$row->_pruefertech).",".($row->_vorsitzender==''?'NULL':$row->_vorsitzender).",'$row->chbemerkungterminaufnverf','$row->chgrp','$row->chlfdnr','$row->chpraxisfirmatext','$row->chpraxiskalendersemester','$row->chpraxisortengl','$row->chstudienberechtprueffach','$row->chthema','$row->chthemaengl',".($row->daanmeldedat==''?'NULL':"'$row->daanmeldedat'").",".($row->daarbeitsvergabedat==''?'NULL':"'$row->daarbeitsvergabedat'").",".($row->dapruefteil1dat==''?'NULL':"'$row->dapruefteil1dat'").",".($row->dapruefungsdat==''?'NULL':"'$row->dapruefungsdat'").",".($row->dastudienberechtpruefdat==''?'NULL':"'$row->dastudienberechtpruefdat'").",".($row->dasvabmeldedat==''?'NULL':"'$row->dasvabmeldedat'").",".($row->dasvanmeldedat==''?'NULL':"'$row->dasvanmeldedat'").",".($row->daterminaufneverf==''?'NULL':"'$row->daterminaufneverf'").",".($row->ingrp==''?'NULL':$row->ingrp).",".($row->inpraxisstudiensemester==''?'NULL':$row->inpraxisstudiensemester).",'$row->mebeschreibung','$row->meerstbeurteilung','$row->mepraxisbeschreibungengl','$row->mezusatzqualifikation','$row->mezweitbeurteilung',".($row->niu_chthema==''?'NULL':$row->niu_chthema).");";
		if(!$result = pg_query($conn, $qry))
		{
			echo $qry.'<BR>'.pg_last_error($conn).' </strong><BR>';
		}
		if ($i%1000==0)
		{
			echo '<BR>'.$i;
			flush();
		}
		elseif($i%10==0)
		{
			echo '.';
			flush();
		}
		$i++;
	}
}
else
	echo mssql_lasterror($conn_ext);

	echo '<BR>Time elapsed: '.(time()-$starttime).' seconds!';

?>
</body>
</html>