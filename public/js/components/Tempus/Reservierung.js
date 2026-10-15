import BsModal from '../Bootstrap/Modal.js';
import ApiReservierung from '../../api/factory/tempus/reservierung.js';
import ReservierungForm from './ReservierungForm.js';

export default {
	name: 'ReservierungModal',
	components: {
		BsModal,
		ReservierungForm
	},
	props: {
		rooms: {
			type: Array,
			default: null
		}
	},
	emits: ['saved'],
	methods: {
		show(start = null, end = null)
		{
			this.$refs.form.reset(start, end, this.rooms);
			this.$refs.modal.show();
		},
		hide()
		{
			this.$refs.modal.hide();
		},
		save()
		{
			let data = this.$refs.form.getData();

			if (!data.start || !data.end)
			{
				this.$fhcAlert.alertError(this.$p.t('ui', 'error_fieldRequired', { field: this.$p.t('ui', 'zeitraum') }));
				return;
			}

			this.$api.call(
				ApiReservierung.addReservierung(
					data.titel,
					data.beschreibung,
					data.orte,
					luxon.DateTime.fromFormat(data.start, 'yyyy-MM-dd HH:mm').toISO(),
					luxon.DateTime.fromFormat(data.end, 'yyyy-MM-dd HH:mm').toISO(),
					data.teilnehmer,
					data.gruppen,
				)
			).then(() => {
				this.$refs.modal.hide();
				this.$emit('saved');
			});
		},
	},
	template: `
		<bs-modal ref="modal" class="bootstrap-prompt" dialogClass="modal-xl" data-cy="reservationModal">
			<template #title>Neue Reservierung</template>
			<template #default>
				<reservierung-form ref="form" />
			</template>
			<template #footer>
				<button type="button" class="btn btn-primary" @click="save">Speichern</button>
			</template>
		</bs-modal>`
}