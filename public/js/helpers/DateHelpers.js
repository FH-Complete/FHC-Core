
// HELPER FILE -- that contains multiple functions which create, handle and manipulate js dates

// custom Error class for DateHelpers.js misuse
class DateHelperError extends Error {
	constructor(message) {
		super(message);
		this.name = "DateHelperError";
	}
}

/**
 * adds padding to a number 
 *
 * @param {number|string} number - the number on which padding should be added.
 * @returns {string} number with padding.
 */
export function numberPadding(number) {
	if(typeof number !== "string" && typeof number !== "number")
	{
		throw new TypeError("function numberPadding in file DateHelpers.js is only usable with strings or numbers");
	}
	if(number.toString().length > 2) 
	{
		throw new DateHelperError("The number on which the padding should be added should not be longer than to 2 characters, please refere to the function numberPadding in the helper file DateHelpers.js");
	}
	return number.toString().length == 1 ? '0' + number.toString() : number.toString();
}

/**
 * formats date to dd.mm.yyyy
 *
 * @param {string|Date} d - the date that should be formatted.
 * @returns {string} formatted date string.
 */
export function formatDate(d) {
	// parameter is of type Date
	if(d instanceof Date)
	{
		if (isNaN(date.valueOf())) {
			return 'N/A';
		}
		// if the date is an invalid string then creating a date from the string will fail and N/A is returned
		return `${numberPadding(d.getDate())}.${numberPadding(d.getMonth() + 1)}.${d.getFullYear()}`;
	}
	// parameter is of type string
	else if (typeof d === "string")
	{
		let date = new Date(d);
		// if the date is an invalid string then creating a date from the string will fail and N/A is returned
		if (isNaN(date.valueOf())) {
			return 'N/A';
		}
		return `${numberPadding(date.getDate())}.${numberPadding(date.getMonth() + 1)}.${date.getFullYear()}`;
	}
	// parameter is not of type string or Date and an exception is thrown
	else
	{
		throw new TypeError("The parameter provided for this function is not a string or a Date object, please refere to the function formatDate in the DateHelpers.js file");
	}
}

/**
 * today in the time zone of the instance, as a Date at midnight
 *
 * The browser can stand in another time zone than the server. A date from the browser then names the
 * next day, and the server refuses the entry as a date in the future.
 *
 * @returns {Date} today at 00:00 local time, with the day of the instance time zone.
 */
export function today() {
	const zone = (typeof FHC_JS_DATA_STORAGE_OBJECT !== "undefined")
		? FHC_JS_DATA_STORAGE_OBJECT.timezone
		: null;

	if (zone && typeof luxon !== "undefined")
	{
		const now = luxon.DateTime.local().setZone(zone);
		if (now.isValid) return new Date(now.year, now.month - 1, now.day);
	}

	// without the time zone of the instance the browser decides
	const local = new Date();
	return new Date(local.getFullYear(), local.getMonth(), local.getDate());
}

/**
 * Date -> 'YYYY-MM-DD' in local time. An API takes the day, never a timestamp.
 *
 * @param {Date} date
 * @returns {string}
 */
export function toIsoDate(date) {
	return `${date.getFullYear()}-${numberPadding(date.getMonth() + 1)}-${numberPadding(date.getDate())}`;
}

/**
 * 'YYYY-MM-DD' (a longer string is cut) -> Date at 00:00 local time.
 *
 * @param {string} iso
 * @returns {Date|null}
 */
export function parseIsoDate(iso) {
	const parts = String(iso ?? '').slice(0, 10).split('-');
	if (parts.length !== 3) return null;

	return new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
}

/**
 * 'YYYY-MM-DD HH:MM:SS' or 'YYYY-MM-DD' -> Date in local time.
 *
 * @param {string} timestamp
 * @returns {Date|null}
 */
export function parseTimestamp(timestamp) {
	if (!timestamp) return null;

	const [datePart, timePart] = String(timestamp).trim().split(' ');
	const [year, month, day] = (datePart ?? '').split('-').map(Number);
	if (!Number.isFinite(year) || !Number.isFinite(month) || !Number.isFinite(day)) return null;

	const [hour, minute, second] = (timePart ?? '').split(':').map(Number);

	return new Date(year, month - 1, day,
		Number.isFinite(hour) ? hour : 0,
		Number.isFinite(minute) ? minute : 0,
		Number.isFinite(second) ? second : 0);
}

/**
 * 'YYYY-MM-DD' (a longer string is cut) -> 'DD.MM.YYYY'. A string operation: no time zone applies.
 *
 * @param {string} iso
 * @returns {string} an empty string for an invalid value
 */
export function isoToDmy(iso) {
	const parts = String(iso ?? '').slice(0, 10).split('-');
	if (parts.length !== 3) return '';

	return `${parts[2]}.${parts[1]}.${parts[0]}`;
}

/**
 * 'DD.MM.YYYY' -> 'YYYY-MM-DD'.
 *
 * @param {string} dmy
 * @returns {string|null} null if the value has another format or the day does not exist (31.02.)
 */
export function dmyToIso(dmy) {
	const match = String(dmy ?? '').trim().match(/^(\d{2})\.(\d{2})\.(\d{4})$/);
	if (!match) return null;

	const [, day, month, year] = match.map(Number);
	const date = new Date(year, month - 1, day);
	if (date.getFullYear() !== year || date.getMonth() !== month - 1 || date.getDate() !== day) return null;

	return `${match[3]}-${match[2]}-${match[1]}`;
}

/**
 * @param {Date} date
 * @param {number} days
 * @returns {Date} a new Date, $days later
 */
export function addDays(date, days) {
	const result = new Date(date);
	result.setDate(result.getDate() + days);
	return result;
}
