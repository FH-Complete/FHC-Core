export default {
	list() {
		return this.$fhcApi.get('api/frontend/v1/stv/grades/list');
	},
	getCertificate(prestudent_id, all) {
		all = all ? '/all' : '';
		return this.$fhcApi.get('api/frontend/v1/stv/grades/getCertificate/' + prestudent_id + all);
	},
	updateCertificate(data) {
		return this.$fhcApi.post('api/frontend/v1/stv/grades/updateCertificate', data);
	},
	getGradeFromPoints(points) {
		return this.$fhcApi.post('api/frontend/v1/stv/grades/getGradeFromPoints', data);
	}
}