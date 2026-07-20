import CoodleApi from "../../../../../api/factory/coodle.js";

import { debounce } from "../../../../../helpers/DebounceHelper.js";

export default {
	name: "CoodleSurveyParticipants",
	components: {},
	props: {
		participantsModelValue: Array,
		externalParticipantsModelValue: Array,
		participantScheduleColorsModelValue: Array,
		authInfo: { type: Object | null },
	},
	emits: [
		"update:participantsModelValue",
		"update:externalParticipantsModelValue",
		"update:participantScheduleColorsModelValue",
	],
	inject: {
		isMobile: "isMobile",
	},
	data() {
		return {
			searchInput: "",
			isSearchingForParticipants: false,
			searchResults: [],
			areSearchResultsShown: false,
			warningGroupSize: 10,
			searchParticipantsAbortController: null,
			searchParticipants: debounce(async () => {
				if (this.searchInput.length < 2) return;

				if (this.searchParticipantsAbortController)
					this.searchParticipantsAbortController.abort();
				this.searchParticipantsAbortController = new AbortController();

				this.isSearchingForParticipants = true;

				const participantsSearchResponse = await this.$api.call(
					CoodleApi.searchParticipants(this.searchInput),
					{ signal: this.searchParticipantsAbortController.signal },
				);
				this.searchResults = participantsSearchResponse.data;

				this.isSearchingForParticipants = false;
			}, 300),
			hideSearchResults: debounce(async () => {
				this.areSearchResultsShown = false;
			}, 100),
			externalParticipantNameInput: "",
			externalParticipantEmailInput: "",
		};
	},
	computed: {
		participants: {
			get() {
				return this.$props.participantsModelValue;
			},
			set(newValue) {
				this.$emit("update:participantsModelValue", newValue);
			},
		},
		externalParticipants: {
			get() {
				return this.$props.externalParticipantsModelValue;
			},
			set(newValue) {
				this.$emit("update:externalParticipantsModelValue", newValue);
			},
		},
		participantScheduleColors: {
			get() {
				return this.$props.participantScheduleColorsModelValue;
			},
			set(newValue) {
				this.$emit(
					"update:participantScheduleColorsModelValue",
					newValue,
				);
			},
		},
		isMaxDisplayedParticipantSchedulesReached() {
			return (
				this.displayedParticipantSchedulesCount >=
				this.participantScheduleColors.length
			);
		},
		displayedParticipantSchedulesCount() {
			return this.participants.filter(
				(participant) => participant.isCalendarShown,
			).length;
		},
		hasAuthUserBeenAddedAsParticipant() {
			return this.participants.some(
				(participant) => participant.uid === this.$props.authInfo?.uid,
			);
		},
	},
	watch: {
		searchInput() {
			if (this.searchInput.length >= 2) {
				this.areSearchResultsShown = true;
				this.searchParticipants();
			} else {
				this.areSearchResultsShown = false;
			}
		},
	},
	methods: {
		async selectSearchResult(searchResult) {
			if (
				searchResult.type === "group" &&
				searchResult.users.length >= this.warningGroupSize
			) {
				let groupSizeWarningMessage =
					"Group (((groupName))) contains (((n))) users. Are you sure you want to add all (((n))) users to your survey?";
				groupSizeWarningMessage = groupSizeWarningMessage.replace(
					"(((groupName)))",
					searchResult.name,
				);
				groupSizeWarningMessage = groupSizeWarningMessage.replaceAll(
					"(((n)))",
					searchResult.users.length,
				);
				const groupSizeConfirmation = await this.$fhcAlert.confirm({
					header: "Warning!",
					message: groupSizeWarningMessage,
					acceptLabel: "Yes",
					rejectLabel: "Cancel",
				});

				if (!groupSizeConfirmation) return;
			}

			const addedParticipants =
				searchResult.type === "group"
					? searchResult.users
					: [searchResult];
			this.addParticipants(addedParticipants);
			this.searchInput = "";
			this.searchResults = [];
		},
		addParticipants(newParticipants) {
			newParticipants = newParticipants
				.filter((newParticipant) => {
					return !this.participants.some(
						(existingParticipant) =>
							existingParticipant.uid === newParticipant.uid,
					);
				})
				.map((newParticipant) => {
					return {
						uid: newParticipant.uid,
						name: newParticipant.name,
						isCalendarShown: false,
					};
				});

			this.participants = this.participants.concat(newParticipants);
		},
		removeParticipant(participantToBeRemoved) {
			this.participants = this.participants.filter(
				(participant) => participant.uid !== participantToBeRemoved.uid,
			);

			let occupiedParticipantColor = this.participantScheduleColors.find(
				(participantColor) =>
					participantColor.uid === participantToBeRemoved.uid,
			);
			if (!occupiedParticipantColor) return;
			occupiedParticipantColor.uid = null;
		},
		showSchedule(participantForCalendarToBeDisplayed) {
			if (this.isMaxDisplayedParticipantSchedulesReached) return;

			this.participants = this.participants.map((participant) => {
				let updatedParticipant = { ...participant };
				if (
					participant.uid === participantForCalendarToBeDisplayed.uid
				) {
					updatedParticipant.isCalendarShown = true;
				}
				return updatedParticipant;
			});

			this.participantScheduleColors.find(
				(participantColor) => !participantColor.uid,
			).uid = participantForCalendarToBeDisplayed.uid;
		},
		hideSchedule(participant) {
			participant.isCalendarShown = false;

			let occupiedParticipantColor = this.participantScheduleColors.find(
				(participantColor) => participantColor.uid === participant.uid,
			);
			if (!occupiedParticipantColor) return;
			occupiedParticipantColor.uid = null;
		},
		hideAllSchedules() {
			this.participants.forEach((participant) => {
				participant.isCalendarShown = false;
			});
			this.participantScheduleColors.forEach((participantColor) => {
				participantColor.uid = null;
			});
		},
		getParticipantScheduleColor(participant) {
			return this.participantScheduleColors.find(
				(participantColor) => participantColor.uid === participant.uid,
			)?.color;
		},
		addExternalParticipant() {
			const nameInput = this.externalParticipantNameInput.trim();
			const emailInput = this.externalParticipantEmailInput.trim();

			if (!nameInput.length || !emailInput.length) {
				this.$fhcAlert.alertError("Missing inputs!");
				return;
			}

			const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
			if (!emailRegex.test(emailInput)) {
				this.$fhcAlert.alertError("Invalid email format!");
				return;
			}

			if (this.externalParticipants.some((externalParticipant) => externalParticipant.email === emailInput)) {
				this.$fhcAlert.alertError("Duplicates not allowed!");
				return;
			}

			this.externalParticipants.push({
				name: nameInput,
				email: emailInput
			});
			this.externalParticipantNameInput = "";
			this.externalParticipantEmailInput = "";
		},
		removeExternalParticipant(email) {
			this.externalParticipants = this.externalParticipants.filter(
				(externalParticipant) => externalParticipant.email !== email,
			);
		},
	},
	template: /*html*/ `
	<div class="row mb-3">
		<div class="col-12 col-md-6 col-xxl-12 d-flex flex-column gap-1">
			<label for="searchInput" class="fw-bold">{{ "Participants" }}</label>
			<div class="position-relative mb-1">
				<input
					v-model="searchInput"
					@focus="areSearchResultsShown = searchInput.length >= 2"
					@blur="hideSearchResults()"
					:placeholder="'Search participants...'"
					id="searchInput"
					class="w-100"
				/>
				<div
					v-if="areSearchResultsShown"
					id="coodleParticipantsSearchResults"
					class="position-absolute w-100 d-flex flex-column overflow-y-auto"
					style="height:200px;"
				>
					<div
						v-if="isSearchingForParticipants"
						class="flex-grow-1 d-flex flex-row justify-content-center align-items-center"
					>
						<i class="fa-solid fa-spinner fa-spin fa-xl"></i>
					</div>
					<div
						v-else-if="!searchResults.length"
						class="d-flex flex-row justify-content-center align-items-center py-3"
					>
						<span>{{ "No results found!" }}</span>
					</div>
					<div v-else>
						<div
							v-for="searchResult in searchResults"
							@click="selectSearchResult(searchResult)"
							type="button"
							class="coodleParticipantSearchResult d-flex flex-row gap-2 justify-content-start align-items-center py-1 px-2 border-bottom border-1"
						>
							<i v-if="searchResult.type === 'user'" class="fa-solid fa-user"></i>
							<i v-else-if="searchResult.type === 'group'" class="fa-solid fa-user-group"></i>
							<span>{{ searchResult.name + (searchResult.uid ? (" (" + searchResult.uid + ")") : "") }}</span>
						</div>
					</div>
				</div>
			</div>
			<div v-if="participants.length" class="d-flex flex-row gap-2 flex-wrap">
				<div
					v-for="participant in participants"
					class="d-flex flex-row align-items-center gap-4 py-1 px-3 border border-1 rounded-pill"
				>
					<span>{{ participant.name }}</span>
					<div class="d-flex flex-row align-items-center gap-3">
						<i
							v-if="participant.isCalendarShown"
							@click="hideSchedule(participant)"
							type="button"
							class="fa-solid fa-calendar"
							:style="{color: getParticipantScheduleColor(participant)}"
						></i>
						<i
							v-else-if="isMaxDisplayedParticipantSchedulesReached"
							class="fa-regular fa-calendar-xmark"
						></i>
						<i
							v-else
							@click="showSchedule(participant)"
							type="button"
							class="fa-regular fa-calendar"
						></i>
						<i @click="removeParticipant(participant)" type="button" class="fa-solid fa-xmark"></i>
					</div>
				</div>
			</div>
			<div v-if="!hasAuthUserBeenAddedAsParticipant && $props.authInfo" class="d-flex flex-row">
				<div
					@click="addParticipants([$props.authInfo])"
					type="button"
					class="py-1 px-3 border border-1 rounded-pill"
				>
					<span>{{ "Add yourself" }}</span>
				</div>
			</div>
			<span v-if="isMaxDisplayedParticipantSchedulesReached" class="fst-italic">
				{{ "You can display no more participant schedules!" }}
			</span>
			<div>
				<span
					v-if="displayedParticipantSchedulesCount"
					@click="hideAllSchedules()"
					type="button"
					class="text-decoration-underline"
				>
					{{ "Hide all participant schedules" }}
				</span>
			</div>
		</div>
		<div class="col-12 col-md-6 col-xxl-12 d-flex flex-column gap-1">
			<span class="fw-bold">{{ "External participants" }}</span>
			<div class="d-flex flex-row gap-2 mb-1">
				<div class="flex-grow-1 d-flex flex-column gap-2">
					<div >
						<input
							v-model="externalParticipantNameInput"
							:placeholder="'Name'"
							id="externalParticipantNameInput"
							maxlength="255"
							style="width:100%"
						/>
					</div>
					<div >
						<input
							v-model="externalParticipantEmailInput"
							:placeholder="'Email'"
							type="email"
							id="externalParticipantEmailInput"
							maxlength="255"
							style="width:100%"
						/>
					</div>
				</div>
				<div class="d-flex flex-row align-items-center justify-content-center">
					<div @click="addExternalParticipant()" type="button" class="p-1 fhcPrimaryHover">
						<i class="fa-solid fa-circle-plus fa-xl"></i>
					</div>
				</div>
			</div>
			<div v-if="externalParticipants.length" class="d-flex flex-row gap-2 flex-wrap">
				<div
					v-for="externalParticipant in externalParticipants"
					:key="externalParticipant.email"
					class="d-flex flex-row align-items-center gap-4 py-1 px-3 border border-1 rounded-pill"
				>
					<div class="d-flex flex-row gap-2 flex-wrap">
						<span>{{ externalParticipant.name }}</span>
						<span>|</span>
						<span>{{ externalParticipant.email }}</span>
					</div>
					<i
						@click="removeExternalParticipant(externalParticipant.email)"
						type="button"
						class="fa-solid fa-xmark"
					></i>
				</div>
			</div>
		</div>
		<span v-if="!participants.length && !externalParticipants.length" class="fst-italic">
			{{ "No participants added yet!" }}
		</span>	
	</div>
	`,
};
