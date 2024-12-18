export default {
	getMobilitaeten (url, config, params){
		return this.$fhcApi.get('api/frontend/v1/stv/mobility/getMobilitaeten/' + params.id);
	},
}