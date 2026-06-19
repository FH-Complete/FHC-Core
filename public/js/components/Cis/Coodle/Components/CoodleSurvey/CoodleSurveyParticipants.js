export default {
	name: "CoodleSurveyParticipants",
	components: {},
	props: {
		participantsModelValue: Array,
	},
	emits: ["update:participantsModelValue"],
	data() {
		return {
			searchInput: "",
			isSearchingForParticipants: false,
			searchResults: [],
			areSearchResultsShown: false,
			// todo: increase depending on feedback
			warningGroupSize: 5,
			// todo: remove
			searchResultsDevExample: [
				{
					uid: "ma1000",
					name: "Test User 10",
					type: "user",
				},
				{
					uid: "ma1001",
					name: "Test User 11",
					type: "user",
				},
				{
					uid: "ma1002",
					name: "Test User 12",
					type: "user",
				},
				{
					uid: "ma1003",
					name: "Test User 13",
					type: "user",
				},
				{
					uid: "ABC-DEF",
					name: "ABC-DEF",
					type: "group",
					users: [
						{
							uid: "ma1003",
							name: "Test User 13",
						},
						{
							uid: "ma1004",
							name: "Test User 14",
						},
					],
				},
				{
					uid: "GHI-JKL",
					name: "GHI-JKL",
					type: "group",
					users: [
						{
							uid: "ma1004",
							name: "Test User 14",
						},
						{
							uid: "ma1005",
							name: "Test User 15",
						},
						{
							uid: "ma1006",
							name: "Test User 16",
						},
						{
							uid: "ma1007",
							name: "Test User 17",
						},
						{
							uid: "ma1008",
							name: "Test User 18",
						},
						{
							uid: "ma1009",
							name: "Test User 19",
						},
					],
				},
				{
					uid: "ma1004",
					name: "Test User 14",
					type: "user",
				},
				{
					uid: "ma1005",
					name: "Test User 15",
					type: "user",
				},
				{
					uid: "ma1006",
					name: "Test User 16",
					type: "user",
				},
				{
					uid: "ma1007",
					name: "Test User 17",
					type: "user",
				},
			],
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
		async hideSearchResults() {
			// todo: clean up, don't use timeout
			setTimeout(() => {
				this.areSearchResultsShown = false;
			}, 100);
		},
		searchParticipants() {
			this.isSearchingForParticipants = true;

			// todo
			this.searchResults = this.searchResultsDevExample;

			this.isSearchingForParticipants = false;
		},
		selectSearchResult(searchResult) {
			if (
				searchResult.type === "group" &&
				searchResult.users.length > this.warningGroupSize
			) {
				let warningMessage =
					"Group (((groupName))) contains (((n))) users. Are you sure you want to add all (((n))) users to your survey?";
				warningMessage = warningMessage.replace(
					"(((groupName)))",
					searchResult.name,
				);
				warningMessage = warningMessage.replaceAll(
					"(((n)))",
					searchResult.users.length,
				);
				if (!window.confirm(warningMessage)) {
					return;
				}
			}

			const addedParticipants =
				searchResult.type === "group"
					? searchResult.users
					: [searchResult];
			this.addParticipants(addedParticipants);
		},
		addParticipants(newParticipants) {
			newParticipants = newParticipants.filter((newParticipant) => {
				return !this.participants.some(
					(existingParticipant) =>
						existingParticipant.uid === newParticipant.uid,
				);
			}).map((newParticipant) => {
				return {
					uid: newParticipant.uid,
					name: newParticipant.name,
					isCalendarShown: false,
				}
			});

			this.participants = this.participants.concat(newParticipants);
		},
		removeParticipant(participantToBeRemoved) {
			this.participants = this.participants.filter(
				(participant) => participant.uid !== participantToBeRemoved.uid,
			);
		},
		showCalendar(participantForCalendarToBeDisplayed) {
			this.participants = this.participants.map((participant) => {
				let updatedParticipant = { ...participant };
				if (
					participant.uid === participantForCalendarToBeDisplayed.uid
				) {
					updatedParticipant.isCalendarShown = true;
				}
				return updatedParticipant;
			});
		},
	},
	template: /*html*/ `
	<div class="d-flex flex-column gap-2 mb-3">
		<label for="searchInput" class="fw-bold">{{ "Participants" }}</label>
		<div class="position-relative" style="max-width:300px;">
			<input
				v-model="searchInput"
				@blur="hideSearchResults()"
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
						<span>{{ searchResult.name }}</span>
					</div>
				</div>
			</div>
		</div>
		<span v-if="!participants.length" class="fst-italic">
			{{ "No participants added yet!" }}
		</span>
		<div v-else class="d-flex flex-row gap-2 flex-wrap">
			<div
				v-for="participant in participants"
				class="d-flex flex-row align-items-center gap-4 py-1 px-3 border border-1 rounded-pill"
			>
				<span>{{ participant.name }}</span>
				<div class="d-flex flex-row align-items-center gap-3">
					<i
						v-if="participant.isCalendarShown"
						@click="participant.isCalendarShown = false"
						type="button"
						class="fa-solid fa-calendar-check fhc-primary-color"
					></i>
					<i
						v-else
						@click="showCalendar(participant)"
						type="button"
						class="fa-regular fa-calendar"
					></i>
					<i @click="removeParticipant(participant)" type="button" class="fa-solid fa-xmark"></i>
				</div>
			</div>
		</div>
	</div>
	`,
};
