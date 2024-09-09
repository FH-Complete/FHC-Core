export default {
	getAllBetriebsmittel(url, config, params){
		return this.$fhcApi.get('api/frontend/v1/betriebsmittel/betriebsmittelP/getAllBetriebsmittel/' + params.type + '/' + params.id);
	},
	addNewBetriebsmittel(form, person_id, formData) {
		return this.$fhcApi.post(form, 'api/frontend/v1/betriebsmittel/betriebsmittelP/addNewBetriebsmittel/' +
			person_id, formData
		);
	},
	loadBetriebsmittel(betriebsmittelperson_id){
		return this.$fhcApi.post('api/frontend/v1/betriebsmittel/betriebsmittelP/loadBetriebsmittel/' + betriebsmittelperson_id);
	},
	updateBetriebsmittel(form, betriebsmittelperson_id, formData) {
		return this.$fhcApi.post(form, 'api/frontend/v1/betriebsmittel/betriebsmittelP/updateBetriebsmittel/' + betriebsmittelperson_id,
			formData);
	},
	deleteBetriebsmittel(betriebsmittelperson_id){
		return this.$fhcApi.post('api/frontend/v1/betriebsmittel/betriebsmittelP/deleteBetriebsmittel/' +	betriebsmittelperson_id);
	},
	getTypenBetriebsmittel(){
		return this.$fhcApi.get('api/frontend/v1/betriebsmittel/betriebsmittelP/getTypenBetriebsmittel/');
	},
	loadInventarliste(query){
		return this.$fhcApi.get('api/frontend/v1/betriebsmittel/betriebsmittelP/loadInventarliste/' +	query);
	}
}