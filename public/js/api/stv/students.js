export default {
	uid(uid) {
		return this.$fhcApi.getUri('api/frontend/v1/stv/students/uid/' + uid);
	},
	prestudent(prestudent_id) {
		return this.$fhcApi.getUri('api/frontend/v1/stv/students/prestudent/' + prestudent_id);
	},
	person(person_id) {
		return this.$fhcApi.getUri('api/frontend/v1/stv/students/person/' + person_id);
	},
	verband(relative_path) {
		return this.$fhcApi.getUri('api/frontend/v1/stv/students/' + relative_path);
	}
}