/**
 * Javascript file for Lehrauftraege annehmen view and tabulator
 * Lehrauftraege annehmen: acceptLehrauftrag.php
 * Lehrauftraege annehmen - Tabulator: acceptLehrauftragData.php
 */

// -----------------------------------------------------------------------------------------------------------------
// Global vars
// -----------------------------------------------------------------------------------------------------------------
const APP_ROOT = FHC_JS_DATA_STORAGE_OBJECT.app_root;

const COLOR_LIGHTGREY = "#f5f5f5";

/**
 * PNG icons used in status- and filter buttons
 * Setting png icons is a workaround to use font-awsome 5.9.0 icons until system can be updated to newer font awsome version.
 * */
const ICON_LEHRAUFTRAG_ORDERED = '<img src="../../../public/images/icons/fa-user-tag.png" style="height: 30px; width: 30px; margin: -6px;">';
const ICON_LEHRAUFTRAG_APPROVED = '<img src="../../../public/images/icons/fa-user-check.png" style="height: 30px; width: 30px; margin: -6px;">';
const ICON_LEHRAUFTRAG_CHANGED = '<img src="../../../public/images/icons/fa-user-edit.png" style="height: 30px; width: 30px; margin: -6px;">';
const ICON_LEHRAUFTRAG_CANCELLED = '<img src="../../../public/images/icons/fa-user-times.png" style="height: 30px; width: 30px; margin: -6px;">';

// Fields that should not be provided in the column picker
var tableWidgetBlacklistArray_columnUnselectable = [
	'status',
	'row_index',
	'betrag',
	'vertrag_id',
	'vertrag_stunden',
	'vertrag_betrag',
	'storniert_von',				// fields from cancelledLehrauftragData
	'letzterStatus_vorStorniert'	// fields from cancelledLehrauftragData
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
		return ("0" + (d.getDate())).slice(-2)  + "." + ("0"+(d.getMonth()+1)).slice(-2) + "." + d.getFullYear();
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
// Tabulator table format functions
// -----------------------------------------------------------------------------------------------------------------

// Returns relative height (depending on screen size)
function func_height(table){
	return $(window).height() * 0.50;
}

// Formats the rows
function func_rowFormatter(row){
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

	/*
	 Formats the color of the rows depending on their status
	 - orange: geaendert
	 - default: bestellte und erteilte (= zu akzeptierende)
	 - green: akzeptierte
	 - grey: all other (marks unselectable)
	 */
	row.getCells().forEach(function(cell){
		if (bestellt != null && (betrag != vertrag_betrag) ||
			bestellt != null && stunden != vertrag_stunden)
		{
			cell.getElement().classList.add('bg-warning');                  // geaenderte
		}
		else if(bestellt != null && erteilt != null && akzeptiert == null)
		{
			return;                                                         // bestellte + erteilte
		}
		else if(bestellt != null && erteilt != null && akzeptiert != null)
		{
			cell.getElement().classList.add('bg-success')                   // akzeptierte
		}
		else
		{
			row.getElement().style["background-color"] = COLOR_LIGHTGREY;   // default
		}
	});
}

// Formats row selectable/unselectable
function func_selectableCheck(row){
	var stunden = parseFloat(row.getData().stunden);
	var vertrag_stunden = parseFloat(row.getData().vertrag_stunden);

	var betrag = parseFloat(row.getData().betrag);
	var vertrag_betrag = parseFloat(row.getData().vertrag_betrag);

	var is_storniert = row.getData().storniert != undefined;

	if (isNaN(betrag))
	{
		betrag = 0;
	}

	// only allow to select bestellte && erteilte && nicht geaenderte Lehraufträge
	return  row.getData().bestellt != null &&   // bestellt
		row.getData().erteilt != null &&        // AND erteilt
		row.getData().akzeptiert == null &&     // AND nicht akzeptiert
		betrag == vertrag_betrag &&
		stunden == vertrag_stunden &&           // AND nicht geaendert
		!is_storniert;                          // AND nicht storniert
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
	// Set literally status to each row - this enables sorting by status despite using icons
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

		if ((bestellt != null && betrag != vertrag_betrag) ||
			(bestellt != null && stunden != vertrag_stunden))
		{
			row.getData().status = 'Geändert';      // geaendert
		}
		else if (bestellt == null && erteilt == null && akzeptiert == null)
		{
			row.getData().status = 'Neu';           // neu
		}
		else if (bestellt != null && erteilt == null && akzeptiert == null)
		{
			row.getData().status = 'Bestellt';      // bestellt
		}
		else if (bestellt != null && erteilt != null && akzeptiert == null)
		{
			row.getData().status = 'Erteilt';       // erteilt
		}
		else if (bestellt != null && erteilt != null && akzeptiert != null)
		{
			row.getData().status = 'Akzeptiert';    // akzeptiert
		}
		else
		{
			row.getData().status = null;            // default
		}
	});
}

