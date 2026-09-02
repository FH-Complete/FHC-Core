
// Everything parses and formats with luxon in the server timezone. new Date("2026-09-02")
// reads UTC midnight and shows the day before west of Greenwich.


function getZone() {
	return globalThis.FHC_JS_DATA_STORAGE_OBJECT?.timezone || 'Europe/Vienna';
}

/**
 * converts a date value into a luxon DateTime in the server timezone
 *
 * @param {string|number|Date|Object} value - ISO string, SQL string, milliseconds, Date or luxon DateTime.
 * @returns {Object|null} luxon DateTime, or null if the value is empty or unparsable.
 */
export function toDateTime(value) {
	if (!value) return null;

	const zone = getZone();
	let date;

	if (value instanceof luxon.DateTime) date = value.setZone(zone);
	else if (value instanceof Date) date = luxon.DateTime.fromJSDate(value, { zone });
	else if (typeof value === 'number') date = luxon.DateTime.fromMillis(value, { zone });
	// fromISO rejects the space that SQL puts between date and time
	else if (typeof value === 'string') date = value.includes(' ')
		? luxon.DateTime.fromSQL(value, { zone })
		: luxon.DateTime.fromISO(value, { zone });
	else return null;

	return date.isValid ? date : null;
}

// current time in the server timezone
export function getNow() {
	return luxon.DateTime.now().setZone(getZone());
}

// today in the server timezone, as yyyy-MM-dd
export function getTodayISO() {
	return getNow().toISODate();
}

// the three formatters return '' for an empty or unparsable value
export function formatDate(value) {
	return toDateTime(value)?.toFormat('dd.MM.yyyy') ?? '';
}

export function formatTime(value) {
	return toDateTime(value)?.toFormat('HH:mm') ?? '';
}

export function formatDateTime(value) {
	return toDateTime(value)?.toFormat('dd.MM.yyyy HH:mm') ?? '';
}

// tabulator sorter for ISO dates, sorts empty values last
export function compareISODateValues(a, b) {
	if (!a && !b) return 0;
	if (!a) return 1;
	if (!b) return -1;

	return String(a).localeCompare(String(b));
}

// pads hours and minutes to two digits
export function numberPadding(number) {
	return String(number).padStart(2, '0');
}
