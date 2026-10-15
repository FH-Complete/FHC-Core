import { waitForOk } from "../../../../support/helpers/network";
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

    tempusApi.deletePlannerEventsForDay("monday");
  });

  it("updates an event room in the planner preview while other previews retain the original room", () => {
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
        let originalRoom = eventData?.orig?.ort_kurzbz;
        if (Array.isArray(originalRoom)) originalRoom = originalRoom.length ? originalRoom[0] : "";

        expect(eventId, "planner event id").to.exist;
        expect(originalRoom, "original event room").to.be.a("string").and.not.be
          .empty;

        tempusPage.expectCalendarEventRoom(eventId, originalRoom);

        tempusPage.changeEventRoom(eventId, originalRoom).then((result) => {
          const { newRoom, updatedEventId } = result;
			tempusPage.expectCalendarEventRoom(updatedEventId, newRoom);

			tempusPage.selectRoleAndWait(LEKTOR);
			tempusPage.expectCalendarEventRoom(eventId, originalRoom);

			tempusPage.selectRoleAndWait(STUDENT);
			tempusPage.expectCalendarEventRoom(eventId, originalRoom);
        });
      });
  });

  it("selects and displays a room for an event", () => {
    tempusPage
      .getCalendarEventsByWeekdayAndTimeRange(
        "Thursday",
        "14:30:00",
        "15:15:00",
      )
      .should("have.length.greaterThan", 0)
      .first()
      .rightclick();

    tempusPage.getEventContextMenuOption("Raumauswahl").click({ force: true });
    waitForOk("@fetchRoomSuggestions");

    tempusPage.selectFirstRoomAndSave().then((selectedRoom) => {
      tempusPage.waitForUpdatedEventId().then((updatedEventId) => {
        waitForOk("@fetchPlanData");
        tempusPage.waitForCalendarToFinishLoading();

        tempusPage.expectCalendarEventRoom(updatedEventId, selectedRoom);
      });
    });
  });

  it("changes an event room", () => {
    tempusPage
      .getCalendarEventsByWeekdayAndTimeRange(
        "Thursday",
        "15:15:00",
        "16:00:00",
      )
      .should("have.length.greaterThan", 0)
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const eventData = JSON.parse(eventJSON);
        const eventId = eventData?.id;
        let originalRoom = eventData?.orig?.ort_kurzbz;
        if (Array.isArray(originalRoom))
          originalRoom = originalRoom.length ? originalRoom[0] : "";

        expect(eventId, "planner event id").to.exist;
        expect(originalRoom, "original event room").to.be.a("string").and.not
          .be.empty;

        tempusPage.changeEventRoom(eventId, originalRoom).then((result) => {
          expect(result.newRoom, "updated room").to.not.eq(originalRoom);
          tempusPage.expectCalendarEventRoom(
            result.updatedEventId,
            result.newRoom,
          );
        });
      });
  });

  it("adds multiple rooms to an event", () => {
    tempusPage
      .getCalendarEventsByWeekdayAndTimeRange(
        "Thursday",
        "15:15:00",
        "16:00:00",
      )
      .should("have.length.greaterThan", 0)
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const eventData = JSON.parse(eventJSON);
        const eventId = eventData?.id;
        const currentRooms = eventData?.orig?.ort_kurzbz ?? [];

        expect(eventId, "planner event id").to.exist;

        tempusPage.getCalendarEventById(eventId).rightclick();
        tempusPage.getEventContextMenuOption("Raumauswahl").click({ force: true });
        waitForOk("@fetchRoomSuggestions");

        tempusPage
          .selectAdditionalRoomsAndSave(currentRooms)
          .then((additionalRooms) => {
            tempusPage.waitForUpdatedEventId().then((updatedEventId) => {
              waitForOk("@fetchPlanData");
              tempusPage.waitForCalendarToFinishLoading();

              tempusPage.expectCalendarEventRooms(updatedEventId, additionalRooms);
            });
          });
      });
  });


  it("moves an event from the calendar to the parking slot", () => {
    tempusPage.getEventParkingSlot().should("exist");
    tempusPage.getParkedEvents().should("have.length", 0);

    tempusPage
      .getCalendarEventsByWeekdayAndStartTime("Wednesday", "08:45:00")
      .first()
      .drag(tempusPage.selectors.parkingSlot);

    tempusPage.getParkedEvents().should("have.length", 1);
  });

 it("keeps a deleted planner event visible in lecturer and student previews", () => {
	tempusPage.syncAndReloadPlanner();

	tempusPage
		.getCalendarEventsBySoftTimeRange("16:10:00", "16:55:00")
		.first()
		.invoke("attr", "data-fhc-draggable-value")
		.then((eventJSON) => {
		expect(eventJSON).to.exist;

		const eventGroupId =
			JSON.parse(eventJSON)?.orig?.eindeutige_kalender_gruppen_id;
		expect(eventGroupId).to.exist;

		tempusPage
			.getCalendarEventByUniqueCalenderGroupId(eventGroupId)
			.rightclick();

		tempusPage
			.getEventContextMenuOption("Delete")
			.click({ force: true });

		waitForOk("@fetchPlanData");

		tempusPage
			.getCalendarEventByUniqueCalenderGroupId(eventGroupId)
			.should("not.exist");

		tempusPage.selectRoleAndWait(LEKTOR);
		tempusPage
			.getCalendarEventByUniqueCalenderGroupId(eventGroupId)
			.should("exist");

		tempusPage.selectRoleAndWait(STUDENT);
		tempusPage
			.getCalendarEventByUniqueCalenderGroupId(eventGroupId)
			.should("exist");
		});
});

