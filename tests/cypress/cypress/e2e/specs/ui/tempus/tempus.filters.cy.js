import { waitForOk } from "../../../../support/helpers/network";
import { tempusPage } from "../../../../support/pages/tempus.po";

context("Tempus filter tests", () => {
  beforeEach(() => {
    tempusPage.visitAndWaitForPlanner();
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
          const eventData = tempusPage.getCalendarEventData($event);

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

        const selectedLecturer = tempusPage.getFirstLecturer(JSON.parse(eventJSON));
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
              const eventData = tempusPage.getCalendarEventData($event);
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

        const selectedLecturer = tempusPage.getFirstLecturer(JSON.parse(eventJSON));
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

        const selectedLecturer = tempusPage.getFirstLecturer(JSON.parse(eventJSON));
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

        const selectedLecturer = tempusPage.getFirstLecturer(JSON.parse(eventJSON));
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
});
