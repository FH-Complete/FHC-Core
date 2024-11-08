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
/*	addNewContract(person_id, formData) {
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/addNewContract/' +
			person_id, formData
		);
	},*/
	addNewContract(data) {
		//TODO(Manu) Refactor
		const { person_id, formData, clickedRows } = data;
		console.log("Person ID:", person_id);
		console.log("Form Data:", formData);
		console.log("Clicked Rows:", clickedRows);
		//return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/addNewContract/' + Object.values(params.person_id).join('/') + '/' +  Object.values(params.formData).join('/') + '/' + Object.values(params.clickedRows).join('/'));
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/addNewContract/', data);
	},
	loadContract(vertrag_id){
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/loadContract/' + vertrag_id);
	},
/*	updateContract(vertrag_id, formData) {
		return this.$fhcApi.post( 'api/frontend/v1/vertraege/vertraege/updateContract/' + vertrag_id,
			formData);
	},*/
	updateContract(data) {
		//TODO(Manu) Refactor
		const { vertrag_id, person_id, formData, clickedRows } = data;
		console.log("Person ID:", person_id);
		console.log("Vertrag ID:", vertrag_id);
		console.log("Form Data:", formData);
		console.log("Clicked Rows:", clickedRows);
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/updateContract/', data);
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
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/updateContractStatus/' + params.vertrag_id + '/' + params.datum + '/' + Object.values(params.status).join('/'));
	},
	deleteContractStatus(params) {
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/deleteContractStatus/' + Object.values(params.vertrag_id).join('/') + '/' +  Object.values(params.status).join('/'));
	},
	deleteLehrauftrag(params) {
		//TODO Manu (Refactor!)
		console.log(params.vertrag_id, params.lehreinheit_id, params.mitarbeiter_uid);
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/deleteLehrauftrag/' + Object.values(params.vertrag_id).join('/') + '/' +  Object.values(params.lehreinheit_id).join('/') + '/' + Object.values(params.mitarbeiter_uid).join('/'));
	},
	deleteBetreuung(params) {
		console.log(params.vertrag_id, params.person_id, params.projektarbeit_id, params.betreuerart_kurzbz);
		return this.$fhcApi.post('api/frontend/v1/vertraege/vertraege/deleteBetreuung/' + Object.values(params.vertrag_id).join('/') + '/' +  Object.values(params.person_id).join('/') + '/' + Object.values(params.projektarbeit_id).join('/') + '/' + Object.values(params.betreuerart_kurzbz).join('/'));
	}
}