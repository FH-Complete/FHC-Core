import CoodleApi from "../../../../../api/factory/coodle.js";

export default {
	name: "CoodleSurveyHeader",
	props: {
		survey: Object | null,
		isEditInProgress: Boolean,
		uid: String | null,
	},
	emits: ["editSurvey", "surveyCanceled"],
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
		async cancelSurvey() {
			if (!this.$props.survey?.id) return;

			const cancellationConfirmationMessage =
				'Are you sure you want to cancel Coodle survey "(((surveyTitle)))"?'.replace(
					"(((surveyTitle)))",
					this.$props.survey?.title,
				);
			const cancellationConfirmation = await this.$fhcAlert.confirm({
				header: "Cancellation confirmation",
				message: cancellationConfirmationMessage,
				acceptLabel: "Yes",
				rejectLabel: "No",
			});
			if (!cancellationConfirmation) return;

			const shouldInformParticipants = await this.$fhcAlert.confirm({
				header: "Inform participants",
				message:
					"Would you like to email all participants to inform them of the survey cancellation?",
				acceptLabel: "Yes",
				rejectLabel: "No",
			});

			const cancellationResponse = await this.$api.call(
				CoodleApi.cancelSurvey(
					this.$props.survey.id,
					shouldInformParticipants,
				),
			);
			if ((cancellationResponse.meta.status = "success")) {
				this.$emit("surveyCanceled");
			}
		},
		async sendReminders() {
			if (!this.$props.survey?.id) return;

			const numberOfParticipantsWithoutVote =
				this.$props.survey.participants.filter(
					(participant) => participant.selection === null,
				).length;

			if (!numberOfParticipantsWithoutVote) {
				this.$fhcAlert.alertError(
					"All participants have already voted!",
				);
				return;
			}

			const reminderConfirmation = await this.$fhcAlert.confirm({
				header: "Reminder confirmation",
				message:
					"Are you sure you want to remind participants to vote?",
				acceptLabel: "Yes",
				rejectLabel: "No",
			});
			if (!reminderConfirmation) return;

			const remindersResponse = this.$api.call(
				CoodleApi.sendReminders(this.$props.survey.id),
			);
			if (remindersResponse.meta.status === "success") {
				this.$fhcAlert.alertSuccess("Reminders successfully sent!");
			}
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
						@click="sendReminders()"
						role="button"
						class="dropdown-item px-3 py-1"
					>
						{{ "Send reminders" }}
					</div>
				</li>
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
