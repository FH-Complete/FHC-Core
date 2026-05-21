import { tempusPage } from "../../../support/pages/tempus.po";

context("Base tempus tests", () => {
  beforeEach(() => {
    cy.login();

    cy.intercept({ method: "GET", url: "**/StgTree" }).as("fetchCourseTree");
    cy.intercept({ method: "GET", url: /\/tempus\/Kalender\/getPlan(?:\?|$)/ }).as("fetchPlanData");
    cy.intercept({ method: "GET", url: /\/tempus\/Kalender\/getPlanLecturer(?:\?|$)/ }).as("fetchLecturerPlanData");
    cy.intercept({ method: "GET", url: /\/tempus\/Kalender\/getPlanStudent(?:\?|$)/ }).as("fetchStudentPlanData");
    cy.intercept({ method: "GET", url: "**/tempus/Kalender/getHistory**" }).as("fetchEventHistory");
    cy.intercept({ method: "GET", url: "**/tempus/coursepicker/getByStg**" }).as("fetchCoursePickerCourses");
    
    tempusPage.visit();

    cy.wait("@fetchCourseTree").its("response.statusCode").should("eq", 200);
    cy.wait("@fetchPlanData").its("response.statusCode").should("eq", 200);
  });

  it("can access Tempus page with valid credentials", () => {
    tempusPage.getTempusOverview().should("be.visible");
  });

  it ("shows all expected page elements", () => {
    tempusPage.getTempusOverview().should("be.visible");
    tempusPage.getSidebarMenu().should("exist");
    tempusPage.getSlideInCoursesMenu().should("exist");
    tempusPage.getCalendarSection().should("be.visible");
    tempusPage.getPreviewRoleOptionsHolder().should("be.visible");
  });

  it("can open courses menu", () => {    
    tempusPage.getSlideInCoursesMenu().should("exist").should("be.hidden");

    tempusPage.openCoursesMenu();

    tempusPage.getCourseTreeRows().should("have.length.greaterThan", 0);
  });

  it("can select one course and show preview of its events", () => {
    tempusPage.getSlideInCoursesMenu().should("exist");
    tempusPage.getCourseTreeRows().should("have.length.greaterThan", 0);
    tempusPage.getCoursePicker().should("exist");
    tempusPage.getCoursePickerRows().should("have.length", 0);

    tempusPage.selectFirstCourse();
    cy.wait("@fetchCoursePickerCourses").its("response.statusCode").should("eq", 200);

    tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);
  });

  it("can search for a course event in the course picker", () => {
    const searchTerm = "PHY1 SO";

    tempusPage.selectFirstCourse();
    cy.wait("@fetchCoursePickerCourses").its("response.statusCode").should("eq", 200);

    tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);

    tempusPage.getCoursePickerSearchInput().type(searchTerm);

    tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);
    tempusPage.getCoursePickerRows().first().should("contain.text", searchTerm);
  });

  it("can drag and drop one course event into the calendar", () => {
    tempusPage.getCalendarSection().should("exist");

    tempusPage.selectFirstCourse();
    cy.wait("@fetchCoursePickerCourses").its("response.statusCode").should("eq", 200);
    
    tempusPage.getCoursePickerRows().first().drag(tempusPage.selectors.calendarSection);
    
    tempusPage.getCalendarEvents().should("have.length", 1);
  });

  it("shows the same number of calendar events for all role previews", () => {
    const rolePreviews = [
      { label: "Planer", fetchAlias: "@fetchPlanData" },
      { label: "Lektor", fetchAlias: "@fetchLecturerPlanData" },
      { label: "Student", fetchAlias: "@fetchStudentPlanData" },
    ];
    const eventCounts = {};

    cy.wrap(rolePreviews).each((rolePreview) => {
      tempusPage.selectPreviewRole(rolePreview.label);
      cy.wait(rolePreview.fetchAlias).its("response.statusCode").should("eq", 200);
      tempusPage.waitForCalendarToFinishLoading();

      tempusPage.getCalendarEvents().then(($events) => {
        eventCounts[rolePreview.label] = $events.length;
      });
    }).then(() => {
      const expectedCount = eventCounts[rolePreviews[0].label];

      Object.entries(eventCounts).forEach(([role, count]) => {
        expect(count, `${role} calendar event count`).to.eq(expectedCount);
      });
    });
  });

  it("shows event details modal when clicking a calendar event", () => {
    tempusPage.waitForCalendarToFinishLoading();
    tempusPage.getCalendarEvents().should("have.length.greaterThan", 0);

    tempusPage.getCalendarEvents().first().click();

    tempusPage.getCalendarEventModal().should("be.visible");
  });

  it("shows event context menu when right clicking a calendar event", () => {
    tempusPage.waitForCalendarToFinishLoading();
    tempusPage.getCalendarEvents().should("have.length.greaterThan", 0);

    tempusPage.getCalendarEvents().first().rightclick();

    tempusPage.getEventContextMenu().should("be.visible");
  });

  it("shows history modal when selecting History from event context menu", () => {
    tempusPage.waitForCalendarToFinishLoading();
    tempusPage.getCalendarEvents().should("have.length.greaterThan", 0);

    tempusPage.getCalendarEvents().first().rightclick();
    tempusPage.getEventContextMenuOption("History").click();
    cy.wait("@fetchEventHistory").its("response.statusCode").should("eq", 200);

    tempusPage.getHistoryModal().should("be.visible");
  });

  it("shows reservation modal when dropping reservation handle on calendar", () => {
    tempusPage.waitForCalendarToFinishLoading();
    tempusPage.getCalendarSection().should("be.visible");

    tempusPage.getReservationDragHandle().drag(tempusPage.selectors.calendarSection);

    tempusPage.getReservationModal().should("be.visible");
  });

  it("can drop event from calendar into parking slot", () => {
    tempusPage.getEventParkingSlot().should("exist");
    tempusPage.getParkedEvents().should("have.length", 0);

    tempusPage.getCalendarEvents().first().drag(tempusPage.selectors.parkingSlot);

    tempusPage.getParkedEvents().should("have.length", 1);
  });
});
