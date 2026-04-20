export default {

	getStudiensemester: function () {
		return this.$fhcApi.get(
			'/components/Cis/Mylv/Studiensemester',
			{}
		);
	},

	getAllStudienSemester: function (studiensemester=undefined, studiengang=undefined, semester=undefined, studienplan=undefined) {
		return this.$fhcApi.get(
			'/api/frontend/v1/Studium/getStudienAllSemester',
			{studiensemester, studiengang, semester, studienplan}
		);
	},

	getStudiengaengeForStudienSemester: function (studiensemester) {
		return this.$fhcApi.get(
			`/api/frontend/v1/Studium/getStudiengaengeForStudienSemester/${studiensemester}`,
			{}
		);
	},
	getStudienplaeneBySemester: function (studiengang, studiensemester) {
		return this.$fhcApi.get(
			`/api/frontend/v1/Studium/getStudienplaeneBySemester`,
			{
				studiengang,
				studiensemester,
			}
		);
	},
	getLvPlanForStudiensemester: function (studiensemester, lvid) {
		return this.$fhcApi.get(
			`/api/frontend/v1/LvPlan/getLvPlanForStudiensemester/${studiensemester}/${lvid}`,
			{
			}
		);
	},
	getLvEvaluierungInfo: function (studiensemester_kurzbz, lvid) {
		return this.$fhcApi.get(
			`/api/frontend/v1/Studium/getLvEvaluierungInfo/${studiensemester_kurzbz}/${lvid}`,
			{
			}
		);
	},
	
}