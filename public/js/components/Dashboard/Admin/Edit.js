import BsConfirm from '../../Bootstrap/Confirm.js';

export default {
	emits: [
		"change",
		"delete"
	],
	props: {
		dashboard_id: Number,
		dashboard_kurzbz: String,
		beschreibung: String
	},
	data() {
		return {
			kurzbz: this.dashboard_kurzbz,
			desc: this.beschreibung
		}
	},
	methods: {
		sendDelete() {
			BsConfirm.popup('Sure?').then(() => this.$emit('delete', this.dashboard_id)).catch();
		}
	},
	template: `<div class="dashboard-admin-edit px-3">
		<div class="mb-3">
			<label for="dashboard-admin-edit-kurzbz">Kurz Bezeichnung</label>
			<input id="dashboard-admin-edit-kurzbz" type="text" class="form-control" v-model="kurzbz">
		</div>
		<div class="mb-3">
			<label for="dashboard-admin-edit-beschreibung">Beschreibung</label>
			<textarea id="dashboard-admin-edit-beschreibung" class="form-control" v-model="desc"></textarea>
		</div>
		<div>
			<button class="btn btn-danger" @click="sendDelete">Delete</button>
			<button class="btn btn-primary" @click="$emit('change', {dashboard_id,dashboard_kurzbz:kurzbz,beschreibung:desc})">Update</button>
		</div>
	</div>`
}
