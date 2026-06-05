const zone = 'Europe/Vienna';

export function getViennaTodayISO() {
	return luxon.DateTime.now().setZone(zone).toISODate();
}

export function formatISODate(dateParam) {
	if (!dateParam) return '';

	const date = luxon.DateTime.fromISO(String(dateParam), { zone });
	return date.isValid ? date.toFormat('dd.MM.yyyy') : '';
}

export function formatDateTime(dateParam) {
	if (!dateParam) return '';

	const date = luxon.DateTime.fromFormat(dateParam, "yyyy-MM-dd HH:mm:ss");
	return date.isValid ? date.toFormat("dd.MM.yyyy HH:mm") : '';
}

export function toViennaDate(dateParam) {
	if (!dateParam) return null;

	return luxon.DateTime.fromISO(String(dateParam), { zone });
}

export function compareISODateValues(a, b) {
	if (!a && !b) return 0;
	if (!a) return 1;
	if (!b) return -1;

	return String(a).localeCompare(String(b));
}
