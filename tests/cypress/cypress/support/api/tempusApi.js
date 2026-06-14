const KALENDER_API = "/index.ci.php/api/frontend/v1/tempus";

export const tempusApi = {
  getStudyPlansTree: () =>
    cy
      .request({
        method: "GET",
        url: `/index.ci.php/api/frontend/v1/lv/StgTree`,
      })
      .then((response) => {
        expect(response.status).to.eq(200);
        expect(response.body).to.have.nested.property("meta.status", "success");
        expect(response.body.data).to.be.an("array");

        return response.body.data;
      }),

  getCoursesByStudyPlan: (studyPlanId, semesterShortCode) =>
    cy
      .request({
        method: "GET",
        url: `/index.ci.php/api/frontend/v1/tempus/coursepicker/getByStg`,
        qs: {
          stg: studyPlanId,
          studiensemester_kurzbz: semesterShortCode,
        },
      })
      .then((response) => {
        expect(response.status).to.eq(200);
        expect(response.body).to.have.nested.property("meta.status", "success");
        expect(response.body.data).to.be.an("array");

        return response.body.data;
      }),

  getPlannerEvents: (startDate, endDate) =>
    cy
      .request({
        method: "GET",
        url: `${KALENDER_API}/Kalender/getPlan`,
        qs: {
          start_date: startDate,
          end_date: endDate,
        },
      })
      .then((response) => {
        expect(response.status).to.eq(200);
        expect(response.body).to.have.nested.property("meta.status", "success");
        expect(response.body.data).to.be.an("array");

        return response.body.data;
      }),

  createKalenderEvent: (lehreinheitId, startDateTime, endDateTime) =>
    cy.request({
      method: "POST",
      url: `${KALENDER_API}/Kalender/addKalenderEvent`,
      body: {
        lehreinheit_id: lehreinheitId,
        start_date: startDateTime,
        end_date: endDateTime,
      },
      failOnStatusCode: false,
    }),

  updateKalenderEvent: (kalenderId, startDateTime, endDateTime) =>
    cy.request({
      method: "POST",
      url: `${KALENDER_API}/Kalender/updateKalenderEvent`,
      form: true,
      body: {
        kalender_id: kalenderId,
        "updatedInfos[start_time]": startDateTime,
        "updatedInfos[end_time]": endDateTime,
      },
      failOnStatusCode: false,
    }),

  deleteKalenderEvent: (kalenderId) =>
    cy.request({
      method: "POST",
      url: `${KALENDER_API}/Kalender/deleteEntry`,
      form: true,
      body: {
        kalender_id: kalenderId,
      },
      failOnStatusCode: false,
    }),

  getSettingsData: () => ({
    ignore_kollision: false,
    kollision_student: false,
    ignore_reservierung: false,
    ignore_zeitsperre: false,
  }),

  updateSettingsData: (options) =>
    cy.request({
      method: "POST",
      url: `${KALENDER_API}/config/set`,
      failOnStatusCode: false,
      headers: { "Cache-Control": "no-cache", Pragma: "no-cache" },
      body: { ...options },
    }),
};
