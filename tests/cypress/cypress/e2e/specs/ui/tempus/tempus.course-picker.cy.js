import { waitForOk } from "../../../../support/helpers/network";
import { tempusPage } from "../../../../support/pages/tempus.po";
import { tempusApi } from "../../../../support/api/tempusApi";

const TARGETED_STUDY_PLAN_SHORT_CODE = "STG5";

context("Tempus course picker tests", () => {
  before(() => {
    tempusPage.visitAndWaitForPlanner();
    tempusPage.setCurrentSemester();
  });

  beforeEach(() => {
    tempusPage.visitAndWaitForPlanner();
  });

  it("shows a course's events after selecting it", () => {
    tempusPage.getSlideInCoursesMenu().should("exist");
    tempusPage.getCourseTreeRows().should("have.length.greaterThan", 0);
    tempusPage.getCoursePicker().should("exist");
    tempusPage.getCoursePickerRows().should("have.length", 0);

    tempusPage.selectCourseByName(TARGETED_STUDY_PLAN_SHORT_CODE);
    waitForOk("@fetchCoursePickerCourses");

    tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);
  });

  it("filters course teaching units using the course-picker search", () => {
    tempusPage.selectCourseByName(TARGETED_STUDY_PLAN_SHORT_CODE);
    waitForOk("@fetchCoursePickerCourses");

    tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);

    tempusPage
      .getCoursePickerRows()
      .last()
      .find("div:first span:first")
      .invoke("text")
      .as("randomCourseText");

    cy.get("@randomCourseText").then((randomCourseText) => {
      tempusPage.getCoursePickerSearchInput().type(randomCourseText);

      tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);
      tempusPage
        .getCoursePickerRows()
        .first()
        .should("contain.text", randomCourseText);
    });
  });

it("for active room selection dialog config option, shows room selection dialog when adding a course from the course-picker", () => {
  	let settingsData = tempusApi.getSettingsData();
	settingsData.roomless_planning = false;
	settingsData.room_planning = "dialog_room_planning";

	tempusApi.updateSettingsData(settingsData).then((response) => {
		tempusApi.deletePlannerEventsForDay("monday");

		tempusPage.visitAndWaitForPlanner();
		tempusPage.waitForCalendarToFinishLoading();

		tempusPage.selectCourseByName(TARGETED_STUDY_PLAN_SHORT_CODE);
		waitForOk("@fetchCoursePickerCourses");

		tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);

		tempusPage.getCalendarEvents().then(($events) => {
			const initialEventCount = $events.length;

			tempusPage.dropCourseOnCalendarPart(0, 10);
			waitForOk("@addCalendarEvent");
			
			tempusPage.getRoomSelectionModal().should("be.visible");
			tempusPage.selectFirstRoomAndSave();

			waitForOk("@addCalendarEvent");
			waitForOk("@fetchPlanData");

			tempusPage.waitForCalendarToFinishLoading();

			tempusPage
				.getCalendarEvents()
				.should("have.length", initialEventCount + 1);
		});
	});
});

it("for active room selection dialog config option, does not add an event to the calendar when the room selection dialog is closed", () => {
  let settingsData = tempusApi.getSettingsData();
  settingsData.roomless_planning = false;
  settingsData.room_planning = "dialog_room_planning";

  tempusApi.updateSettingsData(settingsData).then((response) => {
    tempusApi.deletePlannerEventsForDay("monday");

    tempusPage.visitAndWaitForPlanner();
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage.selectCourseByName(TARGETED_STUDY_PLAN_SHORT_CODE);
    waitForOk("@fetchCoursePickerCourses");

    tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);

    tempusPage.getCalendarEvents().then(($events) => {
      const initialEventCount = $events.length;

      tempusPage.dropCourseOnCalendarPart(0, 10);

      tempusPage.getRoomSelectionModal().should("be.visible");
      tempusPage.getRoomSelectionModal().find("button[data-cy='closeRoomSelection']").click();
      tempusPage.getRoomSelectionModal().should("not.exist");

      tempusPage.waitForCalendarToFinishLoading();

      tempusPage.getCalendarEvents().should("have.length", initialEventCount);
    });
  });
});

it("for active room selection dialog config option, does not add an event to the calendar when no room is selected", () => {
  let settingsData = tempusApi.getSettingsData();
  settingsData.roomless_planning = false;
  settingsData.room_planning = "dialog_room_planning";

  tempusApi.updateSettingsData(settingsData).then((response) => {
    tempusApi.deletePlannerEventsForDay("monday");

    tempusPage.visitAndWaitForPlanner();
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage.selectCourseByName(TARGETED_STUDY_PLAN_SHORT_CODE);
    waitForOk("@fetchCoursePickerCourses");

    tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);

    tempusPage.getCalendarEvents().then(($events) => {
      const initialEventCount = $events.length;

      tempusPage.dropCourseOnCalendarPart(0, 10);

      tempusPage.getRoomSelectionModal().should("be.visible");
      tempusPage.getRoomSelectionModal().find("button[data-cy='saveRoomSelection']").click();
      tempusPage.getRoomSelectionModal().should("exist");

      tempusPage.waitForCalendarToFinishLoading();

      tempusPage.getCalendarEvents().should("have.length", initialEventCount);
    });
  });
});

it("for active priority room config option, adds an event to the calendar without room selection dialog", () => {
  let settingsData = tempusApi.getSettingsData();
  settingsData.roomless_planning = false;
  settingsData.room_planning = "priority_room_planning";

  tempusApi.updateSettingsData(settingsData).then((response) => {
    tempusApi.deletePlannerEventsForDay("monday");

    tempusPage.visitAndWaitForPlanner();
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage.selectCourseByName(TARGETED_STUDY_PLAN_SHORT_CODE);
    waitForOk("@fetchCoursePickerCourses");

    tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);

    tempusPage.getCalendarEvents().then(($events) => {
      const initialEventCount = $events.length;

      tempusPage.dropCourseOnCalendarPart(0, 10);

      tempusPage.getRoomSelectionModal().should("not.exist");
      tempusPage.getCalendarEvents().should("have.length", initialEventCount);

      tempusPage.waitForCalendarToFinishLoading();

      tempusPage.getCalendarEvents().should("have.length", initialEventCount);
    });
  });
});

});
