export default {
	getStudiensemester()
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lv/setup/getStudiensemester/'
		};
	},
	getSprache()
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lv/setup/getSprache/'
		};
	},
	getLehrform()
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lv/setup/getLehrform/'
		};
	},
	getRaumtyp()
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lv/setup/getRaumtyp/'
		};
	},
}
