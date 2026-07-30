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
					'all',
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
			studiengang_kz: null,
			showTable: true
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
			this.$api
				.call(ApiMaTimelocks.loadAllZeitsperrenOE(days, oe))
				.then(result => {
					this.arrayMaTimelocks = result.data;
				})
		},
		loadZeitsperrenAss(days){
			this.$api
				.call(ApiMaTimelocks.loadZeitsperrenAss(days))
				.then(result => {
					this.arrayMaTimelocks = result.data;
				})
		},
		loadZeitsperrenStg(days, stg){
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
		createArrayMa(type){
			this.showTable = true;
			if(type == "fix") {
				this.loadZeitsperrenFixeMa(this.interval);
			}
			else if(type == "lector") {
				this.loadZeitsperrenLector(this.interval);
			}
			else if(type == "oe") {
				this.showTable = false;
				this.selectedOe = null;
				this.$api
					.call(ApiMaTimelocks.getAllOes())
					.then(result => {
						this.listOes = result.data;

					})
					.catch(this.$fhcAlert.handleSystemError);
			}
			else if(type == "lecStg") {
				this.showTable = false;
				this.selectedStg = null;
				this.$api
					.call(ApiMaTimelocks.getAllStg())
					.then(result => {
						this.listStg = result.data;
					})
					.catch(this.$fhcAlert.handleSystemError);
			}
			else if(type == "all") {
				this.loadAllActiveZeitsperren(this.interval);
			}
			else if(type == "ass") {
				this.loadZeitsperrenAss(this.interval);
			}
			else
			{
				this.$fhcAlert.alertError(this.$p.t('ui', 'error_fieldNotFound', {field: "Typ Zeitsperre"}));
			}
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
		link(uid) {

			//link to new profile
			return (
			FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				"/Cis/Profil/View/"+
				uid
			);

			//link to old profile
/*			return (
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				"cis/private/profile/index.php?uid=" +
				uid
			);*/
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
						this.loadZeitsperrenFixeMa(newVal);
					}
					else if(this.type == "lector") {
						this.loadZeitsperrenLector(newVal);
					}
					else if(this.type == "oe") {
						if(this.oe_kurzbz && this.oe_kurzbz !="")
							this.loadZeitsperrenOE(newVal, this.oe_kurzbz);
					}
					else if(this.type == "all")
					{
						this.loadAllActiveZeitsperren(newVal);
					}
					else if(this.type == "ass") {
						this.loadZeitsperrenAss(newVal);
					}
					else
					{
						this.$fhcAlert.alertError(this.$p.t('ui', 'error_fieldNotFound', {field: "Typ Zeitsperre"}));
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
				if(newVal != "") {
					this.loadZeitsperrenOE(this.interval, newVal);
					this.showTable = true;
				}
			}
		},
		selectedStg(newVal) {
			this.studiengang_kz = newVal?.studiengang_kz || '';
		},
		studiengang_kz(newVal){
			{
				if(newVal != "") {
					this.loadZeitsperrenStg(this.interval, newVal);
					this.showTable = true;
				}
			}
		},
		type(newVal){
			{
				if(newVal != "")
					this.createArrayMa(newVal);
			}
		},
	},
	created(){
		this.createArrayMa(this.type);
		this.createDays();
	},
	template: `
	<div class="w-100 h-100">
		<div class="sticky-top bg-white p-3 border-bottom">
			<h4 class="mt-3"><span v-if="type=='all'">{{$p.t('zeitsperren/title_allMa')}}</span>
				<span v-if="type=='fix'">{{$p.t('zeitsperren/title_fix')}}</span>
				<span v-if="type=='lector'">{{$p.t('zeitsperren/title_fixLecturers')}}</span>
				<span v-if="type=='oe'">{{$p.t('zeitsperren/title_byOe')}}</span>
				<span v-if="type=='lecStg'">{{$p.t('zeitsperren/title_lectStg')}}</span>
				<span v-if="type=='ass'">{{$p.t('zeitsperren/title_ass')}}</span>
				{{ mondayBeforeToday.toLocaleDateString('de-AT') }} - {{endInterval}}
			</h4>
			<div class="d-flex align-items-center gap-2 mb-3">
				<label for="days">Anzahl Tage:</label>
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
					<form-form class="g-3" ref="oeSelectForm">
						<form-input
							container-class="w-50"
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
				<label v-if="type=='lecStg'">{{$p.t('zeitsperren/lektor_innen')}}</label>
				<div v-if="type=='lecStg'" class="flex-grow-1">
					<form-form class="g-3" ref="oeSelectForm">
						<form-input
							container-class="w-50"
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
		</div>

		<table v-if="showTable" class="table table-dark table-striped table-bordered">
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
							{{$p.t('zeitsperren/abwesend')}}
							<div v-if="sperre.kurzbz">
								V: <a :href="link(sperre.vertretung_uid)">
								{{ sperre.kurzbz }}
							</a>
							</div>
							<div v-if="sperre.erreichbarkeit_kurzbz">E: {{ sperre.erreichbarkeit_kurzbz }}</div>
						</template>
					</td>
				</tr>
			</tbody>
		</table>

		<div v-if="!arrayMaTimelocks.length">
			<p>{{$p.t('ui/keineEintraegeGefunden')}}</p>
		</div>
	</div>
	`,
}