/**
 * Javascript file for Lehrauftraege bestellen view and tabulator
 * Lehrauftraege bestellen: orderLehrauftrag.php
 * Lehrauftraege bestellen - Tabulator: orderLehrauftragData.php
 */

// -----------------------------------------------------------------------------------------------------------------
// Global vars
// -----------------------------------------------------------------------------------------------------------------

const COLOR_LIGHTGREY = "#f5f5f5";

/**
 * PNG icons used in status- and filter buttons
 * Setting png icons is a workaround to use font-awsome 5.9.0 icons until system can be updated to newer font awsome version.
 * */
const ICON_LEHRAUFTRAG_ORDERED = '<img src="../../../public/images/icons/fa-user-tag.png" style="height: 30px; width: 30px; margin: -6px;">';
const ICON_LEHRAUFTRAG_APPROVED = '<img src="../../../public/images/icons/fa-user-check.png" style="height: 30px; width: 30px; margin: -6px;">';
const ICON_LEHRAUFTRAG_CHANGED = '<img src="../../../public/images/icons/fa-user-edit.png" style="height: 30px; width: 30px; margin: -6px;">';

// Fields that should not be provided in the column picker
var tableWidgetBlacklistArray_columnUnselectable = [
	'status',
	'row_index',
	'personalnummer',
	'betrag',
	'vertrag_id',
	'vertrag_stunden',
	'vertrag_betrag'
];

// -----------------------------------------------------------------------------------------------------------------
// Mutators - setter methods to manipulate table data when entering the tabulator
// -----------------------------------------------------------------------------------------------------------------

// Converts string date postgre style to string DD.MM.YYYY.
// This will allow correct filtering.
var mut_formatStringDate = function(value, data, type, params, component) {
	if (value != null)
	{
		var d = new Date(value);
		return ("0" + (d.getDate())).slice(-2)  + "." + ("0" + (d.getMonth() + 1)).slice(-2) + "." + d.getFullYear();
	}
}

// -----------------------------------------------------------------------------------------------------------------
// Formatters - changes display information, not the data itself
// -----------------------------------------------------------------------------------------------------------------

// Formats null values to a string number '0.00'
var form_formatNulltoStringNumber = function(cell, formatterParams){
	if (cell.getValue() == null){
		if (formatterParams.precision == 1)
		{
			return '0.0';
		}
		return '0.00';
	}
	else {
		return cell.getValue();
	}
}

// -----------------------------------------------------------------------------------------------------------------
// Header filter
// -----------------------------------------------------------------------------------------------------------------

// Filters values using comparison operator or just by string comparison
function hf_filterStringnumberWithOperator(headerValue, rowValue, rowData){

	// If string starts with <, <=, >, >=, !=, ==, compare values with that operator
	var operator = '';
	if (headerValue.match(/([<=>!]{1,2})/g)) {
		var operator_arr = headerValue.match(/([<=>!]{1,2})/g);
		operator = operator_arr[0];

		headerValue = headerValue
			.replace(operator, '')
			.trim()
		;

		// return if value comparison is true
		return eval(rowValue + operator + headerValue);
	}

	// If just a stringnumber, return if exact match found
	return parseFloat(rowValue) == headerValue;
}

// -----------------------------------------------------------------------------------------------------------------
// Custom filters
// -----------------------------------------------------------------------------------------------------------------

/*
 * Filters neue AND geaenderte initially
 * NOTE: This is a workaround. The tabulators callback initialFilter is not used here because
 * it is processed before the callback tableBuilt, where the status is going to be built.
 * The callback dataLoaded is processed after tableBuild and provides the status.
 */
function func_dataLoaded(data, table){

	table.setFilter([
		[
			{field: 'personalnummer', type: '>', value: 0},	   // not dummy
			{field: 'personalnummer', type: '=', value: null}	 // include projektbetreuer
		],
		{field: 'mitarbeiter_uid', type: '!=', value: null},	// AND is Mitarbeiter
		{field: 'stunden', type: '!=', value: null},			// AND has Semesterstunden (not null and not 0)
		{field: 'stunden', type: '!=', value: 0},
		[
			{field: 'status', type: '=', value: 'Neu'},		 // AND neu
			{field: 'status', type: '=', value: 'Geändert'}	 // OR geaendert
		]
	]);
}

