import { getDateForDay } from "../helpers/date";

const KALENDER_API = "/index.ci.php/api/frontend/v1/tempus";

const updatePlannerEventsForDay = (day, predicate, startTime, endTime) => {
  const date = getDateForDay(day);

  return tempusApi.getPlannerEvents(date, date).then((events) =>
    cy.wrap(events).each((event) => {
      if (!predicate(event)) {
        return null;
      }

      return tempusApi.updateKalenderEvent(
        event.kalender_id,
        `${event.datum} ${startTime}`,
        `${event.datum} ${endTime}`,
      );
    }),
  );
};

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

  getStudySemesters: () =>
    cy
      .request({
        method: "GET",
        url: `/index.ci.php/api/frontend/v1/organisation/Studiensemester/getAll`,
        qs: {
          order: "DESC",
        },
      })
      .then((response) => {
        expect(response.status).to.eq(200);
        expect(response.body).to.have.nested.property("meta.status", "success");
        expect(response.body.data).to.be.an("array");

        return response.body.data;
      }),

  getCoursesByStudyPlan: (studyPlanId, semesterShortCode) => {
    let parsedStudyPlans = [
      {
        studiengang_kz: studyPlanId,
      },
    ];

    return cy
      .request({
        method: "POST",
        url: `/index.ci.php/api/frontend/v1/tempus/coursepicker/getByStg`,
        body: {
          studiengaenge: parsedStudyPlans,
          studiensemester_kurzbz: semesterShortCode,
        },
      })
      .then((response) => {
        expect(response.status).to.eq(200);
        expect(response.body).to.have.nested.property("meta.status", "success");
        expect(response.body.data).to.be.an("array");

        return response.body.data;
      });
  },

  getPlannerEvents: (startDate, endDate) =>
    cy
      .request({
        method: "POST",
        url: `${KALENDER_API}/Kalender/getPlan`,
        body: {
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

  deletePlannerEventsForDay: (day) => {
    const date = getDateForDay(day);

    return tempusApi
      .getPlannerEvents(date, date)
      .then((events) =>
        cy
          .wrap(events)
          .each((event) => tempusApi.deleteKalenderEvent(event.kalender_id)),
      );
  },

  resetTempusScenario: () =>
    tempusApi
      .deletePlannerEventsForDay("monday")
      .then(() =>
        updatePlannerEventsForDay(
          "tuesday",
          (event) =>
            event.type === "lehreinheit" &&
            event.beginn === "20:15:00" &&
            event.ende === "21:00:00",
          "19:30",
          "20:15",
        ),
      )
      .then(() =>
        updatePlannerEventsForDay(
          "sunday",
          (event) =>
            event.type === "lehreinheit" &&
            event.beginn === "18:35:00" &&
            event.ende === "19:20:00" &&
            event.lektor?.some((lector) => lector.kurzbz === "DemoLKT1"),
          "17:50",
          "18:35",
        ),
      ),

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
    ignore_resources_collisions: false,
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
