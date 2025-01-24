export default {
	getMobilitaeten (url, config, params){
		return this.$fhcApi.get('api/frontend/v1/stv/mobility/getMobilitaeten/' + params.id);
	},
	getProgramsMobility(){
		return this.$fhcApi.get('api/frontend/v1/stv/mobility/getProgramsMobility/');
	},
	addNewMobility(data){
		//TODO(Manu) formvalidation
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
	getPurposes(url, config, params){
		console.log("in getPurposes");
		//console.log(params);
		return this.$fhcApi.get('api/frontend/v1/stv/mobility/getPurposes/' + params.id);
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

}