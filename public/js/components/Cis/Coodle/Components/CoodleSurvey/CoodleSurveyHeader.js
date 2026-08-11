import CoodleApi from "../../../../../api/factory/coodle.js";

export default {
	name: "CoodleSurveyHeader",
	props: {
		survey: Object | null,
		isEditInProgress: Boolean,
		authUid: String | null,
	},
	emits: ["editSurvey", "surveyCanceled"],
	computed: {
		headerTitle() {
			if (this.$props.survey?.id && !this.$props.isEditInProgress) {
				return this.$props.survey.title;
			} else if (this.$props.survey?.id && this.$props.isEditInProgress) {
				return this.$p.t("coodle/edit_survey");
			} else {
				return this.$p.t("coodle/new_survey");
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
				this.$props.authUid &&
				this.$props.authUid === this.$props.survey?.creator?.uid
			);
		},
	},
	methods: {
		async cancelSurvey() {
			if (!this.$props.survey?.id) return;

			const cancellationConfirmationMessage = this.$p
				.t("coodle/cancel_survey_confirmation_message")
				.replace("(((surveyTitle)))", this.$props.survey?.title);
			const cancellationConfirmation = await this.$fhcAlert.confirm({
				header: this.$p.t("coodle/cancellation_confirmation"),
				message: cancellationConfirmationMessage,
				acceptLabel: this.$p.t("coodle/yes"),
				rejectLabel: this.$p.t("coodle/no"),
			});
			if (!cancellationConfirmation) return;

			const shouldInformParticipants = await this.$fhcAlert.confirm({
				header: this.$p.t("coodle/inform_participants"),
				message: this.$p.t("coodle/email_participants_canceled"),
				acceptLabel: this.$p.t("coodle/yes"),
				rejectLabel: this.$p.t("coodle/no"),
			});

			const cancellationResponse = await this.$api.call(
				CoodleApi.cancelSurvey(
					this.$props.survey.id,
				),
			);
			if ((cancellationResponse.meta.status = "success")) {
				this.$emit("surveyCanceled", { shouldInformParticipants });
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
					this.$p.t("coodle/all_participants_voted"),
				);
				return;
			}

			const reminderConfirmation = await this.$fhcAlert.confirm({
				header: this.$p.t("coodle/reminder_confirmation"),
				message: this.$p.t("coodle/email_participants_reminder"),
				acceptLabel: this.$p.t("coodle/yes"),
				rejectLabel: this.$p.t("coodle/no"),
			});
			if (!reminderConfirmation) return;

			const remindersResponse = await this.$api.call(
				CoodleApi.sendReminders(this.$props.survey.id),
			);
			if (remindersResponse.meta.status === "success") {
				this.$fhcAlert.alertSuccess(
					this.$p.t("coodle/reminders_success"),
				);
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
						{{ $p.t("coodle/send_reminders") }}
					</div>
				</li>
				<li>
					<div
						@click="$emit('editSurvey')"
						role="button"
						class="dropdown-item px-3 py-1"
					>
						{{ $p.t("coodle/edit_survey") }}
					</div>
				</li>
				<li>
					<div
						@click="cancelSurvey()"
						role="button"
						class="dropdown-item px-3 py-1"
					>
						{{ $p.t("coodle/cancel_survey") }}
					</div>
				</li>
			</ul>
		</div>
	</div>
	`,
};
