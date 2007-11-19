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

$conn=pg_connect(CONN_STRING)
	or die("Connection zur FH-Complete Datenbank fehlgeschlagen");
if (!$conn_ext=mssql_connect (STPDB_SERVER, STPDB_USER, STPDB_PASSWD))
	die('Fehler beim Verbindungsaufbau!');
mssql_select_db(STPDB_DB, $conn_ext);

//set_time_limit(60);

//$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$error_log_fas='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_update=0;
$anzahl_fehler=0;
$plausi='';

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
			daPraxisBeginnDat	Timestamp,
			daPraxisEndeDat	Timestamp,
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
			daGebDat	Timestamp,
			chGebOrt	Varchar(256),
			chAutoKennzeichen	Varchar(256),
			NIU_daParkenBis	Timestamp,
			meBemerkung	text,
			chKtoNr	Varchar(256),
			chBankBezeichnung	Varchar(256),
			chBLZ	Varchar(256),
			daEintrittDat	Timestamp,
			inPIN	integer,
			inChipTyp	integer,
			inChipSerNr	integer,
			chSpindNr	Varchar(256),
			chUserName	Varchar(256),
			inKinder	integer,
			chSVNr	Varchar(256),
			chIdentifikationsDokument	Varchar(256),
			chMatrikelNr	Varchar(256),
			daMaturaDat	Timestamp,
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
			daPruefungsDat	Timestamp,
			meBeschreibung	text,
			_PersonLB	integer,
			_cxBeurteilungsStufeDiplArbeit	integer,
			meErstbeurteilung	text,
			meZweitbeurteilung	text,
			daArbeitsVergabeDat	Timestamp,
			_LVFachStud	integer,
			_LVFachLeitung	integer,
			_cxBeurteilungsStufeGesamt	integer,
			_cxBeurteilungsStufeLV1	integer,
			_cxBeurteilungsStufeLV2	integer,
			daAnmeldeDat	Timestamp,
			daStudienberechtPruefDat	Timestamp,
			chStudienberechtPruefFach	Varchar(256),
			meZusatzQualifikation	text,
			daTerminAufneVerf	Timestamp,
			chBemerkungTerminAufnVerf	Varchar(256),
			inGrp	integer,
			chGrp	Varchar(256),
			daSVAnmeldeDat	Timestamp,
			daSVAbmeldeDat	Timestamp,
			chThema	Varchar(256),
			daPruefTeil1dat	Timestamp,
			_cxGebBundesland	integer,
			_GebLand	integer,
			_Staatsbuerger	integer,
			chErsatzKZ	Varchar(256),
			_cxZugangOld	integer,
			_cxZugangFHMag	integer,
			daZugangFHMagDat	Timestamp,
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
			constraint "pk_tbl_sync_stp_person" primary key ("__person"));';
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

$qry=' SELECT TOP 1 bomitgliedentwicklung,boqualnachweis,__person,_cxberufstaetigkeit,_cxbesch1code,_cxbesch2code,_cxbesqual,_cxbundesland,_cxfamilienstand,_cxgebbundesland,_cxgeschlecht,_cxstudstatus,_cxzugang,_cxzugangfhmag,_gebland,_personpraxisbetreuer,_personpraxisfirma,_staat,_staatsbuerger,_stgorgform,_stgvertiefung,bohabilitation,bohauptberuf,briefanrede,chadrbemerkung,chbankbezeichnung,chblz,chemailadresse,chemailbemerkung,chersatzkz,chfirma,chgebort,chhausnr,chhomepage,chkalendersemstataend,chklappe,chktonr,chmatrikelnr,chnachname,chnummer,chort,chplz,chstrasse,chsvnr,chtelbemerkung,chtitel,chusername,chvorname,chvorwahl,daeintrittdat,dagebdat,damaturadat,dapraxisbeginndat,dapraxisendedat,datenquelle,dazugangfhmagdat,flpraxisentgelt,hoechsteausbildung,inausmassbesch,inkinder,inpraxiswochenstd,instudiensemester,mepraxisbeschreibung,personalnr,_cxpersontyp,_cxzugangold,_personschule,aggstg,boanmeldegebuehrbez,bodeutschsehrgut,bodinmgew,bodivmgew,bodonmgew,bodovmgew,boemailfhweb,boformalleinerhalter,boformalleinverdiener,boformfreibetragsbescheid,boformpendlerpauschale,bofrnmgew,bofrvmgew,bominmgew,bomivmgew,bomonmgew,bomovmgew,bopraesenzdienst,bopraxisvollzeit,bostdgeblockt,chautokennzeichen,chberufstitel,chgattin,chidentifikationsdokument,chparkberechtigung,chspindnr,chvenia,chvertiefungzusatz,inchipsernr,inchiptyp,infachbereich,inpin,meausbildung,mebemerkung,meberufstaetigkeit,megewzeit,mekinder,mepublikationen,niu_daparkenbis,olfoto,originalid,position,_cxbeurteilungsstufediplarbeit,_cxbeurteilungsstufegesamt,_cxbeurteilungsstufekommipruef,_cxbeurteilungsstufelv1,_cxbeurteilungsstufelv2,_cxdiplomarbeitmotiv,_cxthemenquelle,_gegenstandnichttech,_gegenstandtech,_lvfachleitung,_lvfachstud,_personlb,_personlb2,_pruefernichttech,_pruefertech,_vorsitzender,chbemerkungterminaufnverf,chgrp,chlfdnr,chpraxisfirmatext,chpraxiskalendersemester,chpraxisortengl,chstudienberechtprueffach,chthema,chthemaengl,daanmeldedat,daarbeitsvergabedat,dapruefteil1dat,dapruefungsdat,dastudienberechtpruefdat,dasvabmeldedat,dasvanmeldedat,daterminaufneverf,ingrp,inpraxisstudiensemester,mebeschreibung,meerstbeurteilung,mepraxisbeschreibungengl,mezusatzqualifikation,mezweitbeurteilung,niu_chthema
		FROM person;';

