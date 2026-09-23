export default {
	getNotizen(url, config, params){
		return this.$fhcApi.get('api/frontend/v1/notiz/notizMitarbeiter/getNotizen/' + params.id + '/' + params.type);
	},
	getUid(){
		return this.$fhcApi.get('api/frontend/v1/notiz/notizMitarbeiter/getUid/');
	},
	addNewNotiz(id, formData) {
		return this.$fhcApi.post('api/frontend/v1/notiz/notizMitarbeiter/addNewNotiz/' + id,
			formData
		);
	},
	loadNotiz(notiz_id){
		return this.$fhcApi.post('api/frontend/v1/notiz/notizMitarbeiter/loadNotiz/', {
			notiz_id
		});
	},
	loadDokumente(notiz_id){
		return this.$fhcApi.post('api/frontend/v1/notiz/notizMitarbeiter/loadDokumente/', {
			notiz_id
		});
	},
	deleteNotiz(notiz_id, type_id, id){
		return this.$fhcApi.post('api/frontend/v1/notiz/notizMitarbeiter/deleteNotiz/', {
			notiz_id,
			type_id,
			id
		});
	},
	updateNotiz(notiz_id, formData){
		return this.$fhcApi.post('api/frontend/v1/notiz/notizMitarbeiter/updateNotiz/' + notiz_id,
			formData
		);
	},
	getMitarbeiter(event){
		return this.$fhcApi.get('api/frontend/v1/notiz/notizMitarbeiter/getMitarbeiter/' + event);
	}
}