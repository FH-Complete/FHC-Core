export default {
	getLETabs()
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lv/setup/getLETabs/'
		};
	},
	getLVTabs()
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lv/setup/getLVTabs/'
		};
	},
}
