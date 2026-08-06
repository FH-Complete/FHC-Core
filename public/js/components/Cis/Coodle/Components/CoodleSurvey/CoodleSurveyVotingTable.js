import CoodleApi from "../../../../../api/factory/coodle.js";
import OrtApi from "../../../../../api/factory/ort.js";

export default {
	name: "CoodleSurveyVotingTable",
	props: {
		survey: { type: Object },
		authUid: { type: String | null },
		authExternalParticipantId: { type: Number | null },
		timeslots: { type: Array },
	},
	emits: ["selectionSubmitted", "surveyCompleted"],
	data() {
		return {
			isVotingInProgress: false,
			isFinalSelectionInProgress: false,
			participantsWithoutAuthUser: [],
			externalParticipantsWithoutAuthUser: [],
			authUserParticipant: null,
			editableAuthUserSelection: null,
			selectedTimeslotId: null,
			isRoomSelectionShown: false,
			selectedRoomIdentifier: null,
			isFetchingAvailableRooms: false,
			availableRooms: [],
			roomFilterText: "",
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
				return false;

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
		filteredAvailableRooms() {
			if (!this.roomFilterText?.length) {
				return this.availableRooms;
			}

			const roomFilterText = this.roomFilterText.toLowerCase();
			return this.availableRooms.filter(
				(room) =>
					room.shortName.toLowerCase().includes(roomFilterText) ||
					room.longName.toLowerCase().includes(roomFilterText),
			);
		},
		timeslotDates() {
			if (!this.$props.timeslots.length) return [];

			let groupedTimeslots = Object.groupBy(
				this.$props.timeslots,
				(timeslot) => {
					return timeslot.startsAt.toISOString().slice(0, 10);
				},
			);
			return Object.keys(groupedTimeslots)
				.sort()
				.map((dateKey) => {
					let timeslotDate = groupedTimeslots[dateKey];
					return {
						count: timeslotDate.length,
						date: timeslotDate[0].date,
						weekday: timeslotDate[0].weekday,
						month: timeslotDate[0].month,
					};
				});
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
		authUid() {
			this.setData();
		},
		authExternalParticipantId() {
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

			if (this.$props.authUid) {
				this.authUserParticipant =
					participants.find(
						(participant) =>
							participant.uid === this.$props.authUid,
					) ?? null;
			}

			this.participantsWithoutAuthUser = this.$props.authUid
				? participants.filter(
						(participant) =>
							participant.uid !== this.$props.authUid,
					)
				: participants;
		},
		parseExternalParticipants() {
			const externalParticipants =
				this.$props.survey.externalParticipants.map(
					(externalParticipant) => {
						externalParticipant = { ...externalParticipant };
						const hasVotedWithoutSelection =
							externalParticipant.selection &&
							externalParticipant.selection.length === 0;

						let selectionEntries = this.$props.timeslots.map(
							(timeslot) => [
								timeslot.id,
								externalParticipant.selection?.includes(
									timeslot.id,
								),
							],
						);
						selectionEntries.push([
							"none",
							hasVotedWithoutSelection,
						]);
						externalParticipant.selection =
							Object.fromEntries(selectionEntries);

						return externalParticipant;
					},
				);

			if (this.$props.authExternalParticipantId) {
				this.authUserParticipant = externalParticipants.find(
					(externalParticipant) =>
						externalParticipant.id ===
						this.$props.authExternalParticipantId,
				);
			}

			this.externalParticipantsWithoutAuthUser = this.$props
				.authExternalParticipantId
				? externalParticipants.filter(
						(externalParticipant) =>
							externalParticipant.id !==
							this.$props.authExternalParticipantId,
					)
				: externalParticipants;
		},
		setData() {
			this.setForms();
			this.setSelectedTimeslotId();
			this.parseParticipants();
			this.parseExternalParticipants();
		},
		setForms() {
			this.isVotingInProgress = false;
			this.isFinalSelectionInProgress = false;
			this.isRoomSelectionShown = false;
			this.selectedRoomIdentifier = null;
			this.roomFilterText = "";
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

			let selectionSubmissionResponse;
			if (this.$props.authExternalParticipantId) {
				selectionSubmissionResponse = await this.$api.call(
					CoodleApi.submitExternalParticipantSelection(
						this.$route.params.surveyId,
						this.$route.params.accessKey,
						selection,
					),
				);
			} else {
				selectionSubmissionResponse = await this.$api.call(
					CoodleApi.submitParticipantSelection(
						this.survey.id,
						selection,
					),
				);
			}

			if (selectionSubmissionResponse.meta.status === "success") {
				this.$emit("selectionSubmitted");
			}
		},
		async prepareFinalSelectionSubmission() {
			if (!this.$props.survey.id) return;

			if (!this.selectedTimeslotId) {
				this.$fhcAlert.alertError(
					this.$p.t("coodle/no_selection_made"),
				);
				return;
			}

			let formattedSelectedTimeslot = "";
			let selectedTimeslot = null;

			if (this.selectedTimeslotId === "none") {
				formattedSelectedTimeslot =
					'"' + this.$p.t("coodle/no_timeslot_possible") + '"';
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

			const finalSelectionConfirmationMessage = this.$p
				.t("coodle/survey_completion_confirmation")
				.replace("(((timeslot)))", formattedSelectedTimeslot)
				.replace("(((surveyTitle)))", this.$props.survey?.title);
			const finalSelectionConfirmation = await this.$fhcAlert.confirm({
				header: "Final confirmation",
				message: finalSelectionConfirmationMessage,
				acceptLabel: this.$p.t("coodle/yes"),
				rejectLabel: this.$p.t("ui/abbrechen"),
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
						header: this.$p.t("coodle/warning"),
						message: this.$p.t("coodle/continue_without_all_votes"),
						acceptLabel: this.$p.t("coodle/yes"),
						rejectLabel: this.$p.t("ui/abbrechen"),
					});
				if (!shouldProceedWithoutAllVotes) return;
			}

			const now = new Date();
			const isFutureTimeslot =
				selectedTimeslot && selectedTimeslot.startsAt > now;
			if (selectedTimeslot && isFutureTimeslot) {
				const shouldReserveARoom = await this.$fhcAlert.confirm({
					header: this.$p.t("coodle/room_reservation"),
					message: this.$p.t("coodle/reserve_room_confirmation"),
					acceptLabel: this.$p.t("coodle/yes"),
					rejectLabel: this.$p.t("coodle/no"),
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
				this.$fhcAlert.alertError(
					this.$p.t("coodle/no_room_selection"),
				);
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
					header: this.$p.t("coodle/inform_participants"),
					message: this.$p.t("coodle/email_participants_result"),
					acceptLabel: this.$p.t("coodle/yes"),
					rejectLabel: this.$p.t("coodle/no"),
				});
			} else {
				const selectedTimeslot = this.$props.timeslots.find(
					(timeslot) => timeslot.id === selectedTimeslotId,
				);
				const now = new Date();
				const isFutureTimeslot = selectedTimeslot.startsAt > now;
				if (isFutureTimeslot) {
					shouldInformParticipants = await this.$fhcAlert.confirm({
						header: this.$p.t("coodle/inform_participants"),
						message: this.$p.t(
							"coodle/email_participants_calendar_invite",
						),
						acceptLabel: this.$p.t("coodle/yes"),
						rejectLabel: this.$p.t("coodle/no"),
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
			this.roomFilterText = "";
		},
		showParticipantProfile(participant) {
			if (!participant?.uid) {
				return;
			}

			const participantHref = this.$router.resolve({
				name: "ProfilView",
				params: { uid: participant.uid },
			}).href;

			window.open(participantHref, "_blank");
		},
		showRoomDetails(room) {
			if (!room.id) {
				return;
			}

			const roomHref =
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				"/CisVue/Cms/content/" +
				room.id;

			window.open(roomHref, "_blank");
		},
	},
	created() {
		this.setData();
		this.setEditableAuthUserSelection();
	},
	template: /*html*/ `
	<div>
		<span v-if="!isSurveyActive" class="fst-italic">
			{{ $p.t("coodle/voting_closed") }}
		</span>
		<span v-else-if="authUserParticipant" :class="{'opacity-0': !isVotingInProgress}" class="fst-italic">
			{{
				$props.survey?.maxSelections === 1 ?
				$p.t("coodle/max_one_selectable_timeslot") :
				$p.t("coodle/up_to_max_selectable_timeslots").replace("(((n)))", $props.survey?.maxSelections)
			}}
		</span>
		<div class="d-flex flex-row">
			<div class="d-flex flex-column flex-shrink-1" style="max-width:100%;">
				<div class="overflow-x-auto">
					<table>
						<tr>
							<td rowspan="2" class="border-1"></td>
							<td v-for="timeslotDate in timeslotDates" :colspan="timeslotDate.count" class="border-1">
								<div class="px-1 d-flex flex-column align-items-center">
									<span>{{ timeslotDate.month.slice(0,3) }}</span>
									<span class="fs-5 fw-bold">{{ timeslotDate.date }}</span>
									<span>{{ timeslotDate.weekday.slice(0,3) }}</span>
								</div>
							</td>
							<td rowspan="2" class="border-1">
								<div class="px-2 py-1 text-center">
									{{ $p.t("coodle/no_timeslot_possible") }}
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
									v-if="participant.name"
									class="d-flex flex-row gap-1 justify-content-between align-items-center"
								>
									<span>
										{{ participant.name }}
									</span>	
									<span
										v-if="participant.uid"
										@click="showParticipantProfile(participant)"
										type="button"
										class="px-1 fhc-primary-color"
									>
										<i class="fa-solid fa-up-right-from-square"></i>
									</span>
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
						<tr v-for="externalParticipant in externalParticipantsWithoutAuthUser">
							<td class="border-1 px-2 py-1">
								<div
									v-if="externalParticipant.name?.length"
									class="d-flex flex-row gap-1 justify-content-between align-items-center"
								>
									{{ externalParticipant.name }}
								</div>
								<div v-else class="text-center">---</div>
							</td>
							<td v-for="timeslot in $props.timeslots" class="border-1">
								<div class="d-flex justify-content-center">
									<i v-if="externalParticipant.selection[timeslot.id]" class="fa-solid fa-check"></i>
								</div>
							</td>
							<td class="border-1">
								<div class="d-flex justify-content-center">
									<i v-if="externalParticipant.selection.none" class="fa-solid fa-check"></i>
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
							<td class="border-1 px-2 py-1">{{ $p.t("coodle/vote_tally") }}</td>
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
							<td class="border-1 px-2 py-1">{{ $p.t("coodle/final_selection") }}</td>
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
				<div v-if="isRoomSelectionShown" class="mt-3">
					<div v-if="isFetchingAvailableRooms" class="d-flex justify-content-center align-items-center py-3">
						<div class="spinner-border" role="status">
							<span class="visually-hidden">{{ $p.t("coodle/loading") }}</span>
						</div>
					</div>
					<div v-else-if="!availableRooms.length" class="d-flex justify-content-center align-items-center py-4 px-1">
						<h5 class="flex-shrink fw-bold text-wrap m-0">
							{{ $p.t("coodle/no_rooms_available") }}
						</h5>
					</div>
					<div v-else class="d-flex flex-column gap-2">
						<div class="d-flex flex-row gap-1">
							<span class="fw-bold">{{ "Selected room: " }}</span>
							<span v-if="selectedRoomIdentifier?.length">{{ selectedRoomIdentifier }}</span>
						</div>
						<div class="d-flex flex-column gap-1 justify-content-start">
							<span class="fw-bold">{{ $p.t("coodle/available_rooms") + ":" }}</span>
							<div class="d-flex flex-row align-items-center gap-2">
								<input v-model="roomFilterText" :placeholder="'Filter rooms...'" id="roomFilterInput" />
								<div
									v-if="roomFilterText?.length"
									@click="roomFilterText = ''"
									type="button"
									class="px-1"
								>
									<i class="fa-solid fa-xmark fa-lg"></i>
								</div>
							</div>
						</div>
						<div v-if="!filteredAvailableRooms.length" class="d-flex flex-row align-items-center justify-content-center py-4 px-1">
							<h5 class="flex-shrink fw-bold text-wrap m-0">
								{{ $p.t("coodle/no_available_rooms_for_filter") }}
							</h5>
						</div>
						<div v-else class="d-flex flex-column overflow-y-auto" style="max-height:200px;">
							<div
								v-for="room in filteredAvailableRooms"
								@click="selectedRoomIdentifier = room.shortName"
								:key="room.shortName"
								:title="room.longName"
								type="button"
								:class="{'fhc-primary-color': selectedRoomIdentifier === room.shortName}"
								class="px-2 py-1 border border-x-2 border-y-1 d-flex flex-row align-items-center gap-1"
							>
								<span>{{ room.shortName }}</span>
								<span
									v-if="room.id"
									@click.stop="showRoomDetails(room)"
									type="button"
									class="px-1 fhc-primary-color"
								>
									<i class="fa-solid fa-up-right-from-square"></i>
								</span>
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
						{{ $p.t("coodle/vote") }}
					</div>
					<div
						v-if="isAuthUserSurveyCreator && !isVotingInProgress && !isFinalSelectionInProgress"
						@click="isFinalSelectionInProgress = true"
						:class="isDarkMode ? 'btn-outline-light' : 'btn-outline-dark'"
						class="btn text-nowrap"
					>
						{{ $p.t("coodle/finalize_survey") }}
					</div>
					<div
						v-if="isVotingInProgress || isFinalSelectionInProgress"
						@click="setForms()"
						:class="isDarkMode ? 'btn-outline-light' : 'btn-outline-dark'"
						class="btn text-nowrap"
					>
						{{ $p.t("ui/abbrechen") }}
					</div>
					<div
						v-if="isVotingInProgress"
						@click="submitParticipantSelection()"
						:class="isDarkMode ? 'btn-light' : 'btn-dark'"
						class="btn text-nowrap"
					>
						{{ $p.t("coodle/submit_vote") }}
					</div>
					<div
						v-if="isFinalSelectionInProgress"
						@click="isRoomSelectionShown ? submitWithRoomSelection() : prepareFinalSelectionSubmission()"
						:class="isDarkMode ? 'btn-light' : 'btn-dark'"
						class="btn text-nowrap"
					>
						{{ $p.t("coodle/submit_final_selection") }}
					</div>
				</div>
			</div>
		</div>
	</div>
	`,
};
