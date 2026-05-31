class TempusPage {
  selectors = {
    calendarBaseGrid: "div[data-cy='tempus'] .fhc-calendar-base-grid",
    parkingSlot: "div[data-cy='tempus'] #parkingslot",
  };

  visit = () => cy.visit("/index.ci.php/tempus", {
    onBeforeLoad(win) {
      win.localStorage.removeItem("tempus_parking");
      win.localStorage.removeItem("tempus_searchtypes");
      win.sessionStorage.removeItem("tempus_searchstr");
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
  getCalendarEvents = () =>  this.getCalendarSection().then(($calendar) => {
    return $calendar.find(".fhc-calendar-base-grid-line-event");
  });
  getLecturerWishOverlays = () => this.getCalendarSection().find(".bg-lecturer-wish");
  getCalendarEventsWithRoom = () => this.getCalendarEvents().filter((index, event) => {
    const eventData = JSON.parse(event.getAttribute("data-fhc-draggable-value"));
    return !!eventData?.orig?.ort_kurzbz;
  });
  getCalendarEventsWithLecturer = () => this.getCalendarEvents().filter((index, event) => {
    const eventData = JSON.parse(event.getAttribute("data-fhc-draggable-value"));
    return eventData?.orig?.lektor?.some((lecturer) => lecturer?.kurzbz);
  });
  getCalendarEventsWithLehreinheit = () => this.getCalendarEvents().filter((index, event) => {
    const eventData = JSON.parse(event.getAttribute("data-fhc-draggable-value"));
    const lehreinheitIds = eventData?.orig?.lehreinheit_id;

    return Array.isArray(lehreinheitIds)
      ? lehreinheitIds.length > 0
      : !!lehreinheitIds;
  });
  getCalendarEventsWithLehreinheitAndRoom = () =>
    this.getCalendarEventsWithLehreinheit().filter((index, event) => {
      const eventData = JSON.parse(event.getAttribute("data-fhc-draggable-value"));

      return !!eventData?.orig?.ort_kurzbz;
    });
  getNavbarSearchInput = () => this.getTempusOverview().find("header .searchbar_input");
  getNavbarRoomSearchResult = (room) => {
    const escapedRoom = room.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

    return cy.contains(
      ".searchbar-result-room .searchbar-result-template-action",
      new RegExp(`^\\s*${escapedRoom}\\s*$`),
    );
  };
  getFirstNavbarEmployeeSearchResult = () =>
    cy.get(".searchbar-result-student .searchbar_data .searchbar-result-template-action").first();
  getSelectedRoomIndicator = (room) =>
    this.getSidebarMenu().contains(".room-selection", room);
  getSelectedRoomRemoveButton = (room) =>
    this.getSelectedRoomIndicator(room).find("i.fa-xmark");
  getSelectedLecturerIndicator = (lecturer) =>
    this.getSidebarMenu().contains(".lecture-selection .fw-semibold", lecturer);
  getSelectedLecturerRemoveButton = (lecturer) =>
    this.getSelectedLecturerIndicator(lecturer).find("i.fa-xmark");
  getSelectedLecturerPlanToggle = (lecturer) =>
    this.getSelectedLecturerIndicator(lecturer)
      .parent()
      .contains(".d-flex.align-items-center", "Plan");
  getSelectedLecturerWishToggle = (lecturer) =>
    this.getSelectedLecturerIndicator(lecturer)
      .parent()
      .contains(".d-flex.align-items-center", "Zeitwünsche");

  getCalendarEventById = (id) => this.getCalendarEvents().filter(`div[data-id="event-${id}"]`);
  getCalendarPartDropTarget = (partIndex) =>
    `${this.selectors.calendarBaseGrid} .part-body:nth-of-type(${partIndex})`;
  dropEventOnCalendarPart = (eventId, partIndex, options = {}) => {
    const event = this.getCalendarEventById(eventId);

    return event.should("be.visible").drag(
      this.getCalendarPartDropTarget(partIndex),
      {
        waitForAnimations: false,
        animationDistanceThreshold: 0,
        ...options,
      },
    );
  };
  dropCourseOnCalendarPart = (courseIndex, partIndex, options = {}) => {
    const course = this.getCoursePickerRows().eq(courseIndex);

    return course.should("be.visible").drag(
      this.getCalendarPartDropTarget(partIndex),
      {
        waitForAnimations: false,
        animationDistanceThreshold: 0,
        ...options,
      },
    );
  };
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
  getCalendarEventsByTimeRange = (startTime, endTime) => this.getCalendarEvents().filter((index, event) => {
    const eventData = JSON.parse(event.getAttribute("data-fhc-draggable-value"));
    return eventData?.orig?.beginn === startTime && eventData?.orig?.ende === endTime;
  });
  getCalendarLoadingEvents = () => this.getCalendarSection().find(".placeholder-glow");
  getCalendarEventModal = () => this.getCalendarSection().find(".bootstrap-modal.show");
  getEventContextMenu = () => cy.get("[data-cy='eventContextMenu']");
  getEventContextMenuOption = (option) => this.getEventContextMenu().contains("button", option);
  getRaumauswahlModal = () =>
    cy.contains(".bootstrap-modal.show .modal-title", "Raumauswahl")
      .closest(".bootstrap-modal.show");
  getRaumauswahlRoomOptions = () =>
    this.getRaumauswahlModal().find(".list-group-item");
  getCalendarEventRoom = (id) =>
    this.getCalendarEventById(id).find(".event-place");
  getStundenrasterToggle = () =>
    cy.contains(".form-check-label", "Stundenraster").parent();
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

  disableStundenraster = () => {
    this.getStundenrasterToggle().then(($toggle) => {
      if ($toggle.find(".fa-toggle-on").length) {
        cy.wrap($toggle).click();
      }
    });

    this.getStundenrasterToggle().find("i").should("have.class", "fa-toggle-off");
  };

  waitForCalendarToFinishLoading = () => {
    this.getCalendarLoadingEvents().should("not.exist");
  };
}

export const tempusPage = new TempusPage();
