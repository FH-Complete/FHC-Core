class TempusPage {
  selectors = {
    calendarSection: "div[data-cy='tempus'] .fhc-calendar-base",
    parkingSlot: "div[data-cy='tempus'] #parkingslot",
  };

  visit = () => cy.visit("/index.ci.php/tempus", {
    onBeforeLoad(win) {
      win.localStorage.removeItem("tempus_parking");
    },
  });

  getTempusOverview = () => cy.get("div[data-cy='tempus']");
  getSlideInCoursesMenu = () => cy.get("div[data-cy='verbandMenu']");
  getSidebarMenu = () => this.getTempusOverview().find("#sidebarMenu");
  getCalendarSection = () => this.getTempusOverview().find(".fhc-calendar-base");
  getCoursePicker = () => this.getTempusOverview().find(".course-picker");
  getPreviewRoleOptionsHolder = () => this.getTempusOverview().find("[data-cy='previewRoleOptionsHolder']");
  getAllCoursesSliderBtn = () => this.getSidebarMenu().find("button[title='Verband']").first();
  getCourseTreeRows = () => this.getSlideInCoursesMenu().find(".p-treetable-tbody tr");
  getCoursePickerRows = () => this.getCoursePicker().find(".course-picker-row");
  getCoursePickerSearchInput = () => this.getCoursePicker().find("input");
  getCalendarEvents = () => this.getCalendarSection().find(".fhc-calendar-base-grid-line-event");
  getCalendarLoadingEvents = () => this.getCalendarSection().find(".placeholder-glow");
  getCalendarEventModal = () => this.getCalendarSection().find(".bootstrap-modal.show");
  getEventContextMenu = () => cy.get("[data-cy='eventContextMenu']");
  getEventContextMenuOption = (option) => this.getEventContextMenu().contains("button", option);
  getHistoryModal = () => cy.get("[data-cy='historyModal']");
  getReservationDragHandle = () => cy.get("[data-cy='reservationDragHandle']");
  getReservationModal = () => cy.get("[data-cy='reservationModal']");
  getEventParkingSlot = () => this.getSidebarMenu().find("#parkingslot");
  getParkedEvents = () => this.getEventParkingSlot().find(".fhc-calendar-base-grid-line-event");
  getPreviewRoleButton = (role) => this.getPreviewRoleOptionsHolder().contains("button", role);

  openCoursesMenu = () => {
    this.getAllCoursesSliderBtn().click();
    this.getSlideInCoursesMenu().should("be.visible");
  };

  selectFirstCourse = () => {
    this.openCoursesMenu();
    this.getCourseTreeRows().first().click();
  };

  selectPreviewRole = (role) => {
    this.getPreviewRoleButton(role).click();
  };

  waitForCalendarToFinishLoading = () => {
    this.getCalendarLoadingEvents().should("not.exist");
  };
}

export const tempusPage = new TempusPage();
