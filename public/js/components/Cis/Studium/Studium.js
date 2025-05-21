import LvUebersicht from "../Mylv/LvUebersicht.js";


export default {
	data(){
		return {
			studienSemester :[],
			selectedStudiensemester: null,
			studiengaenge:[],
			selectedStudiengang:null,
			studienOrdnung: [],
			selectedStudienordnung: null,
			semester:[],
			selectedSemester:null,
			lehrveranstaltungen: [],
			selectedLehrveranstaltung: null,
			menu:null,
		}
	},
	provide(){
		return {
			studium_studiengang : Vue.computed(()=> this.selectedStudiengang),
			studium_studiensemester: Vue.computed(() => this.selectedStudiensemester),
			studium_semester: Vue.computed(() => this.selectedSemester),
			studium_studienordnung: Vue.computed(() => this.selectedStudienordnung),

		}
	},
	components: {
		LvUebersicht,
	},
	watch:{
		selectedStudiensemester: function(newVal, oldVal){
			if(newVal != oldVal){
				const studiensemester =this.getDataFromLocalStorage("sudiensemester");
				if (!studiensemester || (studiensemester && studiensemester != newVal)){
					this.storeDataToLocalStorage("sudiensemester", newVal);
				}
			}
		},
		selectedSemester: function (newVal, oldVal) {
			if (newVal != oldVal) {
				const semester = this.getDataFromLocalStorage("semester");
				if (!semester || (semester && semester != newVal)) {
					this.storeDataToLocalStorage("semester", newVal);
				}
			}
		},
		selectedStudiengang: function (newVal, oldVal) {
			if (newVal != oldVal) {
				const studiengang = this.getDataFromLocalStorage("studiengang");
				if (!studiengang || (studiengang && studiengang != newVal)) {
					this.storeDataToLocalStorage("studiengang", JSON.stringify(newVal));
				}
			}
		},
		selectedStudienordnung: function (newVal, oldVal) {
			if (newVal != oldVal) {
				const studienordnung = this.getDataFromLocalStorage("studienordnung");
				if (!studienordnung || (studienordnung && studienordnung != newVal)) {
					this.storeDataToLocalStorage("studienordnung", JSON.stringify(newVal));
				}
			}
		},
	},
	methods:{
		changeStudiensemester(value){
			let studiensemester = this.$refs.studiensemester;
			studiensemester.selectedIndex = (studiensemester.selectedIndex + value + studiensemester.options.length) % studiensemester.options.length;
			this.changeSelectedStudienSemester(studiensemester.value);
		},
		changeStudiengang(value) {
			let studiengang = this.$refs.studiengaenge;
			studiengang.selectedIndex = (studiengang.selectedIndex + value + studiengang.options.length) % studiengang.options.length;
			this.changeSelectedStudienGang(studiengang.value);
		},
		changeSemester(value) {
			let semester = this.$refs.semester;
			semester.selectedIndex = (semester.selectedIndex + value + semester.options.length) % semester.options.length;
			this.changeSelectedSemester(semester.value);
		},
		changeStudienordnung(value) {
			let studienordnung = this.$refs.studienordnung;
			studienordnung.selectedIndex = (studienordnung.selectedIndex + value + studienordnung.options.length) % studienordnung.options.length;
			this.changeSelectedStudienPlan(studienordnung.value);
		},
		
		storeDataToLocalStorage(key,value){
			localStorage.setItem(key, value);
		},
		getDataFromLocalStorage(key){
			const value = localStorage.getItem(key);
			return value;
		},
		changeSelectedStudienSemester(studiensemester_kurzbz) {
			this.$fhcApi.factory.studium.getAllStudienSemester(studiensemester_kurzbz, this.selectedStudiengang, this.selectedSemester, this.selectedStudienordnung)
				.then(data => data.data)
				.then(res => {
					this.extractPropertyValues(res);
				})
		},
		changeSelectedStudienGang(studiengang_kz) {
			this.$fhcApi.factory.studium.getAllStudienSemester(this.selectedStudiensemester, studiengang_kz, this.selectedSemester, this.selectedStudienordnung)
				.then(data => data.data)
				.then(res => {
					this.extractPropertyValues(res);
				})
		},
		changeSelectedSemester(semester) {
			this.$fhcApi.factory.studium.getAllStudienSemester(this.selectedStudiensemester, this.selectedStudiengang, semester, this.selectedStudienordnung)
				.then(data => data.data)
				.then(res => {
					this.extractPropertyValues(res);
				})
		},
		changeSelectedStudienPlan(studienplan_id) {
			this.$fhcApi.factory.studium.getAllStudienSemester(this.selectedStudiensemester, this.selectedStudiengang, this.selectedSemester, studienplan_id)
				.then(data => data.data)
				.then(res => {
					this.extractPropertyValues(res);
				})
		},
		openLvUebersicht(lehrveranstaltung) {
			this.selectedLehrveranstaltung = lehrveranstaltung;
			//convert lehrveranstaltung properties for compatibility with Stundenplan LvModal
			this.selectedLehrveranstaltung.type ="lehreinheit";
			this.selectedLehrveranstaltung.lehreinheit_id = this.selectedLehrveranstaltung.lehrveranstaltung_id;
			if(this.selectedLehrveranstaltung){
				Vue.nextTick(()=>{
					this.$refs.lvUebersicht.show();
				})
			}
			
		},
		sortStudienSemester(studienSemester){
			let regex = new RegExp(/^(WS|SS)([0-9]{4})/);
			studienSemester.sort((sem1,sem2)=>{
				let [sem1Match, sem1Semester, sem1Year] = sem1.studiensemester_kurzbz.match(regex);
				let [sem2Match, sem2Semester, sem2Year] = sem2.studiensemester_kurzbz.match(regex);
				if(sem1Year == sem2Year){
					return sem1Semester > sem2Semester? -1:1;
				}
				return sem1Year > sem2Year? -1:1;
			})
		},
		setHash(val) {
			// TODO: make this a router param to enable history
			location.hash = val;
		},
		extractPropertyValues(res){
			let { studienSemester, studiengang, semester, studienplan, lehrveranstaltungen } = res;
			this.sortStudienSemester(studienSemester.all);
			this.studienSemester = studienSemester.all;
			this.selectedStudiensemester = studienSemester.preselected.studiensemester_kurzbz;

			this.studiengaenge = studiengang.all;
			this.selectedStudiengang = studiengang.preselected?.studiengang_kz;

			this.semester = semester.all;
			this.selectedSemester = semester?.preselected;

			this.studienOrdnung = studienplan.all;
			this.selectedStudienordnung = studienplan.preselected?.studienplan_id;

			this.lehrveranstaltungen = lehrveranstaltungen;
			this.lehrveranstaltungen.sort((lv1, lv2) => {
				if (lv1.bezeichnung.toLowerCase() > lv2.bezeichnung.toLowerCase()) {
					return 1;
				} else if (lv1.bezeichnung.toLowerCase() < lv2.bezeichnung.toLowerCase()) {
					return -1;
				} else {
					return 0;
				}
			});

			this.lehrveranstaltungen.forEach((lehrveranstaltung)=>{
				lehrveranstaltung.lehrveranstaltungen.sort((lv1,lv2)=>{
					if (lv1.bezeichnung.toLowerCase() > lv2.bezeichnung.toLowerCase()) {
						return 1;
					} else if (lv1.bezeichnung.toLowerCase() < lv2.bezeichnung.toLowerCase()) {
						return -1;
					} else {
						return 0;
					}
				})
			})
		},
		studienordnungTitel(studienordnung){
			if(!studienordnung) return "";
			return `${studienordnung?.bezeichnung}-${studienordnung?.orgform_kurzbz} ( ${studienordnung?.orgform_bezeichnung}, ${studienordnung?.sprache} )`;
		},
		studiengangTitel(studiengang) {
			if (!studiengang) return "";
			return `${studiengang?.kurzbzlang} (${studiengang?.bezeichnung})`;
		},
		studiensemesterTitel(studiensemester){
			if (!studiensemester) return "";
			let studiensemester_regex = new RegExp(/^(WS|SS)([0-9]{4})/);
			let match = studiensemester.match(studiensemester_regex);
			switch(match[1]){
				case "WS":
					return `Wintersemester ${match[2]}`;
				case "SS":
					return `Sommersemester ${match[2]}`;	
				default:
					return `${studiensemester}`;
			}
		}
	},

	computed:{
		selectedLehrveranstaltungTitel(){
			const studiengang = this.studiengaenge.find((studiengang) => studiengang.studiengang_kz == this.selectedStudiengang);
			return `${this.selectedLehrveranstaltung?.bezeichnung} ${this.selectedLehrveranstaltung?.lehrform_kurzbz} / ${studiengang.kurzbzlang}-${this.selectedSemester} ${this.selectedLehrveranstaltung?.orgform_kurzbz} (${this.selectedStudiensemester})`;
		},
		computedStudienOrdnung(){
			if(!this.studienOrdnung) return null;
			return Object.values(this.studienOrdnung).reduce((carry, item)=>{
				if(!carry[item.bezeichnung]){
					carry[item.bezeichnung] = [];
				}
				carry[item.bezeichnung].push(item);
				return carry;
			},{});
		},
		computedStudienOrdnungSelectValues() {
			if (!this.computedStudienOrdnung) return null;
			let result = [];
			Object.entries(this.computedStudienOrdnung).forEach(([key,value])=>{
				result.push({
					bezeichnung: `Studienordnung: ${key}`,
					disabled: true,
				});
				value.forEach((studienplan)=>{
					result.push({
						studienplan:studienplan,
						diabled: false,
						bezeichnung: `${studienplan?.bezeichnung}-${studienplan?.orgform_kurzbz} ( ${studienplan?.orgform_bezeichnung}, ${studienplan?.sprache} )`
							
					});
				})
			});
			return result;
		},
	},
	
	created(){

		const studiensemester = this.getDataFromLocalStorage("sudiensemester") ?? undefined;
		const studiengang = JSON.parse(this.getDataFromLocalStorage("studiengang")) ?? undefined;
		const semester = this.getDataFromLocalStorage("semester") ?? undefined;
		const studienordnung = JSON.parse(this.getDataFromLocalStorage("studienordnung")) ?? undefined;

		// only fetch default data if no data is stored in the local storage
		
		this.$fhcApi.factory.studium.getAllStudienSemester(studiensemester, studiengang, semester, studienordnung)
		.then(data => data.data)
		.then(res => {
			this.extractPropertyValues(res);
		})

	},
	template: `
	<div>
	<h2>Studium</h2>
	<hr>
	<lv-uebersicht ref="lvUebersicht" :titel="selectedLehrveranstaltungTitel" :event="selectedLehrveranstaltung" :studiensemester="selectedStudiensemester" v-if="selectedLehrveranstaltung">
		<template #content>
			<div v-if="Array.isArray(selectedLehrveranstaltung.lektoren) && selectedLehrveranstaltung.lektoren.length>0" class="mb-4">
				<h4>Lektoren:</h4>
				<a :href="'mailto:'+lektor?.email" class="mx-2" v-for="lektor in selectedLehrveranstaltung.lektoren">{{lektor.name}}</a>
			</div>
			<h4>Menu:</h4>
		</template>
	</lv-uebersicht>
	<div class="lvOptions">
		<div>
		<h6>Studiensemester:</h6>
		<div class="input-group">
			<button class="btn btn-outline-secondary" type="button" :disabled="false" @click="changeStudiensemester(-1)">
				<i class="fa fa-caret-left" aria-hidden="true"></i>
			</button>
			<select ref="studiensemester" v-model="selectedStudiensemester" class="form-select" :aria-label="$p.t('global/studiensemester_auswaehlen')" @change="setHash($event.target.value)">
				<option v-for="semester in studienSemester" @click="changeSelectedStudienSemester(semester.studiensemester_kurzbz)" :key="semester" :value="semester.studiensemester_kurzbz">{{studiensemesterTitel(semester.studiensemester_kurzbz)	}}</option>
			</select>
			<button class="btn btn-outline-secondary" type="button" :disabled="false" @click="changeStudiensemester(1)">
				<i class="fa fa-caret-right" aria-hidden="true"></i>
			</button>
		</div>
		</div>

		<div>
		<h6>Studiengang:</h6>
		<div class="input-group">
			<button class="btn btn-outline-secondary" type="button" :disabled="false" @click="changeStudiengang(-1)">
				<i class="fa fa-caret-left" aria-hidden="true"></i>
			</button>
			<select ref="studiengaenge" v-model="selectedStudiengang" class="form-select" :aria-label="$p.t('global/studiensemester_auswaehlen')" @change="setHash($event.target.value)">
				<option v-for="studiengang in studiengaenge" @click="changeSelectedStudienGang(studiengang.studiengang_kz)" :key="studiengang.studiengang_kz" :value="studiengang.studiengang_kz" >{{studiengangTitel(studiengang)}}</option>
			</select>
			<button class="btn btn-outline-secondary" type="button" :disabled="false" @click="changeStudiengang(1)">
				<i class="fa fa-caret-right" aria-hidden="true"></i>
			</button>
		</div>
		</div>

		<div>
		<h6>Semester:</h6>
		<div class="input-group">
			<button class="btn btn-outline-secondary" type="button" :disabled="false" @click="changeSemester(-1)">
				<i class="fa fa-caret-left" aria-hidden="true"></i>
			</button>
			<select ref="semester" v-model="selectedSemester" class="form-select" :aria-label="$p.t('global/studiensemester_auswaehlen')" @change="setHash($event.target.value)">
				<option v-for="sem in semester" @click="changeSelectedSemester(sem)" :key="semester" :value="sem">{{sem}}. Semester</option>
			</select>
			<button class="btn btn-outline-secondary" type="button" :disabled="false" @click="changeSemester(1)">
				<i class="fa fa-caret-right" aria-hidden="true"></i>
			</button>
		</div>
		</div>

		<div>
		<h6>Studienordnung:</h6>
		<div class="input-group">
			<button class="btn btn-outline-secondary" type="button" :disabled="false" @click="changeStudienordnung(-1)">
				<i class="fa fa-caret-left" aria-hidden="true"></i>
			</button>
			<select ref="studienordnung" v-model="selectedStudienordnung" class="form-select" :aria-label="$p.t('global/studiensemester_auswaehlen')" @change="setHash($event.target.value)">
				<option v-for="ordnung in computedStudienOrdnungSelectValues" :disabled="ordnung.disabled" @click="changeSelectedStudienPlan(ordnung?.studienplan?.studienplan_id)" :key="ordnung?.studienplan?.bezeichnung	" :value="ordnung?.studienplan?.studienplan_id">{{ordnung.bezeichnung}}</option>
			</select>
			<button class="btn btn-outline-secondary" type="button" :disabled="false" @click="changeStudienordnung(1)">
				<i class="fa fa-caret-right" aria-hidden="true"></i>
			</button>
		</div>
		</div>
	</div>

	<hr>

	<div class="lvUebersicht " >
	<template v-for="lehrveranstaltung in lehrveranstaltungen" :key="lehrveranstaltung.lehrveranstaltung_id">
		<div  class="card" v-if="Array.isArray(lehrveranstaltung.lehrveranstaltungen) && lehrveranstaltung.lehrveranstaltungen.length >0" >
			<div class="card-header">
				<h5 class=" card-title">{{lehrveranstaltung.bezeichnung}}</h5>
				<h6 class=" card-subtitle">{{lehrveranstaltung.lehrform_kurzbz}}</h6>
			</div>
			<div class="card-body">
				<ul class="list-group list-group-flush">
					<li class="d-flex list-group-item" v-for="lv in lehrveranstaltung.lehrveranstaltungen">
						<a class="link-dark d-block me-auto" href="#" @click="openLvUebersicht(lv)">{{lv.bezeichnung}}</a>
						<p>{{lv.lehrform_kurzbz}}</p>
					</li>	
				</ul>
			</div>
		</div>
	</template>
	</div>


	</div>
	
	`
};