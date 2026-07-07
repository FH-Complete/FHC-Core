export function useContextMenuActions(handlers)
{
	return Vue.computed(() => ({
		lehreinheit: [
			{
				label: 'Raumauswahl',
				icon: 'fa-solid fa-door-open',
				action: handlers.openRaumauswahl
			},
			{
				label: 'Freischalten für Voransicht',
				icon: 'fa-solid fa-chalkboard-user',
				action: handlers.syncToLecturer
			},
			{
				label: 'Freischalten für Live',
				icon: 'fa-solid fa-user-graduate',
				action: handlers.syncToStudent
			},
			{
				label: 'History',
				icon: 'fa-solid fa-clock-rotate-left',
				action: handlers.openHistory
			},
			{
				label: 'Delete',
				icon: 'fa-solid fa-calendar-xmark',
				action: handlers.deleteEntry
			},
		],
		reservierung: [
			{
				label: 'Delete',
				icon: 'fa-solid fa-calendar-xmark',
				action: handlers.deleteEntry
			},
		]
	}));
}