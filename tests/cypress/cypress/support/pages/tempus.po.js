import { waitForOk } from "../helpers/network";

export const PLANER = "Planer";
export const LEKTOR = "Lektor";
export const STUDENT = "Student";
export const SYNC = "Sync";

const weekdayToGridRowMap = {
  "Monday": "1",
  "Tuesday": "2",
  "Wednesday": "3",
  "Thursday": "4",
  "Friday": "5",
  "Saturday": "6",
  "Sunday": "7",
};
const roleFetchAliases = {
  [PLANER]: "@fetchPlanData",
  [LEKTOR]: "@fetchLecturerPlanData",
  [STUDENT]: "@fetchStudentPlanData",
};

class TempusPage {
  selectors = {
    calendarBaseGrid: "div[data-cy='tempus'] .fhc-calendar-base-grid",
    parkingSlot: "div[data-cy='tempus'] #parkingslot",
  };

  cleanupMondayFirstColumnAfterTest = false;

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
  getCalendarEventsPerWeekHolders = () => this.getCalendarBaseGrid().find(".fhc-calendar-base-grid-line");
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
    const course = this.getCoursePickerRows().eq(courseIndex).find("span").first();

    return course.should("be.visible").drag(
      this.getCalendarPartDropTarget(partIndex),
      {
        waitForAnimations: false,
        animationDistanceThreshold: 0, 
        scrollBehavior: "top",
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
  getCalendarEventsByWeekday = (weekday) => this.getCalendarEventsPerWeekHolders().eq(weekdayToGridRowMap[weekday] - 1).find(".fhc-calendar-base-grid-line-event");
  getCalendarEventsByStartTime = (startTime) => this.getCalendarEvents().filter((index, event) => {
    const eventData = JSON.parse(event.getAttribute("data-fhc-draggable-value"));
    return eventData?.orig?.beginn === startTime;
  });
  getCalendarEventsByTimeRange = (startTime, endTime) => this.getCalendarEvents().filter((index, event) => {
    const eventData = JSON.parse(event.getAttribute("data-fhc-draggable-value"));
    return eventData?.orig?.beginn === startTime && eventData?.orig?.ende === endTime;
  });
  getCalendarEventsBySoftTimeRange = (startTime, endTime) => this.getCalendarEvents().filter((index, event) => {
    const eventData = JSON.parse(event.getAttribute("data-fhc-draggable-value"));
    return eventData?.orig?.beginn >= startTime && eventData?.orig?.ende <= endTime;
  });
  getCalendarEventsByWeekdayAndStartTime = (weekday, startTime) => this.getCalendarEventsByWeekday(weekday).filter((index, event) => {
    const eventData = JSON.parse(event.getAttribute("data-fhc-draggable-value"));
    return eventData?.orig?.beginn === startTime;
  });
  getCalendarEventsWithLehreinheitAndRoomByWeekdayAndStartTime = (weekday, startTime) =>
    this.getCalendarEventsByWeekdayAndStartTime(weekday, startTime).filter((index, event) => {
      const eventData = JSON.parse(event.getAttribute("data-fhc-draggable-value"));

      return !!eventData?.orig?.ort_kurzbz && eventData?.orig?.lehreinheit_id;
    });
  getKalenderId = (eventData) =>
    eventData?.orig?.kalender_id ?? eventData?.id;
  getCalendarEventData = ($event) =>
    JSON.parse($event.attr("data-fhc-draggable-value"));
  getFirstLecturer = (eventData) =>
    eventData?.orig?.lektor?.find((lecturer) => lecturer?.kurzbz);
  getUpdatedKalenderId = (interception) => {
    const retval = interception.response.body?.data?.retval;

    return retval?.kalender_id ?? retval;
  };
  getMondayFirstColumnEventData = ($body) =>
    [
      ...$body
        .find(`${this.selectors.calendarBaseGrid} .fhc-calendar-base-grid-line`)
        .first()
        .find(".fhc-calendar-base-grid-line-event"),
    ]
      .map((event) => {
        const eventJSON = event.getAttribute("data-fhc-draggable-value");

        try {
          return eventJSON ? JSON.parse(eventJSON) : null;
        } catch {
          return null;
        }
      })
      .filter(Boolean);
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

  getSemesterSetterButton = () => this.getSidebarMenu().find(".stv-studiensemester button").last();

  openCoursesMenu = () => {
    this.getAllCoursesSliderBtn().click();
    this.getSlideInCoursesMenu().should("be.visible");
  };

  selectFirstCourse = () => {
    this.openCoursesMenu();
    this.getCourseTreeRows().first().click();
  };

  selectCourseByName = (courseShortName) => {
    this.openCoursesMenu();
    this.getCourseTreeRows().contains(courseShortName).click();
  }

  selectPreviewRole = (role) => {
    this.getPreviewRoleButton(role).click();
  };

  syncAndReloadPlanner = () => {
    this.selectPreviewRole(SYNC);
    waitForOk("@syncCalendar");
    waitForOk("@fetchPlanData");
    this.waitForCalendarToFinishLoading();
  };

  selectRoleAndWait = (role) => {
    this.selectPreviewRole(role);
    waitForOk(roleFetchAliases[role]);
    this.waitForCalendarToFinishLoading();
  };

  expectCalendarEventRoom = (eventId, expectedRoom) => {
    this.getCalendarEventRoom(eventId)
      .scrollIntoView()
      .should("be.visible")
      .invoke("text")
      .then((roomText) => {
        expect(roomText.trim(), "event room").to.eq(expectedRoom);
      });
  };

  setupIntercepts = () => {
    cy.intercept({ method: "GET", url: "**/StgTree" }).as("fetchCourseTree");
    cy.intercept({
      method: "GET",
      url: /\/tempus\/Kalender\/getPlan(?:\?|$)/,
    }).as("fetchPlanData");
    cy.intercept({
      method: "GET",
      url: /\/tempus\/Kalender\/getPlanLecturer(?:\?|$)/,
    }).as("fetchLecturerPlanData");
    cy.intercept({
      method: "GET",
      url: /\/tempus\/Kalender\/getPlanStudent(?:\?|$)/,
    }).as("fetchStudentPlanData");
    cy.intercept({ method: "GET", url: "**/tempus/Kalender/getHistory**" }).as(
      "fetchEventHistory",
    );
    cy.intercept({
      method: "GET",
      url: "**/components/stv/studiensemester/now**",
    }).as("getCurrentSemester");
    cy.intercept({
      method: "POST",
      url: "**/components/stv/studiensemester/set**",
    }).as("setSemester");
    cy.intercept({
      method: "GET",
      url: "**/tempus/Kalender/getRaumvorschlag**",
    }).as("fetchRoomSuggestions");
    cy.intercept({
      method: "GET",
      url: "**/tempus/coursepicker/getByStg**",
    }).as("fetchCoursePickerCourses");
    cy.intercept({
      method: "POST",
      url: "**/searchbar/search**",
    }).as("searchbarSearch");
    cy.intercept({
      method: "POST",
      url: "**/tempus/Kalender/addKalenderEvent**",
    }).as("addCalendarEvent");
    cy.intercept({
      method: "POST",
      url: "**/tempus/Kalender/updateKalenderEvent**",
    }).as("updateCalendarEvent");
    cy.intercept({
      method: "POST",
      url: "**/tempus/Kalender/sync**",
    }).as("syncCalendar");
    cy.intercept({
      method: "POST",
      url: "**/tempus/Kalender/syncToStudent**",
    }).as("syncCalendarToStudent");
    cy.intercept({
      method: "POST",
      url: "**/tempus/Kalender/deleteEntry**",
    }).as("deleteCalendarEvent");
  };

  visitAndWaitForPlanner = () => {
    cy.login();
    this.setupIntercepts();
    this.visit();

    waitForOk("@fetchCourseTree");
    waitForOk("@fetchPlanData");
    this.waitForCalendarToFinishLoading();
  };

  reloadPlannerForCleanup = () => {
    this.visit();
    waitForOk("@fetchCourseTree");
    waitForOk("@fetchPlanData");

    return this.waitForCalendarToFinishLoading();
  };

  deleteKalenderEvent = (kalenderId) =>
    cy.request({
      method: "POST",
      url: "/index.ci.php/api/frontend/v1/tempus/Kalender/deleteEntry",
      form: true,
      body: {
        kalender_id: kalenderId,
      },
      failOnStatusCode: false,
    });

  clearMondayFirstColumn = () => {
    this.reloadPlannerForCleanup();

    return cy.get("body").then(($body) => {
      const calendarIds = [
        ...new Set(
          this.getMondayFirstColumnEventData($body)
            .map(this.getKalenderId)
            .filter(Boolean),
        ),
      ];

      if (!calendarIds.length) {
        return;
      }

      return cy
        .wrap(calendarIds, { log: false })
        .each((kalenderId) => {
          return this.deleteKalenderEvent(kalenderId).then((response) => {
            if (response.status !== 200) {
              Cypress.log({
                name: "tempus cleanup",
                message: `Could not delete calendar event ${kalenderId}: ${response.status}`,
              });
            }
          });
        })
        .then(() => {
          return this.reloadPlannerForCleanup();
        });
    });
  };

  clearMondayFirstColumnBeforeAndAfter = () => {
    this.cleanupMondayFirstColumnAfterTest = true;

    return this.clearMondayFirstColumn();
  };

  clearMondayFirstColumnAfterTest = () => {
    const shouldClearMondayFirstColumn = this.cleanupMondayFirstColumnAfterTest;
    this.cleanupMondayFirstColumnAfterTest = false;

    if (shouldClearMondayFirstColumn) {
      return this.clearMondayFirstColumn();
    }
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

  setCurrentSemester = () => {
    this.getSemesterSetterButton().click();
    //waitForOk("@getCurrentSemester");
    //waitForOk("@setSemester");
  }

}

export const tempusPage = new TempusPage();
