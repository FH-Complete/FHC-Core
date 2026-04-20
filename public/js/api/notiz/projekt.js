export default {
	getNotizen(url, config, params){
		return this.$fhcApi.get('api/frontend/v1/notiz/NotizProjekt/getNotizen/' + params.id + '/' + params.type);
	},
	getUid(){
		return this.$fhcApi.get('api/frontend/v1/notiz/NotizProjekt/getUid/');
	},
	addNewNotiz(id, formData) {
		return this.$fhcApi.post('api/frontend/v1/notiz/NotizProjekt/addNewNotiz/' + id,
			formData
		);
	},
	loadNotiz(notiz_id){
		return this.$fhcApi.post('api/frontend/v1/notiz/NotizProjekt/loadNotiz/', {
			notiz_id
		});
	},
	loadDokumente(notiz_id){
		return this.$fhcApi.post('api/frontend/v1/notiz/NotizProjekt/loadDokumente/', {
			notiz_id
		});
	},
	deleteNotiz(notiz_id, type_id, id){
		return this.$fhcApi.post('api/frontend/v1/notiz/NotizProjekt/deleteNotiz/', {
			notiz_id,
			type_id,
			id
		});
	},
	updateNotiz(notiz_id, formData){
		return this.$fhcApi.post('api/frontend/v1/notiz/NotizProjekt/updateNotiz/' + notiz_id,
			formData
		);
	},
	getMitarbeiter(event){
		return this.$fhcApi.get('api/frontend/v1/notiz/NotizProjekt/getMitarbeiter/' + event);
	}
}