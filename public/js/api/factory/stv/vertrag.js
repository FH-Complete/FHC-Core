export default {

	getVertrag(vertrag_id)
	{
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/vertrag/getVertrag',
			params: { vertrag_id },
		};
	},

	cancelVertrag(data)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/stv/vertrag/cancelVertrag/',
			params: data
		};
	}
}
