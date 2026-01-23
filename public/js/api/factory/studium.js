export default {
	getAllStudienSemester(studiensemester=undefined, studiengang=undefined, semester=undefined, studienplan=undefined) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Studium/getAllStudienSemester',
			params: {studiensemester, studiengang, semester, studienplan}
		};
	},
	getStudiengaengeForStudienSemester(studiensemester) {
		return {
			method: 'get',
			url: `/api/frontend/v1/Studium/getStudiengaengeForStudienSemester/${studiensemester}`,
		};
	},
	getStudienplaeneBySemester(studiengang, studiensemester) {
		return {
			method: 'get',
			url: `/api/frontend/v1/Studium/getStudienplaeneBySemester`,
			params: {
				studiengang,
				studiensemester,
			}
		};
	},
	getLvPlanForStudiensemester(studiensemester_kurzbz, lvid) {
		return {
			method: 'get',
			url: `/api/frontend/v1/LvPlan/getLvPlanForStudiensemester/${studiensemester}/${lvid}`,
		};
	},
	getLvEvaluierungInfo(studiensemester_kurzbz, lvid) {
		return {
			method: 'get',
			url: `/api/frontend/v1/Studium/getLvEvaluierungInfo/${studiensemester_kurzbz}/${lvid}`,
		};
	}
};