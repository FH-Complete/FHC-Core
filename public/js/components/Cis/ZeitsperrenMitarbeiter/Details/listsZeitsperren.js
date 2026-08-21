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
					'stg',
					'ma'
				].includes(value)
			}
		},
		tage: {
			type: String
		},
		id: {
			type: String
		},
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
			showTable: true,
			fullName: null,
			maUid: null,
			validStgs: [],
			validOes: []
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
			this.$router.push({
				name: 'ZeitsperrenMa',
				params: {
					type: this.$route.params.type,
					id: oe,
					days: days
				}
			});
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
			this.$router.push({
				name: 'ZeitsperrenMa',
				params: {
					type: this.$route.params.type,
					id: stg,
					days: days
				}
			});
			this.$api
				.call(ApiMaTimelocks.loadZeitsperrenLectorStg(days, stg))
				.then(result => {
					this.arrayMaTimelocks = result.data;
				})
		},
		loadZeitsperrenMa(days, uid){
			this.$api
				.call(ApiMaTimelocks.loadZeitsperrenMa(days, uid))
				.then(result => {
					this.arrayMaTimelocks = result.data;
				})
		},
		createDays(countDays) {
			const today = new Date();
			//get Monday before today
			const mondayBeforeToday = new Date(today);
			const day = today.getDay();
			const diff = day === 0 ? 6 : day - 1;

			mondayBeforeToday.setDate(today.getDate() - diff);

			this.days = Array.from({ length: countDays + 1 }, (_, i) => {
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
					if(this.id && this.isZifferString(this.id))
						this.interval = Number(this.id) < 100 ? Number(this.id) : this.interval;

					this.loadZeitsperrenFixeMa(this.interval);
				}
				else if(type == "lector") {
					if(this.id && this.isZifferString(this.id))
						this.interval = Number(this.id) < 100 ? Number(this.id) : this.interval;
					this.loadZeitsperrenLector(this.interval);
				}
				else if(type == "oe") {
					this.loadOes().then(() => {
						if(this.id && this.validOes.includes(this.id)) {
							const countDays = this.tage ? Number(this.tage) : this.interval;

							this.oe_temp = this.listOes.filter(
								item => item.oe_kurzbz === this.id
							);
							this.selectedOe = this.oe_temp[0];

							this.loadZeitsperrenOE(
								countDays,
								this.selectedOe.oe_kurzbz
							);
						} else {
							if(this.id)
								this.interval = Number(this.id) < 100
									? Number(this.id)
									: this.interval;

							this.showTable = false;
							this.selectedOe = null;
						}
					});
				}
				else if(type == "stg") {
					this.loadStgs().then(() => {
						if(this.id && this.validStgs.includes(Number(this.id)))
						{
							const countDays= this.tage ? Number(this.tage) : this.interval;
							this.stg_temp = this.listStg.filter(
								item => item.studiengang_kz === Number(this.id)
							);
							this.selectedStg = this.stg_temp[0];
							this.loadZeitsperrenStg(countDays, this.id)
						}
						else
						{
							if(this.id && this.isZifferString(this.id))
								this.interval = Number(this.id) < 100 ? Number(this.id) : this.interval;
							this.showTable = false;
							this.selectedStg = null;
						}
					});
				}
				else if(type == "all") {
					if(this.id && this.isZifferString(this.id))
						this.interval = Number(this.id) < 100 ? Number(this.id) : this.interval;
					this.loadAllActiveZeitsperren(this.interval);
				}
				else if(type == "ass") {
					if(this.id && this.isZifferString(this.id))
						this.interval = Number(this.id) < 100 ? Number(this.id) : this.interval;
					this.loadZeitsperrenAss(this.interval);
				}
				else if(type == "ma") {
					if(!this.id)
						this.$fhcAlert.alertError(this.$p.t('ui', 'error_missingId', {id: "mitarbeiteruid"}));
					const countDays= this.tage ? this.tage : this.interval;
					this.loadZeitsperrenMa(countDays, this.id);
					this.loadDetailsMa(this.id);
				}
				else
				{
					//route back if no type found
					this.$router.push({
						name: 'ZeitsperrenMa',
					});
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
			return (
			FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				"/Cis/Profil/View/"+
				uid
			);
		},
		loadDetailsMa(uid){
			if(!uid)
				this.$fhcAlert.alertError(this.$p.t('ui', 'error_fieldNotFound', {field: "UID Mitarbeiter"}));

			this.$api
				.call(ApiMaTimelocks.getDetailsMa(uid))
				.then(result => {
					this.fullName = result.data;
				})
		},
		isZifferString(wert) {
			//to check if the wert of the route param is a ziffer or a real String
			return typeof wert === 'string' && wert.trim() !== '' && !isNaN(wert);
		},
		async loadOes() {
			try {
				const result = await this.$api.call(ApiMaTimelocks.getAllOes());
				this.listOes = result.data.filter(item => item.aktiv);
				this.validOes = this.listOes.map(item => item.oe_kurzbz);
			} catch (e) {
				this.$fhcAlert.handleSystemError(e);
			}
			return this.validOes;
		},
		async loadStgs() {
			try {
				const result = await this.$api.call(ApiMaTimelocks.getAllStg());
				this.listStg = result.data;
				this.validStgs = this.listStg.map(item => item.studiengang_kz);
			} catch (e) {
				this.$fhcAlert.handleSystemError(e);
			}
			return this.validOes;
		},
		checkRoute(){
			if (this.type === 'ma' && !this.id) {
				//const errorString = this.$p.t('ui', 'successSave');
				const errorString = "Keine ID für Mitarbeiter übergeben";
				return errorString;
			}
			if (this.type === 'stg' && !this.isZifferString(this.id)) {
				//const errorString = this.$p.t('ui', 'successSave');
				const errorString = "Studiengangskennzahl darf nur aus Zahlen bestehen";
				return errorString;
			}
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
					else if(this.type == "stg") {
						if(this.selectedStg )
							this.loadZeitsperrenStg(newVal, this.studiengang_kz);
					}
					else if(this.type == "ma") {
						this.loadZeitsperrenMa(newVal, this.maUid);
					}
					else
					{
						this.$fhcAlert.alertError(this.$p.t('ui', 'error_fieldNotFound', {field: "Typ Zeitsperre"}));
					}
					this.createDays(newVal);
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
		const error = this.checkRoute();
		if (error) {
			console.log(error);
			this.$fhcAlert.alertError(error);
		return;
		}

		this.createArrayMa(this.type);
		if(this.tage && Number(this.tage) < 100) {
			this.createDays(Number(this.tage));
			this.interval = Number(this.tage);
		}
		else
			this.createDays(this.interval);

		 if (this.type === 'oe') {
			this.oe_kurzbz = this.id
		} else if (this.type === 'stg'){
			this.studiengang_kz = this.id;
		}
	},
	template: `
	<div class="zeitsperrenma-container">

		<!-- header-->
		<div class="zeitsperrenma-header">
			<h4 class="mt-3"><span v-if="type=='all'">{{$p.t('zeitsperren/title_allMa')}}</span>
				<span v-if="type=='fix'">{{$p.t('zeitsperren/title_fix')}}</span>
				<span v-if="type=='lector'">{{$p.t('zeitsperren/title_fixLecturers')}}</span>
				<span v-if="type=='oe'">{{$p.t('zeitsperren/title_byOe')}}</span>
				<span v-if="type=='stg'">{{$p.t('zeitsperren/title_lectStg')}}</span>
				<span v-if="type=='ass'">{{$p.t('zeitsperren/title_ass')}}</span>
				<span v-if="type=='ma'">{{$p.t('zeitsperren/zeitsperrenVon')}} {{fullName}}</span>
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
				<label v-if="type=='stg'">{{$p.t('zeitsperren/lektor_innen')}}</label>
				<div v-if="type=='stg'" class="flex-grow-1">
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

		<!-- table-->
		<div class="zeitsperrenma-content table-scroll">
			<table v-if="showTable" class="table table-striped table-bordered">
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
	</div>
	`,
}