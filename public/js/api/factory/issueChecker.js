export default {

	checkPerson(person_id)
	{
		return {
			method: 'post',
			url: '/api/frontend/v1/issues/StudentIssueChecker/checkPerson',  
			params: { person_id }
		};
	},
	countPersonOpenIssues(person_id)
	{
		return {
			method: 'get',
			url: '/api/frontend/v1/issues/StudentIssueChecker/countPersonOpenIssues',
			params: { person_id }
		};
	}

}