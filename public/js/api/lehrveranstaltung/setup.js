export default {
	getTabs()
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lv/setup/getTabs/'
		};
	},
	getTab()
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lv/setup/getTab/'
		};
	},
}
