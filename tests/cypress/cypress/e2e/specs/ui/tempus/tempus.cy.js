import { tempusPage } from "../../../../support/pages/tempus.po";

const PLANER = "Planer";
const LEKTOR = "Lektor";
const STUDENT = "Student";
const SYNC = "Sync";

const roleFetchAliases = {
  [PLANER]: "@fetchPlanData",
  [LEKTOR]: "@fetchLecturerPlanData",
  [STUDENT]: "@fetchStudentPlanData",
};

const waitForOk = (alias) =>
  cy.wait(alias).its("response.statusCode").should("eq", 200);

const syncAndReloadPlanner = () => {
  tempusPage.selectPreviewRole(SYNC);
  waitForOk("@syncCalendar");
  waitForOk("@fetchPlanData");
  tempusPage.waitForCalendarToFinishLoading();
};

const selectRoleAndWait = (role) => {
  tempusPage.selectPreviewRole(role);
  waitForOk(roleFetchAliases[role]);
  tempusPage.waitForCalendarToFinishLoading();
};

const getCalendarEventData = ($event) =>
  JSON.parse($event.attr("data-fhc-draggable-value"));

const getFirstLecturer = (eventData) =>
  eventData?.orig?.lektor?.find((lecturer) => lecturer?.kurzbz);

const getUpdatedKalenderId = (interception) => {
  const retval = interception.response.body?.data?.retval;

  return retval?.kalender_id ?? retval;
};

const expectEventRoom = (eventId, expectedRoom) => {
  tempusPage
    .getCalendarEventRoom(eventId)
    .scrollIntoView()
    .should("be.visible")
    .invoke("text")
    .then((roomText) => {
      expect(roomText.trim(), "event room").to.eq(expectedRoom);
    });
};

let cleanupMondayFirstColumnAfterTest = false;

const getKalenderId = (eventData) =>
  eventData?.orig?.kalender_id ?? eventData?.id;

