export default {

	getTag(data)
	{
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/Tags/getTag',
			params: data
		};
	},

	getTags(data)
	{
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/Tags/getTags'
		};
	},

	addTag(data)
	{
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/Tags/addTag',
			params: data
		};
	},

	updateTag(data)
	{
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/Tags/updateTag',
			params: data
		};
	},
	doneTag(data)
	{
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/Tags/doneTag',
			params: data
		};
	},

	deleteTag(data)
	{
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/Tags/deleteTag',
			params: data
		};
	},
};