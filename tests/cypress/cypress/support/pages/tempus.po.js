class TempusPage {
  selectors = {
    calendarSection: "div[data-cy='tempus'] .fhc-calendar-base",
    calendarBaseGrid: "div[data-cy='tempus'] .fhc-calendar-base-grid",
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
  getCalendarBaseGrid = () => this.getCalendarSection().find(".fhc-calendar-base-grid");
  getCoursePicker = () => this.getTempusOverview().find(".course-picker");
  getPreviewRoleOptionsHolder = () => this.getTempusOverview().find("[data-cy='previewRoleOptionsHolder']");
  getAllCoursesSliderBtn = () => this.getSidebarMenu().find("button[title='Verband']").first();
  getCourseTreeRows = () => this.getSlideInCoursesMenu().find(".p-treetable-tbody tr");
  getCoursePickerRows = () => this.getCoursePicker().find(".course-picker-row");
  getCoursePickerSearchInput = () => this.getCoursePicker().find("input");
  getCalendarEvents = () => this.getCalendarSection().find(".fhc-calendar-base-grid-line-event");

  getCalendarEventById = (id) => this.getCalendarEvents().filter(`div[data-id="event-${id}"]`);
  getCalendarPartDropTarget = (partIndex) =>
    `${this.selectors.calendarBaseGrid} .part-body:nth-of-type(${partIndex})`;
  dropEventOnCalendarPart = (eventId, partIndex, options = {}) => {
    const { scrollIntoView = false, ...dragOptions } = options;
    const event = this.getCalendarEventById(eventId);
    const eventToDrag = scrollIntoView ? event.scrollIntoView() : event;

    return eventToDrag.should("be.visible").focus().drag(
      this.getCalendarPartDropTarget(partIndex),
      {
        waitForAnimations: false,
        animationDistanceThreshold: 0,
        scrollBehavior: false,
        ...dragOptions,
      },
    );
  };
  dropCourseOnCalendarPart = (courseIndex, partIndex, options = {}) => {
    const { scrollIntoView = false, ...dragOptions } = options;
    const course = this.getCoursePickerRows().eq(courseIndex);
    const courseToDrag = scrollIntoView ? course.scrollIntoView() : course;

    return courseToDrag.should("be.visible").drag(
      this.getCalendarPartDropTarget(partIndex),
      {
        waitForAnimations: false,
        animationDistanceThreshold: 0,
        ...dragOptions,
      },
    );
  };
  updateKalenderEvent = (kalenderId, updatedInfos, options = {}) =>
    cy.request({
      method: "POST",
      url: "/index.ci.php/api/frontend/v1/tempus/Kalender/updateKalenderEvent",
      body: {
        kalender_id: kalenderId,
        updatedInfos,
      },
      ...options,
    });
  restoreKalenderEventTime = (kalenderId, startTime, endTime, options = {}) =>
    this.updateKalenderEvent(
      kalenderId,
      {
        start_time: startTime,
        end_time: endTime,
      },
      options,
    );
  getEventGridRowFromStyle = (style) =>
    /grid-row:\s*([^;]+)/.exec(style)?.[1]?.trim();
  getEventGridRow = (id) =>
    this.getCalendarEventById(id)
      .invoke("attr", "style")
      .then((style) => this.getEventGridRowFromStyle(style));
  getCalendarEventsByStartTime = (startTime) => this.getCalendarEvents().filter((index, event) => {
    const eventData = JSON.parse(event.getAttribute("data-fhc-draggable-value"));
    return eventData?.orig?.beginn === startTime;
  });
  getCalendarEventsByEndTime = (endTime) => this.getCalendarEvents().filter((index, event) => {
    const eventData = JSON.parse(event.getAttribute("data-fhc-draggable-value"));
    return eventData?.orig?.ende === endTime;
  });
  getCalendarEventsByTimeRange = (startTime, endTime) => this.getCalendarEvents().filter((index, event) => {
    const eventData = JSON.parse(event.getAttribute("data-fhc-draggable-value"));
    return eventData?.orig?.beginn === startTime && eventData?.orig?.ende === endTime;
  });
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
