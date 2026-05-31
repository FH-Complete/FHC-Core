import { waitForOk } from "../../../../support/helpers/network";
import { LEKTOR, PLANER, STUDENT, tempusPage } from "../../../../support/pages/tempus.po";

context("Tempus role preview tests", () => {
  beforeEach(() => {
    tempusPage.visitAndWaitForPlanner();
  });

  afterEach(() => {
    return tempusPage.clearMondayFirstColumnAfterTest();
  });

  it("shows the same number of calendar events for all role previews", () => {
    const rolePreviews = [PLANER, LEKTOR, STUDENT];
    const eventCounts = {};

    tempusPage.syncAndReloadPlanner();

    cy.wrap(rolePreviews)
      .each((role) => {
        tempusPage.selectRoleAndWait(role);

        tempusPage.getCalendarEvents().then(($events) => {
          eventCounts[role] = $events.length;
        });
      })
      .then(() => {
        const expectedCount = eventCounts[rolePreviews[0]];

        Object.entries(eventCounts).forEach(([role, count]) => {
          expect(count, `${role} calendar event count`).to.eq(expectedCount);
        });
      });
  });

  it("on planner event change shows unchanged event on other roles", () => {
    tempusPage.clearMondayFirstColumnBeforeAndAfter();

    tempusPage.syncAndReloadPlanner();

    cy.get(".fhc-calendar-base-grid > div")
      .last()
      .invoke("css", "overflow", "hidden");
    cy.wait(500);
    tempusPage
      .getCalendarEventsByTimeRange("08:00:00", "08:45:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        let eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;

        tempusPage.dropEventOnCalendarPart(eventId, 3, {
          scrollIntoView: true,
        });

        cy.wait("@updateCalendarEvent").then((interception) => {
          expect(interception.response.statusCode).to.eq(200);

          const updatedEventId =
            interception.response.body.data.retval.kalender_id;

          cy.wrap(updatedEventId).as("updatedEventId");
        });
        waitForOk("@fetchPlanData");

        cy.get("@updatedEventId").then((updatedEventId) => {
          tempusPage.getEventGridRow(updatedEventId).then((gridRowStyle) => {
            tempusPage.selectRoleAndWait(LEKTOR);
            tempusPage.getEventGridRow(eventId).then((lecturerGridRow) => {
              expect(lecturerGridRow).to.not.eq(gridRowStyle);
            });

            tempusPage.selectRoleAndWait(STUDENT);
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
    tempusPage.clearMondayFirstColumnBeforeAndAfter();

    tempusPage.syncAndReloadPlanner();

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

        cy.wait("@updateCalendarEvent").then((interception) => {
          expect(interception.response.statusCode).to.eq(200);

          const updatedEventId =
            interception.response.body.data.retval.kalender_id;

          cy.wrap(updatedEventId).as("updatedEventId");
        });
        waitForOk("@fetchPlanData");

        tempusPage.syncAndReloadPlanner();

        cy.get("@updatedEventId").then((updatedEventId) => {
          tempusPage.getEventGridRow(updatedEventId).then((gridRowStyle) => {
            tempusPage.selectRoleAndWait(LEKTOR);
            tempusPage
              .getEventGridRow(updatedEventId)
              .then((lecturerGridRow) => {
                expect(lecturerGridRow).to.eq(gridRowStyle);
              });

            tempusPage.selectRoleAndWait(STUDENT);
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
    tempusPage.clearMondayFirstColumnBeforeAndAfter();

    tempusPage.syncAndReloadPlanner();

    tempusPage
      .getCalendarEventsByTimeRange("08:00:00", "08:45:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        let eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;
        tempusPage.dropEventOnCalendarPart(eventId, 5, {
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
          tempusPage.getEventContextMenuOption("Freischalten für Live").click();
          waitForOk("@syncCalendarToStudent");

          tempusPage.getEventGridRow(updatedEventId).then((gridRowStyle) => {
            tempusPage.selectRoleAndWait(LEKTOR);
            tempusPage
              .getEventGridRow(updatedEventId)
              .then((lecturerGridRow) => {
                expect(lecturerGridRow).to.eq(gridRowStyle);
              });

            tempusPage.selectRoleAndWait(STUDENT);
            tempusPage.getEventGridRow(eventId).then((studentGridRow) => {
              expect(studentGridRow).to.not.eq(gridRowStyle);
            });
          });
        });
      });
  });

  it("event change on lector preview is prohibited", () => {
    tempusPage.syncAndReloadPlanner();

    tempusPage.selectRoleAndWait(LEKTOR);

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
          tempusPage.selectRoleAndWait(LEKTOR);
          tempusPage.getEventGridRow(eventId).then((lecturerGridRow) => {
            expect(lecturerGridRow).to.eq(gridRowStyle);
          });

          tempusPage.selectRoleAndWait(STUDENT);
          tempusPage.getEventGridRow(eventId).then((studentGridRow) => {
            expect(studentGridRow).to.eq(gridRowStyle);
          });
        });
      });
  });

  it("event change on student preview is prohibited", () => {
    tempusPage.syncAndReloadPlanner();

    tempusPage.selectRoleAndWait(STUDENT);

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
          tempusPage.selectRoleAndWait(LEKTOR);
          tempusPage.getEventGridRow(eventId).then((lecturerGridRow) => {
            expect(lecturerGridRow).to.eq(gridRowStyle);
          });

          tempusPage.selectRoleAndWait(STUDENT);
          tempusPage.getEventGridRow(eventId).then((studentGridRow) => {
            expect(studentGridRow).to.eq(gridRowStyle);
          });
        });
      });
  });
});
