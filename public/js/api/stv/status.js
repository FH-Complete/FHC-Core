export default {

//------------- Modal.js------------------------------------------------------

	insertStatus(id, data) {
		return this.$fhcApi.post('api/frontend/v1/stv/status/insertStatus/' + id,
			data
		);
	},
	loadStatus(status_id) {
		return this.$fhcApi.post('api/frontend/v1/stv/status/loadStatus/' + Object.values(status_id).join('/'));
	},
	updateStatus(status_id, data) {
		return this.$fhcApi.post('api/frontend/v1/stv/status/updateStatus/' + Object.values(status_id).join('/'), data);
	},
	getStudienplaene(prestudent_id) {
		return this.$fhcApi.get('api/frontend/v1/stv/prestudent/getStudienplaene/' + prestudent_id);
	},
	getStudiengang(prestudent_id) {
		return this.$fhcApi.get('api/frontend/v1/stv/prestudent/getStudiengang/' + prestudent_id);
	},
	getStatusgruende() {
		return this.$fhcApi.get('api/frontend/v1/stv/status/getStatusgruende/');
	},
	getStati() {
		return this.$fhcApi.get('api/frontend/v1/stv/lists/getStati/');
	},

//------------- Dropdown.js------------------------------------------------------

	addStudent(id, data) {
		return this.$fhcApi.post('api/frontend/v1/stv/status/addStudent/' + id,
			data,
			{errorHeader: id}
		);
	},
	changeStatus(id, data) {
		return this.$fhcApi.post('api/frontend/v1/stv/status/changeStatus/' + id,
			data,
			{errorHeader: id}
		);
	},
	getStatusarray() {
		return this.$fhcApi.get('api/frontend/v1/stv/status/getStatusarray/');
	}
}