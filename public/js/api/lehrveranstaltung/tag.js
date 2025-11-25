export default {

	getTag(data)
	{
		return {
			method: 'get',
			url: 'api/frontend/v1/lv/Tags/getTag',
			params: data
		};
	},

	getTags(data)
	{
		return {
			method: 'get',
			url: 'api/frontend/v1/lv/Tags/getTags'
		};
	},

	addTag(data)
	{
		return {
			method: 'post',
			url: 'api/frontend/v1/lv/Tags/addTag',
			params: data
		};
	},

	updateTag(data)
	{
		return {
			method: 'post',
			url: 'api/frontend/v1/lv/Tags/updateTag',
			params: data
		};
	},
	doneTag(data)
	{
		return {
			method: 'post',
			url: 'api/frontend/v1/lv/Tags/doneTag',
			params: data
		};
	},

	deleteTag(data)
	{
		return {
			method: 'post',
			url: 'api/frontend/v1/lv/Tags/deleteTag',
			params: data
		};
	},
};