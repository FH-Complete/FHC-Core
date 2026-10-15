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

  it("keeps events unchanged in lecturer and student previews after a planner edit", () => {
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

  it("keeps events unchanged in lecturer and student previews after a planner edit and syncing", () => {
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
          tempusPage.getGridRowByContainingUniqueCalendarGroupId(updatedEventGroupId).then((gridRowStyle) => {
            tempusPage.selectRoleAndWait(LEKTOR);
            tempusPage
              .getGridRowByContainingUniqueCalendarGroupId(updatedEventGroupId)
              .then((lecturerGridRow) => {
                expect(lecturerGridRow).not.to.eq(gridRowStyle);
              });

            tempusPage.selectRoleAndWait(STUDENT);
            tempusPage
              .getGridRowByContainingUniqueCalendarGroupId(updatedEventGroupId)
              .then((studentGridRow) => {
                expect(studentGridRow).not.to.eq(gridRowStyle);
              });
          });
        });
      });
  });

  it("updates events in the lecturer preview but not the student preview after releasing and syncing", () => {
    tempusPage.syncAndReloadPlanner();

    tempusPage
      .getCalendarEventsBySoftTimeRange("13:35:00", "14:20:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;

		const updatedEventGroupId = JSON.parse(eventJSON)?.orig?.eindeutige_kalender_gruppen_id;
		expect(updatedEventGroupId).to.exist;

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

        tempusPage.getCalendarEventByUniqueCalenderGroupId(updatedEventGroupId).rightclick();
        tempusPage
          .getEventContextMenuOption("Freischalten für Voransicht")
          .click({ force: true });
        waitForOk("@syncCalendarToLecturer");
        waitForOk("@fetchPlanData");

        tempusPage.syncAndReloadPlanner();

        cy.get("@updatedEventGroupId").then((updatedEventGroupId) => {
          tempusPage
            .getGridRowByContainingUniqueCalendarGroupId(updatedEventGroupId)
            .then((plannerGridRow) => {
              tempusPage.selectRoleAndWait(LEKTOR);
              tempusPage
                .getGridRowByContainingUniqueCalendarGroupId(updatedEventGroupId)
                .then((lecturerGridRow) => {
                  expect(lecturerGridRow).to.eq(plannerGridRow);
                });

              tempusPage.selectRoleAndWait(STUDENT);
              tempusPage
                .getGridRowByContainingUniqueCalendarGroupId(updatedEventGroupId)
                .then((studentGridRow) => {
                  expect(studentGridRow).not.to.eq(plannerGridRow);
                });
            });
        });
      });
  });

  it("updates events in the lecturer and student previews after releasing for live and syncing", () => {
    tempusPage.syncAndReloadPlanner();

    tempusPage
      .getCalendarEventsBySoftTimeRange("13:35:00", "14:20:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;
        const updatedEventGroupId =
          JSON.parse(eventJSON)?.orig?.eindeutige_kalender_gruppen_id;
        expect(updatedEventGroupId).to.exist;

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

        tempusPage
          .getCalendarEventByUniqueCalenderGroupId(updatedEventGroupId)
          .rightclick();
        tempusPage
          .getEventContextMenuOption("Freischalten für Live")
          .click({ force: true });
        waitForOk("@syncCalendarToStudent");
        waitForOk("@fetchPlanData");

        tempusPage.syncAndReloadPlanner();

        cy.get("@updatedEventGroupId").then((updatedEventGroupId) => {
          tempusPage
            .getGridRowByContainingUniqueCalendarGroupId(updatedEventGroupId)
            .then((plannerGridRow) => {
              tempusPage.selectRoleAndWait(LEKTOR);
              tempusPage
                .getGridRowByContainingUniqueCalendarGroupId(updatedEventGroupId)
                .then((lecturerGridRow) => {
                  expect(lecturerGridRow).to.eq(plannerGridRow);
                });

              tempusPage.selectRoleAndWait(STUDENT);
              tempusPage
                .getGridRowByContainingUniqueCalendarGroupId(updatedEventGroupId)
                .then((studentGridRow) => {
                  expect(studentGridRow).to.eq(plannerGridRow);
                });
            });
        });
      });
  });
  
  it("prevents event changes in the lecturer preview", () => {
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

  it("prevents event changes in the student preview", () => {
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