// Performes after row was updated
function func_rowUpdated(row){

	// Refresh status icon and row color
	row.reformat(); // retriggers cell formatters and rowFormatter callback

	// Deselect and disable new selection of updated rows
	row.deselect();
	row.getElement().style["pointerEvents"] = "none";
}

// Hide betrag, if lector has inkludierte Lehre
function func_renderComplete(table){

	// Check if the lectors actual Verwendung has inkludierte Lehre
	FHC_AjaxClient.ajaxCallGet(
		FHC_JS_DATA_STORAGE_OBJECT.called_path + "/checkInkludierteLehre",
		null,
		{
			successCallback: function (data, textStatus, jqXHR)
			{
				// If lector has inkludierte Lehre, hide the column betrag
				if (data.retval)
				{
					table.hideColumn("betrag");
				}
			},
			errorCallback: function (jqXHR, textStatus, errorThrown)
			{
				FHC_DialogLib.alertError("Systemfehler<br>Bitte kontaktieren Sie Ihren Administrator.");
			}
		}
	);
}

// TableWidget Footer element
// -----------------------------------------------------------------------------------------------------------------

/*
 * Hook to overwrite TableWigdgets select-all-button behaviour
 * Select all (filtered) rows and ignore rows that are bestellt and erteilt
 */
function tableWidgetHook_selectAllButton(tableWidgetDiv){
	var resultRows = tableWidgetDiv.find("#tableWidgetTabulator").tabulator('getRows', true)
		.filter(row =>  row.getData().bestellt != null &&	// bestellt
			row.getData().erteilt != null &&				// AND erteilt
			row.getData().akzeptiert == null &&				// AND NOT akzeptiert
			row.getData().status != 'Geändert');				// AND NOT geändert

	tableWidgetDiv.find("#tableWidgetTabulator").tabulator('selectRow', resultRows);
}

// -----------------------------------------------------------------------------------------------------------------
// Tabulator columns format functions
// -----------------------------------------------------------------------------------------------------------------
// Generates status icons
status_formatter = function(cell, formatterParams, onRendered){

	var bestellt = cell.getRow().getData().bestellt;
	var erteilt = cell.getRow().getData().erteilt;
	var akzeptiert = cell.getRow().getData().akzeptiert;
	var is_storniert = cell.getRow().getData().storniert != undefined;

	var stunden = parseFloat(cell.getRow().getData().stunden);
	var vertrag_stunden = parseFloat(cell.getRow().getData().vertrag_stunden);

	var betrag = parseFloat(cell.getRow().getData().betrag);
	var vertrag_betrag = parseFloat(cell.getRow().getData().vertrag_betrag);

	if (isNaN(betrag))
	{
		betrag = 0;
	}

	// commented icons would be so nice to have with fontawsome 5.11...
	if (bestellt != null && isNaN(vertrag_betrag))
	{
		return "<i class='fas fa-user-minus'></i>";     // kein Vertrag
	}
	else if (bestellt != null && (betrag != vertrag_betrag) ||
		bestellt != null && stunden != vertrag_stunden)
	{
		return ICON_LEHRAUFTRAG_CHANGED;               // geaendert
		// return "<i class='fas fa-user-edit'></i>";
	}
	else if (bestellt == null && erteilt == null && akzeptiert == null && !is_storniert)
	{
		return "<i class='fa fa-user-plus'></i>";       // neu
	}
	else if (bestellt != null && erteilt == null && akzeptiert == null)
	{
		return ICON_LEHRAUFTRAG_ORDERED;                // bestellt
		// return "<i class='fa fa-user-tag'></i>";
	}
	else if (bestellt != null && erteilt != null && akzeptiert == null)
	{
		return ICON_LEHRAUFTRAG_APPROVED;               // erteilt
		// return "<i class='fas fa-user-check'></i>";
	}
	else if (bestellt != null && erteilt != null && akzeptiert != null)
	{
		return "<i class='fa fa-handshake-o'></i>";     // akzeptiert
		// return "<i class='fas fa-user-graduate'></i>";
	}
	else if (is_storniert)
	{
		return ICON_LEHRAUFTRAG_CANCELLED;     			// storniert
	}
	else
	{
		return "<i class='fa fa-user'></i>";            // default
	}
};

