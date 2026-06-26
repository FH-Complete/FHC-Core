import CoodleFreeBusyTableFormRow from "./CoodleFreeBusy/CoodleFreeBusyTableFormRow.js";

import FreeBusyApi from "../../../../api/factory/freeBusy.js";

export default {
	name: "CoodleFreeBusy",
	components: { CoodleFreeBusyTableFormRow },
	props: {
		uid: Number | null,
	},
	data() {
		return {
			columnHeaders: [],
			defaultSchedules: [
				{
					description: "Personal schedule",
					type: "Schedule",
				},
				{
					description: "My planned absences",
					type: "Absences",
				},
			],
			externalSchedules: [],
			scheduleEditIndex: null,
			scheduleFormData: null,
			scheduleTypeOptions: [],
		};
	},
	methods: {
		getScheduleTypeText(value) {
			let type = this.scheduleTypeOptions.find(
				(type) => type.value === value,
			);
			return type?.text ?? "Other";
		},
		openScheduleForm(index) {
			this.scheduleEditIndex = index;
			this.setScheduleFormData(
				index >= 0 ? this.externalSchedules[index] : null,
			);
		},
		closeScheduleForm() {
			this.scheduleEditIndex = null;
		},
		setScheduleFormData(schedule) {
			if (schedule) {
				this.scheduleFormData = { ...schedule };
			} else {
				this.scheduleFormData = {
					id: null,
					description: "",
					type: null,
					url: "",
					isActive: true,
				};
			}
		},
		async submitScheduleForm() {
			if (!this.scheduleFormData?.url.length) {
				window.alert("You must enter a URL!");
				return;
			}

			const response = await this.$api.call(
				this.scheduleFormData.id
					? FreeBusyApi.updateFreeBusyEntry(this.scheduleFormData)
					: FreeBusyApi.createFreeBusyEntry(this.scheduleFormData),
			);

			if (response.meta.status === "success") {
				this.closeScheduleForm();
				this.getFreeBusySchedules();
			}
		},
		async deleteSchedule(schedule) {
			if (!schedule?.id) return;

			if (
				!window.confirm(
					"Are you sure you want to remove this schedule from your FreeBusy?",
				)
			) {
				return;
			}

			const scheduleDeletionResponse = await this.$api.call(
				FreeBusyApi.deleteFreeBusyEntry(schedule.id),
			);
			if (scheduleDeletionResponse.meta.status === "success") {
				this.getFreeBusySchedules();
			}
		},
		async getFreeBusyTypes() {
			const freeBusyTypesResponse = await this.$api.call(
				FreeBusyApi.getFreeBusyTypes(),
			);
			this.scheduleTypeOptions = freeBusyTypesResponse.data.map(
				(type) => {
					return {
						value: type.freebusytyp_kurzbz,
						text: type.bezeichnung,
					};
				},
			);
		},
		async getFreeBusySchedules() {
			const freeBusyEntriesResponse = await this.$api.call(
				FreeBusyApi.getFreeBusyEntries(),
			);
			this.externalSchedules = freeBusyEntriesResponse.data.map(
				(freeBusySchedule) => {
					// todo: remove type defaulting if switch to using all types
					let type = this.scheduleTypeOptions.find((type) => {
						return (
							type.value === freeBusySchedule.freebusytyp_kurzbz
						);
					})
						? freeBusySchedule.freebusytyp_kurzbz
						: "Sonstiges";

					return {
						id: freeBusySchedule.freebusy_id,
						description: freeBusySchedule.bezeichnung,
						type,
						url: freeBusySchedule.url,
						isActive: freeBusySchedule.aktiv,
					};
				},
			);
		},
	},
	async created() {
		await this.getFreeBusyTypes();
		await this.getFreeBusySchedules();
	},
	template: /*html*/ `
	<div class="card" style="min-height:100%">
		<div class="card-header">
			<h4>{{ "FreeBusy settings" }}</h4>
		</div>
		<div class="card-body">
			<div class="d-flex flex-column gap-3">
				<div class="d-flex flex-column gap-2">
					<span >
						{{ "Here you can combine different external schedules to create your personal FreeBusy URL, which is used by Coodle." }}
					</span>
					<span >
						{{ "FreeBusy supports effective scheduling by displaying your appointments (without any details such as titles or content) to avoid timing conflicts." }}
					</span>
					<span >
						{{ "To effectively use FreeBusy, you must carefully enter and update your calendar data. Your base schedule and planned absences will be displayed by default, but you can add additional schedules (Google, SOGo, etc)." }}
					</span>
				</div>
				<table id="freeBusyTable">
					<tr>
						<th class="border-1 py-1 px-2 fw-bold">{{ "Description" }}</th>
						<th class="border-1 py-1 px-2 fw-bold">{{ "Type" }}</th>
						<th class="border-1 py-1 px-2 fw-bold" style="width: 50%;">{{ "URL" }}</th>
						<th class="border-1 py-1 px-2 fw-bold">{{ "Active" }}</th>
						<th class="border-1 py-1 px-2 fw-bold">{{ "Actions" }}</th>
					</tr>
					<tr v-for="defaultSchedule in defaultSchedules">
						<td class="border-1 py-1 px-2">{{ defaultSchedule.description }}</td>
						<td class="border-1 py-1 px-2">{{ defaultSchedule.type }}</td>
						<td class="border-1 py-1 px-2"></td>
						<td class="border-1 py-1 px-2">{{ "Yes" }}</td>
						<td class="border-1 py-1 px-2"></td>
					</tr>
					<template v-for="(schedule, index) in externalSchedules">
						<tr v-if="scheduleEditIndex !== index">
							<td class="border-1 py-1 px-2">{{ schedule.description }}</td>
							<td class="border-1 py-1 px-2">{{ getScheduleTypeText(schedule.type) }}</td>
							<td class="border-1 py-1 px-2">{{ schedule.url }}</td>
							<td class="border-1 py-1 px-2">{{ schedule.isActive ? "Yes" : "No" }}</td>
							<td class="border-1 py-1 px-2">
								<div class="w-100 d-flex flex-row gap-1 align-items-center justify-content-evenly">
									<div @click="openScheduleForm(index)" type="button" class="action py-1 px-1">
										<i class="fa-solid fa-pen-to-square"></i>
									</div>
									<div @click="deleteSchedule(schedule)" type="button" class="action py-1 px-1">
										<i class="fa-solid fa-trash-can"></i>
									</div>
								</div>
							</td>
						</tr>
						<coodle-free-busy-table-form-row
							v-else
							v-model:scheduleFormDataModelValue="scheduleFormData"
							@cancelForm="closeScheduleForm()"
							@submitForm="submitScheduleForm()"
							:scheduleTypeOptions="scheduleTypeOptions"
						/>
					</template>
					<tr v-if="scheduleEditIndex !== -1">
						<td colspan="5" class="border-1">
							<div
								@click="openScheduleForm(-1)"
								type="button"
								class="action w-100 d-flex flex-row align-items-center justify-content-center py-2"
							>
								<div>
									<i class="fa-solid fa-circle-plus fa-xl"></i>
								</div>
							</div>
						</td>
					</tr>
					<coodle-free-busy-table-form-row
						v-else
						v-model:scheduleFormDataModelValue="scheduleFormData"
						@cancelForm="closeScheduleForm()"
						@submitForm="submitScheduleForm()"
						:scheduleTypeOptions="scheduleTypeOptions"
						:uid="$props.uid"
					/>
				</table>
			</div>
		</div>
	</div>
	`,
};
