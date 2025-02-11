export default {
	getMessages(url, config, params){
		return this.$fhcApi.get('api/frontend/v1/messages/messages/getMessages/' + params.id + '/' + params.type);
	},
}