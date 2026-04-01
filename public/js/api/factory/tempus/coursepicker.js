export default {
	search(query) {
		return {
			method: 'get',
			url: 'api/frontend/v1/tempus/coursepicker/search',
			params: { query }
		};
	},
	getByStg(stg, studiensemester_kurzbz) {
		return {
			method: 'get',
			url: 'api/frontend/v1/tempus/coursepicker/getByStg',
			params: { stg, studiensemester_kurzbz }
		};
	},

};
