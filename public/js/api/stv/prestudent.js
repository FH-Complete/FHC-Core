export default {

//------------- MultiStatus.js------------------------------------------------------

	getHistoryPrestudent (url, config, params){
		return this.$fhcApi.get('api/frontend/v1/stv/status/getHistoryPrestudent/' + params.id);
	},
	getMaxSem(studiengang_kzs) {
		return 	this.$fhcApi.post('api/frontend/v1/stv/status/getMaxSemester/', {studiengang_kzs});
	},
	advanceStatus(status_id) {
		return this.$fhcApi.post('api/frontend/v1/stv/status/advanceStatus/' + Object.values(status_id).join('/'));
	},
	confirmStatus(status_id) {
		return this.$fhcApi.post('api/frontend/v1/stv/status/confirmStatus/' + Object.values(status_id).join('/'));
	},
	isLastStatus(id) {
		return this.$fhcApi.get('api/frontend/v1/stv/status/isLastStatus/' + id);
	},
	deleteStatus(status_id) {
		return this.$fhcApi.post('api/frontend/v1/stv/status/deleteStatus/' + Object.values(status_id).join('/'));
	},
	getLastBismeldestichtag() {
		return this.$fhcApi.get('api/frontend/v1/stv/status/getLastBismeldestichtag/');
	},

//------------- History.js------------------------------------------------------
	getHistoryPrestudents (url, config, params){
		return this.$fhcApi.get('api/frontend/v1/stv/prestudent/getHistoryPrestudents/' + params.id);
	},

}