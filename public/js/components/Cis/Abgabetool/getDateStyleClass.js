
import { getNow, toDateTime } from "../../../helpers/DateHelpers.js";

export function getDateStyleClass(termin, notenOptions) {
	const today = getNow();
	const datum = toDateTime(termin.datum)?.endOf('day');
	const abgabedatum = toDateTime(termin.abgabedatum);
	termin.diffindays = datum ? datum.diff(today, 'days').days : NaN;
	const isLate = abgabedatum && datum && abgabedatum > datum;

	// GRADE STATUS
	if (termin.note) {
		const opt = typeof termin.note === 'object' ? termin.note : notenOptions.find(nopt => nopt.note == termin.note)
		if (opt?.positiv === true) return 'bestanden';
		else if (opt?.positiv === false) return 'nichtbestanden';
	}
	
	// ACTION REQUIRED FOR GRADE
	if (termin.bezeichnung?.benotbar && datum <= today) {
		return 'beurteilungerforderlich';
	}

	// SUBMISSION STATUS
	if (termin.upload_allowed) {
		if (termin.abgabedatum) {
			return isLate ? 'verspaetet' : 'abgegeben';
		}

		// no submission yet
		if (datum < today) return 'verpasst';
		
	}

	// GENERIC STATUS — applies to all termine
	if (datum < today) return 'verpasst';
	if (termin.diffindays <= 12) return 'abzugeben';
	return 'standard';
}
