export default {
	countOpenIssues()
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/tempus/Issues/countOpenIssues'
		};
	},
	getOpenIssues(von, bis)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/tempus/Issues/getOpenIssues',
			params: {von, bis}
		};
	},
	check(von, bis)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/tempus/Issues/check',
			params: {von, bis}
		};
	},
	getTermineZumIssue(issue_id)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/tempus/Issues/getTermineZumIssue',
			params: { issue_id }
		};
	},
	akzeptieren(issue_id, kalender_id = null)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/tempus/Issues/akzeptieren',
			params: { issue_id, kalender_id }
		};
	},
};