export default {

//------------- Prestudent.js------------------------------------------------------

	get(prestudent_id){
		return 	this.$fhcApi.post('api/frontend/v1/stv/prestudent/get/' + prestudent_id);
	},
	updatePrestudent(prestudent_id, data){
		return this.$fhcApi.post('api/frontend/v1/stv/prestudent/updatePrestudent/' + prestudent_id,
			data
		);
	},
	getBezeichnungZGV() {
		return this.$fhcApi.get('api/frontend/v1/stv/prestudent/getBezeichnungZGV/');
	},
	getBezeichnungMZgv() {
		return this.$fhcApi.get('api/frontend/v1/stv/prestudent/getBezeichnungMZgv/');
	},
	getBezeichnungDZgv() {
		return this.$fhcApi.get('api/frontend/v1/stv/prestudent/getBezeichnungDZgv/');
	},
	getStgs() {
		return this.$fhcApi.get('api/frontend/v1/stv/lists/getStgs/');
	},
	getAusbildung() {
		return this.$fhcApi.get('api/frontend/v1/stv/prestudent/getAusbildung/');
	},
	getAufmerksamdurch() {
		return this.$fhcApi.get('api/frontend/v1/stv/prestudent/getAufmerksamdurch/');
	},
	getBerufstaetigkeit() {
		return this.$fhcApi.get('api/frontend/v1/stv/prestudent/getBerufstaetigkeit/');
	},
	getTypenStg() {
		return this.$fhcApi.get('api/frontend/v1/stv/prestudent/getTypenStg/');
	},
	getBisstandort() {
		return this.$fhcApi.get('api/frontend/v1/stv/prestudent/getBisstandort/');
	},

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