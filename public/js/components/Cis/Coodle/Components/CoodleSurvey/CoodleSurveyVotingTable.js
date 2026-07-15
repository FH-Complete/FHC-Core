import CoodleApi from "../../../../../api/factory/coodle.js";
import OrtApi from "../../../../../api/factory/ort.js";

export default {
	name: "CoodleSurveyVotingTable",
	props: {
		survey: { type: Object },
		authUid: { type: String | null },
		timeslots: { type: Array },
	},
	emits: ["selectionSubmitted", "surveyCompleted"],
	data() {
		return {
			isVotingInProgress: false,
			isFinalSelectionInProgress: false,
			participantsWithoutAuthUser: [],
			authUserParticipant: null,
			editableAuthUserSelection: null,
			selectedTimeslotId: null,
			isRoomSelectionShown: false,
			selectedRoomIdentifier: null,
			isFetchingAvailableRooms: false,
			availableRooms: [],
		};
	},
	computed: {
		isDarkMode() {
			return this.$theme.theme_name.value == "dark";
		},
		isAuthUserSurveyCreator() {
			return (
				this.$props.authUid &&
				this.$props.authUid === this.$props.survey?.creator?.uid
			);
		},
		hasReachedMaxSelections() {
			if (!this.authUserParticipant || !this.editableAuthUserSelection)
				return 0;

			return (
				Object.values(this.editableAuthUserSelection).filter(
					(isSelected) => isSelected,
				).length >= this.$props.survey.maxSelections
			);
		},
		isSurveyActive() {
			return (
				!this.$props.survey.completedAt &&
				!this.$props.survey.canceledAt
			);
		},
	},
	watch: {
		survey: {
			handler() {
				this.setData();
			},
			deep: true,
		},
		timeslots: {
			handler() {
				this.setData();
			},
			deep: true,
		},
		uid() {
			this.setData();
		},
		authUserParticipant: {
			handler() {
				this.setEditableAuthUserSelection();
			},
			deep: true,
		},
		editableAuthUserSelection: {
			handler() {
				if (this.editableAuthUserSelection.none) {
					Object.keys(this.editableAuthUserSelection).forEach(
						(timeslotId) => {
							if (timeslotId !== "none") {
								this.editableAuthUserSelection[timeslotId] =
									false;
							}
						},
					);
				}
			},
			deep: true,
		},
		isVotingInProgress() {
			this.setEditableAuthUserSelection();
		},
		isFinalSelectionInProgress() {
			this.setSelectedTimeslotId();
		},
	},
	methods: {
		parseParticipants() {
			const participants = this.$props.survey.participants.map(
				(participant) => {
					participant = { ...participant };
					const hasVotedWithoutSelection =
						participant.selection &&
						participant.selection.length === 0;

					let selectionEntries = this.$props.timeslots.map(
						(timeslot) => [
							timeslot.id,
							participant.selection?.includes(timeslot.id),
						],
					);
					selectionEntries.push(["none", hasVotedWithoutSelection]);
					participant.selection =
						Object.fromEntries(selectionEntries);

					return participant;
				},
			);

			this.authUserParticipant = participants.filter(
				(participant) =>
					this.$props.authUid &&
					participant.uid === this.$props.authUid,
			)[0];

			this.participantsWithoutAuthUser = participants.filter(
				(participant) =>
					!this.$props.authUid ||
					participant.uid !== this.$props.authUid,
			);
		},
		setData() {
			this.setForms();
			this.setSelectedTimeslotId();
			this.parseParticipants();
		},
		setForms() {
			this.isVotingInProgress = false;
			this.isFinalSelectionInProgress = false;
			this.isRoomSelectionShown = false;
			this.selectedRoomIdentifier = null;
		},
		setEditableAuthUserSelection() {
			this.editableAuthUserSelection = this.authUserParticipant?.selection
				? { ...this.authUserParticipant.selection }
				: null;
		},
		setSelectedTimeslotId() {
			if (!this.$props.survey.completedAt) {
				this.selectedTimeslotId = null;
			} else if (this.$props.survey.selectedTimeslotId) {
				this.selectedTimeslotId = this.$props.survey.selectedTimeslotId;
			} else {
				this.selectedTimeslotId = "none";
			}
		},
		async submitParticipantSelection() {
			const selectedTimeslots = Object.entries(
				this.editableAuthUserSelection,
			)
				.filter((timeslotSelectionPair) => timeslotSelectionPair[1])
				.map((timeslotSelectionPair) => timeslotSelectionPair[0]);

			let selection;
			if (!selectedTimeslots.length) {
				selection = null;
			} else if (selectedTimeslots.includes("none")) {
				selection = [];
			} else {
				selection = selectedTimeslots.map(Number);
			}

			const selectionSubmissionResponse = await this.$api.call(
				CoodleApi.submitParticipantSelection(this.survey.id, selection),
			);

			if (selectionSubmissionResponse.meta.status === "success") {
				this.$emit("selectionSubmitted");
			}
		},
		async prepareSubmission() {
			if (!this.$props.survey.id) return;

			if (!this.selectedTimeslotId) {
				this.$fhcAlert.alertError("You haven't made a selection!");
				return;
			}

			let formattedSelectedTimeslot = "";
			let selectedTimeslot = null;

			if (this.selectedTimeslotId === "none") {
				formattedSelectedTimeslot = '"No appointment is possible"';
			} else {
				selectedTimeslot = this.$props.timeslots.find(
					(timeslot) => timeslot.id === this.selectedTimeslotId,
				);
				if (!selectedTimeslot) return;
				formattedSelectedTimeslot =
					selectedTimeslot.weekday +
					" " +
					selectedTimeslot.fullDate +
					" " +
					selectedTimeslot.startTime +
					"-" +
					selectedTimeslot.endTime;
			}

			const finalSelectionConfirmationMessage =
				'Are you sure you want to select option (((timeslot))) and finalize Coodle survey "(((surveyTitle)))"?'
					.replace("(((timeslot)))", formattedSelectedTimeslot)
					.replace("(((surveyTitle)))", this.$props.survey?.title);
			const finalSelectionConfirmation = await this.$fhcAlert.confirm({
				header: "Final confirmation",
				message: finalSelectionConfirmationMessage,
				acceptLabel: "Yes",
				rejectLabel: "Cancel",
			});
			if (!finalSelectionConfirmation) return;

			let participantsThatHaveNotVoted =
				this.participantsWithoutAuthUser.reduce((sum, participant) => {
					return (
						sum +
						(!Object.values(participant.selection).filter(
							(isSelected) => isSelected,
						).length
							? 1
							: 0)
					);
				}, 0);

			if (
				this.authUserParticipant &&
				!Object.values(this.authUserParticipant.selection).filter(
					(isSelected) => isSelected,
				).length
			) {
				participantsThatHaveNotVoted++;
			}

			if (participantsThatHaveNotVoted) {
				const shouldProceedWithoutAllVotes =
					await this.$fhcAlert.confirm({
						header: "Warning!",
						message:
							"Not all participants have voted. Are you sure you want to proceed?",
						acceptLabel: "Yes",
						rejectLabel: "Cancel",
					});
				if (!shouldProceedWithoutAllVotes) return;
			}

			const now = new Date();
			const isFutureTimeslot = selectedTimeslot && selectedTimeslot.startsAt > now;
			if (selectedTimeslot && isFutureTimeslot) {
				const shouldReserveARoom = await this.$fhcAlert.confirm({
					header: "Warning!",
					message:
						"Would you like to reserve a room for the selected timeslot?",
					acceptLabel: "Yes",
					rejectLabel: "No",
				});
				if (shouldReserveARoom) {
					this.isRoomSelectionShown = true;
					this.fetchAvailableRooms();
					return;
				}
			}

			this.submitFinalSelection();
		},
		submitWithRoomSelection() {
			if (this.availableRooms.length && !this.selectedRoomIdentifier) {
				this.$fhcAlert.alertError("You haven't selected a room!");
				return;
			}
			this.submitFinalSelection();
		},
		async submitFinalSelection() {
			let shouldInformParticipants = false;
			const selectedTimeslotId =
				this.selectedTimeslotId === "none"
					? null
					: this.selectedTimeslotId;

			if (!selectedTimeslotId) {
				shouldInformParticipants = await this.$fhcAlert.confirm({
					header: "Inform participants",
					message:
						"Would you like to email all participants to inform them of the survey result?",
					acceptLabel: "Yes",
					rejectLabel: "No",
				});
			} else {
				const selectedTimeslot = this.$props.timeslots.find(
					(timeslot) => timeslot.id === selectedTimeslotId,
				);
				const now = new Date();
				const isFutureTimeslot = selectedTimeslot.startsAt > now;
				if (isFutureTimeslot) {
					shouldInformParticipants = await this.$fhcAlert.confirm({
						header: "Inform participants",
						message:
							"Would you like to email all participants with a calendar invite?",
						acceptLabel: "Yes",
						rejectLabel: "No",
					});
				}
			}

			const completionResponse = await this.$api.call(
				CoodleApi.completeSurvey(
					this.$props.survey.id,
					selectedTimeslotId,
					this.selectedRoomIdentifier,
					shouldInformParticipants,
				),
			);
			if (completionResponse.meta.status === "success") {
				this.$emit("surveyCompleted");
			}
		},
		async fetchAvailableRooms() {
			this.availableRooms = [];

			const selectedTimeslotInfo = this.$props.timeslots.find(
				(timeslot) => timeslot.id === this.selectedTimeslotId,
			);

			this.isFetchingAvailableRooms = true;
			const roomsResponse = await this.$api.call(
				OrtApi.getRooms(
					selectedTimeslotInfo.startsAt.toISOString(),
					selectedTimeslotInfo.startTime,
					selectedTimeslotInfo.endTime,
					"",
					this.$props.survey.participants.length,
				),
			);
			this.isFetchingAvailableRooms = false;

			this.availableRooms = roomsResponse.data.retval
				.filter((room) => room.reservieren)
				.map((room) => {
					return {
						id: room.content_id,
						shortName: room.ort_kurzbz,
						longName: room.bezeichnung,
					};
				});
		},
		cancelRoomSelection() {
			this.isRoomSelectionShown = false;
			this.selectedRoomIdentifier = null;
			this.availableRooms = [];
			this.isFetchingAvailableRooms = false;
		},
		getParticipantProfileHref(participant) {
			if (!participant?.uid) {
				return "";
			}

			return this.$router.resolve({
				name: "ProfilView",
				params: { uid: participant.uid },
			}).href;
		},
		getRoomInfoHref(roomId) {
			return (
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				"/CisVue/Cms/content/" +
				roomId
			);
		},
	},
	created() {
		this.setData();
		this.setEditableAuthUserSelection();
	},
	template: /*html*/ `
	<div>
		<span v-if="!isSurveyActive" class="fst-italic">
			{{ "Voting is closed for this survey." }}
		</span>
		<span v-else-if="authUserParticipant" :class="{'opacity-0': !isVotingInProgress}" class="fst-italic">
			{{
				$props.survey?.maxSelections === 1 ?
				"You can only select one option" :
				"You can select up to (((n))) options.".replace("(((n)))", $props.survey?.maxSelections)
			}}
		</span>
		<div class="d-flex flex-row">
			<div class="d-flex flex-column flex-shrink-1" style="max-width:100%;">
				<div class="overflow-x-auto">
					<table>
						<tr>
							<td rowspan="2" class="border-1"></td>
							<td v-for="timeslot in $props.timeslots" class="border-1">
								<div class="px-1 d-flex flex-column align-items-center">
									<span>{{ timeslot.month.slice(0,3) }}</span>
									<span class="fs-5 fw-bold">{{ timeslot.date }}</span>
									<span>{{ timeslot.weekday.slice(0,3) }}</span>
								</div>
							</td>
							<td rowspan="2" class="border-1">
								<div class="px-2 py-1">
									{{ "No appointment is possible" }}
								</div>
							</td>
						</tr>
						<tr>
							<td v-for="timeslot in $props.timeslots" class="border-1">
								<div class="px-2 d-flex flex-column align-items-start text-nowrap">
									<span>{{ timeslot.startTime + " -" }}</span>
									<span>{{ timeslot.endTime }}</span>
								</div>
							</td>
						</tr>
						<tr v-for="participant in participantsWithoutAuthUser">
							<td class="border-1 px-2 py-1">
								<div
									v-if="participant.uid"
									class="d-flex flex-row gap-1 justify-content-between align-items-center"
								>
									{{ participant.name }}
									<a :href="getParticipantProfileHref(participant)" target="_blank" class="px-1 fhc-primary-color">
										<i class="fa-solid fa-up-right-from-square"></i>
									</a>
								</div>
								<div v-else class="text-center">---</div>
							</td>
							<td v-for="timeslot in $props.timeslots" class="border-1">
								<div class="d-flex justify-content-center">
									<i v-if="participant.selection[timeslot.id]" class="fa-solid fa-check"></i>
								</div>
							</td>
							<td class="border-1">
								<div class="d-flex justify-content-center">
									<i v-if="participant.selection.none" class="fa-solid fa-check"></i>
								</div>
							</td>
						</tr>
						<tr
							v-if="authUserParticipant"
							:class="{'fhc-body-bg': isVotingInProgress}"
						>
							<td class="border-1 px-2 py-1">{{ authUserParticipant.name }}</td>
							<td v-for="timeslot in $props.timeslots" class="border-1">
								<div class="d-flex justify-content-center py-3">
									<input
										v-if="isSurveyActive"
										v-model="editableAuthUserSelection[timeslot.id]"
										:disabled="
											!isVotingInProgress ||
											(!editableAuthUserSelection[timeslot.id] && hasReachedMaxSelections) ||
											editableAuthUserSelection.none
										"
										:role="!isVotingInProgress ||
											(!editableAuthUserSelection[timeslot.id] && hasReachedMaxSelections) ||
											editableAuthUserSelection.none ? '' : 'button'"
										type="checkbox"
										style="width: 20px; height: 20px;"
									/>
									<i v-else-if="authUserParticipant.selection[timeslot.id]" class="fa-solid fa-check"></i>
								</div>
							</td>
							<td class="border-1">
								<div class="d-flex justify-content-center py-3">
									<input
										v-if="isSurveyActive"
										v-model="editableAuthUserSelection.none"
										:disabled="!isVotingInProgress"
										:role="isVotingInProgress ? 'button' : ''"
										type="checkbox"
										style="width: 20px; height: 20px;"
									/>
									<i v-else-if="authUserParticipant.selection.none" class="fa-solid fa-check"></i>
								</div>
							</td>
						</tr>
						<tr v-if="$props.survey.voteTallies" class="fw-bold">
							<td class="border-1 px-2 py-1">{{ "Vote tally" }}</td>
							<td v-for="timeslot in $props.timeslots" class="border-1">
								<div class="d-flex flex-row justify-content-center">
									{{ $props.survey.voteTallies[timeslot.id] }}
								</div>
							</td>
							<td class="border-1">
								<div class="d-flex flex-row justify-content-center">
									{{ $props.survey.voteTallies.none }}
								</div>
							</td>
						</tr>
						<tr
							v-if="isAuthUserSurveyCreator || $props.survey.completedAt"
							:class="{'fhc-body-bg': isFinalSelectionInProgress}"
							class="fw-bold"
						>
							<td class="border-1 px-2 py-1">{{ "Final selected appointment" }}</td>
							<td v-for="timeslot in $props.timeslots" class="border-1">
								<div class="d-flex flex-row justify-content-center py-3">
									<input
										v-if="isSurveyActive"
										v-model="selectedTimeslotId"
										:value="timeslot.id"
										:disabled="!isFinalSelectionInProgress || isRoomSelectionShown"
										:role="isFinalSelectionInProgress ? 'button' : ''"
										type="radio"
										style="width: 20px; height: 20px;"
									/>
									<i v-else-if="selectedTimeslotId === timeslot.id" class="fa-solid fa-check"></i>
								</div>
							</td>
							<td class="border-1">
								<div class="d-flex flex-row justify-content-center py-3">
									<input
										v-if="isSurveyActive"
										v-model="selectedTimeslotId"
										:value="'none'"
										:disabled="!isFinalSelectionInProgress || isRoomSelectionShown"
										:role="isFinalSelectionInProgress ? 'button' : ''"
										type="radio"
										style="width: 20px; height: 20px;"
									/>
									<i v-else-if="selectedTimeslotId === 'none'" class="fa-solid fa-check"></i>
								</div>
							</td>
						</tr>
					</table>
				</div>
				<div v-if="isRoomSelectionShown" class="mt-3 overflow-y-auto" style="max-height: 200px;">
					<div v-if="isFetchingAvailableRooms" class="d-flex justify-content-center align-items-center py-3">
						<div class="spinner-border" role="status">
							<span class="visually-hidden">Loading...</span>
						</div>
					</div>
					<div v-else-if="!availableRooms.length" class="d-flex justify-content-center align-items-center py-3 px-1">
						<h5 class="flex-shrink fw-bold text-wrap">
							{{ "No rooms available!" }}
						</h5>
					</div>
					<div v-else class="d-flex flex-column gap-2">
						<span class="fw-bold">{{ "Available rooms:" }}</span>
						<div class="d-flex flex-row flex-wrap gap-2 flex-shrink-1">
							<div
								v-for="room in availableRooms"
								@click="selectedRoomIdentifier = room.shortName"
								:key="room.shortName"
								:title="room.longName"
								type="button"
								:class="{'fhc-primary-border-color fw-bold': selectedRoomIdentifier === room.shortName}"
								class="border border-2 rounded-pill py-1 px-2 d-flex flex-row align-items-center gap-1"
							>
								<span>{{ room.shortName }}</span>
								<a v-if="room.id" :href="getRoomInfoHref(room.id)" target="_blank" class="px-1 fhc-primary-color">
									<i class="fa-solid fa-up-right-from-square"></i>
								</a>
							</div>
					</div>
					</div>
				</div>
				<div v-if="isSurveyActive" class="d-flex flex-row gap-2 mt-3 justify-content-end">
					<div
						v-if="authUserParticipant && !isVotingInProgress && !isFinalSelectionInProgress"
						@click="isVotingInProgress = true"
						:class="isDarkMode ? 'btn-outline-light' : 'btn-outline-dark'"
						class="btn text-nowrap"
					>
						{{ "Vote" }}
					</div>
					<div
						v-if="isAuthUserSurveyCreator && !isVotingInProgress && !isFinalSelectionInProgress"
						@click="isFinalSelectionInProgress = true"
						:class="isDarkMode ? 'btn-outline-light' : 'btn-outline-dark'"
						class="btn text-nowrap"
					>
						{{ "Finalize survey" }}
					</div>
					<div
						v-if="isVotingInProgress || isFinalSelectionInProgress"
						@click="setForms()"
						:class="isDarkMode ? 'btn-outline-light' : 'btn-outline-dark'"
						class="btn text-nowrap"
					>
						{{ "Cancel" }}
					</div>
					<div
						v-if="isVotingInProgress"
						@click="submitParticipantSelection()"
						:class="isDarkMode ? 'btn-light' : 'btn-dark'"
						class="btn text-nowrap"
					>
						{{ "Submit vote" }}
					</div>
					<div
						v-if="isFinalSelectionInProgress"
						@click="isRoomSelectionShown ? submitWithRoomSelection() : prepareSubmission()"
						:class="isDarkMode ? 'btn-light' : 'btn-dark'"
						class="btn text-nowrap"
					>
						{{ "Submit final selection" }}
					</div>
				</div>
			</div>
		</div>
	</div>
	`,
};
