export default {
	get() {
		return {
			method: 'get',
			url: '/api/frontend/v1/lv/config/get'
		};
	},
	set(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/lv/config/set',
			params
		};
	}
};
