export default {

	getByLV(lehrveranstaltung_id)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/Lehrveranstaltung/loadByLV/' + encodeURIComponent(lehrveranstaltung_id)
		};
	},
	getByStg(studiengang_kz, semester)
	{
		return ("/api/frontend/v1/Lehrveranstaltung/loadByStudiengang/" + encodeURIComponent(studiengang_kz) + "/" + encodeURIComponent(semester));
	},

	getByEmpStg(mitarbeiter_uid, stg)
	{
		return ("/api/frontend/v1/Lehrveranstaltung/loadByEmployee/" + encodeURIComponent(mitarbeiter_uid) + "/" + encodeURIComponent(stg));
	},

	getTable(url)
	{
		return {
			method: 'get',
			url: url
		};
	},

}