export default {
	getMessages(url, config, params){
		console.log("in api", params);
		return this.$fhcApi.get('api/frontend/v1/messages/messages/getMessages/' + params.id + '/' + params.type);
	},
}