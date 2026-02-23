export default {
	uid(uid, studiensemester_kurzbz) {
		let url = 'api/frontend/v1/stv/students/'
			+ encodeURIComponent(studiensemester_kurzbz)
			+ '/uid/'
			+ encodeURIComponent(uid);
		return this.$fhcApi.getUri(url);
	},
	prestudent(prestudent_id, studiensemester_kurzbz) {
		let url = 'api/frontend/v1/stv/students/'
			+ encodeURIComponent(studiensemester_kurzbz)
			+ '/prestudent/'
			+ encodeURIComponent(prestudent_id);
		return this.$fhcApi.getUri(url);
	},
	person(person_id, studiensemester_kurzbz) {
		let url = 'api/frontend/v1/stv/students/'
			+ encodeURIComponent(studiensemester_kurzbz)
			+ '/person/'
			+ encodeURIComponent(person_id);
		return this.$fhcApi.getUri(url);
	},
	verband(relative_path) {
		return this.$fhcApi.getUri('api/frontend/v1/stv/students/' + relative_path);
	}
}