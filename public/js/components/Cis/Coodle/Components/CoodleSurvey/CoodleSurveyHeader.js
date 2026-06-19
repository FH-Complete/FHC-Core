export default {
	name: "CoodleSurveyHeader",
	props: {
		survey: Object | null,
		isEditInProgress: Boolean,
		uid: String | null,
	},
	emits: ["editSurvey"],
	computed: {
		headerTitle() {
			if (this.$props.survey?.id && !this.$props.isEditInProgress) {
				return this.$props.survey.title;
			} else if (this.$props.survey?.id && this.$props.isEditInProgress) {
				return "Edit survey";
			} else {
				return "New survey";
			}
		},
		isSurveyActive() {
			return (
				!this.$props.survey.completedAt &&
				!this.$props.survey.canceledAt
			);
		},
		isAuthUserSurveyCreator() {
			return (
				this.$props.uid &&
				this.$props.uid === this.$props.survey?.creator?.uid
			);
		},
	},
	methods: {
		cancelSurvey() {
			if (!this.$props.survey?.id) return;

			if (
				!window.confirm(
					'Are you sure you want to cancel Coodle survey "(((surveyTitle)))"?'.replace(
						"(((surveyTitle)))",
						this.$props.survey?.title,
					),
				)
			) {
				return;
			}
			console.log("canceling...");
			// todo
		},
	},
	template: /*html*/ `
	<div class="d-flex flex-row align-items-center justify-content-between">
		<h4 class="text-wrap m-0">{{ headerTitle }}</h4>
		<div
			v-if="isAuthUserSurveyCreator && !isEditInProgress && isSurveyActive"
			class="dropdown"
		>
			<div
				role="button"
				class="px-2"
				data-bs-toggle="dropdown"
				data-bs-auto-close="true"
				aria-expanded="false"
			>
				<i class="fa-solid fa-ellipsis-vertical fa-lg"></i>
			</div>
			<ul class="dropdown-menu dropdown-menu-end" style="min-width:0;'">
				<li>
					<div
						@click="$emit('editSurvey')"
						role="button"
						class="dropdown-item px-3 py-1"
					>
						{{ "Edit survey" }}
					</div>
				</li>
				<li>
					<div
						@click="cancelSurvey()"
						role="button"
						class="dropdown-item px-3 py-1"
					>
						{{ "Cancel survey" }}
					</div>
				</li>
			</ul>
		</div>
	</div>
	`,
};