// -----------------------------------------------------------------------------------------------------------------
// Tabulator table format functions
// -----------------------------------------------------------------------------------------------------------------

// Returns relative height (depending on screen size)
function func_height(table){
	return $(window).height() * 0.50;
}

// Formats the group header
function func_groupHeader(data) {
	return data[0].lv_bezeichnung + "&nbsp;&nbsp;" + ' ( LV-ID: ' + data[0].lehrveranstaltung_id + ' )';  // change name to lehrveranstaltung;
}

// Formats the rows
function func_rowFormatter(row){
	var is_dummy = (row.getData().personalnummer <= 0 && row.getData().personalnummer != null);
	var is_mitarbeiter = row.getData().mitarbeiter_uid != null;
	var has_stunden = row.getData().stunden != 0 && row.getData().stunden != null;

	var bestellt = row.getData().bestellt;
	var erteilt = row.getData().erteilt;
	var akzeptiert = row.getData().akzeptiert;

	var stunden = parseFloat(row.getData().stunden);
	var vertrag_stunden = parseFloat(row.getData().vertrag_stunden);

	var betrag = parseFloat(row.getData().betrag);
	var vertrag_betrag = parseFloat(row.getData().vertrag_betrag);

	if (isNaN(betrag))
	{
		betrag = 0;
	}

	if (isNaN(stunden))
	{
		stunden = 0;
	}

	if (isNaN(vertrag_stunden))
	{
		vertrag_stunden = 0;
	}

	if (isNaN(vertrag_betrag))
	{
		vertrag_betrag = 0;
	}

	/*
	Formats the color of the rows depending on their status
	- blue: dummy lectors
	- bold: geaendert
	- default (white): neu und erteilt
	- green: akzeptiert
	- grey: all other (marks unselectable), not mitarbeiter, has no semesterstunden
	 */
	row.getCells().forEach(function(cell){
		if(is_dummy)
		{
			cell.getElement().classList.add('bg-info');						  // dummy lectors
		}
		else if ((!is_mitarbeiter || !has_stunden) && akzeptiert == null)
		{
			row.getElement().style["background-color"] = COLOR_LIGHTGREY;
		}
		else if (bestellt != null && (betrag != vertrag_betrag) ||
			bestellt != null && stunden != vertrag_stunden &&
			!row._row.element.classList.contains('tabulator-calcs'))		// exclude calculation rows
		{
			row.getElement().style['font-weight'] = 'bold';					// geaendert
		}
		else if(bestellt == null)
		{
			return;															 // neu und erteilt
		}
		else if(bestellt != null && erteilt != null && akzeptiert != null)
		{
			cell.getElement().classList.add('bg-success')					   // akzeptiert
		}
		else
		{
			row.getElement().style["background-color"] = COLOR_LIGHTGREY;	   // default
		}
	});
}

// Formats row selectable/unselectable
function func_selectableCheck(row){
	var is_dummy = (row.getData().personalnummer <= 0 && row.getData().personalnummer != null);
	var is_mitarbeiter = row.getData().mitarbeiter_uid != null;
	var has_stunden = row.getData().stunden != 0 && row.getData().stunden != null;

	var stunden = parseFloat(row.getData().stunden);
	var vertrag_stunden = parseFloat(row.getData().vertrag_stunden);

	var betrag = parseFloat(row.getData().betrag);
	var vertrag_betrag = parseFloat(row.getData().vertrag_betrag);

	if (isNaN(betrag))
	{
		betrag = 0;
	}

	if (isNaN(stunden))
	{
		stunden = 0;
	}

	if (isNaN(vertrag_stunden))
	{
		vertrag_stunden = 0;
	}

	if (isNaN(vertrag_betrag))
	{
		vertrag_betrag = 0;
	}

	// Only allow to select neue and geaenderte
	return  !is_dummy &&												// NOT dummy lector
		is_mitarbeiter &&											   // AND is Mitarbeiter
		has_stunden &&												  // AND has Semesterstunden (not null and not 0)
		((row.getData().bestellt == null) ||							  // AND neue
		(row.getData().bestellt != null && betrag != vertrag_betrag) ||   // OR geaenderte
		(row.getData().bestellt != null && stunden != vertrag_stunden));
}

// Adds column status
function func_tableBuilt(table) {
	// Add status column to table
	table.addColumn(
		{
			title: "<i class='fa fa-user-o'></i>",
			field: "status",
			width:40,
			align:"center",
			downloadTitle: 'Status',
			formatter: status_formatter,
			tooltip: status_tooltip
		}, true
	);
}

