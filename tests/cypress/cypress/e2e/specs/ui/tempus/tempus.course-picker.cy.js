import { waitForOk } from "../../../../support/helpers/network";
import { getDateForDay } from "../../../../support/helpers/date";
import { tempusPage } from "../../../../support/pages/tempus.po";
import { tempusApi } from "../../../../support/api/tempusApi";

const TARGETED_STUDY_PLAN_SHORT_CODE = "STG5";

const deleteMondayEvents = () =>
  tempusApi
    .getPlannerEvents(getDateForDay("monday"), getDateForDay("monday"))
    .then((events) => {
      events.forEach((event) => {
        tempusApi.deleteKalenderEvent(event.kalender_id);
      });
    });

context("Tempus course picker tests", () => {
  before(() => {
    tempusPage.visitAndWaitForPlanner();
    tempusPage.setCurrentSemester();
  });

  beforeEach(() => {
    tempusPage.visitAndWaitForPlanner();
  });

  it("can select one course and show preview of its events", () => {
    tempusPage.getSlideInCoursesMenu().should("exist");
    tempusPage.getCourseTreeRows().should("have.length.greaterThan", 0);
    tempusPage.getCoursePicker().should("exist");
    tempusPage.getCoursePickerRows().should("have.length", 0);

    tempusPage.selectCourseByName(TARGETED_STUDY_PLAN_SHORT_CODE);
    waitForOk("@fetchCoursePickerCourses");

    tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);
  });

  it("can search for a course event in the course picker", () => {
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

  it("can drag and drop one course event into the calendar", () => {
    deleteMondayEvents();
    tempusPage.getCalendarSection().should("exist");
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage.selectCourseByName(TARGETED_STUDY_PLAN_SHORT_CODE);
    waitForOk("@fetchCoursePickerCourses");

    cy.wait(1000);

    tempusPage.getCalendarEvents().then(($events) => {
      cy.wrap($events.length).as("initialEventCount");
    });

    tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);
    tempusPage.dropCourseOnCalendarPart(0, 10);

    waitForOk("@addCalendarEvent");
    waitForOk("@fetchPlanData");

    tempusPage.waitForCalendarToFinishLoading();
    
    cy.wait(1000);
    
    cy.get("@initialEventCount").then((initialEventCount) => {
      tempusPage
        .getCalendarEvents()
        .should("have.length", initialEventCount + 1);
    });
  });
});
