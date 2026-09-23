export default {
	copy(data)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/lv/lehreinheit/copy/',
			params: data
		};
	},
	delete(data)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/lv/lehreinheit/delete/',
			params: data
		};
	},
	add(newData)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/lv/lehreinheit/add/',
			params: newData
		};
	},
	get(lehreinheit)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/lv/lehreinheit/get/' + encodeURIComponent(lehreinheit)
		};
	},
	update(updatedData)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/lv/lehreinheit/update/',
			params: updatedData
		};
	},
}