// Sets status values into column status
function func_renderStarted(table){
	// set literally status to each row - this enables sorting by status despite using icons
	table.getRows().forEach(function(row){
		var bestellt = row.getData().bestellt;
		var erteilt = row.getData().erteilt;
		var akzeptiert = row.getData().akzeptiert;

		var stunden = parseFloat(row.getData().stunden);
		var vertrag_stunden = parseFloat(row.getData().vertrag_stunden);

		var betrag = parseFloat(row.getData().betrag);
		var vertrag_betrag = parseFloat(row.getData().vertrag_betrag);

		if (isNaN(betrag))
		{
			betrag = 0;
		}

		if (isNaN(stunden))
		{
			stunden = 0;
		}

		if (isNaN(vertrag_stunden))
		{
			vertrag_stunden = 0;
		}

		if (isNaN(vertrag_betrag))
		{
			vertrag_betrag = 0;
		}

		if ((bestellt != null && betrag != vertrag_betrag) ||
			(bestellt != null && stunden != vertrag_stunden))
		{
			row.getData().status = 'Geändert';		// geaendert
		}
		else if (bestellt == null && erteilt == null && akzeptiert == null)
		{
			row.getData().status = 'Neu';			// neu
		}
		else if (bestellt != null && erteilt == null && akzeptiert == null)
		{
			row.getData().status = 'Bestellt';		// bestellt
		}
		else if (bestellt != null && erteilt != null && akzeptiert == null)
		{
			row.getData().status = 'Erteilt';		// erteilt
		}
		else if (bestellt != null && erteilt != null && akzeptiert != null)
		{
			row.getData().status = 'Akzeptiert';	// akzeptiert
		}
		else
		{
			row.getData().status = null;			// default
		}
	});
}

// Performes after row was updated
function func_rowUpdated(row){

	// Refresh status icon and row color
	row.reformat(); // retriggers cell formatters and rowFormatter callback

	// Format font-weight normal (needed after geaenderte were bestellt)
	row.getElement().style['font-weight'] = 'normal';

	// Deselect and disable new selection of updated rows (ordering done)
	row.deselect();
	row.getElement().style["pointerEvents"] = "none";
}

// TableWidget Footer element
// -----------------------------------------------------------------------------------------------------------------

/*
 * Hook to overwrite TableWigdgets select-all-button behaviour
 * Select all (filtered) rows and ignore rows which have status bestellt
 */
function tableWidgetHook_selectAllButton(tableWidgetDiv){
	var resultRows = tableWidgetDiv.find("#tableWidgetTabulator").tabulator('getRows', true)
		.filter(row => (
			row.getData().personalnummer > 0 ||					// not dummies
			row.getData().personalnummer == null) &&			// include Projektbetreuer
			row.getData().mitarbeiter_uid != null &&			// AND is Mitarbeiter
			row.getData().stunden != null &&					// AND has Semesterstunden (not null and not 0)
			row.getData().stunden != 0 &&
			(
				(row.getData().bestellt == null) ||				// AND neu
				(row.getData().bestellt != null &&				// OR (bestellt
				row.getData().status == 'Geändert')				// AND geaendert)
			)
		);

	tableWidgetDiv.find("#tableWidgetTabulator").tabulator('selectRow', resultRows);
}

