export default {
	getMessages(url, config, params){
		return this.$fhcApi.get('api/frontend/v1/messages/messages/getMessages/' + params.id + '/' + params.type);
	},
	getVorlagen(){
		return this.$fhcApi.get('api/frontend/v1/messages/messages/getVorlagen/');
	},
	getMsgVarsLoggedInUser(){
		return this.$fhcApi.get('api/frontend/v1/messages/messages/getMsgVarsLoggedInUser/');
	},
	getMessageVarsPerson(){
		return this.$fhcApi.get('api/frontend/v1/messages/messages/getMessageVarsPerson/');
	},
	getMsgVarsPrestudent(uid){
		return this.$fhcApi.get('api/frontend/v1/messages/messages/getMsgVarsPrestudent/' + uid);
	},
	getPersonIdFromUid(uid){
		return this.$fhcApi.get('api/frontend/v1/messages/messages/getPersonIdFromUid/' + uid);
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
		return this.$fhcApi.post(form,'api/frontend/v1/messages/messages/sendMessage/' + id,
			data);
	},
	deleteMessage(messageId){
		return this.$fhcApi.post('api/frontend/v1/messages/messages/deleteMessage/' + messageId);
	}
}