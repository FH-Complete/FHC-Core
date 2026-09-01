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
				label: 'Lehreinheit bearbeiten',
				icon: 'fa-solid fa-calendar',
				action: handlers.openLehreinheit,
				visible: (orig) => (orig?.lehreinheit_id?.length ?? 0) > 1
			},
			{
				label: 'Ressourcen zuordnen',
				icon: 'fa-solid fa-table-list',
				action: handlers.openResourcesAssignmentModal
			},
			{
				label: 'Tags',
				icon: 'fa-solid fa-tags',
				action: handlers.openTagsModal
			},
			{
				label: 'Freischalten für Voransicht',
				icon: 'fa-solid fa-chalkboard-user',
				action: handlers.syncToLecturer,
				visible: (orig) => (orig?.status_kurzbz === 'planning')
			},
			{
				label: 'Freischalten für Live',
				icon: 'fa-solid fa-user-graduate',
				action: handlers.syncToStudent,
				visible: (orig) => (['planning', 'pre_preview', 'preview'].includes(orig?.status_kurzbz))
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
				label: 'Raumauswahl',
				icon: 'fa-solid fa-door-open',
				action: handlers.openRaumauswahl
			},
			{
				label: 'Delete',
				icon: 'fa-solid fa-calendar-xmark',
				action: handlers.deleteEntry
			},
		]
	}));
}