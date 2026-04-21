export default {
  getAllOrganizationalUnits() {
    return {
      method: "get",
      url: "api/frontend/v1/organisation/organizationalUnitApi/getAllOrganizationalUnits",
    };
  },
}