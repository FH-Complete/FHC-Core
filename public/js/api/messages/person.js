export default {
	getMessages(url, config, params) {
		console.log('page ' + params.page + ' size ' + params.size);
		return this.$fhcApi.get('api/frontend/v1/messages/messages/getMessages/' + params.id + '/' + params.type + '/' + params.size + '/' + params.page);
	},
	getVorlagen(){
		return this.$fhcApi.get('api/frontend/v1/messages/messages/getVorlagen/');
	},
	getMsgVarsLoggedInUser(){
		return this.$fhcApi.get('api/frontend/v1/messages/messages/getMsgVarsLoggedInUser/');
	},
	getMessageVarsPerson(params){
		return this.$fhcApi.get('api/frontend/v1/messages/messages/getMessageVarsPerson/' + params.id + '/' + params.type_id);
	},
	getMsgVarsPrestudent(params){
		return this.$fhcApi.get('api/frontend/v1/messages/messages/getMsgVarsPrestudent/' + params.id + '/' + params.type_id);
	},
	getPersonId(params){
		return this.$fhcApi.get('api/frontend/v1/messages/messages/getPersonId/'+ params.id + '/' + params.type_id);
	},
	getUid(params){
		return this.$fhcApi.get('api/frontend/v1/messages/messages/getUid/'+ params.id + '/' + params.type_id);
	},
	getVorlagentext(vorlage_kurzbz){
		return this.$fhcApi.get('api/frontend/v1/messages/messages/getVorlagentext/' + vorlage_kurzbz);
	},
	getNameOfDefaultRecipient(params){
		return this.$fhcApi.get('api/frontend/v1/messages/messages/getNameOfDefaultRecipient/' + params.id + '/' + params.type_id);
	},
	getPreviewText(params, data){
		return this.$fhcApi.post('api/frontend/v1/messages/messages/getPreviewText/' + params.id + '/' + params.type_id,
			data);
	},
	getReplyData(messageId){
		return this.$fhcApi.get('api/frontend/v1/messages/messages/getReplyData/' + messageId);
	},
	sendMessage(form, id, data) {
		console.log("id" + id);
		return this.$fhcApi.post(form,'api/frontend/v1/messages/messages/sendMessage/' + id,
			data);
	},
/*	sendMessage(id, data) {
		return this.$fhcApi.post('api/frontend/v1/messages/messages/sendMessage/' + id,
			data);
	},*/
	deleteMessage(messageId){
		return this.$fhcApi.post('api/frontend/v1/messages/messages/deleteMessage/' + messageId);
	}
}