// Generates status tooltip
status_tooltip = function(cell){
	var bestellt = cell.getRow().getData().bestellt;
	var erteilt = cell.getRow().getData().erteilt;
	var akzeptiert = cell.getRow().getData().akzeptiert;
	var is_storniert = cell.getRow().getData().storniert != undefined;
	var letzterStatus_vorStorniert = cell.getRow().getData().letzterStatus_vorStorniert;


	var stunden = parseFloat(cell.getRow().getData().stunden);
	var vertrag_stunden = parseFloat(cell.getRow().getData().vertrag_stunden);

	var betrag = parseFloat(cell.getRow().getData().betrag);
	var vertrag_betrag = parseFloat(cell.getRow().getData().vertrag_betrag);

	if (isNaN(betrag))
	{
		betrag = 0;
	}

	if (letzterStatus_vorStorniert != undefined && letzterStatus_vorStorniert == 'akzeptiert')
	{
		letzterStatus_vorStorniert = 'angenommen';
	}

	var text = FHC_PhrasesLib.t("ui", "lehrauftragInBearbeitung");

	if (bestellt != null && erteilt == null && akzeptiert == null
		&& (betrag != vertrag_betrag || stunden != vertrag_stunden))        // geaendert (when never erteilt before)
	{
		text += FHC_PhrasesLib.t("ui", "wartetAufErteilung");;
		return text;
	}
	else if (bestellt != null && erteilt != null && akzeptiert == null
		&& (betrag != vertrag_betrag || stunden != vertrag_stunden))        // geaendert (when has been erteilt once)
	{
		text += FHC_PhrasesLib.t("ui", "wartetAufErneuteErteilung");
		return text;
	}
	else if (bestellt != null && erteilt == null && akzeptiert == null)     // bestellt
	{
		return FHC_PhrasesLib.t("ui", "letzterStatusBestellt");
	}
	else if (bestellt != null && erteilt != null && akzeptiert == null)     // erteilt
	{
		return FHC_PhrasesLib.t("ui", "letzterStatusErteilt");
	}
	else if (bestellt != null && erteilt != null && akzeptiert != null)     // akzeptiert
	{
		return FHC_PhrasesLib.t("ui", "letzterStatusAngenommen");
	}
	else if (is_storniert)													// storniert
	{
		return FHC_PhrasesLib.t("ui", "vertragWurdeStorniert");
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

// Generates storniert tooltip
storniert_tooltip = function(cell){
	if (cell.getRow().getData().storniert_von != null) {
		return FHC_PhrasesLib.t("ui", "storniertVon") + cell.getRow().getData().storniert_von;
	}
}

$(function() {
	// Pruefen ob Promise unterstuetzt wird
	// Tabulator funktioniert nicht mit IE
	var canPromise = !! window.Promise;
	if(!canPromise)
	{
		alert("Diese Seite kann mit ihrem Browser nicht angezeigt werden. Bitte verwenden Sie Firefox, Chrome oder Edge um die Seite anzuzeigen");
		window.location.href='about:blank';
		return;
	}

	// Redraw table on resize to fit tabulators height to windows height
	window.addEventListener('resize', function(){
		$('#tableWidgetTabulator').tabulator('setHeight', $(window).height() * 0.50);
		$('#tableWidgetTabulator').tabulator('redraw', true);
	});

	// Show all rows
	$("#show-all").click(function(){
		$('#tableWidgetTabulator').tabulator('clearFilter');
	});

	// Show only rows with ordered lehrauftraege
	$("#show-ordered").click(function(){
		$('#tableWidgetTabulator').tabulator('setFilter',
			[
				{field: 'bestellt', type: '!=', value: null},
				{field: 'erteilt', type: '=', value: null},
				{field: 'akzeptiert', type: '=', value: null}
			]
		);
	});

	// Show only rows with erteilte lehrauftraege
	$("#show-approved").click(function(){
		$('#tableWidgetTabulator').tabulator('setFilter', [
				{field: 'bestellt', type: '!=', value: null},   // filter when is bestellt
				{field: 'erteilt', type: '!=', value: null},    // and is erteilt
				{field: 'akzeptiert', type: '=', value: null}  // and is not akzeptiert
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

	// Set png-icons into filter-buttons
	$(".btn-lehrauftrag").each(function(){
		switch(this.id) {
			case 'show-ordered':
				this.innerHTML = ICON_LEHRAUFTRAG_ORDERED;
				break;
			case 'show-approved':
				this.innerHTML = ICON_LEHRAUFTRAG_APPROVED;
				break;
			case 'show-cancelled':
				this.innerHTML = ICON_LEHRAUFTRAG_CANCELLED;
				break;
		}
	});

	// De/activate and un/focus on clicked button
	$(".btn-lehrauftrag").click(function() {

		// De/activate and un/focus on clicked button
		$(".btn-lehrauftrag").removeClass('focus').removeClass('active');
		$(this).addClass('focus').addClass('active');
	});

	// Performs download PDF accepted Lehrauftraege
	$("#ul-download-pdf").on('click', 'li', function(){
		var uid = $("#uid").val();
		var studiensemester = $('#studiensemester').val();

		if ($(this).attr('value') != null && $(this).attr('value') != '')
		{
			var selected = $(this).attr('value');

			if (selected == 'etw' || selected == 'lehrgang')
			{
				window.open(APP_ROOT + 'cis/private/pdfExport.php' +
					'?xml=lehrauftrag_annehmen.xml.php' +
					'&xsl=Lehrauftrag' +
					'&xsl_oe_kurzbz=' + selected +
					'&stg_kz=' +
					'&uid=' + uid +
					'&ss=' + studiensemester, '_parent'
				);
			}
		}
	});

	// Redraw table stornierte lehrauftraege on button click
	$('#collapseCancelledLehrauftraege').on('shown.bs.collapse', function () {
		$('[tableuniqueid = cancelledLehrauftrag] #tableWidgetTabulator').tabulator('redraw', true);
	});

	// Approve Lehrauftraege
	$("#accept-lehrauftraege").click(function(){

		// Get selected rows data
		var selected_data = $('#tableWidgetTabulator').tabulator('getSelectedData')
			.map(function(data){
				// reduce to necessary fields
				return {
					'row_index' : data.row_index,
					'vertrag_id' : data.vertrag_id
				}
			});

		// Alert and exit if no lehraufgang is selected
		if (selected_data.length == 0)
		{
			FHC_DialogLib.alertInfo('Bitte wählen Sie erst zumindest einen Lehrauftrag');

			// Emtpy password field
			$("#password").val('');

			return;
		}

		// Get password for verification
		var password = $("#password").val();
		if (password == '')
		{
			FHC_DialogLib.alertInfo('Bitte verifizieren Sie sich mit Ihrem Login Passwort.');

			// Focus on password field
			$("#password").focus();

			return;
		}

		// Prepare data object for ajax call
		var data = {
			'password': password,
			'selected_data': selected_data
		};

		FHC_AjaxClient.ajaxCallPost(
			FHC_JS_DATA_STORAGE_OBJECT.called_path + "/acceptLehrauftrag",
			data,
			{
				successCallback: function (data, textStatus, jqXHR)
				{
					if (data.error && data.retval != null)
					{
						// Print error message
						FHC_DialogLib.alertWarning(data.retval);
					}

					if (!data.error && data.retval != null)
					{
						// Update status 'Erteilt'
						$('#tableWidgetTabulator').tabulator('updateData', data.retval);

						// Print success message
						FHC_DialogLib.alertSuccess(data.retval.length + " Lehraufträge wurden akzeptiert.");
					}
				},
				errorCallback: function (jqXHR, textStatus, errorThrown)
				{
					FHC_DialogLib.alertError("Systemfehler<br>Bitte kontaktieren Sie Ihren Administrator.");
				}
			}
		);

		// Empty password field
		$("#password").val('');

	});

});
