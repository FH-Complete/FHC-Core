export default {
	getAllVertraege(url, config, params){
		return this.$fhcApi.get('api/frontend/v1/vertraege/vertraege/getAllVertraege/' + params.person_id);
	},
	getAllContractsNotAssigned(url, config, params){
		return this.$fhcApi.get('api/frontend/v1/vertraege/vertraege/getAllContractsNotAssigned/' + params.person_id);
	},
	getAllContractsAssigned(url, config, params){
		console.log(params.person_id, params.vertrag_id);
		return this.$fhcApi.get('api/frontend/v1/vertraege/vertraege/getAllContractsAssigned/' + params.person_id + '/' + params.vertrag_id);
	},
	getAllContractsNotAssigned2(person_id){
		return this.$fhcApi.get('api/frontend/v1/vertraege/vertraege/getAllContractsNotAssigned/' + person_id);
	},
	getStatiOfContract(url, config, params){
		return this.$fhcApi.get('api/frontend/v1/vertraege/vertraege/getStatiOfContract/' + params.vertrag_id);
	},
	getAllContractTypes(){
		return this.$fhcApi.get('api/frontend/v1/vertraege/vertraege/getAllContractTypes/');
	},
	getAllContractStati(){
		return this.$fhcApi.get('api/frontend/v1/vertraege/vertraege/getAllContractStati/');
	},
	addNewContract(person_id, formData) {
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/addNewContract/' +
			person_id, formData
		);
	},
	loadContract(vertrag_id){
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/loadContract/' + vertrag_id);
	},
	updateContract(vertrag_id, formData) {
		return this.$fhcApi.post( 'api/frontend/v1/vertraege/vertraege/updateContract/' + vertrag_id,
			formData);
	},
	deleteContract(vertrag_id){
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/deleteContract/' + vertrag_id);
	},
	loadContractStatus(params){
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/loadContractStatus/' + Object.values(params.vertrag_id).join('/') + '/' + Object.values(params.status).join('/'));
	},
	insertContractStatus(params) {
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/insertContractStatus/' + params.vertrag_id + '/' + params.datum + '/' + Object.values(params.status).join('/'));
	},
	updateContractStatus(params) {
		console.log("API", params.vertrag_id, params.status, params.datum);
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/updateContractStatus/' + params.vertrag_id + '/' + params.datum + '/' + Object.values(params.status).join('/'));
	},
	deleteContractStatus(params) {
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/deleteContractStatus/' + Object.values(params.vertrag_id).join('/') + '/' +  Object.values(params.status).join('/'));
	}
}