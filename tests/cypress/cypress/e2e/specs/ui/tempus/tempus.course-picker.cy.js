import { waitForOk } from "../../../../support/helpers/network";
import { tempusPage } from "../../../../support/pages/tempus.po";

context("Tempus course picker tests", () => {
  beforeEach(() => {
    tempusPage.visitAndWaitForPlanner();
  });

  afterEach(() => {
    return tempusPage.clearMondayFirstColumnAfterTest();
  });

  it("can select one course and show preview of its events", () => {
    tempusPage.getSlideInCoursesMenu().should("exist");
    tempusPage.getCourseTreeRows().should("have.length.greaterThan", 0);
    tempusPage.getCoursePicker().should("exist");
    tempusPage.getCoursePickerRows().should("have.length", 0);

    tempusPage.selectFirstCourse();
    waitForOk("@fetchCoursePickerCourses");

    tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);
  });

  it("can search for a course event in the course picker", () => {
    tempusPage.selectFirstCourse();
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
    tempusPage.clearMondayFirstColumnBeforeAndAfter();

    tempusPage.getCalendarSection().should("exist");
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage.selectFirstCourse();
    waitForOk("@fetchCoursePickerCourses");

    tempusPage.getCalendarEvents().then(($events) => {
      cy.wrap($events.length).as("initialEventCount");
    });

    tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);
    tempusPage.dropCourseOnCalendarPart(0, 10);

    waitForOk("@addCalendarEvent");
    waitForOk("@fetchPlanData");

    tempusPage.waitForCalendarToFinishLoading();

    cy.get("@initialEventCount").then((initialEventCount) => {
      tempusPage
        .getCalendarEvents()
        .should("have.length", initialEventCount + 1);
    });
  });
});
