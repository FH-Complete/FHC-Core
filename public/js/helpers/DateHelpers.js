
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
		const jetzt = luxon.DateTime.local().setZone(zone);
		if (jetzt.isValid) return new Date(jetzt.year, jetzt.month - 1, jetzt.day);
	}

	// without the time zone of the instance the browser decides
	const lokal = new Date();
	return new Date(lokal.getFullYear(), lokal.getMonth(), lokal.getDate());
}
