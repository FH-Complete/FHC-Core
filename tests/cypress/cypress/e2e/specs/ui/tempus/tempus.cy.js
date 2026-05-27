import { tempusPage } from "../../../../support/pages/tempus.po";

const PLANER = "Planer";
const LEKTOR = "Lektor";
const STUDENT = "Student";
const SYNC = "Sync";

const roleFetchAliases = {
  [PLANER]: "@fetchPlanData",
  [LEKTOR]: "@fetchLecturerPlanData",
  [STUDENT]: "@fetchStudentPlanData",
};

const waitForOk = (alias) =>
  cy.wait(alias).its("response.statusCode").should("eq", 200);

const syncAndReloadPlanner = () => {
  tempusPage.selectPreviewRole(SYNC); // ensure all events are loaded
  waitForOk("@syncCalendar");
  waitForOk("@fetchPlanData");
  tempusPage.waitForCalendarToFinishLoading();
};

const selectRoleAndWait = (role) => {
  tempusPage.selectPreviewRole(role);
  waitForOk(roleFetchAliases[role]);
  tempusPage.waitForCalendarToFinishLoading();
};

const formatIsoDateTimeForKalenderApi = (isoDateTime) =>
  isoDateTime.slice(0, 16).replace("T", " ");

let eventRestoreOnFailure = null;

