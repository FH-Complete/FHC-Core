const DummyPermissionsAPI = "/index.ci.php/system/DummyPermissions";

export const rolesinrolesApi = {
	mainRoleR: function() {
		cy
			.request({
				method: 'GET',
				url: DummyPermissionsAPI,
			})
			.then((response) => {
				expect(response.status).to.eq(200);
				expect(response.body).to.have.nested.property("meta.status", "success");
				expect(response.body.data).to.be.an("array");

				return response.body.data;
			})
	},
};

