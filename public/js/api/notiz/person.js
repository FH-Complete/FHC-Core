export default {
	getNotizen (url, config, params){
		return this.$fhcApi.get('api/frontend/v1/notiz/notizPerson/getNotizen/' + params.id + '/' + params.type);
	},
	getUid(){
		return this.$fhcApi.get('api/frontend/v1/notiz/notizPerson/getUid/');
	},
	addNewNotiz(id, formData) {
		return this.$fhcApi.post('api/frontend/v1/notiz/notizPerson/addNewNotiz/' + id,
			formData
		);
	},
	loadNotiz(notiz_id){
		return this.$fhcApi.post('api/frontend/v1/notiz/notizPerson/loadNotiz/', {
			notiz_id
		});
	},
	loadDokumente(notiz_id){
		return this.$fhcApi.post('api/frontend/v1/notiz/notizPerson/loadDokumente/', {
			notiz_id
		});
	},
	deleteNotiz(notiz_id, type_id, id){
		return this.$fhcApi.post('api/frontend/v1/notiz/notizPerson/deleteNotiz/', {
			notiz_id,
			type_id,
			id
		});
	},
	updateNotiz(notiz_id, formData){
		return this.$fhcApi.post('api/frontend/v1/notiz/notizPerson/updateNotiz/' + notiz_id,
			formData
		);
	},
	getMitarbeiter(event){
		return this.$fhcApi.get('api/frontend/v1/notiz/notizPerson/getMitarbeiter/' + event);
	},
	isBerechtigt(id, type_id){
		return this.$fhcApi.get('api/frontend/v1/notiz/notizPerson/isBerechtigt/');
	}
}