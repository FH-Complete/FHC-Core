var LVREGEL_lvRegelTypen=''; // Array mit den Regeltypen
var LVREGELnewcounter=0; // Counter fuer neue Regeln
var LVREGELStudienplanLehrveranstaltungID=''; // ID der ausgewaehlten Lehrveranstaltungszuordnung

/**
 * Laedt die Regeln zu einer Lehrveranstaltungszuordnung
 */
function LVRegelnloadRegeln(studienplan_lehrveranstaltung_id)
{
	LVREGELStudienplanLehrveranstaltungID=studienplan_lehrveranstaltung_id;

	//$('#menueRechts').hide();


	// Laden der Regeltypen
	$.ajax(
	{
		dataType: "json",
		url: "../../soap/fhcomplete.php",
		async: false,
		data: {
				"typ": "json",
				"class": "lvregel",
				"method":	"loadLVRegelTypen"
			},
		success: function(data) {
			if(data.error=='false')
			{
				LVREGEL_lvRegelTypen=data.result;
			}
			else
			{
				alert('RegelTypen konnten nicht geladen werden:'+data.errormsg);
			}
		},
		error: loadError
	});

	// Laden der Regeln
	$.ajax(
	{
		dataType: "json",
		url: "../../soap/fhcomplete.php",
		data: {
				"typ": "json",
				"class": "lvregel",
				"method":	"getLVRegelTree",
				"parameter_0": studienplan_lehrveranstaltung_id
			},
		success: function (data) {
			if(data.error=='true')
				alert('Fehler:'+data.errormsg);
			else
				drawLVRegeln(data.return);
		},
		error: loadError
	});
}

/**
 * Zeichnet die geladenen Regeln
 */
function drawLVRegeln(data)
{
	$('#LVREGELDetailsDialog').html(getChilds(data));
	jqUi("#LVREGELDetailsDialog").dialog( "open" );
}

/**
 * Erstellt den Regelbaum
 */
function getChilds(data, parent='')
{
	var obj = '';
	obj = obj+'<ul id="lvregel_ul'+parent+'">';

	for(var i in data)
	{
		obj = obj+drawRegel(data[i][0]);

		if(!jQuery.isEmptyObject(data[i]['childs']))
			obj=obj+getChilds(data[i]['childs'], data[i][0].lvregel_id);

	}		

	obj = obj+'</ul>';

	if(parent=='')
	{
		// Hinzufuegen Button
		obj = obj+'<a href="#Hinzufuegen" title="Regel hinzufügen" onclick="addRegel(\'\'); return false;"><img src="../../skin/images/plus.png" /> Regel hinzufügen</a>';
	}
	return obj;
}

/**
 * Zeichnet den Eintrag fuer eine Regel
 */
function drawRegel(regel)
{
	var val='';

	if(regel.neu==true)
		var neustyle='class="newLVRegel"';
	else
		var neustyle='';

	val = val+'<li id="lvregel_li'+regel.lvregel_id+'" '+neustyle+'>';

	val = val+'<input size="2" type="text" value="'+regel.lvregel_id+'" />';
	val = val+'<input type="hidden" id="lvregel_lvregel_id_parent'+regel.lvregel_id+'" value="'+ClearNull(regel.lvregel_id_parent)+'" />';
	val = val+'<input type="hidden" id="lvregel_studienplan_lehrveranstaltung_id'+regel.lvregel_id+'" value="'+regel.studienplan_lehrveranstaltung_id+'" />';
	if(regel.neu==true)
	{
		val = val+'<input type="hidden" id="lvregel_neu_'+regel.lvregel_id+'" value="true" />';
	}
	else
		val = val+'<input type="hidden" id="lvregel_neu_'+regel.lvregel_id+'" value="false"/>';

	// Operator DropDown
	val = val+'<select id="lvregel_operator'+regel.lvregel_id+'">';
	val = val+'<option value="u" '+(regel.operator=='u'?'selected':'')+'>U</option>';
	val = val+'<option value="o" '+(regel.operator=='o'?'selected':'')+'>O</option>';
	val = val+'<option value="x" '+(regel.operator=='x'?'selected':'')+'>X</option>';
	val = val+'</select>';

	//LVRegelTypen
	val = val+'<select id="lvregel_lvregeltyp'+regel.lvregel_id+'">';

	for(var i in LVREGEL_lvRegelTypen)
	{
		if(LVREGEL_lvRegelTypen[i].lvregeltyp_kurzbz==regel.lvregeltyp_kurzbz)
			var selected='selected';
		else
			var selected='';

		val = val+'<option value="'+LVREGEL_lvRegelTypen[i].lvregeltyp_kurzbz+'" '+selected+'>'+LVREGEL_lvRegelTypen[i].bezeichnung+'</option>';
	}
	val = val+'</select>';

	// Parameter
	val = val+'<input type="text" size="1" id="lvregel_parameter'+regel.lvregel_id+'" value="'+ClearNull(regel.parameter)+'" />';

	// Lehrveranstaltung ID
	val = val+'<input type="text" size="4" id="lvregel_lehrveranstaltung_id'+regel.lvregel_id+'" value="'+ClearNull(regel.lehrveranstaltung_id)+'" />';

	// Speichern Button
	val = val+' <input type="button" onclick="saveRegel(\''+regel.lvregel_id+'\');return false;" value="ok">';

	if(regel.neu==true)
	{
		// Loeschen Button
		val = val+' <a href="#Loeschen" title="Regel entfernen" onclick="$(\'#lvregel_li'+regel.lvregel_id+'\').remove(); return false;"><img src="../../skin/images/delete_round.png" height="12px"/></a>';
	}
	else
	{
		// Hinzufuegen Button
		val = val+' <a href="#Hinzufuegen" title="Unterregel hinzufügen" onclick="addRegel('+regel.lvregel_id+'); return false;"><img src="../../skin/images/plus.png" /></a>';

		// Loeschen Button
		val = val+' <a href="#Loeschen" title="Regel entfernen" onclick="deleteRegel('+regel.lvregel_id+'); return false;"><img src="../../skin/images/delete_round.png" height="12px"/></a>';
	}
	val = val+'</li>';
	return val;
}

