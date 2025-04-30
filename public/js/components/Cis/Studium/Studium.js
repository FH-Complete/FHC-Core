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
		}
	},
	methods:{
		changeSelectedStudienSemester(studiensemester) {
			this.$fhcApi.factory.studium.getAllStudienSemester(studiensemester.studiensemester_kurzbz, this.selectedStudiengang, this.selectedSemester, this.selectedStudienordnung)
				.then(data => data.data)
				.then(res => {
					this.extractPropertyValues(res);
				})
		},
		changeSelectedStudienGang(studiengang) {
			this.$fhcApi.factory.studium.getAllStudienSemester(this.selectedStudiensemester, studiengang.studiengang_kz, this.selectedSemester, this.selectedStudienordnung)
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
		changeSelectedStudienPlan(studienplan) {
			this.$fhcApi.factory.studium.getAllStudienSemester(this.selectedStudiensemester, this.selectedStudiengang, this.selectedSemester, studienplan.studienplan_id)
				.then(data => data.data)
				.then(res => {
					this.extractPropertyValues(res);
				})
		},
		/* loadStudienplan(studiengang, studiensemester){
			this.$fhcApi.factory.studium.getStudienplaeneBySemester(studiengang, studiensemester)
				.then(data => data.data)
				.then(res => {
					console.log("This is the result", res)
				})
		}, */
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
			let { studienSemester, studiengang, semester, studienplan } = res;
			this.sortStudienSemester(studienSemester.all);
			this.studienSemester = studienSemester.all;
			this.selectedStudiensemester = studienSemester.preselected.studiensemester_kurzbz;

			this.studiengaenge = studiengang.all;
			this.selectedStudiengang = studiengang.preselected?.studiengang_kz;

			this.semester = semester.all;
			this.selectedSemester = semester?.preselected;

			console.log(studienplan.all,"all studienplan")
			this.studienOrdnung = studienplan.all;
			console.log(this.studienOrdnung,"studienordnung")
			this.selectedStudienordnung = studienplan.preselected?.studienplan_id;
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
		
	},
	
	created(){
		this.$fhcApi.factory.studium.getAllStudienSemester()
		.then(data => data.data)
		.then(res => {
			this.	extractPropertyValues(res);
		})
	},
	/* selectedStudiensemester(newStudiensemester, oldStudiensemester) {
		if (newStudiensemester !== oldStudiensemester) {
			this.$fhcApi.factory.studium.getAllStudienSemester(newStudiensemester, this.selectedStudiengang, this.selectedSemester)
				.then(data => data.data)
				.then(res => {
					this.extractPropertyValues(res);
				})
		}
	},
	selectedStudiengang(newStudiengang, oldStudiengang) {
		if (newStudiengang !== oldStudiengang) {
			this.$fhcApi.factory.studium.getAllStudienSemester(this.selectedStudiensemester, newStudiengang, this.selectedSemester)
				.then(data => data.data)
				.then(res => {
					this.extractPropertyValues(res);
				})
		}
	},
	selectedSemester(newSemester, oldSemester) {
		if (newSemester !== oldSemester) {
			this.$fhcApi.factory.studium.getAllStudienSemester(this.selectedStudiensemester, this.selectedStudiengang, newSemester)
				.then(data => data.data)
				.then(res => {
					this.extractPropertyValues(res);
				})
		}
	} */
	template: `
	<h2>this is a test titel</h2>
	<div class="input-group">
		<button class="btn btn-outline-secondary" type="button" :disabled="false" @click="prevSem">
			<i class="fa fa-caret-left" aria-hidden="true"></i>
		</button>
		<select ref="studiensemester" v-model="selectedStudiensemester" class="form-select" :aria-label="$p.t('global/studiensemester_auswaehlen')" @change="setHash($event.target.value)">
			<option v-for="semester in studienSemester" @click="changeSelectedStudienSemester(semester)" :key="semester" :value="semester.studiensemester_kurzbz">{{studiensemesterTitel(semester.studiensemester_kurzbz)	}}</option>
		</select>
		<button class="btn btn-outline-secondary" type="button" :disabled="false" @click="nextSem">
			<i class="fa fa-caret-right" aria-hidden="true"></i>
		</button>
	</div>

	<div class="input-group">
		<button class="btn btn-outline-secondary" type="button" :disabled="false" @click="prevSem">
			<i class="fa fa-caret-left" aria-hidden="true"></i>
		</button>
		<select ref="studiengaenge" v-model="selectedStudiengang" class="form-select" :aria-label="$p.t('global/studiensemester_auswaehlen')" @change="setHash($event.target.value)">
			<option v-for="studiengang in studiengaenge" @click="changeSelectedStudienGang(studiengang)" :key="studiengang.studiengang_kz" :value="studiengang.studiengang_kz" >{{studiengangTitel(studiengang)}}</option>
		</select>
		<button class="btn btn-outline-secondary" type="button" :disabled="false" @click="nextSem">
			<i class="fa fa-caret-right" aria-hidden="true"></i>
		</button>
	</div>

	<div class="input-group">
		<button class="btn btn-outline-secondary" type="button" :disabled="false" @click="prevSem">
			<i class="fa fa-caret-left" aria-hidden="true"></i>
		</button>
		<select ref="semester" v-model="selectedSemester" class="form-select" :aria-label="$p.t('global/studiensemester_auswaehlen')" @change="setHash($event.target.value)">
			<option v-for="sem in semester" @click="changeSelectedSemester(sem)" :key="semester" :value="sem">{{sem}}. Semester</option>
		</select>
		<button class="btn btn-outline-secondary" type="button" :disabled="false" @click="nextSem">
			<i class="fa fa-caret-right" aria-hidden="true"></i>
		</button>
	</div>

	<div class="input-group">
		<button class="btn btn-outline-secondary" type="button" :disabled="false" @click="prevSem">
			<i class="fa fa-caret-left" aria-hidden="true"></i>
		</button>
		<select ref="studienordnung" v-model="selectedStudienordnung" class="form-select" :aria-label="$p.t('global/studiensemester_auswaehlen')" @change="setHash($event.target.value)">
			<option v-for="ordnung in studienOrdnung" @click="changeSelectedStudienPlan(ordnung)" :key="ordnung.bezeichnung	" :value="ordnung.studienplan_id">{{studienordnungTitel(ordnung)}}</option>
		</select>
		<button class="btn btn-outline-secondary" type="button" :disabled="false" @click="nextSem">
			<i class="fa fa-caret-right" aria-hidden="true"></i>
		</button>
	</div>
	
	`
};