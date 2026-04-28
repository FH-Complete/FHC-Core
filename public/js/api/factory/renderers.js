
export default {
		
	loadRenderers() {
		return {
			method: 'get',
			url: '/api/frontend/v1/RendererLoader/GetRenderers',
			params: {
			}
		};
	},
	loadTempusRenderers() {
		return {
			method: 'get',
			url: '/api/frontend/v1/RendererLoader/GetTempusRenderers',
			params: {
			}
		};
	},
}