import { waitForOk } from "../../../../support/helpers/network";
import { getDateForDay } from "../../../../support/helpers/date";
import { tempusApi } from "../../../../support/api/tempusApi";
import {
  LEKTOR,
  STUDENT,
  tempusPage,
} from "../../../../support/pages/tempus.po";

const TARGETED_STUDY_PLAN_SHORT_CODE = "STG5";

context("Tempus event mutation tests", () => {
  beforeEach(() => {
    tempusPage.visitAndWaitForPlanner();

    tempusApi
      .getPlannerEvents(getDateForDay("monday"), getDateForDay("monday"))
      .then((events) => {
        events.forEach((event) => {
          tempusApi.deleteKalenderEvent(event.kalender_id);
        });
      });
  });

  it("room change on planner preview updates planner event, but keeps original room on other previews", () => {
    tempusPage.syncAndReloadPlanner();

    tempusPage
      .getCalendarEventsWithLehreinheitAndRoomByWeekdayAndStartTime(
        "Wednesday",
        "08:00:00",
      )
      .should("have.length.greaterThan", 0);

    tempusPage
      .getCalendarEventsWithLehreinheitAndRoomByWeekdayAndStartTime(
        "Wednesday",
        "08:00:00",
      )
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const eventData = JSON.parse(eventJSON);
        const eventId = eventData?.id;
        const originalRoom = eventData?.orig?.ort_kurzbz;
        expect(eventId, "planner event id").to.exist;
        expect(originalRoom, "original event room").to.be.a("string").and.not.be
          .empty;

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
    tempusPage.syncAndReloadPlanner();

    tempusPage
      .getCalendarEventsWithLehreinheitAndRoomByWeekdayAndStartTime(
        "Wednesday",
        "08:00:00",
      )
      .should("have.length.greaterThan", 0);

    tempusPage
      .getCalendarEventsWithLehreinheitAndRoomByWeekdayAndStartTime(
        "Wednesday",
        "08:00:00",
      )
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const eventData = JSON.parse(eventJSON);
        const eventId = eventData?.id;
        const originalRoom = eventData?.orig?.ort_kurzbz;
        expect(eventId, "planner event id").to.exist;
        expect(originalRoom, "original event room").to.be.a("string").and.not.be
          .empty;

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
      .getCalendarEventsByWeekdayAndStartTime("Wednesday", "08:45:00")
      .first()
      .drag(tempusPage.selectors.parkingSlot);

    tempusPage.getParkedEvents().should("have.length", 1);
  });

  it("event deletion on planner preview preservers event on planner, but shows it as unsynced on lektor and student preview", () => {
    tempusPage.syncAndReloadPlanner();

    tempusPage
      .getCalendarEventsByWeekdayAndStartTime("Wednesday", "09:40:00")
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
    tempusPage.syncAndReloadPlanner();

    tempusPage
      .getCalendarEventsByWeekdayAndStartTime("Wednesday", "10:25:00")
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
    tempusPage.getCalendarSection().should("exist");
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage
      .getCalendarEventsByWeekdayAndStartTime("Thursday", "08:00:00")
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
        .realMouseMove(0, 5)
        .realMouseMove(0, 5)
        .realMouseMove(0, 10)
        .realMouseMove(0, 100)
        .realMouseUp();

      cy.wait("@updateCalendarEvent").then((interception) => {
        expect(interception.response.statusCode).to.eq(200);
        expect(interception.response.body.data.retval).to.exist;
        const updatedEventId =
          interception.response.body.data.retval?.kalender_id ??
          interception.response.body.data.retval;
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

  it.skip("can top resize an event on planner preview", () => {
    tempusPage.getCalendarSection().should("exist");
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage
      .getCalendarEventsByWeekdayAndStartTime("Friday", "10:25:00")
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
        .realMouseMove(0, -5)
        .realMouseMove(0, -5)
        .realMouseMove(0, -10)
        .realMouseMove(0, -20)
        .realMouseUp();

      cy.wait("@updateCalendarEvent").then((interception) => {
        expect(interception.response.statusCode).to.eq(200);
        expect(interception.response.body.data.retval).to.exist;

        const updatedEventId =
          interception.response.body.data.retval?.kalender_id ??
          interception.response.body.data.retval;

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

  it.skip("can drag and drop one course event into the calendar when Stundenraster is disabled", () => {
    tempusPage.setCurrentSemester();

    tempusPage.getCalendarSection().should("exist");
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage.disableStundenraster();
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage.selectCourseByName(TARGETED_STUDY_PLAN_SHORT_CODE);
    waitForOk("@fetchCoursePickerCourses");

    cy.wait(1000);

    tempusPage.getCalendarEvents().then(($events) => {
      cy.wrap($events.length).as("initialEventCount");
    });

    tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);

    tempusPage
      .getCoursePickerRows()
      .first()
      .scrollIntoView()
      .should("be.visible")
      .drag(".fhc-calendar-base-grid-line:first", {
        waitForAnimations: true,
        animationDistanceThreshold: 0,
        target: { position: "top" },
      });

    waitForOk("@addCalendarEvent");
    waitForOk("@fetchPlanData");

    tempusPage.waitForCalendarToFinishLoading();

    cy.get("@initialEventCount").then((initialEventCount) => {
      tempusPage
        .getCalendarEvents()
        .should("have.length", initialEventCount + 1);
    });
  });

  it.skip("can drag and drop an existing event when Stundenraster is disabled", () => {
    tempusPage.syncAndReloadPlanner();
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage.disableStundenraster();

    cy.wait(1000);

    tempusPage
      .getCalendarEventsByWeekdayAndStartTime("Wednesday", "11:20:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;

        tempusPage
          .getCalendarEventById(eventId)
          .should("be.visible")
          .drag(".fhc-calendar-base-grid-line:first", {
            waitForAnimations: true,
            animationDistanceThreshold: 0,
            target: { position: "top" },
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

          tempusPage
            .getCalendarEventsByWeekday("Monday")
            .first()
            .invoke("attr", "data-fhc-draggable-value")
            .then((movedEventJSON) => {
              const movedEventId = JSON.parse(movedEventJSON)?.id;
              expect(movedEventId).to.eq(updatedEventId);
            });
        });
      });
  });
});
