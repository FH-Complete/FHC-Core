export default {
	getDocumentsUnaccepted(url, config, params) {
		return this.$fhcApi.get('api/frontend/v1/stv/dokumente/getDocumentsUnaccepted/' + params.id + '/' + params.studiengang_kz);
	},
	getDocumentsAccepted(url, config, params) {
		return this.$fhcApi.get('api/frontend/v1/stv/dokumente/getDocumentsAccepted/' + params.id + '/' + params.studiengang_kz);
	},
	deleteZuordnung(params){
		return this.$fhcApi.post('api/frontend/v1/stv/dokumente/deleteZuordnung/' + params.prestudent_id + '/' + params.dokument_kurzbz);
	},
	createZuordnung(params){
		return this.$fhcApi.post('api/frontend/v1/stv/dokumente/createZuordnung/'
			+ params.prestudent_id + '/'
			+ params.dokument_kurzbz);
	},
	loadAkte(akte_id){
		return this.$fhcApi.get('api/frontend/v1/stv/dokumente/loadAkte/' + akte_id);
	},
	getDoktypen(){
		return this.$fhcApi.get('api/frontend/v1/stv/dokumente/getDoktypen/');
	},
	updateFile(akte_id, data){
		return this.$fhcApi.post('api/frontend/v1/stv/dokumente/updateAkte/' + akte_id,
			data);
	},
	deleteFile(akte_id){
		return this.$fhcApi.post('api/frontend/v1/stv/dokumente/deleteAkte/' + akte_id);
	},
	uploadFile(prestudent_id, data){
		return this.$fhcApi.post('api/frontend/v1/stv/dokumente/uploadDokument/' + prestudent_id,
			data);
	},
}