context("Base tempus tests", () => {
  beforeEach(() => {
    cy.login();

    cy.intercept({ method: "GET", url: "**/StgTree" }).as("fetchCourseTree");
    cy.intercept({
      method: "GET",
      url: /\/tempus\/Kalender\/getPlan(?:\?|$)/,
    }).as("fetchPlanData");
    cy.intercept({
      method: "GET",
      url: /\/tempus\/Kalender\/getPlanLecturer(?:\?|$)/,
    }).as("fetchLecturerPlanData");
    cy.intercept({
      method: "GET",
      url: /\/tempus\/Kalender\/getPlanStudent(?:\?|$)/,
    }).as("fetchStudentPlanData");
    cy.intercept({ method: "GET", url: "**/tempus/Kalender/getHistory**" }).as(
      "fetchEventHistory",
    );
    cy.intercept({
      method: "GET",
      url: "**/tempus/coursepicker/getByStg**",
    }).as("fetchCoursePickerCourses");
    cy.intercept({
      method: "POST",
      url: "**/tempus/Kalender/addKalenderEvent**",
    }).as("addCalendarEvent");
    cy.intercept({
      method: "POST",
      url: "**/tempus/Kalender/updateKalenderEvent**",
    }).as("updateCalendarEvent");
    cy.intercept({
      method: "POST",
      url: "**/tempus/Kalender/sync**",
    }).as("syncCalendar");
    cy.intercept({
      method: "POST",
      url: "**/tempus/Kalender/syncToLecturer**",
    }).as("syncCalendarToLecturer");
    cy.intercept({
      method: "POST",
      url: "**/tempus/Kalender/syncToStudent**",
    }).as("syncCalendarToStudent");
    cy.intercept({
      method: "POST",
      url: "**/tempus/Kalender/deleteEntry**",
    }).as("deleteCalendarEvent");

    tempusPage.visit();

    waitForOk("@fetchCourseTree");
    waitForOk("@fetchPlanData");
  });

  afterEach(function () {
    const eventRestore = eventRestoreOnFailure;
    eventRestoreOnFailure = null;

    if (this.currentTest.state !== "failed" || !eventRestore) {
      return;
    }

    return tempusPage
      .restoreKalenderEventTime(
        eventRestore.kalenderId,
        eventRestore.startTime,
        eventRestore.endTime,
        { failOnStatusCode: false },
      )
      .its("status")
      .should("eq", 200);
  });

  // it("can access Tempus page with valid credentials", () => {
  //   tempusPage.getTempusOverview().should("be.visible");
  // });

  // it("shows all expected page elements", () => {
  //   tempusPage.getTempusOverview().should("be.visible");
  //   tempusPage.getSidebarMenu().should("exist");
  //   tempusPage.getSlideInCoursesMenu().should("exist");
  //   tempusPage.getCalendarSection().should("be.visible");
  //   tempusPage.getPreviewRoleOptionsHolder().should("be.visible");
  // });

  // it("can open courses menu", () => {
  //   tempusPage.getSlideInCoursesMenu().should("exist").should("be.hidden");

  //   tempusPage.openCoursesMenu();

  //   tempusPage.getCourseTreeRows().should("have.length.greaterThan", 0);
  // });

  // it("can select one course and show preview of its events", () => {
  //   tempusPage.getSlideInCoursesMenu().should("exist");
  //   tempusPage.getCourseTreeRows().should("have.length.greaterThan", 0);
  //   tempusPage.getCoursePicker().should("exist");
  //   tempusPage.getCoursePickerRows().should("have.length", 0);

  //   tempusPage.selectFirstCourse();
  //   waitForOk("@fetchCoursePickerCourses");

  //   tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);
  // });

  // it("can search for a course event in the course picker", () => {
  //   tempusPage.selectFirstCourse();
  //   waitForOk("@fetchCoursePickerCourses");

  //   tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);

  //   tempusPage
  //     .getCoursePickerRows()
  //     .last()
  //     .find("div:first span:first")
  //     .invoke("text")
  //     .as("randomCourseText");

  //   cy.get("@randomCourseText").then((randomCourseText) => {
  //     tempusPage.getCoursePickerSearchInput().type(randomCourseText);

  //     tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);
  //     tempusPage
  //       .getCoursePickerRows()
  //       .first()
  //       .should("contain.text", randomCourseText);
  //   });
  // });

  // it("can drag and drop one course event into the calendar", () => {
  //   tempusPage.getCalendarSection().should("exist");
  //   tempusPage.waitForCalendarToFinishLoading();

  //   tempusPage.selectFirstCourse();
  //   waitForOk("@fetchCoursePickerCourses");

  //   tempusPage.getCalendarEvents().then(($events) => {
  //     cy.wrap($events.length).as("initialEventCount");
  //   });

  //   tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);
  //   tempusPage.dropCourseOnCalendarPart(0, 10);

  //   waitForOk("@addCalendarEvent");
  //   waitForOk("@fetchPlanData");

  //   tempusPage.waitForCalendarToFinishLoading();

  //   cy.get("@initialEventCount").then((initialEventCount) => {
  //     tempusPage
  //       .getCalendarEvents()
  //       .should("have.length", initialEventCount + 1);
  //   });
  // });

  // it("shows the same number of calendar events for all role previews", () => {
  //   const rolePreviews = [
  //     { label: PLANER, fetchAlias: roleFetchAliases[PLANER] },
  //     { label: LEKTOR, fetchAlias: roleFetchAliases[LEKTOR] },
  //     { label: STUDENT, fetchAlias: roleFetchAliases[STUDENT] },
  //   ];
  //   const eventCounts = {};

  //   syncAndReloadPlanner();

  //   cy.wrap(rolePreviews)
  //     .each((rolePreview) => {
  //       selectRoleAndWait(rolePreview.label);

  //       tempusPage.getCalendarEvents().then(($events) => {
  //         eventCounts[rolePreview.label] = $events.length;
  //       });
  //     })
  //     .then(() => {
  //       const expectedCount = eventCounts[rolePreviews[0].label];

  //       Object.entries(eventCounts).forEach(([role, count]) => {
  //         expect(count, `${role} calendar event count`).to.eq(expectedCount);
  //       });
  //     });
  // });

  // it("shows event details modal when clicking a calendar event", () => {
  //   tempusPage.waitForCalendarToFinishLoading();
  //   tempusPage.getCalendarEvents().should("have.length.greaterThan", 0);

  //   tempusPage.getCalendarEvents().first().click();

  //   tempusPage.getCalendarEventModal().should("be.visible");
  // });

  // it("shows event context menu when right clicking a calendar event", () => {
  //   tempusPage.waitForCalendarToFinishLoading();
  //   tempusPage.getCalendarEvents().should("have.length.greaterThan", 0);

  //   tempusPage.getCalendarEvents().first().rightclick();

  //   tempusPage.getEventContextMenu().should("be.visible");
  // });

  // it("shows history modal when selecting History from event context menu", () => {
  //   tempusPage.waitForCalendarToFinishLoading();
  //   tempusPage.getCalendarEvents().should("have.length.greaterThan", 0);

  //   tempusPage.getCalendarEvents().first().rightclick();
  //   tempusPage.getEventContextMenuOption("History").click();
  //   waitForOk("@fetchEventHistory");

  //   tempusPage.getHistoryModal().should("be.visible");
  // });

  // it.skip("shows reservation modal when dropping reservation handle on calendar", () => {
  //   tempusPage.waitForCalendarToFinishLoading();
  //   tempusPage.getCalendarBaseGrid().should("be.visible");
  //   cy.wait(1000); // wait for potential animations
  //   tempusPage
  //     .getReservationDragHandle()
  //     .drag(
  //       tempusPage.selectors.calendarBaseGrid + " .part-body:nth-of-type(18)",
  //       {
  //         waitForAnimations: false,
  //         animationDistanceThreshold: 0,
  //       },
  //     );

  //   cy.wait(3000); // wait for potential animations
  //   tempusPage.getReservationModal().should("be.visible");
  // });

  // it("can drop event from calendar into parking slot", () => {
  //   tempusPage.getEventParkingSlot().should("exist");
  //   tempusPage.getParkedEvents().should("have.length", 0);

  //   tempusPage
  //     .getCalendarEvents()
  //     .first()
  //     .drag(tempusPage.selectors.parkingSlot);

  //   tempusPage.getParkedEvents().should("have.length", 1);
  // });

  it("on planner event change shows unchanged event on other roles", () => {
    syncAndReloadPlanner();

    cy.get(".fhc-calendar-base-grid > div")
    .last()
    .invoke(
      "css",
      "overflow",
      "hidden",
    );
    cy.wait(500); // ensure overflow change is applied before dragging, otherwise the drag can fail due to the calendar scrolling
    tempusPage
      .getCalendarEventsByTimeRange("08:00:00", "08:45:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const eventData = JSON.parse(eventJSON);
        const originalStartTime = formatIsoDateTimeForKalenderApi(
          eventData.orig.isostart,
        );
        const originalEndTime = formatIsoDateTimeForKalenderApi(
          eventData.orig.isoend,
        );
        let eventId = eventData?.id;
        expect(eventId).to.exist;

        tempusPage.dropEventOnCalendarPart(eventId, 2);

        cy.wait("@updateCalendarEvent").then((interception) => {
          expect(interception.response.statusCode).to.eq(200);

          const updatedEventId =
            interception.response.body.data.retval.kalender_id;

          eventRestoreOnFailure = {
            kalenderId: updatedEventId,
            startTime: originalStartTime,
            endTime: originalEndTime,
          };

          cy.wrap(updatedEventId).as("updatedEventId");
        });
        waitForOk("@fetchPlanData");

        cy.get("@updatedEventId").then((updatedEventId) => {
          tempusPage.getEventGridRow(updatedEventId).then((gridRowStyle) => {
            selectRoleAndWait(LEKTOR);
            tempusPage.getEventGridRow(eventId).then((lecturerGridRow) => {
              expect(lecturerGridRow).to.not.eq(gridRowStyle);
            });

            selectRoleAndWait(STUDENT);
            tempusPage.getEventGridRow(eventId).then((studentGridRow) => {
              expect(studentGridRow).to.not.eq(gridRowStyle);
            });
          });
        });
      });

    cy.get(".fhc-calendar-base-grid > div:last-child()").invoke(
      "css",
      "overflow",
      "auto",
    );
  });

  it("sync after planner preview event change loads event in same way on all previews", () => {
    syncAndReloadPlanner();

    tempusPage
      .getCalendarEventsByTimeRange("08:00:00", "08:45:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const eventData = JSON.parse(eventJSON);
        const originalStartTime = formatIsoDateTimeForKalenderApi(
          eventData.orig.isostart,
        );
        const originalEndTime = formatIsoDateTimeForKalenderApi(
          eventData.orig.isoend,
        );
        let eventId = eventData?.id;
        expect(eventId).to.exist;

        tempusPage.dropEventOnCalendarPart(eventId, 3, {
          scrollIntoView: true,
        });

        cy.wait("@updateCalendarEvent").then((interception) => {
          expect(interception.response.statusCode).to.eq(200);

          const updatedEventId =
            interception.response.body.data.retval.kalender_id;

          eventRestoreOnFailure = {
            kalenderId: updatedEventId,
            startTime: originalStartTime,
            endTime: originalEndTime,
          };

          cy.wrap(updatedEventId).as("updatedEventId");
        });
        waitForOk("@fetchPlanData");

        syncAndReloadPlanner();

        cy.get("@updatedEventId").then((updatedEventId) => {
          tempusPage.getEventGridRow(updatedEventId).then((gridRowStyle) => {
            selectRoleAndWait(LEKTOR);
            tempusPage
              .getEventGridRow(updatedEventId)
              .then((lecturerGridRow) => {
                expect(lecturerGridRow).to.eq(gridRowStyle);
              });

            selectRoleAndWait(STUDENT);
            tempusPage
              .getEventGridRow(updatedEventId)
              .then((studentGridRow) => {
                expect(studentGridRow).to.eq(gridRowStyle);
              });
          });
        });
      });
  });

  it("live unlock after planner preview event change loads event in same way on planner and lektor, but not on student preview", () => {
    syncAndReloadPlanner();

    tempusPage
      .getCalendarEventsByTimeRange("08:00:00", "08:45:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        let eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;
        tempusPage.dropEventOnCalendarPart(eventId, 4, {
          scrollIntoView: true,
        });

        cy.wait("@updateCalendarEvent")
          .its("response.body.data.retval.kalender_id")
          .as("updatedEventId");
        waitForOk("@fetchPlanData");

        tempusPage.waitForCalendarToFinishLoading();

        cy.get("@updatedEventId").then((updatedEventId) => {
          tempusPage
            .getCalendarEventById(updatedEventId)
            .should("exist")
            .rightclick();
          tempusPage.getEventContextMenuOption("Freischalten für Live").click(); // TODO: Check wether live is for studnet
          waitForOk("@syncCalendarToStudent");

          tempusPage.getEventGridRow(updatedEventId).then((gridRowStyle) => {
            selectRoleAndWait(LEKTOR);
            tempusPage
              .getEventGridRow(updatedEventId)
              .then((lecturerGridRow) => {
                expect(lecturerGridRow).to.eq(gridRowStyle);
              });

            selectRoleAndWait(STUDENT);
            tempusPage.getEventGridRow(eventId).then((studentGridRow) => {
              expect(studentGridRow).to.not.eq(gridRowStyle);
            });
          });
        });
      });
  });

  it("event change on lector preview is prohibited", () => {
    syncAndReloadPlanner();

    selectRoleAndWait(LEKTOR);

    tempusPage
      .getCalendarEventsByTimeRange("08:00:00", "08:45:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        let eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;

        tempusPage.dropEventOnCalendarPart(eventId, 15);

        cy.get("@updateCalendarEvent").should("not.exist");

        tempusPage.getEventGridRow(eventId).then((gridRowStyle) => {
          selectRoleAndWait(LEKTOR);
          tempusPage.getEventGridRow(eventId).then((lecturerGridRow) => {
            expect(lecturerGridRow).to.eq(gridRowStyle);
          });

          selectRoleAndWait(STUDENT);
          tempusPage.getEventGridRow(eventId).then((studentGridRow) => {
            expect(studentGridRow).to.eq(gridRowStyle);
          });
        });
      });
  });

  it("event change on student preview is prohibited", () => {
    syncAndReloadPlanner();

    selectRoleAndWait(STUDENT);

    tempusPage
      .getCalendarEventsByTimeRange("08:00:00", "08:45:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        let eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;

        tempusPage.dropEventOnCalendarPart(eventId, 15);

        cy.get("@updateCalendarEvent").should("not.exist");

        tempusPage.getEventGridRow(eventId).then((gridRowStyle) => {
          selectRoleAndWait(LEKTOR);
          tempusPage.getEventGridRow(eventId).then((lecturerGridRow) => {
            expect(lecturerGridRow).to.eq(gridRowStyle);
          });

          selectRoleAndWait(STUDENT);
          tempusPage.getEventGridRow(eventId).then((studentGridRow) => {
            expect(studentGridRow).to.eq(gridRowStyle);
          });
        });
      });
  });

  it("event deletion on planner preview preservers event on planner, but shows it as unsynced on lektor and student preview", () => {
    syncAndReloadPlanner();

    tempusPage
      .getCalendarEvents()
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        let eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;
        tempusPage
          .getCalendarEventById(eventId)
          .should("be.visible")
          .rightclick();
        tempusPage.getEventContextMenuOption("Delete").click();

        cy.wait("@deleteCalendarEvent");
        waitForOk("@fetchPlanData");

        tempusPage.waitForCalendarToFinishLoading();

        tempusPage.getCalendarEventById(eventId).should("be.visible");

        selectRoleAndWait(LEKTOR);
        tempusPage.getCalendarEventById(eventId).should("not.exist");

        selectRoleAndWait(STUDENT);
        tempusPage.getCalendarEventById(eventId).should("not.exist");
      });
  });

  it("syncing event deletion on planner preview removes event from other previews", () => {
    syncAndReloadPlanner();

    tempusPage
      .getCalendarEvents()
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        let eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;
        tempusPage
          .getCalendarEventById(eventId)
          .should("be.visible")
          .rightclick();
        tempusPage.getEventContextMenuOption("Delete").click();

        cy.wait("@deleteCalendarEvent");
        waitForOk("@fetchPlanData");

        syncAndReloadPlanner();

        tempusPage.getCalendarEventById(eventId).should("not.exist");

        selectRoleAndWait(LEKTOR);
        tempusPage.getCalendarEventById(eventId).should("not.exist");

        selectRoleAndWait(STUDENT);
        tempusPage.getCalendarEventById(eventId).should("not.exist");
      });
  });

  it("can bottom resize an event on planner preview", () => {
    tempusPage.getCalendarSection().should("exist");
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage
      .getCalendarEventsByStartTime("08:00:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        cy.wrap(JSON.parse(eventJSON)).its("id").as("eventId");
        cy.wrap(JSON.parse(eventJSON).orig)
          .its("beginn")
          .as("originalEventStart");
        cy.wrap(JSON.parse(eventJSON).orig).its("ende").as("originalEventEnd");
      });

    cy.get("@eventId").then((eventId) => {
      tempusPage
        .getCalendarEventById(eventId)
        .find(".fhc-resize-bar--bottom")
        .first()
        .realHover()
        .realMouseDown("center", {
          button: "left",
        })
        .realMouseMove(0, 120)
        .realMouseUp();

      cy.wait("@updateCalendarEvent").then((interception) => {
        expect(interception.response.statusCode).to.eq(200);

        const updatedEventId =
          interception.response.body.data.retval.kalender_id;

        cy.wrap(updatedEventId).as("updatedEventId");
      });
      waitForOk("@fetchPlanData");

      cy.get("@updatedEventId").then((updatedEventId) => {
        tempusPage
          .getCalendarEventById(updatedEventId)
          .invoke("attr", "data-fhc-draggable-value")
          .then((eventJSON) => {
            let newEventStart = JSON.parse(eventJSON)?.orig?.beginn;
            let newEventEnd = JSON.parse(eventJSON)?.orig?.ende;

            cy.get("@originalEventStart").then((originalEventStart) => {
              expect(newEventStart).to.eq(originalEventStart);
            });
            cy.get("@originalEventEnd").then((originalEventEnd) => {
              expect(newEventEnd).to.not.eq(originalEventEnd);
            });
          });
      });
    });
  });

  it("can top resize an event on planner preview", () => {
    tempusPage.getCalendarSection().should("exist");
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage
      .getCalendarEventsByStartTime("09:40:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        cy.wrap(JSON.parse(eventJSON)).its("id").as("eventId");
        cy.wrap(JSON.parse(eventJSON).orig)
          .its("beginn")
          .as("originalEventStart");
        cy.wrap(JSON.parse(eventJSON).orig).its("ende").as("originalEventEnd");
      });

    cy.get("@eventId").then((eventId) => {
      tempusPage
        .getCalendarEventById(eventId)
        .find(".fhc-resize-bar--top")
        .first()
        .realHover()
        .realMouseDown("center", {
          button: "left",
        })
        .realMouseMove(0, -40)
        .realMouseUp();

      cy.wait("@updateCalendarEvent").then((interception) => {
        expect(interception.response.statusCode).to.eq(200);

        const updatedEventId =
          interception.response.body.data.retval.kalender_id;

        cy.wrap(updatedEventId).as("updatedEventId");
      });
      waitForOk("@fetchPlanData");

      cy.get("@updatedEventId").then((updatedEventId) => {
        tempusPage
          .getCalendarEventById(updatedEventId)
          .invoke("attr", "data-fhc-draggable-value")
          .then((eventJSON) => {
            let newEventStart = JSON.parse(eventJSON)?.orig?.beginn;
            let newEventEnd = JSON.parse(eventJSON)?.orig?.ende;

            cy.get("@originalEventStart").then((originalEventStart) => {
              expect(newEventStart).to.not.eq(originalEventStart);
            });
            cy.get("@originalEventEnd").then((originalEventEnd) => {
              expect(newEventEnd).to.eq(originalEventEnd);
            });
          });
      });
    });
  });

  // check live issue -- done
  // check delete -- DONE
  // resize - dones
  // sve al bez stunden raster
  // course picker search
  // raumauswal
  // btn click to current semester
  // filter za raum und fuer person
  // filter ode u sidebar
});