const getMondayFirstColumnEventData = ($body) =>
  [
    ...$body
      .find(
        `${tempusPage.selectors.calendarBaseGrid} .fhc-calendar-base-grid-line`,
      )
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

const reloadPlannerForCleanup = () => {
  tempusPage.visit();
  waitForOk("@fetchCourseTree");
  waitForOk("@fetchPlanData");

  return tempusPage.waitForCalendarToFinishLoading();
};

const deleteKalenderEvent = (kalenderId) =>
  cy.request({
    method: "POST",
    url: "/index.ci.php/api/frontend/v1/tempus/Kalender/deleteEntry",
    form: true,
    body: {
      kalender_id: kalenderId,
    },
    failOnStatusCode: false,
  });

const clearMondayFirstColumn = () => {
  reloadPlannerForCleanup();

  return cy.get("body").then(($body) => {
    const calendarIds = [
      ...new Set(
        getMondayFirstColumnEventData($body).map(getKalenderId).filter(Boolean),
      ),
    ];

    if (!calendarIds.length) {
      return;
    }

    return cy
      .wrap(calendarIds, { log: false })
      .each((kalenderId) => {
        return deleteKalenderEvent(kalenderId).then((response) => {
          if (response.status !== 200) {
            Cypress.log({
              name: "tempus cleanup",
              message: `Could not delete calendar event ${kalenderId}: ${response.status}`,
            });
          }
        });
      })
      .then(() => {
        return reloadPlannerForCleanup();
      });
  });
};

const clearMondayFirstColumnBeforeAndAfter = () => {
  cleanupMondayFirstColumnAfterTest = true;

  return clearMondayFirstColumn();
};

context("Base tempus tests", () => {
  beforeEach(() => {
    cy.login();

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

    tempusPage.visit();

    waitForOk("@fetchCourseTree");
    waitForOk("@fetchPlanData");
    tempusPage.waitForCalendarToFinishLoading();
  });

  afterEach(() => {
    const shouldCleanupMondayFirstColumn = cleanupMondayFirstColumnAfterTest;
    cleanupMondayFirstColumnAfterTest = false;

    if (shouldCleanupMondayFirstColumn) {
      return clearMondayFirstColumn();
    }
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
    clearMondayFirstColumnBeforeAndAfter();

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

  it("shows the same number of calendar events for all role previews", () => {
    const rolePreviews = [
      { label: PLANER, fetchAlias: roleFetchAliases[PLANER] },
      { label: LEKTOR, fetchAlias: roleFetchAliases[LEKTOR] },
      { label: STUDENT, fetchAlias: roleFetchAliases[STUDENT] },
    ];
    const eventCounts = {};

    syncAndReloadPlanner();

    cy.wrap(rolePreviews)
      .each((rolePreview) => {
        selectRoleAndWait(rolePreview.label);

        tempusPage.getCalendarEvents().then(($events) => {
          eventCounts[rolePreview.label] = $events.length;
        });
      })
      .then(() => {
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

  it("room change on planner preview updates planner event, but keeps original room on other previews", () => {
    clearMondayFirstColumnBeforeAndAfter();

    syncAndReloadPlanner();

    tempusPage
      .getCalendarEventsWithLehreinheitAndRoom()
      .should("have.length.greaterThan", 0);

    tempusPage
      .getCalendarEventsWithLehreinheitAndRoom()
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const eventData = JSON.parse(eventJSON);
        const eventId = eventData?.id;
        const originalRoom = eventData?.orig?.ort_kurzbz;
        expect(eventId, "planner event id").to.exist;
        expect(originalRoom, "original event room").to.be.a("string").and.not
          .be.empty;

        expectEventRoom(eventId, originalRoom);

        tempusPage
          .getCalendarEventById(eventId)
          .should("be.visible")
          .rightclick();
        tempusPage.getEventContextMenuOption("Raumauswahl").click();
        waitForOk("@fetchRoomSuggestions");

        tempusPage
          .getRaumauswahlRoomOptions()
          .should("have.length.greaterThan", 0)
          .then(($roomOptions) => {
            const roomOption = [...$roomOptions].find((option) => {
              const room = option.innerText.trim();

              return room && room !== originalRoom;
            });

            expect(roomOption, "different room suggestion").to.exist;

            cy.wrap(roomOption.innerText.trim()).as("newRoom");
            cy.wrap(roomOption).click();
          });

        cy.wait("@updateCalendarEvent").then((interception) => {
          expect(interception.response.statusCode).to.eq(200);

          const updatedEventId = getUpdatedKalenderId(interception);
          expect(updatedEventId, "updated planner event id").to.exist;

          cy.wrap(updatedEventId).as("updatedEventId");
        });
        waitForOk("@fetchPlanData");
        tempusPage.waitForCalendarToFinishLoading();

        cy.get("@newRoom").then((newRoom) => {
          cy.get("@updatedEventId").then((updatedEventId) => {
            expectEventRoom(updatedEventId, newRoom);

            selectRoleAndWait(LEKTOR);
            expectEventRoom(eventId, originalRoom);

            selectRoleAndWait(STUDENT);
            expectEventRoom(eventId, originalRoom);
          });
        });
      });
  });

  it("sync after planner preview room change loads new room on all previews", () => {
    clearMondayFirstColumnBeforeAndAfter();

    syncAndReloadPlanner();

    tempusPage
      .getCalendarEventsWithLehreinheitAndRoom()
      .should("have.length.greaterThan", 0);

    tempusPage
      .getCalendarEventsWithLehreinheitAndRoom()
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const eventData = JSON.parse(eventJSON);
        const eventId = eventData?.id;
        const originalRoom = eventData?.orig?.ort_kurzbz;
        expect(eventId, "planner event id").to.exist;
        expect(originalRoom, "original event room").to.be.a("string").and.not
          .be.empty;

        tempusPage
          .getCalendarEventById(eventId)
          .scrollIntoView()
          .should("be.visible")
          .rightclick();
        tempusPage.getEventContextMenuOption("Raumauswahl").click();
        waitForOk("@fetchRoomSuggestions");

        tempusPage
          .getRaumauswahlRoomOptions()
          .should("have.length.greaterThan", 0)
          .then(($roomOptions) => {
            const roomOption = [...$roomOptions].find((option) => {
              const room = option.innerText.trim();

              return room && room !== originalRoom;
            });

            expect(roomOption, "different room suggestion").to.exist;

            cy.wrap(roomOption.innerText.trim()).as("newRoom");
            cy.wrap(roomOption).click();
          });

        cy.wait("@updateCalendarEvent").then((interception) => {
          expect(interception.response.statusCode).to.eq(200);

          const updatedEventId = getUpdatedKalenderId(interception);
          expect(updatedEventId, "updated planner event id").to.exist;

          cy.wrap(updatedEventId).as("updatedEventId");
        });
        waitForOk("@fetchPlanData");
        tempusPage.waitForCalendarToFinishLoading();

        cy.get("@newRoom").then((newRoom) => {
          cy.get("@updatedEventId").then((updatedEventId) => {
            expectEventRoom(updatedEventId, newRoom);

            syncAndReloadPlanner();
            expectEventRoom(updatedEventId, newRoom);

            selectRoleAndWait(LEKTOR);
            expectEventRoom(updatedEventId, newRoom);

            selectRoleAndWait(STUDENT);
            expectEventRoom(updatedEventId, newRoom);
          });
        });
      });
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
    cy.wait(1000);
    tempusPage
      .getReservationDragHandle()
      .drag(
        tempusPage.selectors.calendarBaseGrid + " .part-body:nth-of-type(18)",
        {
          waitForAnimations: false,
          animationDistanceThreshold: 0,
        },
      );

    cy.wait(3000);
    tempusPage.getReservationModal().should("be.visible");
  });

  it("can drop event from calendar into parking slot", () => {
    tempusPage.getEventParkingSlot().should("exist");
    tempusPage.getParkedEvents().should("have.length", 0);

    tempusPage
      .getCalendarEvents()
      .first()
      .drag(tempusPage.selectors.parkingSlot);

    tempusPage.getParkedEvents().should("have.length", 1);
  });

  it("filters calendar events by selecting the first event room in the navbar", () => {
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage
      .getCalendarEventsWithRoom()
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const selectedRoom = JSON.parse(eventJSON)?.orig?.ort_kurzbz;
        expect(selectedRoom, "first event room").to.be.a("string").and.not.be
          .empty;
        expect(
          selectedRoom.length,
          "room search query length",
        ).to.be.greaterThan(1);

        tempusPage.getNavbarSearchInput().clear().type(selectedRoom, {
          parseSpecialCharSequences: false,
        });
        waitForOk("@searchbarSearch");

        tempusPage
          .getNavbarRoomSearchResult(selectedRoom)
          .should("be.visible")
          .click();
        waitForOk("@fetchPlanData");
        tempusPage.waitForCalendarToFinishLoading();

        tempusPage.getSelectedRoomIndicator(selectedRoom).should("be.visible");
        tempusPage.getCalendarEvents().should("have.length.greaterThan", 0);
        tempusPage.getCalendarEvents().each(($event) => {
          const eventData = getCalendarEventData($event);

          expect(eventData?.orig?.ort_kurzbz, "event data room").to.eq(
            selectedRoom,
          );
          cy.wrap($event)
            .find(".event-place")
            .invoke("text")
            .then((eventPlace) => {
              expect(eventPlace.trim(), "event body room").to.eq(selectedRoom);
            });
        });
      });
  });

  it("removes the selected room filter", () => {
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage
      .getCalendarEventsWithRoom()
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const selectedRoom = JSON.parse(eventJSON)?.orig?.ort_kurzbz;
        expect(selectedRoom, "first event room").to.be.a("string").and.not.be
          .empty;
        expect(
          selectedRoom.length,
          "room search query length",
        ).to.be.greaterThan(1);

        tempusPage.getCalendarEvents().then(($events) => {
          const originalEventCount = $events.length;
          cy.wrap(originalEventCount).as("originalEventCount");
        });

        tempusPage.getNavbarSearchInput().clear().type(selectedRoom, {
          parseSpecialCharSequences: false,
        });
        waitForOk("@searchbarSearch");

        tempusPage
          .getNavbarRoomSearchResult(selectedRoom)
          .should("be.visible")
          .click();
        waitForOk("@fetchPlanData");
        tempusPage.waitForCalendarToFinishLoading();

        tempusPage
          .getSelectedRoomIndicator(selectedRoom)
          .should("be.visible");
        tempusPage.getCalendarEvents().should("have.length.greaterThan", 0);

        tempusPage
          .getSelectedRoomRemoveButton(selectedRoom)
          .click();
        waitForOk("@fetchPlanData");
        tempusPage.waitForCalendarToFinishLoading();

        tempusPage.getSelectedRoomIndicator(selectedRoom).should("not.exist");
        cy.get("@originalEventCount").then((originalEventCount) => {
          tempusPage
            .getCalendarEvents()
            .should("have.length", originalEventCount);
        });
      });
  });

  it("filters calendar events by selecting the first event lecturer in the navbar", () => {
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage
      .getCalendarEventsWithLecturer()
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const selectedLecturer = getFirstLecturer(JSON.parse(eventJSON));
        expect(selectedLecturer?.kurzbz, "first event lecturer").to.exist;

        const lecturerSearch =
          selectedLecturer.mitarbeiter_uid ??
          selectedLecturer.uid ??
          selectedLecturer.kurzbz;
        expect(lecturerSearch, "lecturer search query").to.be.a("string").and
          .not.be.empty;

        tempusPage.getNavbarSearchInput().clear().type(lecturerSearch, {
          parseSpecialCharSequences: false,
        });
        waitForOk("@searchbarSearch");

        tempusPage
          .getFirstNavbarEmployeeSearchResult()
          .should("be.visible")
          .invoke("text")
          .then((lecturerName) => {
            const selectedLecturerName = lecturerName.trim();

            tempusPage.getFirstNavbarEmployeeSearchResult().click();
            waitForOk("@fetchPlanData");
            tempusPage.waitForCalendarToFinishLoading();

            tempusPage
              .getSelectedLecturerIndicator(selectedLecturerName)
              .should("be.visible");
            tempusPage.getCalendarEvents().should("have.length.greaterThan", 0);
            tempusPage.getCalendarEvents().each(($event) => {
              const eventData = getCalendarEventData($event);
              const eventLecturers = eventData?.orig?.lektor ?? [];

              expect(
                eventLecturers.map((lecturer) => lecturer.kurzbz),
                "event data lecturers",
              ).to.include(selectedLecturer.kurzbz);
              cy.wrap($event)
                .find(".event-lectors")
                .then(($lecturers) => {
                  const bodyLecturers = [...$lecturers].map((lecturer) =>
                    lecturer.innerText.trim(),
                  );

                  expect(bodyLecturers, "event body lecturers").to.include(
                    selectedLecturer.kurzbz,
                  );
                });
            });
          });
      });
  });

  it("removes the selected lecturer filter", () => {
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage
      .getCalendarEventsWithLecturer()
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const selectedLecturer = getFirstLecturer(JSON.parse(eventJSON));
        expect(selectedLecturer?.kurzbz, "first event lecturer").to.exist;

        const lecturerSearch =
          selectedLecturer.mitarbeiter_uid ??
          selectedLecturer.uid ??
          selectedLecturer.kurzbz;
        expect(lecturerSearch, "lecturer search query").to.be.a("string").and
          .not.be.empty;

        tempusPage.getCalendarEvents().then(($events) => {
          const originalEventCount = $events.length;
          cy.wrap(originalEventCount).as("originalEventCount");
        });

        tempusPage.getNavbarSearchInput().clear().type(lecturerSearch, {
          parseSpecialCharSequences: false,
        });
        waitForOk("@searchbarSearch");

        tempusPage
          .getFirstNavbarEmployeeSearchResult()
          .should("be.visible")
          .invoke("text")
          .then((lecturerName) => {
            const selectedLecturerName = lecturerName.trim();

            tempusPage.getFirstNavbarEmployeeSearchResult().click();
            waitForOk("@fetchPlanData");
            tempusPage.waitForCalendarToFinishLoading();

            tempusPage
              .getSelectedLecturerIndicator(selectedLecturerName)
              .should("be.visible");
            tempusPage.getCalendarEvents().should("have.length.greaterThan", 0);

            tempusPage
              .getSelectedLecturerRemoveButton(selectedLecturerName)
              .click();
            waitForOk("@fetchPlanData");
            tempusPage.waitForCalendarToFinishLoading();

            tempusPage
              .getSelectedLecturerIndicator(selectedLecturerName)
              .should("not.exist");
            cy.get("@originalEventCount").then((originalEventCount) => {
              tempusPage
                .getCalendarEvents()
                .should("have.length", originalEventCount);
            });
          });
      });
  });

  it("hides calendar events when the selected lecturer plan toggle is deselected", () => {
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage
      .getCalendarEventsWithLecturer()
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const selectedLecturer = getFirstLecturer(JSON.parse(eventJSON));
        expect(selectedLecturer?.kurzbz, "first event lecturer").to.exist;

        const lecturerSearch =
          selectedLecturer.mitarbeiter_uid ??
          selectedLecturer.uid ??
          selectedLecturer.kurzbz;
        expect(lecturerSearch, "lecturer search query").to.be.a("string").and
          .not.be.empty;

        tempusPage.getNavbarSearchInput().clear().type(lecturerSearch, {
          parseSpecialCharSequences: false,
        });
        waitForOk("@searchbarSearch");

        tempusPage
          .getFirstNavbarEmployeeSearchResult()
          .should("be.visible")
          .invoke("text")
          .then((lecturerName) => {
            const selectedLecturerName = lecturerName.trim();

            tempusPage.getFirstNavbarEmployeeSearchResult().click();
            waitForOk("@fetchPlanData");
            tempusPage.waitForCalendarToFinishLoading();

            tempusPage
              .getSelectedLecturerIndicator(selectedLecturerName)
              .should("be.visible");
            tempusPage.getCalendarEvents().should("have.length.greaterThan", 0);

            tempusPage
              .getSelectedLecturerPlanToggle(selectedLecturerName)
              .click();
            tempusPage.getCalendarEvents().should("not.exist");
          });
      });
  });

  it("shows lecturer wish overlays when the selected lecturer wish toggle is disabled", () => {
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage
      .getCalendarEventsWithLecturer()
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const selectedLecturer = getFirstLecturer(JSON.parse(eventJSON));
        expect(selectedLecturer?.kurzbz, "first event lecturer").to.exist;

        const lecturerSearch =
          selectedLecturer.mitarbeiter_uid ??
          selectedLecturer.uid ??
          selectedLecturer.kurzbz;
        expect(lecturerSearch, "lecturer search query").to.be.a("string").and
          .not.be.empty;

        tempusPage.getNavbarSearchInput().clear().type(lecturerSearch, {
          parseSpecialCharSequences: false,
        });
        waitForOk("@searchbarSearch");

        tempusPage
          .getFirstNavbarEmployeeSearchResult()
          .should("be.visible")
          .invoke("text")
          .then((lecturerName) => {
            const selectedLecturerName = lecturerName.trim();

            tempusPage.getFirstNavbarEmployeeSearchResult().click();
            waitForOk("@fetchPlanData");

            tempusPage.waitForCalendarToFinishLoading();

            tempusPage
              .getSelectedLecturerIndicator(selectedLecturerName)
              .should("be.visible");
            tempusPage
              .getSelectedLecturerWishToggle(selectedLecturerName)
              .should("be.visible")
              .click();
            tempusPage
              .getLecturerWishOverlays()
              .should("have.length", 0)

            tempusPage
              .getSelectedLecturerWishToggle(selectedLecturerName)
              .should("be.visible")
              .click();
            tempusPage.getLecturerWishOverlays().should("have.length.greaterThan", 0);
          });
      });
  });

  it("on planner event change shows unchanged event on other roles", () => {
    clearMondayFirstColumnBeforeAndAfter();

    syncAndReloadPlanner();

    cy.get(".fhc-calendar-base-grid > div")
      .last()
      .invoke("css", "overflow", "hidden");
    cy.wait(500);
    tempusPage
      .getCalendarEventsByTimeRange("08:00:00", "08:45:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        let eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;

        tempusPage.dropEventOnCalendarPart(eventId, 3, {
          scrollIntoView: true,
        });

        cy.wait("@updateCalendarEvent").then((interception) => {
          expect(interception.response.statusCode).to.eq(200);

          const updatedEventId =
            interception.response.body.data.retval.kalender_id;

          cy.wrap(updatedEventId).as("updatedEventId");
        });
        waitForOk("@fetchPlanData");

        cy.get("@updatedEventId").then((updatedEventId) => {
          tempusPage.getEventGridRow(updatedEventId).then((gridRowStyle) => {
            selectRoleAndWait(LEKTOR);
            tempusPage.getEventGridRow(eventId).then((lecturerGridRow) => {
              expect(lecturerGridRow).to.not.eq(gridRowStyle);
            });

            selectRoleAndWait(STUDENT);
            tempusPage.getEventGridRow(eventId).then((studentGridRow) => {
              expect(studentGridRow).to.not.eq(gridRowStyle);
            });
          });
        });
      });

    cy.get(".fhc-calendar-base-grid > div:last-child()").invoke(
      "css",
      "overflow",
      "auto",
    );
  });

  it("sync after planner preview event change loads event in same way on all previews", () => {
    clearMondayFirstColumnBeforeAndAfter();

    syncAndReloadPlanner();

    tempusPage
      .getCalendarEventsByTimeRange("08:00:00", "08:45:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        let eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;

        tempusPage.dropEventOnCalendarPart(eventId, 4, {
          scrollIntoView: true,
        });

        cy.wait("@updateCalendarEvent").then((interception) => {
          expect(interception.response.statusCode).to.eq(200);

          const updatedEventId =
            interception.response.body.data.retval.kalender_id;

          cy.wrap(updatedEventId).as("updatedEventId");
        });
        waitForOk("@fetchPlanData");

        syncAndReloadPlanner();

        cy.get("@updatedEventId").then((updatedEventId) => {
          tempusPage.getEventGridRow(updatedEventId).then((gridRowStyle) => {
            selectRoleAndWait(LEKTOR);
            tempusPage
              .getEventGridRow(updatedEventId)
              .then((lecturerGridRow) => {
                expect(lecturerGridRow).to.eq(gridRowStyle);
              });

            selectRoleAndWait(STUDENT);
            tempusPage
              .getEventGridRow(updatedEventId)
              .then((studentGridRow) => {
                expect(studentGridRow).to.eq(gridRowStyle);
              });
          });
        });
      });
  });

  it("live unlock after planner preview event change loads event in same way on planner and lektor, but not on student preview", () => {
    clearMondayFirstColumnBeforeAndAfter();

    syncAndReloadPlanner();

    tempusPage
      .getCalendarEventsByTimeRange("08:00:00", "08:45:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        let eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;
        tempusPage.dropEventOnCalendarPart(eventId, 5, {
          scrollIntoView: true,
        });

        cy.wait("@updateCalendarEvent")
          .its("response.body.data.retval.kalender_id")
          .as("updatedEventId");
        waitForOk("@fetchPlanData");

        tempusPage.waitForCalendarToFinishLoading();

        cy.get("@updatedEventId").then((updatedEventId) => {
          tempusPage
            .getCalendarEventById(updatedEventId)
            .should("exist")
            .rightclick();
          tempusPage.getEventContextMenuOption("Freischalten für Live").click();
          waitForOk("@syncCalendarToStudent");

          tempusPage.getEventGridRow(updatedEventId).then((gridRowStyle) => {
            selectRoleAndWait(LEKTOR);
            tempusPage
              .getEventGridRow(updatedEventId)
              .then((lecturerGridRow) => {
                expect(lecturerGridRow).to.eq(gridRowStyle);
              });

            selectRoleAndWait(STUDENT);
            tempusPage.getEventGridRow(eventId).then((studentGridRow) => {
              expect(studentGridRow).to.not.eq(gridRowStyle);
            });
          });
        });
      });
  });

  it("event change on lector preview is prohibited", () => {
    syncAndReloadPlanner();

    selectRoleAndWait(LEKTOR);

    tempusPage
      .getCalendarEventsByTimeRange("08:00:00", "08:45:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        let eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;

        tempusPage.dropEventOnCalendarPart(eventId, 15);

        cy.get("@updateCalendarEvent").should("not.exist");

        tempusPage.getEventGridRow(eventId).then((gridRowStyle) => {
          selectRoleAndWait(LEKTOR);
          tempusPage.getEventGridRow(eventId).then((lecturerGridRow) => {
            expect(lecturerGridRow).to.eq(gridRowStyle);
          });

          selectRoleAndWait(STUDENT);
          tempusPage.getEventGridRow(eventId).then((studentGridRow) => {
            expect(studentGridRow).to.eq(gridRowStyle);
          });
        });
      });
  });

  it("event change on student preview is prohibited", () => {
    syncAndReloadPlanner();

    selectRoleAndWait(STUDENT);

    tempusPage
      .getCalendarEventsByTimeRange("08:00:00", "08:45:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        let eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;

        tempusPage.dropEventOnCalendarPart(eventId, 15);

        cy.get("@updateCalendarEvent").should("not.exist");

        tempusPage.getEventGridRow(eventId).then((gridRowStyle) => {
          selectRoleAndWait(LEKTOR);
          tempusPage.getEventGridRow(eventId).then((lecturerGridRow) => {
            expect(lecturerGridRow).to.eq(gridRowStyle);
          });

          selectRoleAndWait(STUDENT);
          tempusPage.getEventGridRow(eventId).then((studentGridRow) => {
            expect(studentGridRow).to.eq(gridRowStyle);
          });
        });
      });
  });

  it("event deletion on planner preview preservers event on planner, but shows it as unsynced on lektor and student preview", () => {
    clearMondayFirstColumnBeforeAndAfter();

    syncAndReloadPlanner();

    tempusPage
      .getCalendarEvents()
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        let eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;
        tempusPage
          .getCalendarEventById(eventId)
          .should("be.visible")
          .rightclick();
        tempusPage.getEventContextMenuOption("Delete").click();

        cy.wait("@deleteCalendarEvent");
        waitForOk("@fetchPlanData");

        tempusPage.waitForCalendarToFinishLoading();

        tempusPage.getCalendarEventById(eventId).should("be.visible");

        selectRoleAndWait(LEKTOR);
        tempusPage.getCalendarEventById(eventId).should("not.exist");

        selectRoleAndWait(STUDENT);
        tempusPage.getCalendarEventById(eventId).should("not.exist");
      });
  });

  it("syncing event deletion on planner preview removes event from other previews", () => {
    clearMondayFirstColumnBeforeAndAfter();

    syncAndReloadPlanner();

    tempusPage
      .getCalendarEvents()
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        let eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;
        tempusPage
          .getCalendarEventById(eventId)
          .should("be.visible")
          .rightclick();
        tempusPage.getEventContextMenuOption("Delete").click();

        cy.wait("@deleteCalendarEvent");
        waitForOk("@fetchPlanData");

        syncAndReloadPlanner();

        tempusPage.getCalendarEventById(eventId).should("not.exist");

        selectRoleAndWait(LEKTOR);
        tempusPage.getCalendarEventById(eventId).should("not.exist");

        selectRoleAndWait(STUDENT);
        tempusPage.getCalendarEventById(eventId).should("not.exist");
      });
  });

  it("can bottom resize an event on planner preview", () => {
    clearMondayFirstColumnBeforeAndAfter();

    tempusPage.getCalendarSection().should("exist");
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage
      .getCalendarEventsByStartTime("08:00:00")
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
        .realMouseMove(0, 120)
        .realMouseUp();

      cy.wait("@updateCalendarEvent").then((interception) => {
        expect(interception.response.statusCode).to.eq(200);

        const updatedEventId =
          interception.response.body.data.retval.kalender_id;

        cy.wrap(updatedEventId).as("updatedEventId");
      });
      waitForOk("@fetchPlanData");

      cy.get("@updatedEventId").then((updatedEventId) => {
        tempusPage
          .getCalendarEventById(updatedEventId)
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

  it("can top resize an event on planner preview", () => {
    clearMondayFirstColumnBeforeAndAfter();

    tempusPage.getCalendarSection().should("exist");
    tempusPage.waitForCalendarToFinishLoading();

    tempusPage
      .getCalendarEventsByStartTime("09:40:00")
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
        .realMouseMove(0, -40)
        .realMouseUp();

      cy.wait("@updateCalendarEvent").then((interception) => {
        expect(interception.response.statusCode).to.eq(200);

        const updatedEventId =
          interception.response.body.data.retval.kalender_id;

        cy.wrap(updatedEventId).as("updatedEventId");
      });
      waitForOk("@fetchPlanData");

      cy.get("@updatedEventId").then((updatedEventId) => {
        tempusPage
          .getCalendarEventById(updatedEventId)
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

  it("can drag and drop one course event into the calendar when Stundenraster is disabled", () => {
    clearMondayFirstColumnBeforeAndAfter();

    tempusPage.getCalendarSection().should("exist");
    tempusPage.waitForCalendarToFinishLoading();
    tempusPage.disableStundenraster();

    tempusPage.selectFirstCourse();
    waitForOk("@fetchCoursePickerCourses");

    tempusPage.getCalendarEvents().then(($events) => {
      cy.wrap($events.length).as("initialEventCount");
    });

    tempusPage.getCoursePickerRows().should("have.length.greaterThan", 0);
    tempusPage.dropCourseOnCalendarPart(0, 1);

    waitForOk("@addCalendarEvent");
    waitForOk("@fetchPlanData");

    tempusPage.waitForCalendarToFinishLoading();

    cy.get("@initialEventCount").then((initialEventCount) => {
      tempusPage
        .getCalendarEvents()
        .should("have.length", initialEventCount + 1);
    });
  });



  it("can drag and drop an event on part 6 when Stundenraster is disabled", () => {
    clearMondayFirstColumnBeforeAndAfter();

    syncAndReloadPlanner();
    tempusPage.disableStundenraster();

    tempusPage
      .getCalendarEventsByTimeRange("08:00:00", "08:45:00")
      .first()
      .invoke("attr", "data-fhc-draggable-value")
      .then((eventJSON) => {
        expect(eventJSON).to.exist;

        const eventId = JSON.parse(eventJSON)?.id;
        expect(eventId).to.exist;

        tempusPage.getEventGridRow(eventId).as("originalGridRow");
        tempusPage.dropEventOnCalendarPart(eventId, 6, {
          scrollIntoView: true,
        });

        cy.wait("@updateCalendarEvent").then((interception) => {
          expect(interception.response.statusCode).to.eq(200);

          const updatedEventId = getUpdatedKalenderId(interception);
          expect(updatedEventId, "updated planner event id").to.exist;

          cy.wrap(updatedEventId).as("updatedEventId");
        });
        waitForOk("@fetchPlanData");
        tempusPage.waitForCalendarToFinishLoading();

        cy.get("@updatedEventId").then((updatedEventId) => {
          tempusPage.getCalendarEventById(updatedEventId).should("be.visible");

          cy.get("@originalGridRow").then((originalGridRow) => {
            tempusPage.getEventGridRow(updatedEventId).then((updatedGridRow) => {
              expect(updatedGridRow).to.not.eq(originalGridRow);
            });
          });
        });
      });
  });
});
