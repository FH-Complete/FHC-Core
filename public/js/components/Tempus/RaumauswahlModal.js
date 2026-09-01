import BsModal from "../Bootstrap/Modal.js";
import ApiKalender from "../../api/factory/tempus/kalender.js";
import FormInput from "../../../js/components/Form/Input.js";


export default {
	name: "RaumauswahlModal",
	components: {
		BsModal,
		Listbox: primevue.listbox,
		FormInput
	},
	emits: ["saved"],
	data() {
		return {
			entries: [],
			selectedRooms: [],
			event: null,
			loading: false,
			mode: 'edit',
			pendingEvent: null
		};
	},
	methods: {
		async show(orig)
		{
			if (!orig?.kalender_id)
				return;

			this.mode = 'edit';
			this.event = orig;
			this.loading = true;
			this.entries = [];
			this.selectedRooms = [];
			this.location = this.event.location

			this.$refs.modal.show();

			let result = await this.$api.call(ApiKalender.getRaumvorschlag(orig.kalender_id));

			let fetchedEntries = result.data ?? [];
			let currentRooms = orig.ort_kurzbz ?? [];

			for (let ort_kurzbz of currentRooms)
			{
				let exists = fetchedEntries.some((r) => r.ort_kurzbz === ort_kurzbz);

				if (!exists)
					fetchedEntries.push({ ort_kurzbz, score: null, details: [] });
			}

			this.entries = fetchedEntries;
			this.selectedRooms = this.entries.filter((room) => {return currentRooms.includes(room.ort_kurzbz)});
			this.entries = [
				...this.entries.filter((room) => currentRooms.includes(room.ort_kurzbz)),
				...this.entries.filter((room) => !currentRooms.includes(room.ort_kurzbz)),
			];

			this.loading = false;
		},
		hide() {
			this.selectedRooms = [];
			this.pendingEvent = null;

			this.$refs.modal.hide();
		},
		showForNew(lehreinheit_id, start_time, end_time, vorschlaege)
		{
			this.mode = 'new'
			this.pendingEvent = {lehreinheit_id, start_time, end_time}
			this.event = null;
			this.loading = false;
			this.entries = vorschlaege ?? [];
			this.selectedRooms = [];

			this.$refs.modal.show();
		},

		async confirm() {

			if (this.mode === 'new')
			{
				const result = await this.$api.call(
					ApiKalender.addKalenderEvent(
						this.pendingEvent.lehreinheit_id,
						this.selectedRooms.map((room) => room.ort_kurzbz),
						this.pendingEvent.start_time,
						this.pendingEvent.end_time
					)
				).then((result) => result.data)

				if (result.needs_room_selection)
				{
					this.showForNew(this.pendingEvent.lehreinheit_id, this.pendingEvent.start_time, this.pendingEvent.end_time, result.raum_vorschlaege);
					return;
				}

				this.hide();
				this.$emit('saved');
				return;
			}
			let orte = {};

			orte.ort_kurzbz = this.selectedRooms.map((room) => room.ort_kurzbz);
			orte.location = this.location;

			if (orte.ort_kurzbz.length < 1 && orte.location?.trim().length < 1)
			{
				await this.$api.call(
					ApiKalender.deleteOrtEntry(this.event.kalender_id),
				);
			}
			else
			{
				await this.$api.call(
					ApiKalender.updateKalenderEvent(this.event.kalender_id, {orte}),
				);
			}

			this.hide();
			this.$emit("saved");
		},
	},
	watch: {
		selectedRooms(newVal) {
			let sorted = this.entries.filter((room) => {
				return newVal.some((sel) => sel.ort_kurzbz === room.ort_kurzbz);
			});

			let same = sorted.length === newVal.length;

			if (same)
			{
				same = sorted.every((room, i) => {
					return room.ort_kurzbz === newVal[i].ort_kurzbz;
				});
			}

			if (!same)
				this.selectedRooms = sorted;
		},
	},
	template: `
		<bs-modal
			ref="modal"
			class="bootstrap-prompt"
			data-cy="raumauswahlModal"
		>
			<template #title>{{ $p.t('lehre','roomselection') }}</template>
			<template #default>
				<div v-if="loading" class="text-center text-muted py-4">
					<i class="fa-solid fa-spinner fa-spin me-2"></i>{{ $p.t('ui','loading') }}
				</div>
				<Listbox
					v-else
					v-model="selectedRooms"
					:options="entries"
					option-label="ort_kurzbz"
					multiple
					filter
					:checkmark="true"
					:highlightOnSelect="false"
					class="w-100"
					listStyle="max-height: 300px"
					:emptyMessage="$p.t('ui', 'keineEintraegeGefunden')"
				>
				
				<template #option="{ option }">
					<div class="room-option">
						<i class="fa-solid fa-door-open text-muted"></i>
						<span v-if="option.raumtyp_kurzbz">
							[{{ option.raumtyp_kurzbz }}]
						</span>
						<span v-else></span>
						<span class="fw-semibold">
							{{ option.ort_kurzbz }}
						</span>
						<span class="text-muted small text-nowrap">
							{{ option.max_person }} Pers.
						</span>
						<i
							v-if="option.ausstattung?.length"
							class="fa-solid fa-circle-info text-muted"
							v-tooltip.bottom="{
								value: option.ausstattung,
								class: 'custom-tooltip',
							}"
						></i>
						<span v-else></span>
						<span class="text-muted text-end">
							{{ option.score }}
						</span>
					</div>
				</template>
					
				</Listbox>
				<form-input
					v-if="mode === 'edit'"
					label="Location"
					type="textarea"
					container-class="col-12"
					name="location"
					v-model="location"
				></form-input>
			
				<div class="d-flex justify-content-end gap-2 mt-3">
					<button type="button" class="btn btn-secondary" @click="hide">
						{{ $p.t('ui','abbrechen') }}
					</button>
					<button
						type="button"
						class="btn btn-primary"
						@click="confirm"
					>
						{{ $p.t('global','speichern') }}
					</button>
				</div>
			</template>
		</bs-modal>
	`,
};