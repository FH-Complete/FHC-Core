const DummyPermissionsAPI = "/index.ci.php/system/DummyPermissions/";

export const rolesinrolesApi = {
	permissionToMainRole: () => {
		cy
			.request({
				method: 'GET',
				url: DummyPermissionsAPI + "permissionToMainRole",
			})
			.then((response) => {
				expect(response.status).to.eq(200);
				expect(response.body).to.equals("DummyPermissions::permissionToMainRole");
			})
	},
	permissionToBasicRole: () => {

		cy
			.request({
				method: 'GET',
				url: DummyPermissionsAPI + "permissionToBasicRole",
			})
			.then((response) => {
				expect(response.status).to.eq(200);
				expect(response.body).to.equals("DummyPermissions::permissionToBasicRole");
			})
	},
	permissionToUser: () => {
		cy
			.request({
				method: 'GET',
				url: DummyPermissionsAPI + "permissionToUser",
			})
			.then((response) => {
				expect(response.status).to.eq(200);
				expect(response.body).to.equals("DummyPermissions::permissionToUser");
			})
	},
};

