import {CoreFilterCmpt} from "../../../filter/Filter.js";
import ApiMaTimelocks from "../../../../api/factory/zeitsperren.js";
import FormForm from "../../../Form/Form.js";
import FormInput from "../../../Form/Input.js";
import PvAutoComplete from "../../../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";

export default {
	name: "listsTimelocks",
	components: {
		FormForm,
		FormInput,
		PvAutoComplete,
		CoreFilterCmpt
	},
	props: {
		type: {
			type: String,
			default: 'all',
			validator(value) {
				return [
					'fix',
					'lector',
					'oe',
					'ass',
					'lecStg'
				].includes(value)
			}
		}
	},
	data(){
		return {
			interval: 14,
			days: {},
			arrayMaTimelocks: [],
			listOes: [],
			filteredOes: [],
			selectedOe: null,
			oe_kurzbz: null,
			listStg: [],
			filteredStg: [],
			selectedStg: null,
			studiengang_kz: null
		}
	},
	methods: {
		loadAllActiveZeitsperren(days){
			this.$api
				.call(ApiMaTimelocks.loadAllActiveZeitsperren(days))
				.then(result => {
					this.arrayMaTimelocks = result.data;
				})
		},
		loadZeitsperrenFixeMa(days){
			this.$api
				.call(ApiMaTimelocks.loadAllZeitsperrenFixeMa(days))
				.then(result => {
					this.arrayMaTimelocks = result.data;
				})
		},
		loadZeitsperrenLector(days){
			this.$api
				.call(ApiMaTimelocks.loadAllZeitsperrenLector(days))
				.then(result => {
					this.arrayMaTimelocks = result.data;
				})
		},
		loadZeitsperrenOE(days, oe){
			console.log("in function oe " + oe);
			this.$api
				.call(ApiMaTimelocks.loadAllZeitsperrenOE(days, oe))
				.then(result => {
					this.arrayMaTimelocks = result.data;
				})
		},
		loadZeitsperrenAss(days){
			console.log("in ASS ");
			this.$api
				.call(ApiMaTimelocks.loadZeitsperrenAss(days))
				.then(result => {
					this.arrayMaTimelocks = result.data;
				})
		},
		loadZeitsperrenStg(days, stg){
			console.log("in function stg " + stg);
			this.$api
				.call(ApiMaTimelocks.loadZeitsperrenLectorStg(days, stg))
				.then(result => {
					this.arrayMaTimelocks = result.data;
				})
		},
		createDays() {
			const today = new Date();

			//get Monday before today
			const mondayBeforeToday = new Date(today);
			const day = today.getDay();
			const diff = day === 0 ? 6 : day - 1;

			mondayBeforeToday.setDate(today.getDate() - diff);

			this.days = Array.from({ length: this.interval+1 }, (_, i) => {
				const date = new Date(mondayBeforeToday);
				date.setDate(mondayBeforeToday.getDate() + i);

				return {
					date: date.toISOString().substring(0, 10), // 2026-07-13
					weekday: date.toLocaleDateString('de-AT', { weekday: 'short' }),
					day: date.toLocaleDateString('de-AT', {
						day: '2-digit',
						month: '2-digit'
					})
				};
			});
		},
		isBlocked(mitarbeiter, tag) {
			if (!mitarbeiter?.sperren) {
				return false;
			}

			return mitarbeiter.sperren.some(s =>
				tag >= s.vondatum &&
				tag <= s.bisdatum
			);
		},
		getSperre(mitarbeiter, tag){
			return mitarbeiter.sperren.find(s =>
				tag >= s.vondatum &&
				tag <= s.bisdatum
			);
		},
		limitDays(event) {
			let value = event.target.value.replace(/\D/g, '').slice(0, 2);
			event.target.value = value;
			this.interval = value ? Number(value) : null;
		},
		filterOes(event) {
			const query = event.query.toLowerCase();

			this.filteredOes = this.listOes.filter(item =>
				item.label.toLowerCase().includes(query)
			);
		},
		filterStg(event) {
			const query = event.query.toLowerCase();

			this.filteredStg = this.listStg.filter(item =>
				item.label.toLowerCase().includes(query)
			);
		},
		//TODO(Manu) check link profile
		link(uid) {
			//https://www.fhcomplete.local/cis.php/Cis/Profil/getView/bell
			return (
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				"/Cis/Profil/getView/" +
				uid
			);
		}
	},
	computed: {
		today(){
			const today = new Date();
			const day = today.toLocaleDateString('de-AT', {
				day: '2-digit',
				month: '2-digit',
				year: 'numeric'
			});
			return day;
		},
		mondayBeforeToday(){
			const today = new Date();
			//get Monday before today
			const mondayBeforeToday = new Date(today);
			const day = today.getDay();
			const diff = day === 0 ? 6 : day - 1;

			mondayBeforeToday.setDate(today.getDate() - diff);

			return mondayBeforeToday;
		},
		endInterval(){
			const today = new Date();
			const end = new Date(this.mondayBeforeToday);
			end.setDate(end.getDate() + this.interval);

			return end.toLocaleDateString('de-AT', {
				day: '2-digit',
				month: '2-digit',
				year: 'numeric'
			});
		},
		mitarbeiter() {
			const map = {};

			//vor version with uid in query
			this.arrayMaTimelocks.forEach(z => {
				if (!map[z.uid]) {
					map[z.uid] = [];
				}

				map[z.uid].push(z);
			});

			return Object.entries(map).map(([mitarbeiter_uid, sperren]) => ({
				mitarbeiter_uid,
				sperren
			}));
		},
	},
	watch: {
		interval: {
			handler(newVal) {
				if (newVal) {
					if(this.type == "fix") {
						console.log("fix");
						this.loadZeitsperrenFixeMa(newVal);
					}
					else if(this.type == "lector") {
						console.log("lector");
						this.loadZeitsperrenLector(newVal);
					}
					else if(this.type == "oe") {
						console.log("oe: in handler");
						if(this.oe_kurzbz && this.oe_kurzbz !="")
							this.loadZeitsperrenOE(newVal, this.oe_kurzbz);
						else
							console.log("Bitte OE auswählen");
					}
					else if(this.type == "all")
					{
						console.log("all active");
						this.loadAllActiveZeitsperren(newVal);
					}
					else if(this.type == "ass") {
						console.log("ass");
						this.loadZeitsperrenAss(newVal);
					}
					else
					{
						console.log("not defined");
					}
					this.createDays();
				}
			},
			deep: true,
		},
		selectedOe(newVal) {
			this.oe_kurzbz = newVal?.oe_kurzbz || '';
		},
		oe_kurzbz(newVal){
			{
				if(newVal != "")
					this.loadZeitsperrenOE(this.interval, newVal);
			}
		},
		selectedStg(newVal) {
			this.studiengang_kz = newVal?.studiengang_kz || '';
		},
		studiengang_kz(newVal){
			{
				console.log("STG triggered");
				if(newVal != "")
					this.loadZeitsperrenStg(this.interval, newVal);
			}
		},
	},
	created(){
		if(this.type == "fix") {
			console.log("fix");
			this.loadZeitsperrenFixeMa(this.interval);
		}
		else if(this.type == "lector") {
			console.log("lector");
			this.loadZeitsperrenLector(this.interval);
		}
		else if(this.type == "oe") {
			console.log("oeg");
			this.$api
				.call(ApiMaTimelocks.getAllOes())
				.then(result => {
					this.listOes = result.data;
/*					this.listStg = this.listOes.filter(item =>
						item.label.includes("[Studiengang]") && item.aktiv || item.label.includes("[Lehrgang]") && item.aktiv
					);*/
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
		else if(this.type == "lecStg") {
			console.log("lecStg");
			this.$api
				.call(ApiMaTimelocks.getAllStg())
				.then(result => {
					this.listStg = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
		else if(this.type == "all") {
			console.log("all active");
			this.loadAllActiveZeitsperren(this.interval);
		}
		else if(this.type == "ass") {
			console.log("ass");
			this.loadZeitsperrenAss(this.interval);
		}
		else
		{
			console.log("not defined");
		}
		this.createDays();

/*		console.log('adams, 2026-07-16');
		console.log(this.isBlocked('adams', '2026-07-16'));*/
	},
	template: `
	<div class="w-100 h-100">
	type: {{type}}
		<h3 class="mt-3"><span v-if="type=='all'">Mitarbeiter*innen mit aktuellen </span>Zeitsperren 
			<span v-if="type=='fix'">Fixangestellte</span>
			<span v-if="type=='lector'">aller fixer Lektoren</span> 
			<span v-if="type=='oe'">nach Organisationseinheit</span> {{ mondayBeforeToday.toLocaleDateString('de-AT') }} - {{endInterval}}			
		</h3>
				<div class="d-flex align-items-center gap-2">
					<label for="days" class="mb-0">Anzahl Tage:</label>
				
					<input
					  id="days"
					  v-model.number="interval"
					  type="number"
					  class="form-control"
					  style="width: 90px"
					  min="1"
					  max="99"
					  @input="limitDays"
					>
					<label v-if="type=='oe'">{{$p.t('lehre/organisationseinheit')}}</label>
					<div v-if="type=='oe'" class="flex-grow-1">
						<form-form class="g-3 mt-2" ref="oeSelectForm">
							<form-input
								container-class="mb-3 w-50"
								type="autocomplete"
								name="oe_kurzbz"
								v-model="selectedOe"
								forceSelection
								optionLabel="label"
								optionValue="oe_kurzbz"
								:suggestions="filteredOes"
								dropdown
								@complete="filterOes"
								>
									<template #option="slotProps">
										<div
											:class="!slotProps.option.aktiv
											? 'item-inactive'
											: ''"
											>
												{{slotProps.option.label}}
										</div>
									</template>
							</form-input>
						</form-form>
					</div>
					
					<label v-if="type=='lecStg'">{{$p.t('studium/lektoren')}}</label>
					<div v-if="type=='lecStg'" class="flex-grow-1 mt-2">
						<form-form class="g-3 mt-2" ref="oeSelectForm">
							<form-input
								container-class="mb-3 w-50"
								type="autocomplete"
								name="studiengang_kz"
								v-model="selectedStg"
								forceSelection
								optionLabel="label"
								optionValue="studiengang_kz"
								:suggestions="filteredStg"
								dropdown
								@complete="filterStg"
								>
									<template #option="slotProps">
										<div
											:class="!slotProps.option.aktiv
											? 'item-inactive'
											: ''"
											>
												{{slotProps.option.label}}
										</div>
									</template>
							</form-input>
						</form-form>
					</div>
			  </div>
			  					
		<br> count results: {{mitarbeiter.length}}, days: {{interval}}
		<br> type: {{type}} <br> {{oe_kurzbz}} | {{selectedOe}}
		<br> {{selectedStg}} | {{studiengang_kz}}
		{{arrayMaTimelocks[0]}}
		<hr>
	
		
		
		<table class="table table-dark table-striped table-bordered">
			<thead>
				<tr>
				  <th scope="col"> UID </th>
					<th v-for="day in days" :key="day.date">
						<div>{{ day.weekday }}</div>
						<div>{{ day.day }}</div>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr
					v-for="m in mitarbeiter"
					:key="m.uid"
				>
					<td>{{m.sperren[0].nachname}} {{m.sperren[0].vorname}}</td>
			
					<td
						v-for="day in days"
						:key="day.date"
						:class="{ 'table-warning': day.weekday === 'Sa' || day.weekday === 'So' }"
					>
						<template v-if="sperre = getSperre(m, day.date)">
							abwesend
							<div v-if="sperre.kurzbz">
								V: {{ sperre.kurzbz }}
							</div>
							<a :href="link(sperre.vertretung_uid)">
								{{ sperre.kurzbz }}
							</a>
							<div v-if="sperre.erreichbarkeit_kurzbz">E: {{ sperre.erreichbarkeit_kurzbz }}</div>
						</template>
					</td>
				</tr>
			</tbody>
		</table>
		
		

	</div>
	`,
}