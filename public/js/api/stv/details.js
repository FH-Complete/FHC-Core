export default {
	get(prestudent_id, studiensemester_kurzbz) {
		let url = 'api/frontend/v1/stv/student/get/'
			+ encodeURIComponent(prestudent_id)
			+ '/'
			+ encodeURIComponent(studiensemester_kurzbz);
		return this.$fhcApi.post(url);
	},
	save(form, prestudent_id, studiensemester_kurzbz, data) {
		let url = 'api/frontend/v1/stv/student/save/'
			+ encodeURIComponent(prestudent_id)
			+ '/'
			+ encodeURIComponent(studiensemester_kurzbz);
		return this.$fhcApi.post(form, url, data);
	},
}
