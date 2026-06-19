import { waitForOk } from "../../../../support/helpers/network";
import { tempusPage } from "../../../../support/pages/tempus.po";

context("Tempus smoke tests", () => {
  beforeEach(() => {
    tempusPage.visitAndWaitForPlanner();
  });

  it("can access Tempus page with valid credentials", () => {
    tempusPage.getTempusOverview().should("be.visible");
  });

  it("shows all expected page elements", () => {
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

  it("shows Raumauswahl modal when selecting Raumauswahl from event context menu", () => {
    tempusPage.waitForCalendarToFinishLoading();
    tempusPage
      .getCalendarEventsWithLehreinheit()
      .should("have.length.greaterThan", 0);

    tempusPage.getCalendarEventsWithLehreinheit().first().rightclick();
    tempusPage.getEventContextMenuOption("Raumauswahl").click();
    waitForOk("@fetchRoomSuggestions");

    tempusPage.getRaumauswahlModal().should("be.visible");
  });

  it("shows resources modal when selecting Ressourcen zuordnen from event context menu", () => {
    tempusPage.waitForCalendarToFinishLoading();
    tempusPage
      .getCalendarEventsWithLehreinheit()
      .should("have.length.greaterThan", 0);

    tempusPage.getCalendarEventsWithLehreinheit().first().rightclick();
    tempusPage.getEventContextMenuOption("Ressourcen zuordnen").click();
    waitForOk("@fetchResourceSuggestions");

    tempusPage.getResourcesModal().should("be.visible");
  });

  it("shows history modal when selecting History from event context menu", () => {
    tempusPage.waitForCalendarToFinishLoading();
    tempusPage.getCalendarEvents().should("have.length.greaterThan", 0);

    tempusPage.getCalendarEvents().first().rightclick();
    tempusPage.getEventContextMenuOption("History").click();
    waitForOk("@fetchEventHistory");

    tempusPage.getHistoryModal().should("be.visible");
  });

  it.skip("shows reservation modal when dropping reservation handle on calendar", () => {
    tempusPage.waitForCalendarToFinishLoading();
    tempusPage.getCalendarBaseGrid().should("be.visible");

    tempusPage
      .getReservationDragHandle()
      .scrollIntoView()
      .drag(tempusPage.selectors.calendarBaseGrid, {
        waitForAnimations: false,
        animationDistanceThreshold: 0,
      });

    tempusPage.getReservationModal().should("be.visible");
  });
});
