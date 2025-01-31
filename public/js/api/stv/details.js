export default {
	get(prestudent_id) {
		return this.$fhcApi.post('api/frontend/v1/stv/student/get/' + prestudent_id);
	},
	save(form, prestudent_id, data) {
		return this.$fhcApi.post(form, 'api/frontend/v1/stv/student/save/' + prestudent_id,
			data
		);
	},
}
