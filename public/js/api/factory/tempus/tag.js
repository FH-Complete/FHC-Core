export default {

	getTag(data)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/tempus/Tags/getTag',
			params: data
		};
	},

	getTags(data)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/tempus/Tags/getTags'
		};
	},
	getTagsByCalendar(calenderGroupId)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/tempus/Tags/getTagsByCalendar/' + calenderGroupId,
		};
	},
	addTag(data)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/tempus/Tags/addTag',
			params: data
		};
	},
	updateTag(data)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/tempus/Tags/updateTag',
			params: data
		};
	},
	doneTag(data)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/tempus/Tags/doneTag',
			params: data
		};
	},

	deleteTag(data)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/tempus/Tags/deleteTag',
			params: data
		};
	},
};
