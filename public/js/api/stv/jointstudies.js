export default {
	getStudies(url, config, params) {
		return this.$fhcApi.get('api/frontend/v1/stv/GemeinsameStudien/getStudien/' + params.id);
	},
	getTypenMobility(){
		return this.$fhcApi.get('api/frontend/v1/stv/GemeinsameStudien/getTypenMobility/');
	},
	getStudiensemester(){
		return this.$fhcApi.get('api/frontend/v1/stv/GemeinsameStudien/getStudiensemester/');
	},
	getStudyprograms(){
		return this.$fhcApi.get('api/frontend/v1/stv/GemeinsameStudien/getStudienprogramme/');
	},
	getListPartner(){
		return this.$fhcApi.get('api/frontend/v1/stv/GemeinsameStudien/getPartnerfirmen/');
	},
	getStatiPrestudent(){
		return this.$fhcApi.get('api/frontend/v1/stv/GemeinsameStudien/getStatiPrestudent/');
	},
	loadStudy(id){
		return this.$fhcApi.get('api/frontend/v1/stv/GemeinsameStudien/loadStudie/' + id);
	},
	insertStudy(form, data){
		return this.$fhcApi.post(form,'api/frontend/v1/stv/GemeinsameStudien/insertStudie/', data);
	},
	updateStudy(form, data){
		return this.$fhcApi.post(form,'api/frontend/v1/stv/GemeinsameStudien/updateStudie/', data);
	},
	deleteStudy(id){
		return this.$fhcApi.post('api/frontend/v1/stv/GemeinsameStudien/deleteStudie/' + id);
	},

}