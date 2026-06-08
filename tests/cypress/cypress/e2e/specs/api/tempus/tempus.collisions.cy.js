const KALENDER_API = "/index.ci.php/api/frontend/v1/tempus";

const getStudyPlansTree = () =>
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
    });

const getCoursesByStudyPlan = (studyPlanId, semesterShortCode) =>
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
    });

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

const createKalenderEvent = (lehreinheitId, startDateTime, endDateTime) =>
  cy.request({
    method: "POST",
    url: `${KALENDER_API}/Kalender/addKalenderEvent`,
    body: {
      lehreinheit_id: lehreinheitId,
      start_date: startDateTime,
      end_date: endDateTime,
    },
    failOnStatusCode: false,
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

  it("event creation works for non collision case", () => {
    getStudyPlansTree().then((stgTree) => {
      let studyPlan = stgTree.find(
        (plan) => plan.name === "BIF (BIF) - Informatik/Computer Science",
      );
      expect(studyPlan, "study plan for test event creation").to.exist;

      getCoursesByStudyPlan(studyPlan.studiengang_kz, "SS2026").then(
        (courses) => {
          let course = courses.find(
            (course) =>
              course.lehrfach === "KREKO" &&
              course.lektoren.some((lector) => lector.kurzbz === "BoehmTh"),
          );
          expect(course, "course for test event creation").to.exist;

          updateSettingsData(getSettingsData()).then((response) => {
            getPlannerEvents("2026-06-08", "2026-06-08").then((events) => {
              const lehreinheitId = course.unr;
              expect(lehreinheitId, "lehreinheit id for test event creation").to
                .exist;

              const startDateTime = `2026-06-08 18:35`;
              const endDateTime = `2026-06-08 19:20`;

              createKalenderEvent(
                lehreinheitId,
                startDateTime,
                endDateTime,
              ).then((response) => {
                console.log(response);
                expect(response.status).to.eq(200);
                expect(response.body).to.have.nested.property(
                  "meta.status",
                  "success",
                );
              });
            });
          });
        },
      );
    });
  });

  it("prohibited event creation due to zeitsperre collision", () => {
    getStudyPlansTree().then((stgTree) => {
      let studyPlan = stgTree.find(
        (plan) => plan.name === "BMB (BMB) - Maschinenbau",
      );
      expect(studyPlan, "study plan for test event creation").to.exist;

      getCoursesByStudyPlan(studyPlan.studiengang_kz, "SS2026").then(
        (courses) => {
          let course = courses.find(
            (course) =>
              course.lehrfach === "ETMB" &&
              course.lektoren.some((lector) => lector.kurzbz === "CarralSa"),
          );
          expect(course, "course for test event creation").to.exist;

          updateSettingsData(getSettingsData()).then((response) => {
            getPlannerEvents("2026-06-08", "2026-06-08").then((events) => {
              const lehreinheitId = course.unr;
              expect(lehreinheitId, "lehreinheit id for test event creation").to
                .exist;

              const startDateTime = `2026-06-08 18:35`;
              const endDateTime = `2026-06-08 19:20`;

              createKalenderEvent(
                lehreinheitId,
                startDateTime,
                endDateTime,
              ).then((response) => {
                console.log(response.body.errors);
                expect(response.status).to.eq(500);
                expect(response.body).to.have.nested.property(
                  "meta.status",
                  "error",
                );
                expect(response.body.errors).to.be.an("array");

                let hasTimeLockCollisionError = response.body.errors.some(
                  (error) =>
                    error.message
                      .toLowerCase()
                      .includes("zeitsperre kollision") ||
                    error.message.toLowerCase().includes("time lock collision"),
                );
                expect(
                  hasTimeLockCollisionError,
                  "response contains time lock collision error",
                ).to.be.true;
              });
            });
          });
        },
      );
    });
  });

  it("prohibited event creation due to student collision", () => {
    getStudyPlansTree().then((stgTree) => {
      let studyPlan = stgTree.find(
        (plan) => plan.name === "BMB (BMB) - Maschinenbau",
      );
      expect(studyPlan, "study plan for test event creation").to.exist;

      getCoursesByStudyPlan(studyPlan.studiengang_kz, "SS2026").then(
        (courses) => {
          let course = courses.find(
            (course) =>
              course.lehrfach === "DYN2" &&
              course.lektoren.some((lector) => lector.kurzbz === "Froeh N"),
          );
          expect(course, "course for test event creation").to.exist;

          updateSettingsData(getSettingsData()).then((response) => {
            getPlannerEvents("2026-06-08", "2026-06-08").then((events) => {
              const lehreinheitId = course.unr;
              expect(lehreinheitId, "lehreinheit id for test event creation").to
                .exist;

              const startDateTime = `2026-06-08 18:35`;
              const endDateTime = `2026-06-08 19:20`;

              createKalenderEvent(
                lehreinheitId,
                startDateTime,
                endDateTime,
              ).then((response) => {
                console.log(response.body.errors);
                expect(response.status).to.eq(500);
                expect(response.body).to.have.nested.property(
                  "meta.status",
                  "error",
                );
                expect(response.body.errors).to.be.an("array");
                let hasStudentCollisionError = response.body.errors.some(
                  (error) =>
                    error.message.toLowerCase().includes("verband kollision") ||
                    error.message.toLowerCase().includes("student collision"),
                );
                expect(
                  hasStudentCollisionError,
                  "response contains student collision error",
                ).to.be.true;
              });
            });
          });
        },
      );
    });
  });

  it("prohibited event creation due to direct student collision", () => {
    let settingsData = getSettingsData();
    settingsData.kollision_student = true;

    getStudyPlansTree().then((stgTree) => {
      let studyPlan = stgTree.find(
        (plan) => plan.name === "BMB (BMB) - Maschinenbau",
      );
      expect(studyPlan, "study plan for test event creation").to.exist;

      getCoursesByStudyPlan(studyPlan.studiengang_kz, "SS2026").then(
        (courses) => {
          let course = courses.find(
            (course) =>
              course.lehrfach === "DYN2" &&
              course.lektoren.some((lector) => lector.kurzbz === "Froeh N"),
          );
          expect(course, "course for test event creation").to.exist;

          updateSettingsData(settingsData).then((response) => {
            getPlannerEvents("2026-06-08", "2026-06-08").then((events) => {
              const lehreinheitId = course.unr;
              expect(lehreinheitId, "lehreinheit id for test event creation").to
                .exist;

              const startDateTime = `2026-06-08 18:35`;
              const endDateTime = `2026-06-08 19:20`;

              createKalenderEvent(
                lehreinheitId,
                startDateTime,
                endDateTime,
              ).then((response) => {
                console.log(response.body.errors);
                expect(response.status).to.eq(500);
                expect(response.body).to.have.nested.property(
                  "meta.status",
                  "error",
                );
                expect(response.body.errors).to.be.an("array");
                let hasStudentCollisionError = response.body.errors.some(
                  (error) =>
                    error.message
                      .toLowerCase()
                      .includes("studierende kollision") ||
                    error.message.toLowerCase().includes("student collision"),
                );
                expect(
                  hasStudentCollisionError,
                  "response contains student collision error",
                ).to.be.true;
              });
            });
          });
        },
      );
    });
  });

  it("prohibited event creation due to lector collision", () => {
    getStudyPlansTree().then((stgTree) => {
      let studyPlan = stgTree.find(
        (plan) => plan.name === "BMR (BMR) - Mechatronik/Robotik",
      );
      expect(studyPlan, "study plan for test event creation").to.exist;

      getCoursesByStudyPlan(studyPlan.studiengang_kz, "SS2026").then(
        (courses) => {
          let course = courses.find(
            (course) =>
              course.lehrfach === "IROBO" &&
              course.lektoren.some((lector) => lector.kurzbz === "StujaKe"),
          );
          expect(course, "course for test event creation").to.exist;

          updateSettingsData(getSettingsData()).then((response) => {
            getPlannerEvents("2026-06-08", "2026-06-08").then((events) => {
              const lehreinheitId = course.unr;
              expect(lehreinheitId, "lehreinheit id for test event creation").to
                .exist;

              const startDateTime = `2026-06-08 18:35`;
              const endDateTime = `2026-06-08 19:20`;

              createKalenderEvent(
                lehreinheitId,
                startDateTime,
                endDateTime,
              ).then((response) => {
                console.log(response.body.errors);
                expect(response.status).to.eq(500);
                expect(response.body).to.have.nested.property(
                  "meta.status",
                  "error",
                );
                expect(response.body.errors).to.be.an("array");
                let hasStudentCollisionError = response.body.errors.some(
                  (error) =>
                    error.message.toLowerCase().includes("verband kollision") ||
                    error.message.toLowerCase().includes("student collision"),
                );
                expect(
                  hasStudentCollisionError,
                  "response contains student collision error",
                ).to.be.true;
              });
            });
          });
        },
      );
    });
  });

  it("event update works for non collision case", () => {
    updateSettingsData(getSettingsData()).then((response) => {
      getPlannerEvents("2026-06-09", "2026-06-09").then((events) => {
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
      getPlannerEvents("2026-06-08", "2026-06-08").then((events) => {
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
    updateSettingsData(getSettingsData()).then((response) => {
      getPlannerEvents("2026-06-08", "2026-06-08").then((events) => {
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
              error.message.toLowerCase().includes("verband kollision") ||
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

  it("prohibited event update due to student collision", () => {
    let settingsData = getSettingsData();
    settingsData.kollision_student = true;

    updateSettingsData(settingsData).then((response) => {
      getPlannerEvents("2026-06-08", "2026-06-08").then((events) => {
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
      getPlannerEvents("2026-06-09", "2026-06-09").then((events) => {
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
      getPlannerEvents("2026-06-08", "2026-06-08").then((events) => {
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
      getPlannerEvents("2026-06-08", "2026-06-08").then((events) => {
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
      getPlannerEvents("2026-06-08", "2026-06-08").then((events) => {
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