// -----------------------------------------------------------------------------------------------------------------
// Tabulator columns format functions
// -----------------------------------------------------------------------------------------------------------------
// Generates status icons
status_formatter = function(cell, formatterParams, onRendered){
	var is_dummy = (cell.getRow().getData().personalnummer <= 0 && cell.getRow().getData().personalnummer != null);

	var bestellt = cell.getRow().getData().bestellt;
	var erteilt = cell.getRow().getData().erteilt;
	var akzeptiert = cell.getRow().getData().akzeptiert;

	var stunden = parseFloat(cell.getRow().getData().stunden);
	var vertrag_stunden = parseFloat(cell.getRow().getData().vertrag_stunden);

	var betrag = parseFloat(cell.getRow().getData().betrag);
	var vertrag_betrag = parseFloat(cell.getRow().getData().vertrag_betrag);

	if (isNaN(betrag))
	{
		betrag = 0;
	}

	if (isNaN(stunden))
	{
		stunden = 0;
	}

	if (isNaN(vertrag_stunden))
	{
		vertrag_stunden = 0;
	}

	if (isNaN(vertrag_betrag))
	{
		vertrag_betrag = 0;
	}

	// commented icons would be so nice to have with fontawsome 5.11...
	if (is_dummy)
	{
		return "<i class='fa fa-user-secret'></i>";	// dummy lector
	}
	else if (bestellt != null && isNaN(vertrag_betrag))
	{
		return "<i class='fa fa-user-minus'></i>";	 // kein Vertrag
	}
	else if (bestellt != null && (betrag != vertrag_betrag) ||	 // geaendert
		bestellt != null && stunden != vertrag_stunden)		 // geaendert ((if betrag is 0 or null)
	{
		return ICON_LEHRAUFTRAG_CHANGED;			   // geaendert
		// return "<i class='fas fa-user-edit'></i>";
	}
	else if (bestellt == null && erteilt == null && akzeptiert == null)
	{
		return "<i class='fa fa-user-plus'></i>";	  // neu
	}
	else if (bestellt != null && erteilt == null && akzeptiert == null)
	{
		return ICON_LEHRAUFTRAG_ORDERED;			   // bestellt
		// return "<i class='fa fa-user-tag'></i>";
	}
	else if (bestellt != null && erteilt != null && akzeptiert == null)
	{
		return ICON_LEHRAUFTRAG_APPROVED;			  // erteilt
		// return "<i class='fas fa-user-check'></i>";
	}
	else if (bestellt != null && erteilt != null && akzeptiert != null)
	{
		return "<i class='fa fa-handshake-o'></i>";	// akzeptiert
	}
	else
	{
		return "<i class='fa fa-user'></i>";	// default
	}
};

// Generates status tooltip
status_tooltip = function(cell){

	var is_dummy = (cell.getRow().getData().personalnummer <= 0 && cell.getRow().getData().personalnummer != null);
	var is_mitarbeiter = cell.getRow().getData().mitarbeiter_uid != null;
	var has_stunden = cell.getRow().getData().stunden != 0 && cell.getRow().getData().stunden != null;

	var bestellt = cell.getRow().getData().bestellt;
	var erteilt = cell.getRow().getData().erteilt;
	var akzeptiert = cell.getRow().getData().akzeptiert;

	var betrag = parseFloat(cell.getRow().getData().betrag);
	var stunden = parseFloat(cell.getRow().getData().stunden);
	var stundensatz = parseFloat(cell.getRow().getData().stundensatz);

	var vertrag_betrag = parseFloat(cell.getRow().getData().vertrag_betrag);
	var vertrag_stunden = parseFloat(cell.getRow().getData().vertrag_stunden);
	var vertrag_stundensatz = 0;

	if (isNaN(betrag))
	{
		betrag = 0;
	}

	if (isNaN(stunden))
	{
		stunden = 0;
	}

	if (isNaN(vertrag_stunden))
	{
		vertrag_stunden = 0;
	}

	if (isNaN(vertrag_betrag))
	{
		vertrag_betrag = 0;
	}

	// Calculate vertrag stundensatz
	if (vertrag_stunden != 0)
	{
		vertrag_stundensatz = vertrag_betrag/vertrag_stunden;
	}

	// Return tooltip message
	if (is_dummy)	// dummy (no lector)
	{
		return FHC_PhrasesLib.t("ui", "neuerLehrauftragOhneLektorVerplant");
	}
	else if (!is_mitarbeiter)
	{
		return 'Lektor ist nicht als Mitarbeiter erfasst.'
	}
	else if (!has_stunden)
	{
		return 'Es wurden noch keine Stunden zugeteilt.'
	}
	else if (vertrag_betrag == 0)	// neu
	{
		return FHC_PhrasesLib.t("ui", "neuerLehrauftragWartetAufBestellung");
	}
	else if (betrag != vertrag_betrag ||									// geaendert
		bestellt != null && stunden != vertrag_stunden)						// geaendert (if betrag is 0 or null)
	{
		var text = FHC_PhrasesLib.t("ui", "nachAenderungStundensatzStunden",
			new Array(stundensatz, stunden));
		text += "\n";
		text += FHC_PhrasesLib.t("ui", "vorAenderungStundensatzStunden",
			new Array(vertrag_stundensatz, vertrag_stunden));
		return text;
	}
	else if (bestellt != null && erteilt == null && akzeptiert == null)		 // bestellt
	{
		return FHC_PhrasesLib.t("ui", "letzterStatusBestellt");
	}
	else if (bestellt != null && erteilt != null && akzeptiert == null)		 // erteilt
	{
		return FHC_PhrasesLib.t("ui", "letzterStatusErteilt");
	}
	else if (bestellt != null && erteilt != null && akzeptiert != null)		 // akzeptiert
	{
		return FHC_PhrasesLib.t("ui", "letzterStatusAngenommen");
	}
}

