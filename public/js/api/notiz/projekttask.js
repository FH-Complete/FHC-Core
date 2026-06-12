export default {
	getNotizen(url, config, params){
		return this.$fhcApi.get('api/frontend/v1/notiz/notizProjekttask/getNotizen/' + params.id + '/' + params.type);
	},
	getUid(){
		return this.$fhcApi.get('api/frontend/v1/notiz/notizProjekttask/getUid/');
	},
	addNewNotiz(id, formData) {
		return this.$fhcApi.post('api/frontend/v1/notiz/notizProjekttask/addNewNotiz/' + id,
			formData
		);
	},
	loadNotiz(notiz_id){
		return this.$fhcApi.post('api/frontend/v1/notiz/notizProjekttask/loadNotiz/', {
			notiz_id
		});
	},
	loadDokumente(notiz_id){
		return this.$fhcApi.post('api/frontend/v1/notiz/notizProjekttask/loadDokumente/', {
			notiz_id
		});
	},
	deleteNotiz(notiz_id, type_id, id){
		return this.$fhcApi.post('api/frontend/v1/notiz/notizProjekttask/deleteNotiz/', {
			notiz_id,
			type_id,
			id
		});
	},
	updateNotiz(notiz_id, formData){
		return this.$fhcApi.post('api/frontend/v1/notiz/notizProjekttask/updateNotiz/' + notiz_id,
			formData
		);
	},
	getMitarbeiter(event){
		return this.$fhcApi.get('api/frontend/v1/notiz/notizProjekttask/getMitarbeiter/' + event);
	}
}