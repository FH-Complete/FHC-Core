
const zone = 'Europe/Vienna';
const today = luxon.DateTime.now().setZone(zone);

export function getDateStyleClass(termin, notenOptions) {
	const datum = luxon.DateTime.fromISO(termin.datum, { zone }).endOf('day');
	const abgabedatum = termin.abgabedatum ? luxon.DateTime.fromISO(termin.abgabedatum, { zone }) : null;
	termin.diffindays = datum.diff(today, 'days').days;
	const isLate = abgabedatum && abgabedatum > datum;

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
		if (termin.diffindays <= 12) return 'abzugeben';
		return 'standard';
	}

	// GENERIC STATUS
	return datum < today ? 'verpasst' : 'standard';
}