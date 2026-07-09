export default {
	search(query) {
		return {
			method: 'get',
			url: 'api/frontend/v1/tempus/coursepicker/search',
			params: { query }
		};
	},
	getByStg(studiengaenge, studiensemester_kurzbz) {
		return {
			method: 'post',
			url: 'api/frontend/v1/tempus/coursepicker/getByStg',
			params: { studiengaenge, studiensemester_kurzbz }
		};
	},

};
