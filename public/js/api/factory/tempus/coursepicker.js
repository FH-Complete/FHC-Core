export default {
	getCourses(filter, studiensemester_kurzbz) {
		return {
			method: 'post',
			url: 'api/frontend/v1/tempus/coursepicker/getCourses',
			params: { ...filter, studiensemester_kurzbz }
		};
	},

};