/**
 * Speichert eine Regel
 */
function saveRegel(id)
{
	var neu = $('#lvregel_neu_'+id).val();
	var lvregeltyp_kurzbz = $('#lvregel_lvregeltyp'+id+' option:selected').val();
	var parameter = $('#lvregel_parameter'+id).val();
	var lehrveranstaltung_id = $('#lvregel_lehrveranstaltung_id'+id).val();
	var operator = $('#lvregel_operator'+id+' option:selected').val();
	var studienplan_lehrveranstaltung_id = $('#lvregel_studienplan_lehrveranstaltung_id'+id).val();
	var lvregel_id_parent = $('#lvregel_lvregel_id_parent'+id).val();
	lvregel_id_parent=ClearNull(lvregel_id_parent);

	// Vorhandene Eintraege werden vor dem Speichern geladen
	if(neu=='false')
	{
		loaddata = {
			"method": "load",
			"parameter_0": id
		};
	}
	else
		loaddata={};

	savedata = {
		"lvregeltyp_kurzbz":lvregeltyp_kurzbz,
		"parameter":parameter,
		"lehrveranstaltung_id":lehrveranstaltung_id,
		"operator":operator,
		"studienplan_lehrveranstaltung_id":studienplan_lehrveranstaltung_id,
		"lvregel_id_parent":lvregel_id_parent,
		"insertvon": user,
		"updatevon": user
	};
	
	$.ajax(
	{
		dataType: "json",
		url: "../../soap/fhcomplete.php",
		type: "POST",
		data: {
				"typ": "json",
				"class": "lvregel",
				"method": "save",
				"loaddata": JSON.stringify(loaddata),
				"savedata": JSON.stringify(savedata),
			},
		success: function(data) {
			if(data.error=='true')
				alert('Fehler:'+data.errormsg);
			else
			{
				// Gespeicherte Zeile neue Zeichnen
				//$('#lvregel_li'+id).parent().append(drawRegel(data.result[0]));
				$(drawRegel(data.result[0])).insertAfter('#lvregel_li'+id);
				// Neu Zeile entfernen
				$('#lvregel_li'+id).remove();
			}
		},
		error: loadError
	});
}

/**
 * Fuegt eine neue leere Zeile zum Eintragen von neuen Regeln hinzu
 */
function addRegel(lvregel_id_parent)
{
	LVREGELnewcounter=LVREGELnewcounter+1;

	var regel= new Object();
	regel.neu=true;
	regel.lvregel_id='NEU_'+LVREGELnewcounter;
	regel.parameter='';
	regel.operator='u';
	regel.regeltyp_kurzbz='';
	regel.lehrveranstaltung_id='';
	regel.studienplan_lehrveranstaltung_id=LVREGELStudienplanLehrveranstaltungID;
	regel.lvregel_id_parent=lvregel_id_parent;

	if($('#lvregel_ul'+lvregel_id_parent).length>0)
	{	
		$('#lvregel_ul'+lvregel_id_parent).append(drawRegel(regel));
	}
	else
	{
		$('#lvregel_li'+lvregel_id_parent).append('<ul id="lvregel_ul'+lvregel_id_parent+'">'+drawRegel(regel)+'<ul>');
	}
}

/** 
 * Loescht eine Regel
 */
function deleteRegel(id)
{
	$.ajax(
	{
		dataType: "json",
		url: "../../soap/fhcomplete.php",
		type: "POST",
		data: {
				"typ": "json",
				"class": "lvregel",
				"method": "delete",
				"parameter_0":id
			},
		success: function(data) {
			if(data.error=='true')
				alert('Fehler:'+data.errormsg);
			else
				$('#lvregel_li'+id).remove();
		},
		error: loadError
	});
}

/**
 * Entfernt Null Werte
 */
function ClearNull(value)
{
	if(value===null)
		return '';
	else
		return value;
}
