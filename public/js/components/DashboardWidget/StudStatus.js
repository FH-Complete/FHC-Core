import AbstractWidget from './Abstract.js';

import ApiStudstatus from '../../api/factory/widget/studstatus.js';

export default {
	name: "WidgetStudstatus",
	mixins: [AbstractWidget],
	data(){
		return {
			statusData: [],
			role: 0
		};
	},
	mounted(){
		this.getTodos();
	},
	computed: {
		countTodosLeitung(){
			return this.statusData.length;
		},
		countTodosAss(){
			return this.statusData.reduce(
				(sum, item) => sum + item.anzahl,
				0
			);
		},
		linkStudstatus(){
			return (
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				"/lehre/Studierendenantrag/leitung/"
			);
		},
	},
	methods: {
		getTodos(){
			this.$api
				.call(ApiStudstatus.getTodos())
				.then(result => {
					this.statusData = result.data[0];
					this.role = result.data[1];
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
	template:/*html*/`
	<div class="p-3 w-100 h-100 overflow-auto" style="padding: 1rem 1rem;">
		<div v-if="role== 0">
			{{$p.t('dashboard','noPermissionStudstatus')}}
		</div>
		<div v-else-if="role==1 && countTodosLeitung">
			<p><span class="fw-bold">{{countTodosLeitung}} {{$p.t('dashboard','antraegeToEdit')}}: </span> </p>

			<div v-for="status in statusData">
				<span class="fw-bold">{{status.typ}}</span> {{status.vorname}} {{status.nachname}} ({{status.prestudent_id}})
			</div>
		</div>
		<div v-else-if="role==2 && countTodosAss">
			<span class="fw-bold">{{countTodosAss}} {{$p.t('dashboard','antraegeOpen')}}:</span>
			<br><br>

			<div v-for="status in statusData">
				{{status.studiengang}} ({{status.anzahl}})
			</div>
		</div>
		<div v-else>
			{{$p.t('dashboard','antraegeNoOpen')}}
		</div>

		<div v-if="role!=0" class="mt-3">
			<a :href="linkStudstatus" target='blank'>{{$p.t('dashboard','linkVerwaltungStudstatus')}}</a>
		</div>
	</div>
	`
};