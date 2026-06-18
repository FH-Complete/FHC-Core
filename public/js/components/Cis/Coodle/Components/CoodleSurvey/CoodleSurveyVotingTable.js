export default {
	name: "CoodleSurveyVotingTable",
	props: {
		survey: { type: Object },
		uid: { type: String | null },
		timeslots: { type: Array },
	},
	emits: [],
	data() {
		return {
			isVotingInProgress: false,
			isFinalSelectionInProgress: false,
			participantsWithoutAuthUser: [],
			authUserParticipant: null,
			editableAuthUserSelection: null,
			selectedTimeslotId: null,
		};
	},
	computed: {
		isDarkMode() {
			return this.$theme.theme_name.value == "dark";
		},
		isAuthUserSurveyCreator() {
			return (
				this.$props.uid &&
				this.$props.uid === this.$props.survey?.creator?.uid
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
					this.$props.uid && participant.uid === this.$props.uid,
			)[0];

			this.participantsWithoutAuthUser = participants.filter(
				(participant) =>
					!this.$props.uid || participant.uid !== this.$props.uid,
			);
		},
		setData() {
			this.isVotingInProgress = false;
			this.isFinalSelectionInProgress = false;

			this.setSelectedTimeslotId();
			this.parseParticipants();
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
		submitVote() {
			// todo
			console.log("voting...");
		},
		submitFinalSelection() {
			if (!this.$props.survey.id) return;

			let selectedTimeslot = "";

			if (!this.selectedTimeslotId) {
				window.alert("You haven't made a selection!");
				return;
			} else if (this.selectedTimeslotId === "none") {
				selectedTimeslot = '"No appointment is possible"';
			} else {
				const selectedTimeslotInfo = this.$props.timeslots.find(
					(timeslot) => timeslot.id === this.selectedTimeslotId,
				);
				if (!selectedTimeslotInfo) return;
				selectedTimeslot =
					selectedTimeslotInfo.weekday +
					" " +
					selectedTimeslotInfo.fullDate +
					" " +
					selectedTimeslotInfo.startTime +
					"-" +
					selectedTimeslotInfo.endTime;
			}

			if (
				!window.confirm(
					'Are you sure you want to select option (((selectedTimeslot))) and finalize Coodle survey "(((surveyTitle)))"?'
						.replace("(((selectedTimeslot)))", selectedTimeslot)
						.replace(
							"(((surveyTitle)))",
							this.$props.survey?.title,
						),
				)
			) {
				return;
			}

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
				!Object.values(this.authUserParticipant.selection).filter(
					(isSelected) => isSelected,
				).length
			) {
				participantsThatHaveNotVoted++;
			}

			if (participantsThatHaveNotVoted) {
				if (
					!window.confirm(
						"Not all participants have voted. Are you sure you want to proceed?",
					)
				) {
					return;
				}
			}

			console.log("finalizing...");
			// todo
		},
	},
	created() {
		this.setData();
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
		<table>
			<tr>
				<td class="border-1"></td>
				<td v-for="timeslot in $props.timeslots" class="border-1">
					<div class="px-1 d-flex flex-column align-items-center">
						<span>{{ timeslot.month.slice(0,3) }}</span>
						<span class="fs-5 fw-bold">{{ timeslot.date }}</span>
						<span>{{ timeslot.weekday.slice(0,3) }}</span>
					</div>
				</td>
				<td class="border-1"></td>
			</tr>
			<tr>
				<td class="border-1"></td>
				<td v-for="timeslot in $props.timeslots" class="border-1">
					<div class="px-2 d-flex flex-column align-items-start">
						<span>{{ timeslot.startTime + " -" }}</span>
						<span>{{ timeslot.endTime }}</span>
					</div>
				</td>
				<td class="border-1">
					<div class="px-2 py-1">
						{{ "No appointment is possible" }}
					</div>
				</td>
			</tr>
			<tr v-for="participant in participantsWithoutAuthUser">
				<td class="border-1 px-2 py-1">{{ participant.name }}</td>
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
				:style="!isVotingInProgress ? '' : 'background-color:' + (isDarkMode ? '#111111' : '#EEEEEE')"
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
			<tr v-if="$props.survey.sums" class="fw-bold">
				<td class="border-1 px-2 py-1">{{ "Vote tally" }}</td>
				<td v-for="timeslot in $props.timeslots" class="border-1">
					<div class="d-flex flex-row justify-content-center">
						{{ $props.survey.sums[timeslot.id] }}
					</div>
				</td>
				<td class="border-1">
					<div class="d-flex flex-row justify-content-center">
						{{ $props.survey.sums.none }}
					</div>
				</td>
			</tr>
			<tr
				v-if="isAuthUserSurveyCreator || $props.survey.completedAt"
				:style="!isFinalSelectionInProgress ? '' : 'background-color:' + (isDarkMode ? '#111111' : '#EEEEEE')"
				class="fw-bold"
			>
				<td class="border-1 px-2 py-1">{{ "Final timeslot" }}</td>
				<td v-for="timeslot in $props.timeslots" class="border-1">
					<div class="d-flex flex-row justify-content-center py-3">
						<input
							v-if="isSurveyActive"
							v-model="selectedTimeslotId"
							:value="timeslot.id"
							:disabled="!isFinalSelectionInProgress"
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
							:disabled="!isFinalSelectionInProgress"
							:role="isFinalSelectionInProgress ? 'button' : ''"
							type="radio"
							style="width: 20px; height: 20px;"
						/>
						<i v-else-if="selectedTimeslotId === 'none'" class="fa-solid fa-check"></i>
					</div>
				</td>
			</tr>
		</table>
		<div v-if="isSurveyActive" class="d-flex flex-row gap-2 mt-3">
			<div
				v-if="authUserParticipant && !isVotingInProgress && !isFinalSelectionInProgress"
				@click="isVotingInProgress = true"
				:class="isDarkMode ? 'btn-outline-light' : 'btn-outline-dark'"
				class="btn text-nowrap"
			>
				Vote
			</div>
			<div
				v-if="isAuthUserSurveyCreator && !isVotingInProgress && !isFinalSelectionInProgress"
				@click="isFinalSelectionInProgress = true"
				:class="isDarkMode ? 'btn-outline-light' : 'btn-outline-dark'"
				class="btn text-nowrap"
			>
				Finalize survey
			</div>
			<div
				v-if="isVotingInProgress || isFinalSelectionInProgress"
				@click="() => {
					isVotingInProgress = false;	
					isFinalSelectionInProgress = false;	
				}"
				:class="isDarkMode ? 'btn-outline-light' : 'btn-outline-dark'"
				class="btn text-nowrap"
			>
				Cancel
			</div>
			<div
				v-if="isVotingInProgress"
				@click="submitVote()"
				:class="isDarkMode ? 'btn-light' : 'btn-dark'"
				class="btn text-nowrap"
			>
				Submit vote
			</div>
			<div
				v-if="isFinalSelectionInProgress"
				@click="submitFinalSelection()"
				:class="isDarkMode ? 'btn-light' : 'btn-dark'"
				class="btn text-nowrap"
			>
				Submit final selection
			</div>
		</div>
	</div>
	`,
};
