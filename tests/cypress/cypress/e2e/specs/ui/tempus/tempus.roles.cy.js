import { waitForOk } from "../../../../support/helpers/network";
import { tempusApi } from "../../../../support/api/tempusApi";
import {
  LEKTOR,
  PLANER,
  STUDENT,
  tempusPage,
} from "../../../../support/pages/tempus.po";

context("Tempus role preview tests", () => {
  beforeEach(() => {
    tempusPage.visitAndWaitForPlanner();

    tempusApi.deletePlannerEventsForDay("monday");
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
    tempusPage.syncAndReloadPlanner();

    tempusPage
      .getCalendarEventsByTimeRange("13:35:00", "14:20:00")
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
          expect(interception.response.body.data).to.exist;

          const updatedEventGroupId =
            interception.response.body.data?.eindeutige_kalender_gruppen_id ??
            interception.response.body.data;

          cy.wrap(updatedEventGroupId).as("updatedEventGroupId");
        });
        waitForOk("@fetchPlanData");

        cy.get("@updatedEventGroupId").then((updatedEventGroupId) => {
          tempusPage.getGridRowByContainingUniqueCalendarGroupId(updatedEventGroupId).then((gridRowStyle) => {
            tempusPage.selectRoleAndWait(LEKTOR);
            tempusPage.getGridRowByContainingUniqueCalendarGroupId(updatedEventGroupId).then((lecturerGridRow) => {
              expect(lecturerGridRow).to.not.eq(gridRowStyle);
            });

            tempusPage.selectRoleAndWait(STUDENT);
            tempusPage.getGridRowByContainingUniqueCalendarGroupId(updatedEventGroupId).then((studentGridRow) => {
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
    tempusPage.syncAndReloadPlanner();

    tempusPage
      .getCalendarEventsBySoftTimeRange("13:35:00", "14:20:00")
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
          expect(interception.response.body.data).to.exist;
		
          const updatedEventGroupId =
            interception.response.body.data?.eindeutige_kalender_gruppen_id ??
            interception.response.body.data;

          cy.wrap(updatedEventGroupId).as("updatedEventGroupId");
        });
        waitForOk("@fetchPlanData");

        tempusPage.syncAndReloadPlanner();

        cy.get("@updatedEventGroupId").then((updatedEventGroupId) => {
			console.log("Updated Event Group ID:", updatedEventGroupId); // Debugging line
          tempusPage.getGridRowByContainingUniqueCalendarGroupId(updatedEventGroupId).then((gridRowStyle) => {
            tempusPage.selectRoleAndWait(LEKTOR);
            tempusPage
              .getGridRowByContainingUniqueCalendarGroupId(updatedEventGroupId)
              .then((lecturerGridRow) => {
                expect(lecturerGridRow).to.eq(gridRowStyle);
              });

            tempusPage.selectRoleAndWait(STUDENT);
            tempusPage
              .getGridRowByContainingUniqueCalendarGroupId(updatedEventGroupId)
              .then((studentGridRow) => {
                expect(studentGridRow).to.eq(gridRowStyle);
              });
          });
        });
      });
  });

  it.skip("live unlock after planner preview event change loads event in same way on planner and lektor, but not on student preview", () => {
    tempusPage.syncAndReloadPlanner();

    tempusPage
      .getCalendarEventsBySoftTimeRange("08:00:00", "08:45:00")
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
          .its("response.body.data.kalender_id")
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

          tempusPage.getGridRowByContainingEventId(updatedEventId).then((gridRowStyle) => {
            tempusPage.selectRoleAndWait(LEKTOR);
            tempusPage
              .getGridRowByContainingEventId(updatedEventId)
              .then((lecturerGridRow) => {
                expect(lecturerGridRow).to.eq(gridRowStyle);
              });

            tempusPage.selectRoleAndWait(STUDENT);
            tempusPage.getGridRowByContainingEventId(eventId).then((studentGridRow) => {
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
      .getCalendarEventsBySoftTimeRange("08:00:00", "14:20:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        let eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;

        tempusPage.dropEventOnCalendarPart(eventId, 15);

        cy.get("@updateCalendarEvent").should("not.exist");

        tempusPage.getGridRowByContainingEventId(eventId).then((gridRowStyle) => {
          tempusPage.selectRoleAndWait(LEKTOR);
          tempusPage.getGridRowByContainingEventId(eventId).then((lecturerGridRow) => {
            expect(lecturerGridRow).to.eq(gridRowStyle);
          });

          tempusPage.selectRoleAndWait(STUDENT);
          tempusPage.getGridRowByContainingEventId(eventId).then((studentGridRow) => {
            expect(studentGridRow).to.eq(gridRowStyle);
          });
        });
      });
  });

  it("event change on student preview is prohibited", () => {
    tempusPage.syncAndReloadPlanner();

    tempusPage.selectRoleAndWait(STUDENT);

    tempusPage
      .getCalendarEventsBySoftTimeRange("08:00:00", "14:20:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        let eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;

        tempusPage.dropEventOnCalendarPart(eventId, 15);

        cy.get("@updateCalendarEvent").should("not.exist");

        tempusPage.getGridRowByContainingEventId(eventId).then((gridRowStyle) => {
          tempusPage.selectRoleAndWait(LEKTOR);
          tempusPage.getGridRowByContainingEventId(eventId).then((lecturerGridRow) => {
            expect(lecturerGridRow).to.eq(gridRowStyle);
          });

          tempusPage.selectRoleAndWait(STUDENT);
          tempusPage.getGridRowByContainingEventId(eventId).then((studentGridRow) => {
            expect(studentGridRow).to.eq(gridRowStyle);
          });
        });
      });
  });
});
