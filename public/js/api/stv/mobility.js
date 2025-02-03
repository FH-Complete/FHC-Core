export default {
	getMobilitaeten (url, config, params){
		return this.$fhcApi.get('api/frontend/v1/stv/mobility/getMobilitaeten/' + params.id);
	},
	getProgramsMobility(){
		return this.$fhcApi.get('api/frontend/v1/stv/mobility/getProgramsMobility/');
	},
	addNewMobility(data){
		return this.$fhcApi.post('api/frontend/v1/stv/mobility/insertMobility/', data);
	},
	loadMobility(bisio_id){
		return this.$fhcApi.get('api/frontend/v1/stv/mobility/loadMobility/' + bisio_id);
	},
	updateMobility(data){
		return this.$fhcApi.post('api/frontend/v1/stv/mobility/updateMobility/', data);
	},
	deleteMobility(bisio_id){
		return this.$fhcApi.post('api/frontend/v1/stv/mobility/deleteMobility/' + bisio_id);
	},
	getLVList(studiengang_kz){
		return this.$fhcApi.get('api/frontend/v1/stv/mobility/getLVList/' + studiengang_kz);
	},
	getAllLehreinheiten(data){
		return this.$fhcApi.post('api/frontend/v1/stv/mobility/getAllLehreinheiten/', data)
	},
	getLvsandLesByStudent(uid){
		return this.$fhcApi.get('api/frontend/v1/stv/mobility/getLvsandLesByStudent/' + uid);
	},
	getPurposes(url, config, params){
		return this.$fhcApi.get('api/frontend/v1/stv/mobility/getPurposes/' + params.id);
	},
	getSupports(url, config, params){
		return this.$fhcApi.get('api/frontend/v1/stv/mobility/getSupports/' + params.id);
	},
	getListPurposes() {
		return this.$fhcApi.get('api/frontend/v1/stv/mobility/getListPurposes/');
	},
	getListSupports() {
		return this.$fhcApi.get('api/frontend/v1/stv/mobility/getListSupports/');
	},
	deleteMobilityPurpose(params) {
		return this.$fhcApi.post('api/frontend/v1/stv/mobility/deleteMobilityPurpose/' + params.bisio_id, params);
	},
	addMobilityPurpose(params) {
		return this.$fhcApi.post('api/frontend/v1/stv/mobility/addMobilityPurpose/' + params.bisio_id, params);
	},
	deleteMobilitySupport(params) {
		return this.$fhcApi.post('api/frontend/v1/stv/mobility/deleteMobilitySupport/' + params.bisio_id, params);
	},
	addMobilitySupport(params) {
		return this.$fhcApi.post('api/frontend/v1/stv/mobility/addMobilitySupport/' + params.bisio_id, params);
	},

}