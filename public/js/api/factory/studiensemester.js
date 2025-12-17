export default {

	getAll(order = null, start = null)
	{
		return {
			method: 'get',
			url: 'api/frontend/v1/organisation/studiensemester/getAll',
			params: { order, start }
		};
	}
}