it("keeps a deleted planner event invisible after syncing", () => {
  tempusPage.syncAndReloadPlanner();

  tempusPage
    .getCalendarEventsBySoftTimeRange("16:55:00", "17:40:00")
    .first()
    .invoke("attr", "data-fhc-draggable-value")
    .then((eventJSON) => {
      expect(eventJSON).to.exist;

      const eventGroupId =
        JSON.parse(eventJSON)?.orig?.eindeutige_kalender_gruppen_id;
      expect(eventGroupId).to.exist;

      tempusPage
        .getCalendarEventByUniqueCalenderGroupId(eventGroupId)
        .rightclick();

      tempusPage
        .getEventContextMenuOption("Delete")
        .click({ force: true });

      waitForOk("@fetchPlanData");

      tempusPage
        .getCalendarEventByUniqueCalenderGroupId(eventGroupId)
        .should("not.exist");

      tempusPage.syncAndReloadPlanner();

      tempusPage
        .getCalendarEventByUniqueCalenderGroupId(eventGroupId)
        .should("not.exist");

      tempusPage.selectRoleAndWait(LEKTOR);
      tempusPage
        .getCalendarEventByUniqueCalenderGroupId(eventGroupId)
        .should("exist");

      tempusPage.selectRoleAndWait(STUDENT);
      tempusPage
        .getCalendarEventByUniqueCalenderGroupId(eventGroupId)
        .should("exist");
    });
});

  it("resizes an event from the bottom in the planner preview", () => {
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
        expect(interception.response.body.data).to.exist;
        const updatedGroupEventId =
          interception.response.body.data?.eindeutige_kalender_gruppen_id ??
          interception.response.body.data;
        cy.wrap(updatedGroupEventId).as("updatedGroupEventId");
      });
      waitForOk("@fetchPlanData");

      cy.get("@updatedGroupEventId").then((updatedGroupEventId) => {
        tempusPage
          .getCalendarEventByUniqueCalenderGroupId(updatedGroupEventId)
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

  it.skip("resizes an event from the top in the planner preview", () => {
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
        expect(interception.response.body.data).to.exist;

        const updatedGroupEventId =
          interception.response.body.data?.eindeutige_kalender_gruppen_id ??
          interception.response.body.data;

        cy.wrap(updatedGroupEventId).as("updatedGroupEventId");
      });
      waitForOk("@fetchPlanData");

      cy.get("@updatedGroupEventId").then((updatedGroupEventId) => {
        tempusPage
          .getCalendarEventByUniqueCalenderGroupId(updatedGroupEventId)
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

  it.skip("adds a course event to the calendar when Stundenraster is disabled", () => {
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

  it.skip("moves an existing event when Stundenraster is disabled", () => {
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
