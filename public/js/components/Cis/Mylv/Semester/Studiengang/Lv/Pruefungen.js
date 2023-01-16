import BsModal from '../../../../../Bootstrap/Modal.js';

const pruefungen = {};

export default {
	components: {
		BsModal
	},
	mixins: [
		BsModal
	],
	props: {
		lehrveranstaltung_id: Number,
		bezeichnung: String,
		/*
		 * NOTE(chris): 
		 * Hack to expose in "emits" declared events to $props which we use 
		 * in the v-bind directive to forward all events.
		 * @see: https://github.com/vuejs/core/issues/3432
		*/
		onHideBsModal: Function,
		onHiddenBsModal: Function,
		onHidePreventedBsModal: Function,
		onShowBsModal: Function,
		onShownBsModal: Function
	},
	data: () => ({
		result: true,
		pruefungen: null
	}),
	created() {
		if (pruefungen[this.lehrveranstaltung_id])
			this.pruefungen = pruefungen[this.lehrveranstaltung_id];
		else
			axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Cis/Mylv/Pruefungen/' + this.lehrveranstaltung_id).then(res => {
				this.pruefungen = pruefungen[this.lehrveranstaltung_id] = res.data.retval || [];
			});
	},
	mounted() {
		this.modal = this.$refs.modalContainer.modal;
	},
	popup(options) {
		return BsModal.popup.bind(this)(null, options);
	},
	template: `<bs-modal ref="modalContainer" class="bootstrap-alert" v-bind="$props" body-class="">
		<template v-slot:title>
			Prüfungen: {{bezeichnung}}
		</template>
		<template v-slot:default>
			<div v-if="!pruefungen" class="text-center">
				<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
			</div>
			<p v-else-if="!pruefungen.length" class="alert alert-info mb-0">
				Keine Prüfungen vorhanden!
			</p>
			<table v-else class="table table-hover">
				<thead>
					<td>&nbsp;</td>
					<td>Datum</td>
					<td class="text-end">Note</td>
				</thead>
				<tbody>
					<tr v-for="pruefung in pruefungen" :key="pruefung.pruefung_id">
						<th>{{pruefung.pruefungstyp_kurzbz}}</th>
						<td>{{pruefung.datum}}</td>
						<td class="text-end">{{pruefung.note}}</td>
					</tr>
				</tbody>
			</table>
		</template>
	</bs-modal>`
}
