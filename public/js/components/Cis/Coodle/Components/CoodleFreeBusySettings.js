export default {
	name: "CoodleFreeBusySettings",
	data() {
		return {
			isGeneralScheduleShown: false,
			isPersonalScheduleShown: false,
			isWaitingOnResponse: false,
		};
	},
	methods: {
		fetchPrivacySettings() {},
		updatePrivacySettings() {},
	},
	template: /*html*/ `
	<div class="card" style="min-height:100%">
		<div class="card-header">
			<h4>{{ "FreeBusy settings" }}</h4>
		</div>
		<div class="card-body">
			<div class="gap-3">
				<div class="d-flex flex-column gap-2">
					<span >
						{{ "Here you can combine different external scheduling informations to create your personal FreeBusy URL, which is used by Coodle." }}
					</span>
					<span >
						{{ "FreeBusy supports effective scheduling by displaying your appointments (without any details such as titles or content) to avoid timing conflicts." }}
					</span>
					<span >
						{{ "To effectively use FreeBusy, you must carefully enter and update your calendar data." }}
					</span>
				</div>
			</div>
		</div>
	</div>
	`,
}