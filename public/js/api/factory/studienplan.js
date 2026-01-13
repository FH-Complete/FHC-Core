export default {

	getStudienplaeneBySemester(studiengang_kz, studiensemester_kurzbz, ausbildungssemester, orgform_kurzbz)
	{
		return {
			method: 'get',
			url: 'api/frontend/v1/organisation/studienplan/getBySemester',
			params: { studiengang_kz, studiensemester_kurzbz, ausbildungssemester, orgform_kurzbz },
		};
	}
}