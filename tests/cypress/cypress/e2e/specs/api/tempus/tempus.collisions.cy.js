const KALENDER_API = "/index.ci.php/api/frontend/v1/tempus";

const getPlannerEvents = (startDate, endDate) =>
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
    });

const updateKalenderEvent = (kalenderId, startDateTime, endDateTime) =>
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
  });

const getSettingsData = () => ({
  ignore_kollision: false,
  kollision_student: false,
  ignore_reservierung: false,
  ignore_zeitsperre: false,
});

const updateSettingsData = (options) =>
  cy.request({
    method: "POST",
    url: `${KALENDER_API}/config/set`,
    failOnStatusCode: false,
    headers: { "Cache-Control": "no-cache", Pragma: "no-cache" },
    body: { ...options },
  });

describe("Tempus Kalender API", () => {
  beforeEach(() => {
    cy.login();
  });

  it("event update works for non collision case", () => {
    updateSettingsData(getSettingsData()).then((response) => {
      getPlannerEvents("2026-06-02", "2026-06-02").then((events) => {
        const sourceEvent = events.find(
          (event) =>
            event.type === "lehreinheit" &&
            event.beginn === "19:30:00" &&
            event.ende === "20:15:00" &&
            event.organisationseinheit === "kfSprachen",
        );
        expect(sourceEvent, "source event without collision for update test").to
          .exist;

        const startDateTime = `${sourceEvent.datum} 20:15`;
        const endDateTime = `${sourceEvent.datum} 21:00`;

        updateKalenderEvent(
          sourceEvent.kalender_id,
          startDateTime,
          endDateTime,
        ).then((response) => {
          expect(response.status).to.eq(200);
          expect(response.body).to.have.nested.property(
            "meta.status",
            "success",
          );
        });
      });
    });
  });

  it("prohibited event update due to room collision", () => {
    updateSettingsData(getSettingsData()).then((response) => {
      getPlannerEvents("2026-06-01", "2026-06-01").then((events) => {
        const sourceEvent = events.find(
          (event) =>
            event.type === "lehreinheit" &&
            event.beginn === "19:30:00" &&
            event.ende === "20:15:00" &&
            event.organisationseinheit === "kfSocialSkills",
        );
        expect(
          sourceEvent,
          "source event with fixed time and room for collision test",
        ).to.exist;

        const startDateTime = `${sourceEvent.datum} 20:15`;
        const endDateTime = `${sourceEvent.datum} 21:00`;

        updateKalenderEvent(
          sourceEvent.kalender_id,
          startDateTime,
          endDateTime,
        ).then((response) => {
          expect(response.status).to.eq(500);
          expect(response.body).to.have.nested.property("meta.status", "error");
          expect(response.body.errors).to.be.an("array");

          let hasRoomCollisionError = response.body.errors.some(
            (error) =>
              error.message.toLowerCase().includes("raum kollision") ||
              error.message.toLowerCase().includes("room collision"),
          );
          expect(
            hasRoomCollisionError,
            "response contains room collision error",
          ).to.be.true;
        });
      });
    });
  });

  it("prohibited event update due to student collision", () => {
    let settingsData = getSettingsData();
    settingsData.kollision_student = true;

    updateSettingsData(settingsData).then((response) => {
      getPlannerEvents("2026-06-01", "2026-06-01").then((events) => {
        const sourceEvent = events.find(
          (event) =>
            event.type === "lehreinheit" &&
            event.beginn === "19:30:00" &&
            event.ende === "20:15:00" &&
            event.organisationseinheit === "kfMathematik",
        );
        expect(
          sourceEvent,
          "source event with fixed time and room for collision test",
        ).to.exist;

        const startDateTime = `${sourceEvent.datum} 20:15`;
        const endDateTime = `${sourceEvent.datum} 21:00`;

        updateKalenderEvent(
          sourceEvent.kalender_id,
          startDateTime,
          endDateTime,
        ).then((response) => {
          expect(response.status).to.eq(500);
          expect(response.body).to.have.nested.property("meta.status", "error");
          expect(response.body.errors).to.be.an("array");
          console.log(response.body.errors);
          let hasStudentCollisionError = response.body.errors.some(
            (error) =>
              error.message.toLowerCase().includes("studierende kollision") ||
              error.message.toLowerCase().includes("student collision"),
          );
          expect(
            hasStudentCollisionError,
            "response contains student collision error",
          ).to.be.true;
        });
      });
    });
  });

  it("prohibited event update due to lector collision", () => {
    let settingsData = getSettingsData();
    settingsData.kollision_student = false;

    updateSettingsData(settingsData).then((response) => {
      getPlannerEvents("2026-06-02", "2026-06-02").then((events) => {
        console.log(events);
        const sourceEvent = events.find(
          (event) =>
            event.type === "lehreinheit" &&
            event.beginn === "19:30:00" &&
            event.ende === "20:15:00" &&
            event.organisationseinheit === "kfWirtschaftRecht",
        );
        expect(
          sourceEvent,
          "source event with fixed time and room for collision test",
        ).to.exist;

        const startDateTime = `${sourceEvent.datum} 20:15`;
        const endDateTime = `${sourceEvent.datum} 21:00`;

        updateKalenderEvent(
          sourceEvent.kalender_id,
          startDateTime,
          endDateTime,
        ).then((response) => {
          expect(response.status).to.eq(500);
          expect(response.body).to.have.nested.property("meta.status", "error");
          expect(response.body.errors).to.be.an("array");
          console.log(response.body.errors);
          let hasLectorCollisionError = response.body.errors.some(
            (error) =>
              error.message.toLowerCase().includes("lektorin kollision") ||
              error.message.toLowerCase().includes("lector collision"),
          );
          expect(
            hasLectorCollisionError,
            "response contains lector collision error",
          ).to.be.true;
        });
      });
    });
  });

  it("prohibited event update due to lector - zeitsperre collision", () => {
    let settingsData = getSettingsData();
    settingsData.kollision_student = false;

    updateSettingsData(settingsData).then((response) => {
      getPlannerEvents("2026-06-01", "2026-06-01").then((events) => {
        const sourceEvent = events.find(
          (event) =>
            event.type === "lehreinheit" &&
            event.beginn === "19:30:00" &&
            event.ende === "20:15:00" &&
            event.organisationseinheit === "bic",
        );
        expect(
          sourceEvent,
          "source event with fixed time and room for collision test",
        ).to.exist;

        const startDateTime = `2026-06-02 20:15`;
        const endDateTime = `2026-06-02 21:00`;

        updateKalenderEvent(
          sourceEvent.kalender_id,
          startDateTime,
          endDateTime,
        ).then((response) => {
          expect(response.status).to.eq(500);
          expect(response.body).to.have.nested.property("meta.status", "error");
          expect(response.body.errors).to.be.an("array");

          let hasTimeLockCollisionError = response.body.errors.some(
            (error) =>
              error.message.toLowerCase().includes("zeitsperre kollision") ||
              error.message.toLowerCase().includes("time lock collision"),
          );
          expect(
            hasTimeLockCollisionError,
            "response contains time lock collision error",
          ).to.be.true;
        });
      });
    });
  });

  it("prohibited event update due to lector - reservation collision", () => {
    let settingsData = getSettingsData();
    settingsData.kollision_student = false;

    updateSettingsData(settingsData).then((response) => {
      getPlannerEvents("2026-06-01", "2026-06-01").then((events) => {
        const sourceEvent = events.find(
          (event) =>
            event.type === "lehreinheit" &&
            event.beginn === "19:30:00" &&
            event.ende === "20:15:00" &&
            event.organisationseinheit === "bic",
        );
        expect(
          sourceEvent,
          "source event with fixed time and room for collision test",
        ).to.exist;

        const startDateTime = `${sourceEvent.datum} 20:15`;
        const endDateTime = `${sourceEvent.datum} 21:00`;

        updateKalenderEvent(
          sourceEvent.kalender_id,
          startDateTime,
          endDateTime,
        ).then((response) => {
          expect(response.status).to.eq(500);
          expect(response.body).to.have.nested.property("meta.status", "error");
          expect(response.body.errors).to.be.an("array");
          console.log(response.body.errors);
          let hasReservationCollisionError = response.body.errors.some(
            (error) =>
              error.message.toLowerCase().includes("reservierung kollision") ||
              error.message.toLowerCase().includes("reservation collision"),
          );
          expect(
            hasReservationCollisionError,
            "response contains reservation collision error",
          ).to.be.true;
        });
      });
    });
  });

  it("prohibited reservation update due to lector - reservation collision", () => {
    let settingsData = getSettingsData();
    settingsData.kollision_student = false;

    updateSettingsData(settingsData).then((response) => {
      getPlannerEvents("2026-06-01", "2026-06-01").then((events) => {
        const sourceEvent = events.find(
          (event) =>
            event.type === "reservierung" &&
            event.beginn === "20:15:00" &&
            event.ende === "21:00:00",
        );
        expect(
          sourceEvent,
          "source event with fixed time and room for collision test",
        ).to.exist;

        const startDateTime = `${sourceEvent.datum} 19:30`;
        const endDateTime = `${sourceEvent.datum} 20:15`;

        updateKalenderEvent(
          sourceEvent.kalender_id,
          startDateTime,
          endDateTime,
        ).then((response) => {
          expect(response.status).to.eq(500);
          expect(response.body).to.have.nested.property("meta.status", "error");
          expect(response.body.errors).to.be.an("array");
          console.log(response.body.errors);
          let hasReservationCollisionError = response.body.errors.some(
            (error) =>
              error.message.toLowerCase().includes("lektorin kollision") ||
              error.message.toLowerCase().includes("reservation collision"),
          );
          expect(
            hasReservationCollisionError,
            "response contains reservation collision error",
          ).to.be.true;
        });
      });
    });
  });
});
