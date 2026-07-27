import CoodleFreeBusyTableFormRow from "./CoodleFreeBusy/CoodleFreeBusyTableFormRow.js";

import FreeBusyApi from "../../../../api/factory/freeBusy.js";

export default {
	name: "CoodleFreeBusy",
	components: { CoodleFreeBusyTableFormRow },
	props: {
		authUid: Number | null,
	},
	data() {
		return {
			columnHeaders: [],
			defaultSchedules: [
				{
					description: this.$p.t("coodle/freebusy_personal_schedule"),
					type: this.$p.t("coodle/schedule"),
				},
				{
					description: this.$p.t("coodle/freebusy_planned_absences"),
					type: this.$p.t("coodle/absences"),
				},
			],
			externalSchedules: [],
			scheduleEditIndex: null,
			scheduleFormData: null,
			scheduleTypes: [],
			isSubmittingScheduleForm: false,
		};
	},
	methods: {
		getScheduleTypeText(value) {
			let type = this.scheduleTypes.find((type) => type.value === value);
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
			if (this.isSubmittingScheduleForm) return;

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

			const scheduleRemovalConfirmation = await this.$fhcAlert.confirm({
				header: this.$p.t("coodle/removal_confirmation"),
				message:
					this.$p.t("coodle/freebusy_removal_confirmation_message"),
				acceptLabel: this.$p.t("coodle/yes"),
				rejectLabel: this.$p.t("ui/abbrechen"),
			});
			if (!scheduleRemovalConfirmation) return;

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
			this.scheduleTypes = freeBusyTypesResponse.data.map((type) => {
				return {
					value: type.freebusytyp_kurzbz,
					text: type.bezeichnung,
					urlDefault: type.url_default,
				};
			});
		},
		async getFreeBusySchedules() {
			const freeBusyEntriesResponse = await this.$api.call(
				FreeBusyApi.getFreeBusyEntries(),
			);
			this.externalSchedules = freeBusyEntriesResponse.data.map(
				(freeBusySchedule) => {
					return {
						id: freeBusySchedule.freebusy_id,
						description: freeBusySchedule.bezeichnung,
						type: freeBusySchedule.freebusytyp_kurzbz,
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
			<h4>{{ $p.t("coodle/freebusy_settings") }}</h4>
		</div>
		<div class="card-body">
			<div class="d-flex flex-column gap-3">
				<div class="d-flex flex-column gap-2">
					<span>
						{{ $p.t("coodle/freebusy_explainer") }}
					</span>
				</div>
				<div class="overflow-x-auto">
					<table id="freeBusyTable" style="min-width:100%;">
						<tr>
							<th class="border-1 py-1 px-2 fw-bold">{{ $p.t("coodle/description") }}</th>
							<th class="border-1 py-1 px-2 fw-bold">{{ $p.t("coodle/type") }}</th>
							<th class="border-1 py-1 px-2 fw-bold" style="width: 50%;">{{ "URL" }}</th>
							<th class="border-1 py-1 px-2 fw-bold">{{ $p.t("coodle/active") }}</th>
							<th class="border-1 py-1 px-2 fw-bold">{{ $p.t("coodle/actions") }}</th>
						</tr>
						<tr v-for="defaultSchedule in defaultSchedules">
							<td class="border-1 py-1 px-2">{{ defaultSchedule.description }}</td>
							<td class="border-1 py-1 px-2">{{ defaultSchedule.type }}</td>
							<td class="border-1 py-1 px-2"></td>
							<td class="border-1 py-1 px-2">{{ $p.t("coodle/yes") }}</td>
							<td class="border-1 py-1 px-2"></td>
						</tr>
						<template v-for="(schedule, index) in externalSchedules">
							<tr v-if="scheduleEditIndex !== index">
								<td class="border-1 py-1 px-2">{{ schedule.description }}</td>
								<td class="border-1 py-1 px-2">{{ getScheduleTypeText(schedule.type) }}</td>
								<td class="border-1 py-1 px-2">{{ schedule.url }}</td>
								<td class="border-1 py-1 px-2">{{ schedule.isActive ? $p.t("coodle/yes") : $p.t("coodle/no") }}</td>
								<td class="border-1 py-1 px-2">
									<div class="w-100 d-flex flex-row gap-1 align-items-center justify-content-evenly">
										<div
											@click="openScheduleForm(index)"
											:title="$p.t('coodle/edit')"
											type="button"
											class="action py-1 px-1"
										>
											<i class="fa-solid fa-pen-to-square"></i>
										</div>
										<div
											@click="deleteSchedule(schedule)"
											:title="$p.t('global/loeschen')"
											type="button"
											class="action py-1 px-1"
										>
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
								:scheduleTypes="scheduleTypes"
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
							:scheduleTypes="scheduleTypes"
						/>
					</table>
				</div>
			</div>
		</div>
	</div>
	`,
};