// Generates bestellt tooltip
bestellt_tooltip = function(cell){
	if (cell.getRow().getData().bestellt_von != null)
	{
		return FHC_PhrasesLib.t("ui", "bestelltVon") + cell.getRow().getData().bestellt_von;
	}
}

// Generates erteilt tooltip
erteilt_tooltip = function(cell){
	if (cell.getRow().getData().erteilt_von != null) {
		return FHC_PhrasesLib.t("ui", "erteiltVon") + cell.getRow().getData().erteilt_von;
	}
}

// Generates akzeptiert tooltip
akzeptiert_tooltip = function(cell){
	if (cell.getRow().getData().akzeptiert_von != null) {
		return FHC_PhrasesLib.t("ui", "angenommenVon") + cell.getRow().getData().akzeptiert_von;
	}
}

$(function() {

	// Redraw table on resize to fit tabulators height to windows height
	window.addEventListener('resize', function(){
		$('#tableWidgetTabulator').tabulator('setHeight', $(window).height() * 0.50);
		$('#tableWidgetTabulator').tabulator('redraw', true);
	});

	// Show all rows
	$("#show-all").click(function(){
		$('#tableWidgetTabulator').tabulator('clearFilter');
	});

	// Show only rows with new lehrauftraege (not dummy lectors or external projektbetreuer; stunden not 0 or null)
	$("#show-new").click(function(){
		$('#tableWidgetTabulator').tabulator('setFilter',
			[
				[
					{field: 'personalnummer', type: '>', value: 0},	 // not dummy lector
					{field: 'personalnummer', type: '=', value: null}   // include Projektbetreuer
				],
				{field: 'mitarbeiter_uid', type: '!=', value: null},	// AND is Mitarbeiter
				{field: 'stunden', type: '!=', value: null},			// AND has Semesterstunden (not null and not 0)
				{field: 'stunden', type: '!=', value: 0},
				{field: 'bestellt', type: '=', value: null},
				{field: 'erteilt', type: '=', value: null},
				{field: 'akzeptiert', type: '=', value: null}
			]
		);
	});

	// Show only rows with ordered lehrauftraege
	$("#show-ordered").click(function(){
		$('#tableWidgetTabulator').tabulator('setFilter',
			[
				[
					{field: 'personalnummer', type: '>', value: 0},	 // not dummy lector
					{field: 'personalnummer', type: '=', value: null}   // include Projektbetreuer
				],
				{field: 'bestellt', type: '!=', value: null},
				{field: 'erteilt', type: '=', value: null},
				{field: 'akzeptiert', type: '=', value: null}
			]
		);
	});

	// Show only rows with erteilte lehrauftraege
	$("#show-approved").click(function(){
		$('#tableWidgetTabulator').tabulator('setFilter',
			[
				{field: 'bestellt', type: '!=', value: null},
				{field: 'erteilt', type: '!=', value: null},
				{field: 'akzeptiert', type: '=', value: null}
			]
		);
	});

	// Show only rows with akzeptierte lehrauftraege
	$("#show-accepted").click(function(){
		$('#tableWidgetTabulator').tabulator('setFilter',
			[
				{field: 'bestellt', type: '!=', value: null},
				{field: 'erteilt', type: '!=', value: null},
				{field: 'akzeptiert', type: '!=', value: null}
			]
		);
	});

	// Show only rows with geaenderte lectors
	$("#show-changed").click(function(){
		// needs custom filter to compare fields betrag and vertrag_betrag
		$('#tableWidgetTabulator').tabulator('setFilter',
			[
				[
					{field: 'personalnummer', type: '>', value: 0},	 // NOT dummy lector
					{field: 'personalnummer', type: '=', value: null}   // include Projektbetreuer
				],
				{field: 'mitarbeiter_uid', type: '!=', value: null},	// AND is Mitarbeiter
				{field: 'stunden', type: '!=', value: null},			// AND has Semesterstunden (not null and not 0)
				{field: 'stunden', type: '!=', value: 0},
				{field: 'bestellt', type: '!=', value: null},		   // bestellt AND
				{field: 'status', type: '=', value: 'Geändert'}		 // geaendert
			]
		);
	});

	// Show only rows with dummy lectors
	$("#show-dummies").click(function(){
		$('#tableWidgetTabulator').tabulator('setFilter',
			[
				{field: 'personalnummer', type: '!=', value: null},
				{field: 'personalnummer', type: '<=', value: 0},
			]
		);
	});

	// Set png-icons into filter-buttons
	$(".btn-lehrauftrag").each(function(){
		switch(this.id) {
			case 'show-ordered':
				this.innerHTML = ICON_LEHRAUFTRAG_ORDERED;
				break;
			case 'show-approved':
				this.innerHTML = ICON_LEHRAUFTRAG_APPROVED;
				break;
			case 'show-changed':
				this.innerHTML = ICON_LEHRAUFTRAG_CHANGED;
				break;
		}
	});

	// De/activate and un/focus on clicked button, En-/Disable 'Lehrauftrag bestellen'
	$(".btn-lehrauftrag").click(function() {

		// De/activate and un/focus on clicked button
		$(".btn-lehrauftrag").removeClass('focus').removeClass('active');
		$(this).addClass('focus').addClass('active');

		//Enable button 'Lehrauftrag bestellen' by default
		$('#order-lehrauftraege').attr('disabled', false).attr('title', '');

		// Disable button Lehrauftrag bestellen for dummies
		if (this.id == 'show-dummies')
		{
			$('#order-lehrauftraege').attr('disabled', true).attr('title', 'Lehraufträge ohne Lektorzuteilung können nicht bestellt werden.');
		}
	});

	// Order Lehrauftraege
	$("#order-lehrauftraege").click(function(){

		var selected_data = $('#tableWidgetTabulator').tabulator('getSelectedData')
			.filter(function(val){
				// filter pseudo lines of groupBy (e.g. the bottom calculations lines)
				return val.row_index != null || typeof(val.row_index) !== 'undefined';
			})
			.map(function(data){
				// reduce to necessary fields
				return {
					'row_index' : data.row_index,
					'lehreinheit_id' : data.lehreinheit_id,
					'lehrveranstaltung_id' : data.lehrveranstaltung_id,
					'person_id' : data.person_id,
					'mitarbeiter_uid' : data.mitarbeiter_uid,
					'vertrag_id' : data.vertrag_id,
					'projektarbeit_id' : data.projektarbeit_id,
					'stunden' : data.stunden,
					'betrag' : data.betrag,
					'studiensemester_kurzbz' : data.studiensemester_kurzbz,
					'studiengang_kz' : data.studiengang_kz,
					'lv_oe_kurzbz' : data.lv_oe_kurzbz
				}
			});

		// Alert and exit if no lehraufgang is selected
		if (selected_data.length == 0)
		{
			FHC_DialogLib.alertInfo('Bitte wählen Sie erst zumindest einen Lehrauftrag');
			return;
		}

		/*
		 * Prepare data object for ajax call
		 * NOTE: Stringify to send only ONE post param (json string) instead of many single post params.
		 * This avoids issues with POST param limitation.
		 */
		var data = {
			'selected_data': JSON.stringify(selected_data)
		};

		FHC_AjaxClient.ajaxCallPost(
			FHC_JS_DATA_STORAGE_OBJECT.called_path + "/orderLehrauftrag",
			data,
			{
				successCallback: function (data, textStatus, jqXHR)
				{
					if (!data.error && data.retval != null)
					{
						// Update status 'Bestellt'
						$('#tableWidgetTabulator').tabulator('updateData', data.retval);

						// Print success message
						FHC_DialogLib.alertSuccess("Alle " + data.retval.length + " Lehraufträge wurden bestellt.")
					}

					if (data.error && data.retval != null)
					{
						// Print error message
						FHC_DialogLib.alertError(data.retval);
					}
				},
				errorCallback: function (jqXHR, textStatus, errorThrown)
				{
					FHC_DialogLib.alertError("Sytemfehler<br>Bitte kontaktieren Sie Ihren Administrator.");
				}
			}
		);
	});
});
