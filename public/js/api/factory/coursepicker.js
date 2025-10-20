
export default {
	getCourses(searchfilter) {
		return {
			method: 'get',
			url: '/api/frontend/v1/tempus/getCourses',
			params: {  searchfilter }
		};
	}
};
