export default {
	getAllVertraege(url, config, params){
		return this.$fhcApi.get('api/frontend/v1/vertraege/vertraege/getAllVertraege/' + params.person_id);
	},
	getAllContractsNotAssigned(url, config, params){
		return this.$fhcApi.get('api/frontend/v1/vertraege/vertraege/getAllContractsNotAssigned/' + params.person_id);
	},
	getAllContractsAssigned(url, config, params){
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
	addNewContract(form, data) {
		return this.$fhcApi.post(form,'api/frontend/v1/vertraege/vertraege/addNewContract/', data);
	},
	loadContract(vertrag_id){
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/loadContract/' + vertrag_id);
	},
	updateContract(form, data) {
		return this.$fhcApi.post(form,'api/frontend/v1/vertraege/vertraege/updateContract/', data);
	},
	deleteContract(vertrag_id){
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/deleteContract/' + vertrag_id);
	},
	loadContractStatus(params){
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/loadContractStatus/' + params.vertrag_id, params);
	},
	insertContractStatus(form, params) {
		return this.$fhcApi.post(form,'api/frontend/v1/vertraege/vertraege/insertContractStatus/' + params.vertrag_id, params);
	},
	updateContractStatus(form, params) {
		return this.$fhcApi.post(form,'api/frontend/v1/vertraege/vertraege/updateContractStatus/' + params.vertrag_id, params);
	},
	deleteContractStatus(params) {
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/deleteContractStatus/' + params.vertrag_id, params);
	},
	deleteLehrauftrag(params) {
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/deleteLehrauftrag/' + params.vertrag_id, params);
	},
	deleteBetreuung(params) {
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/deleteBetreuung/' + params.vertrag_id, params);
	},
	getMitarbeiter(params){
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/getMitarbeiter/');
	},
	getHeader(person_id){
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/getHeader/' + person_id);
	},
	getPersonAbteilung(person_id){
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/getPersonAbteilung/' + person_id);
	},
	getLeitungOrg(oekurzbz){
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/getLeitungOrg/' + oekurzbz);
	},
	getMitarbeiter_uid(person_id){
		return this.$fhcApi.get('api/frontend/v1/vertraege/vertraege/getMitarbeiter_uid/' + person_id);
	},

}