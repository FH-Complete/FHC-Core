import { getDateForDay } from '../../../../support/helpers/date';
import { waitForOk } from '../../../../support/helpers/network';
import { tempusPage } from '../../../../support/pages/tempus.po';
import { tempusApi } from '../../../../support/api/tempusApi';
import { getTempusTestSemester, TEMPUS_TEST_STUDY_PLAN_SHORT_CODE } from '../../../../support/helpers/tempusTestData';

let TEMPUS_TEST_SEMESTER;

context('Tempus smoke tests', () => {
	before(() => {
		cy.login();
		getTempusTestSemester().then((semester) => {
			TEMPUS_TEST_SEMESTER = semester;
		});
	});

	beforeEach(() => {
		tempusPage.visitAndWaitForPlanner();
		tempusApi.deletePlannerEventsForDay('monday');
	});

	it('displays the Tempus page for an authenticated user', () => {
		tempusPage.getTempusOverview().should('be.visible');
	});

	it('shows all expected page elements', () => {
		tempusPage.getTempusOverview().should('be.visible');
		tempusPage.getSidebarMenu().should('exist');
		tempusPage.getSlideInCoursesMenu().should('exist');
		tempusPage.getCalendarSection().should('be.visible');
		tempusPage.getPreviewRoleOptionsHolder().should('be.visible');
	}); 

	it('loads events in every calendar mode', () => {
		const monday = getDateForDay('monday');

		tempusApi
			.getDegreeProgramTree()
			.then((stgTree) => {
				const studyPlan = stgTree.find((plan) => plan.name.includes(TEMPUS_TEST_STUDY_PLAN_SHORT_CODE));
				expect(studyPlan, 'study plan for test event creation').to.exist;

				return tempusApi.getCoursesByStudyPlan(studyPlan.studiengang_kz, TEMPUS_TEST_SEMESTER);
			})
			.then((courses) => {
				const course = courses.find((c) => c.lehrfach === 'MAT');
				expect(course, 'course for test event creation').to.exist;

				return tempusApi.createKalenderEvent(course.lehreinheit_id, `${monday} 17:50`, `${monday} 18:35`);
			})
			.then((response) => {
				expect(response.status).to.eq(200);
				expect(response.body).to.have.nested.property('meta.status', 'success');
			});

		tempusPage
			.getCalendarModeButtons()
			.should('have.length.greaterThan', 0)
			.each(($modeButton) => {
				cy.wrap($modeButton).click().should('have.class', 'active');
				tempusPage.waitForCalendarToFinishLoading();

				if ($modeButton.find('.fa-table-list').length) {
					tempusPage.getCalendarTableRows().should('have.length.greaterThan', 0);
				} else {
					tempusPage.getCalendarEvents().should('have.length.greaterThan', 0);
				}
			});
	});
	it('opens the courses menu', () => {
		tempusPage.getSlideInCoursesMenu().should('exist').should('be.hidden');

		tempusPage.openCoursesMenu();

		tempusPage.getCourseTreeRows().should('have.length.greaterThan', 0);
	});

	it('shows event details modal when clicking a calendar event', () => {
		tempusPage.waitForCalendarToFinishLoading();
		tempusPage.getCalendarEvents().should('have.length.greaterThan', 0);

		tempusPage.getCalendarEvents().first().click();

		tempusPage.getCalendarEventModal().should('be.visible');
	});

	it('shows the event context menu when right-clicking a calendar event', () => {
		tempusPage.waitForCalendarToFinishLoading();
		tempusPage.getCalendarEvents().should('have.length.greaterThan', 0);

		tempusPage.getCalendarEvents().first().rightclick();

		tempusPage.getEventContextMenu().should('be.visible');
	});

	it('opens the room-selection modal from the event context menu', () => {
		tempusPage.waitForCalendarToFinishLoading();
		tempusPage.getCalendarEventsWithLehreinheit().should('have.length.greaterThan', 0);

		tempusPage.getCalendarEventsWithLehreinheit().first().rightclick();
		tempusPage.getEventContextMenuOption('Raumauswahl').click({ force: true });
		waitForOk('@fetchRoomSuggestions');

		tempusPage.getRoomSelectionModal().should('be.visible');
	});

	it('opens the resources modal from the event context menu', () => {
		tempusPage.waitForCalendarToFinishLoading();
		tempusPage.getCalendarEventsByWeekdayAndStartTime('Sunday', '17:50:00').should('have.length.greaterThan', 0);

		tempusPage.getCalendarEventsByWeekdayAndStartTime('Sunday', '17:50:00').first().rightclick();
		tempusPage.getEventContextMenuOption('Ressourcen zuordnen').click({ force: true });
		waitForOk('@fetchResourcesSuggestions');
		waitForOk('@fetchAssignedResources');

		tempusPage.getResourcesModal().should('be.visible');
	});

	//   it("shows tags modal when selecting Tags from event context menu", () => {
	//     tempusPage.waitForCalendarToFinishLoading();
	//     tempusPage
	//       .getCalendarEventsByWeekdayAndStartTime("Sunday", "16:55:00")
	//       .should("have.length.greaterThan", 0);

	//     tempusPage.getCalendarEventsByWeekdayAndStartTime("Sunday", "16:55:00").first().rightclick();
	//     tempusPage.getEventContextMenuOption("Tags").click({ force: true });
	//     waitForOk("@fetchTags");
	//     waitForOk("@fetchTagsByCalendar");

	//     tempusPage.getTagsModal().should("be.visible");
	//   });

	it('opens the history modal from the event context menu', () => {
		tempusPage.waitForCalendarToFinishLoading();
		tempusPage.getCalendarEvents().should('have.length.greaterThan', 0);

		tempusPage.getCalendarEvents().first().rightclick();
		tempusPage.getEventContextMenuOption('History').click({ force: true });
		waitForOk('@fetchEventHistory');

		tempusPage.getHistoryModal().should('be.visible');
	});

	it.skip('opens the reservation modal when a reservation is dropped on the calendar', () => {
		tempusPage.waitForCalendarToFinishLoading();
		tempusPage.getCalendarBaseGrid().should('be.visible');

		tempusPage.getReservationDragHandle().scrollIntoView().drag(tempusPage.selectors.calendarBaseGrid, {
			waitForAnimations: false,
			animationDistanceThreshold: 0,
		});

		tempusPage.getReservationModal().should('be.visible');
	});
});
