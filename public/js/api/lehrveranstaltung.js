export default {

	getByLV(lehrveranstaltung_id)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/Lehrveranstaltung/loadByLV/' + encodeURIComponent(lehrveranstaltung_id)
		};
	},
	getByStg(studiensemester_kurzbz, studiengang_kz, semester = null)
	{
		let path = "/api/frontend/v1/Lehrveranstaltung/getByStg/" + encodeURIComponent(studiensemester_kurzbz) + "/" + encodeURIComponent(studiengang_kz);

		if (semester)
			path += "/" + encodeURIComponent(semester);

		return path;
	},
	getByEmp(studiensemester_kurzbz, mitarbeiter_uid, stg = null)
	{
		let path = "/api/frontend/v1/Lehrveranstaltung/getByEmp/" + encodeURIComponent(studiensemester_kurzbz) + "/" + encodeURIComponent(mitarbeiter_uid);

		if (stg)
			path += "/" + encodeURIComponent(stg);

		return path;
	},
	getTable(url)
	{
		return {
			method: 'get',
			url: url
		};
	},

}