if($result_ext = mssql_query($conn_ext, $qry))
{
	while($row=mssql_fetch_object($result_ext))
	{
		$qry="INSERT INTO sync.stp_person (bomitgliedentwicklung,boqualnachweis,__person,_cxberufstaetigkeit,_cxbesch1code,_cxbesch2code,_cxbesqual,_cxbundesland,_cxfamilienstand,_cxgebbundesland,_cxgeschlecht,_cxstudstatus,_cxzugang,_cxzugangfhmag,_gebland,_personpraxisbetreuer,_personpraxisfirma,_staat,_staatsbuerger,_stgorgform,_stgvertiefung,bohabilitation,bohauptberuf,briefanrede,chadrbemerkung,chbankbezeichnung,chblz,chemailadresse,chemailbemerkung,chersatzkz,chfirma,chgebort,chhausnr,chhomepage,chkalendersemstataend,chklappe,chktonr,chmatrikelnr,chnachname,chnummer,chort,chplz,chstrasse,chsvnr,chtelbemerkung,chtitel,chusername,chvorname,chvorwahl,daeintrittdat,dagebdat,damaturadat,dapraxisbeginndat,dapraxisendedat,datenquelle,dazugangfhmagdat,flpraxisentgelt,hoechsteausbildung,inausmassbesch,inkinder,inpraxiswochenstd,instudiensemester,mepraxisbeschreibung,personalnr,_cxpersontyp,_cxzugangold,_personschule,aggstg,boanmeldegebuehrbez,bodeutschsehrgut,bodinmgew,bodivmgew,bodonmgew,bodovmgew,boemailfhweb,boformalleinerhalter,boformalleinverdiener,boformfreibetragsbescheid,boformpendlerpauschale,bofrnmgew,bofrvmgew,bominmgew,bomivmgew,bomonmgew,bomovmgew,bopraesenzdienst,bopraxisvollzeit,bostdgeblockt,chautokennzeichen,chberufstitel,chgattin,chidentifikationsdokument,chparkberechtigung,chspindnr,chvenia,chvertiefungzusatz,inchipsernr,inchiptyp,infachbereich,inpin,meausbildung,mebemerkung,meberufstaetigkeit,megewzeit,mekinder,mepublikationen,niu_daparkenbis,originalid,position,_cxbeurteilungsstufediplarbeit,_cxbeurteilungsstufegesamt,_cxbeurteilungsstufekommipruef,_cxbeurteilungsstufelv1,_cxbeurteilungsstufelv2,_cxdiplomarbeitmotiv,_cxthemenquelle,_gegenstandnichttech,_gegenstandtech,_lvfachleitung,_lvfachstud,_personlb,_personlb2,_pruefernichttech,_pruefertech,_vorsitzender,chbemerkungterminaufnverf,chgrp,chlfdnr,chpraxisfirmatext,chpraxiskalendersemester,chpraxisortengl,chstudienberechtprueffach,chthema,chthemaengl,daanmeldedat,daarbeitsvergabedat,dapruefteil1dat,dapruefungsdat,dastudienberechtpruefdat,dasvabmeldedat,dasvanmeldedat,daterminaufneverf,ingrp,inpraxisstudiensemester,mebeschreibung,meerstbeurteilung,mepraxisbeschreibungengl,mezusatzqualifikation,mezweitbeurteilung,niu_chthema)
				VALUES ('$row->bomitgliedentwicklung','$row->boqualnachweis','$row->__person','$row->_cxberufstaetigkeit','$row->_cxbesch1code','$row->_cxbesch2code','$row->_cxbesqual','$row->_cxbundesland','$row->_cxfamilienstand','$row->_cxgebbundesland','$row->_cxgeschlecht','$row->_cxstudstatus','$row->_cxzugang','$row->_cxzugangfhmag','$row->_gebland','$row->_personpraxisbetreuer','$row->_personpraxisfirma','$row->_staat','$row->_staatsbuerger','$row->_stgorgform','$row->_stgvertiefung','$row->bohabilitation','$row->bohauptberuf','$row->briefanrede','$row->chadrbemerkung','$row->chbankbezeichnung','$row->chblz','$row->chemailadresse','$row->chemailbemerkung','$row->chersatzkz','$row->chfirma','$row->chgebort','$row->chhausnr','$row->chhomepage','$row->chkalendersemstataend','$row->chklappe','$row->chktonr','$row->chmatrikelnr','$row->chnachname','$row->chnummer','$row->chort','$row->chplz','$row->chstrasse','$row->chsvnr','$row->chtelbemerkung','$row->chtitel','$row->chusername','$row->chvorname','$row->chvorwahl','$row->daeintrittdat','$row->dagebdat','$row->damaturadat','$row->dapraxisbeginndat','$row->dapraxisendedat','$row->datenquelle','$row->dazugangfhmagdat','$row->flpraxisentgelt','$row->hoechsteausbildung','$row->inausmassbesch','$row->inkinder','$row->inpraxiswochenstd','$row->instudiensemester','$row->mepraxisbeschreibung','$row->personalnr','$row->_cxpersontyp','$row->_cxzugangold','$row->_personschule','$row->aggstg','$row->boanmeldegebuehrbez','$row->bodeutschsehrgut','$row->bodinmgew','$row->bodivmgew','$row->bodonmgew','$row->bodovmgew','$row->boemailfhweb','$row->boformalleinerhalter','$row->boformalleinverdiener','$row->boformfreibetragsbescheid','$row->boformpendlerpauschale','$row->bofrnmgew','$row->bofrvmgew','$row->bominmgew','$row->bomivmgew','$row->bomonmgew','$row->bomovmgew','$row->bopraesenzdienst','$row->bopraxisvollzeit','$row->bostdgeblockt','$row->chautokennzeichen','$row->chberufstitel','$row->chgattin','$row->chidentifikationsdokument','$row->chparkberechtigung','$row->chspindnr','$row->chvenia','$row->chvertiefungzusatz','$row->inchipsernr','$row->inchiptyp','$row->infachbereich','$row->inpin','$row->meausbildung','$row->mebemerkung','$row->meberufstaetigkeit','$row->megewzeit','$row->mekinder','$row->mepublikationen','$row->niu_daparkenbis','$row->originalid','$row->position','$row->_cxbeurteilungsstufediplarbeit','$row->_cxbeurteilungsstufegesamt','$row->_cxbeurteilungsstufekommipruef','$row->_cxbeurteilungsstufelv1','$row->_cxbeurteilungsstufelv2','$row->_cxdiplomarbeitmotiv','$row->_cxthemenquelle','$row->_gegenstandnichttech','$row->_gegenstandtech','$row->_lvfachleitung','$row->_lvfachstud','$row->_personlb','$row->_personlb2','$row->_pruefernichttech','$row->_pruefertech','$row->_vorsitzender','$row->chbemerkungterminaufnverf','$row->chgrp','$row->chlfdnr','$row->chpraxisfirmatext','$row->chpraxiskalendersemester','$row->chpraxisortengl','$row->chstudienberechtprueffach','$row->chthema','$row->chthemaengl','$row->daanmeldedat','$row->daarbeitsvergabedat','$row->dapruefteil1dat','$row->dapruefungsdat','$row->dastudienberechtpruefdat','$row->dasvabmeldedat','$row->dasvanmeldedat','$row->daterminaufneverf','$row->ingrp','$row->inpraxisstudiensemester','$row->mebeschreibung','$row->meerstbeurteilung','$row->mepraxisbeschreibungengl','$row->mezusatzqualifikation','$row->mezweitbeurteilung','$row->niu_chthema')";
		if(!$result = pg_query($conn, $qry))
		{
		}

	}
}

?>
</body>
</html>