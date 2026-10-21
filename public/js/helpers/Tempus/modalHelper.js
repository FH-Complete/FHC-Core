export function normalizeTime(value) {
	if (!value)
		return null;
	if (value instanceof Date)
		return luxon.DateTime.fromJSDate(value).toFormat('HH:mm');

	const dt = luxon.DateTime.fromFormat(value, 'HH:mm:ss');
	return dt.isValid ? dt.toFormat('HH:mm') : value;
}

export function normalizeDate(value) {
	if (!value)
		return null;
	if (value instanceof Date)
		return luxon.DateTime.fromJSDate(value).toFormat('yyyy-MM-dd');

	return value.slice(0, 10);
}
export function parseJsonField(value, fallback = []) {
	if (typeof value !== 'string')
		return value || fallback;

	try {
		return JSON.parse(value);
	} catch (e) {
		console.error('Failed to parse JSON field:', e);
		return fallback;
	}
}

export function profilLink(uid) {
	return FHC_JS_DATA_STORAGE_OBJECT.app_root
		+ FHC_JS_DATA_STORAGE_OBJECT.ci_router
		+ `/Cis/Profil/View/${uid}`;
}

export function buildLektorenLinks(lektoren) {
	if (!Array.isArray(lektoren))
		return {};

	const links = {};
	lektoren.forEach(lektor => {
		links[lektor.kurzbz] = profilLink(lektor.mitarbeiter_uid);
	});
	return links;
}