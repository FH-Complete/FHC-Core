import { waitForOk } from "../../../../support/helpers/network";
import { LEKTOR, STUDENT, tempusPage } from "../../../../support/pages/tempus.po";

context("Tempus event mutation tests", () => {
  beforeEach(() => {
    tempusPage.visitAndWaitForPlanner();
  });

  afterEach(() => {
    return tempusPage.clearMondayFirstColumnAfterTest();
  });

  it("room change on planner preview updates planner event, but keeps original room on other previews", () => {
    tempusPage.clearMondayFirstColumnBeforeAndAfter();

    tempusPage.syncAndReloadPlanner();

    tempusPage
      .getCalendarEventsWithLehreinheitAndRoom()
      .should("have.length.greaterThan", 0);

    tempusPage
      .getCalendarEventsWithLehreinheitAndRoom()
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const eventData = JSON.parse(eventJSON);
        const eventId = eventData?.id;
        const originalRoom = eventData?.orig?.ort_kurzbz;
        expect(eventId, "planner event id").to.exist;
        expect(originalRoom, "original event room").to.be.a("string").and.not
          .be.empty;

        tempusPage.expectCalendarEventRoom(eventId, originalRoom);

        tempusPage
          .getCalendarEventById(eventId)
          .should("be.visible")
          .rightclick();
        tempusPage.getEventContextMenuOption("Raumauswahl").click();
        waitForOk("@fetchRoomSuggestions");

        tempusPage
          .getRaumauswahlRoomOptions()
          .should("have.length.greaterThan", 0)
          .then(($roomOptions) => {
            const roomOption = [...$roomOptions].find((option) => {
              const room = option.innerText.trim();

              return room && room !== originalRoom;
            });

            expect(roomOption, "different room suggestion").to.exist;

            cy.wrap(roomOption.innerText.trim()).as("newRoom");
            cy.wrap(roomOption).click();
          });

        cy.wait("@updateCalendarEvent").then((interception) => {
          expect(interception.response.statusCode).to.eq(200);

          const updatedEventId = tempusPage.getUpdatedKalenderId(interception);
          expect(updatedEventId, "updated planner event id").to.exist;

          cy.wrap(updatedEventId).as("updatedEventId");
        });
        waitForOk("@fetchPlanData");
        tempusPage.waitForCalendarToFinishLoading();

        cy.get("@newRoom").then((newRoom) => {
          cy.get("@updatedEventId").then((updatedEventId) => {
            tempusPage.expectCalendarEventRoom(updatedEventId, newRoom);

            tempusPage.selectRoleAndWait(LEKTOR);
            tempusPage.expectCalendarEventRoom(eventId, originalRoom);

            tempusPage.selectRoleAndWait(STUDENT);
            tempusPage.expectCalendarEventRoom(eventId, originalRoom);
          });
        });
      });
  });

  it("sync after planner preview room change loads new room on all previews", () => {
    tempusPage.clearMondayFirstColumnBeforeAndAfter();

    tempusPage.syncAndReloadPlanner();

    tempusPage
      .getCalendarEventsWithLehreinheitAndRoom()
      .should("have.length.greaterThan", 0);

    tempusPage
      .getCalendarEventsWithLehreinheitAndRoom()
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const eventData = JSON.parse(eventJSON);
        const eventId = eventData?.id;
        const originalRoom = eventData?.orig?.ort_kurzbz;
        expect(eventId, "planner event id").to.exist;
        expect(originalRoom, "original event room").to.be.a("string").and.not
          .be.empty;

        tempusPage
          .getCalendarEventById(eventId)
          .scrollIntoView()
          .should("be.visible")
          .rightclick();
        tempusPage.getEventContextMenuOption("Raumauswahl").click();
        waitForOk("@fetchRoomSuggestions");

        tempusPage
          .getRaumauswahlRoomOptions()
          .should("have.length.greaterThan", 0)
          .then(($roomOptions) => {
            const roomOption = [...$roomOptions].find((option) => {
              const room = option.innerText.trim();

              return room && room !== originalRoom;
            });

            expect(roomOption, "different room suggestion").to.exist;

            cy.wrap(roomOption.innerText.trim()).as("newRoom");
            cy.wrap(roomOption).click();
          });

        cy.wait("@updateCalendarEvent").then((interception) => {
          expect(interception.response.statusCode).to.eq(200);

          const updatedEventId = tempusPage.getUpdatedKalenderId(interception);
          expect(updatedEventId, "updated planner event id").to.exist;

          cy.wrap(updatedEventId).as("updatedEventId");
        });
        waitForOk("@fetchPlanData");
        tempusPage.waitForCalendarToFinishLoading();

        cy.get("@newRoom").then((newRoom) => {
          cy.get("@updatedEventId").then((updatedEventId) => {
            tempusPage.expectCalendarEventRoom(updatedEventId, newRoom);

            tempusPage.syncAndReloadPlanner();
            tempusPage.expectCalendarEventRoom(updatedEventId, newRoom);

            tempusPage.selectRoleAndWait(LEKTOR);
            tempusPage.expectCalendarEventRoom(updatedEventId, newRoom);

            tempusPage.selectRoleAndWait(STUDENT);
            tempusPage.expectCalendarEventRoom(updatedEventId, newRoom);
          });
        });
      });
  });

  it("can drop event from calendar into parking slot", () => {
    tempusPage.getEventParkingSlot().should("exist");
    tempusPage.getParkedEvents().should("have.length", 0);

    tempusPage
      .getCalendarEvents()
      .first()
      .drag(tempusPage.selectors.parkingSlot);

    tempusPage.getParkedEvents().should("have.length", 1);
  });

  it("event deletion on planner preview preservers event on planner, but shows it as unsynced on lektor and student preview", () => {
    tempusPage.clearMondayFirstColumnBeforeAndAfter();

    tempusPage.syncAndReloadPlanner();

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

        tempusPage.selectRoleAndWait(LEKTOR);
        tempusPage.getCalendarEventById(eventId).should("not.exist");

        tempusPage.selectRoleAndWait(STUDENT);
        tempusPage.getCalendarEventById(eventId).should("not.exist");
      });
  });

  it("syncing event deletion on planner preview removes event from other previews", () => {
    tempusPage.clearMondayFirstColumnBeforeAndAfter();

    tempusPage.syncAndReloadPlanner();

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

        tempusPage.syncAndReloadPlanner();

        tempusPage.getCalendarEventById(eventId).should("not.exist");

        tempusPage.selectRoleAndWait(LEKTOR);
        tempusPage.getCalendarEventById(eventId).should("not.exist");

        tempusPage.selectRoleAndWait(STUDENT);
        tempusPage.getCalendarEventById(eventId).should("not.exist");
      });
  });

  it("can bottom resize an event on planner preview", () => {
    tempusPage.clearMondayFirstColumnBeforeAndAfter();

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
    tempusPage.clearMondayFirstColumnBeforeAndAfter();

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

  it("can drag and drop one course event into the calendar when Stundenraster is disabled", () => {
    tempusPage.clearMondayFirstColumnBeforeAndAfter();

    tempusPage.getCalendarSection().should("exist");
    tempusPage.waitForCalendarToFinishLoading();
    tempusPage.disableStundenraster();

    tempusPage.selectFirstCourse();
    waitForOk("@fetchCoursePickerCourses");

    tempusPage.getCalendarEvents().then(($events) => {
      cy.wrap($events.length).as("initialEventCount");
    });

    tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);
    tempusPage.dropCourseOnCalendarPart(0, 1);

    waitForOk("@addCalendarEvent");
    waitForOk("@fetchPlanData");

    tempusPage.waitForCalendarToFinishLoading();

    cy.get("@initialEventCount").then((initialEventCount) => {
      tempusPage
        .getCalendarEvents()
        .should("have.length", initialEventCount + 1);
    });
  });

  it("can drag and drop an event on part 6 when Stundenraster is disabled", () => {
    tempusPage.clearMondayFirstColumnBeforeAndAfter();

    tempusPage.syncAndReloadPlanner();
    tempusPage.disableStundenraster();

    tempusPage
      .getCalendarEventsByTimeRange("08:00:00", "08:45:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;

        tempusPage.getEventGridRow(eventId).as("originalGridRow");
        tempusPage.dropEventOnCalendarPart(eventId, 6, {
          scrollIntoView: true,
        });

        cy.wait("@updateCalendarEvent").then((interception) => {
          expect(interception.response.statusCode).to.eq(200);

          const updatedEventId = tempusPage.getUpdatedKalenderId(interception);
          expect(updatedEventId, "updated planner event id").to.exist;

          cy.wrap(updatedEventId).as("updatedEventId");
        });
        waitForOk("@fetchPlanData");
        tempusPage.waitForCalendarToFinishLoading();

        cy.get("@updatedEventId").then((updatedEventId) => {
          tempusPage.getCalendarEventById(updatedEventId).should("be.visible");

          cy.get("@originalGridRow").then((originalGridRow) => {
            tempusPage.getEventGridRow(updatedEventId).then((updatedGridRow) => {
              expect(updatedGridRow).to.not.eq(originalGridRow);
            });
          });
        });
      });
  });
});
