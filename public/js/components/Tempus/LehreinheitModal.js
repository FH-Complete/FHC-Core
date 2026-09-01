import BsModal from "../Bootstrap/Modal.js";
import ApiKalender from "../../api/factory/tempus/kalender.js";

export default {
	name: "LehreinheitModal",
	components: {
		BsModal,
	},
	emits: ["saved"],
	data() {
		return {
			entries: [],
			removedEntries: [],
			event: null,
		};
	},
	computed: {
		remainingCount() {
			return this.entries.length - this.removedEntries.length;
		},
	},
	methods: {
		async show(orig)
		{
			if (!orig?.kalender_id)
				return;
			this.event = orig;

			await this.$api.call(ApiKalender.getLehreinheiten(orig.kalender_id))
				.then(result => result.data)
				.then(result => {

					this.entries = result
					this.removedEntries = [];
					if (this.entries.length > 1)
					{
						this.$refs.modal.show();
					}
				})
				.catch(error => {
					this.$fhcAlert.handleSystemError(error);
					this.close();
				});
		},
		hide() {
			this.removedEntries = [];
			this.$refs.modal.hide();
		},
		async confirm() {
			let lehreinheiten_ids = this.removedEntries.map((entry) => entry.lehreinheit_id);
			await this.$api.call(ApiKalender.deleteFromKalenderEvent(this.event.kalender_id, lehreinheiten_ids));

			this.hide();
			this.$emit("saved");
		},
		isRemoved(entry) {
			return this.removedEntries.includes(entry);
		},
		removeEntry(entry) {
			if (this.remainingCount <= 1)
				return;
			this.removedEntries.push(entry);
		},
		restoreEntry(entry) {
			let idx = this.removedEntries.indexOf(entry);
			if (idx !== -1)
				this.removedEntries.splice(idx, 1);
		},
	},
	template: `
		<bs-modal
			ref="modal"
			class="bootstrap-prompt"
			data-cy="lehreinheitModal"
		>
			<template #title>{{ $p.t('lehre','lehreinheit') }}</template>
			<template #default>
				<div style="max-height: 300px; overflow-y: auto;">
					<div v-if="entries.length === 0" class="text-muted text-center py-3">
						{{ $p.t('ui', 'keineEintraegeGefunden') }}
					</div>
					<div
						v-for="(option, index) in entries"
						:key="option.lehreinheit_id"
						class="d-flex align-items-center justify-content-between w-100 py-1 border-bottom"
					>
						<span :class="{ 'text-decoration-line-through text-muted': isRemoved(option) }">({{ option.lehreinheit_id }}) {{ option.title }} </span>
						<button
							v-if="isRemoved(option)"
							type="button"
							class="btn btn-sm btn-outline-secondary"
							@click="restoreEntry(option)"
						>
							<i class="fa-solid fa-rotate-left"></i>
						</button>
						<button
							v-else
							type="button"
							class="btn btn-sm btn-outline-danger"
							:disabled="remainingCount <= 1"
							@click="removeEntry(option)"
						>
							<i class="fa-solid fa-trash"></i>
						</button>
					</div>
				</div>

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