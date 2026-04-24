export default {
  getAllStudyPlans() {
    return {
      method: "get",
      url: "api/frontend/v1/organisation/studienplan/getAllStudyPlans",
    };
  },
  getStudyPlansByOrganizationalUnitAndSemesterDates(organizationalUnitShortCode, startDate, endDate) {
    return {
      method: "get",
      url: `api/frontend/v1/organisation/studienplan/getStudyPlansByOrganizationalUnitAndSemesterDates/${organizationalUnitShortCode}?filter[startDate]=${startDate}&filter[endDate]=${endDate}`,
    };
  },
  getStudienplaeneBySemester(
    studiengang_kz,
    studiensemester_kurzbz,
    ausbildungssemester,
    orgform_kurzbz,
  ) {
    return {
      method: "get",
      url: "api/frontend/v1/organisation/studienplan/getBySemester",
      params: {
        studiengang_kz,
        studiensemester_kurzbz,
        ausbildungssemester,
        orgform_kurzbz,
      },
    };
  },
};
