export default {
	getNotizen(url, config, params){
		return this.$fhcApi.get('api/frontend/v1/notiz/notizAnrechnung/getNotizen/' + params.id + '/' + params.type);
	},
	getUid(){
		return this.$fhcApi.get('api/frontend/v1/notiz/notizAnrechnung/getUid/');
	},
	addNewNotiz(id, formData) {
		return this.$fhcApi.post('api/frontend/v1/notiz/notizAnrechnung/addNewNotiz/' + id,
			formData
		);
	},
	loadNotiz(notiz_id){
		return this.$fhcApi.post('api/frontend/v1/notiz/notizAnrechnung/loadNotiz/', {
			notiz_id
		});
	},
	loadDokumente(notiz_id){
		return this.$fhcApi.post('api/frontend/v1/notiz/notizAnrechnung/loadDokumente/', {
			notiz_id
		});
	},
	deleteNotiz(notiz_id, type_id, id){
		return this.$fhcApi.post('api/frontend/v1/notiz/notizAnrechnung/deleteNotiz/', {
			notiz_id,
			type_id,
			id
		});
	},
	updateNotiz(notiz_id, formData){
		return this.$fhcApi.post('api/frontend/v1/notiz/notizAnrechnung/updateNotiz/' + notiz_id,
			formData
		);
	},
	getMitarbeiter(event){
		return this.$fhcApi.get('api/frontend/v1/notiz/notizAnrechnung/getMitarbeiter/' + event);
	}
}