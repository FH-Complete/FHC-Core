import ActionsNew from './Actions/New.js';
import ActionsColumns from './Actions/Columns.js';

export default {
	components: {
		ActionsNew,
		ActionsColumns
	},
	props: {
		selectedData: Array,
		columns: Array,
		stgL: Array,
		stgA: Array
	},
	emits: [
		'reload',
		'download',
		'action:approve',
		'action:reject',
		'action:reopen'
	],
	data() {
		return {
			currentStudent: ''
		}
	},
	computed: {
		selectedCanBeApproved() {
			if (!this.selectedData.length)
				return false;
			if (!this.selectedData.every(val => this.stgL.includes(val.studiengang_kz)))
				return false;
			return this.selectedData.filter(row => {
				return (row.typ == 'Wiederholung' && row.status == 'Lvszugewiesen') || (row.typ != 'Wiederholung' && (row.status == 'Erstellt' || row.status == 'ErstelltStgl'));
			}).length == this.selectedData.length;
		},
		selectedCanBeRejected() {
			if (!this.selectedData.length)
				return false;
			if (!this.selectedData.every(val => this.stgL.includes(val.studiengang_kz)))
				return false;
			return this.selectedData.filter(row => {
				return (row.typ == 'Unterbrechung' && row.status == 'Erstellt');
			}).length == this.selectedData.length;
		},
		selectedCanBeReopened() {
			if (!this.selectedData.length)
				return false;
			if (!this.selectedData.every(val => this.stgA.includes(val.studiengang_kz)))
				return false;
			return this.selectedData.filter(row => {
				return (row.typ == 'Wiederholung' && row.status == 'Verzichtet');
			}).length == this.selectedData.length;
		},
		newUrl() {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + this.currentStudent;
		}
	},
	methods: {
		hideModal() {
			bootstrap.Modal.getInstance(this.$refs.modal).hide();
		}
	},
	template: `
	<div class="studierendenantrag-leitung-actions fhc-table-actions d-flex flex-wrap justify-content-between mb-2">
		<div class="d-flex align-items-center gap-2">
			<actions-new @reload="$emit('reload')"></actions-new>
			<button type="button" class="btn btn-outline-secondary" @click="$emit('reload')" :title="$p.t('table','reload')">
				<i class="fa-solid fa-rotate-right"></i>
			</button>
			<span>{{$p.t('table', 'with_selected', {count: selectedData.length})}}</span>
			<button v-if="stgL.length" :disabled="!selectedCanBeApproved" type="button" class="btn btn-outline-secondary" @click="$emit('action:approve')">{{$p.t('studierendenantrag', 'btn_approve')}}</button>
			<button v-if="stgL.length" :disabled="!selectedCanBeRejected" type="button" class="btn btn-outline-secondary" @click="$emit('action:reject')">{{$p.t('studierendenantrag', 'btn_reject')}}</button>
			<button v-if="stgA.length" :disabled="!selectedCanBeReopened" type="button" class="btn btn-outline-secondary" @click="$emit('action:reopen')">{{$p.t('studierendenantrag', 'btn_reopen')}}</button>
		</div>
		<div>
			<button type="button" class="btn btn-link" data-bs-toggle="collapse" href="#columns" :title="$p.t('table','spaltenEinAusblenden')"><i class="fa fa-table-columns"></i></button>
			<button type="button" class="btn btn-link" @click="$emit('download')" :title="$p.t('table','download')"><i class="fa fa-download"></i></button>
		</div>
		<div class="col-12">
			<actions-columns id="columns" class="collapse" :columns="columns"></actions-columns>
		</div>
	</div>
	`
}
