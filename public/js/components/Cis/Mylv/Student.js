import MylvSemester from "./Semester.js";
import Phrasen from "../../../mixins/Phrasen.js";

// TODO(chris): phrase: global/studiensemester_auswaehlen
// TODO(chris): phrase: next & prev +aria-label

export default {
	components: {
		MylvSemester
	},
	mixins: [
		Phrasen
	],
	data: () => {
		return {
			firstLoad: true,
			studiensemester: null,
			lvs: {},
			currentSemester: null
		};
	},
	computed: {
		ready() {
			return this.studiensemester !== null && (!this.firstLoad || this.current.lvs !== null);
		},
		current() {
			if (this.currentSemester === null)
				return { semester: null, lvs: [] };
			if (this.lvs[this.currentSemester] === undefined) {
				this.lvs[this.currentSemester] = {
					semester: this.currentSemester, 
					lvs: null
				};
				axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Cis/Mylv/Lvs/' + this.currentSemester).then(res => {
					this.lvs[this.currentSemester].lvs = res.data.retval || [];
					this.firstLoad = false;
				});
			}
			return this.lvs[this.currentSemester];
		},
		nearestSem() {
			let now = Date.now();
			let nearestSem = null;
			let nearestSemDiff = 0;
			this.studiensemester.forEach(sem => {
				let start = new Date(sem.start);
				let end = new Date(sem.ende);
				if (now >= start && now <= end) {
					nearestSem = sem.studiensemester_kurzbz;
					nearestSemDiff = 0;
					return;
				}
				let diff = Math.min(Math.abs(now - start), Math.abs(now - end));
				if (nearestSem === null || diff < nearestSemDiff) {
					nearestSem = sem.studiensemester_kurzbz;
					nearestSemDiff = diff;
				}

			});
			return nearestSem;
		},
		currentIsFirst() {
			return this.studiensemester[0].studiensemester_kurzbz == this.currentSemester;
		},
		currentIsLast() {
			return this.studiensemester[this.studiensemester.length-1].studiensemester_kurzbz == this.currentSemester;
		}
	},
	methods: {
		prevSem() {
			this.$refs.studiensemester.selectedIndex--;
			this.$refs.studiensemester.dispatchEvent(new Event('change', { bubbles: true }));
		},
		nextSem() {
			this.$refs.studiensemester.selectedIndex++;
			this.$refs.studiensemester.dispatchEvent(new Event('change', { bubbles: true }));
		},
		setHash(val) {
			location.hash = val;
		}
	},
	created() {
		axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Cis/Mylv/Studiensemester').then(res => {
			this.studiensemester = res.data.retval || [];
			const hash = location.hash.substring(1);
			if (hash && this.studiensemester.filter(s => s.studiensemester_kurzbz == hash).length)
				this.currentSemester = hash;
			else
				this.currentSemester = this.nearestSem;
		});
	},
	template: `

	<h2>{{p.t('lehre/myLV')}}</h2>
	<hr>
	<div class="mylv-student" v-if="ready">
		<div v-if="currentSemester" class="row justify-content-center mb-3">
			<div class="col-auto d-none">
				<label class="col-form-label">{{$p.t('lehre/studiensemester')}}</label>
			</div>
			<div class="col-auto">
				<div class="input-group">
					<button class="btn btn-outline-secondary" type="button" :disabled="currentIsFirst" @click="prevSem">
						<i class="fa fa-caret-left" aria-hidden="true"></i>
					</button>
					<select ref="studiensemester" v-model="currentSemester" class="form-select" :aria-label="p.t('global/studiensemester_auswaehlen')" @change="setHash($event.target.value)">
						<option v-for="semester in studiensemester" :key="semester.studiensemester_kurzbz">{{semester.studiensemester_kurzbz}}</option>
					</select>
					<button class="btn btn-outline-secondary" type="button" :disabled="currentIsLast" @click="nextSem">
						<i class="fa fa-caret-right" aria-hidden="true"></i>
					</button>
				</div>
			</div>
		</div>
		<div class="alert alert-danger" role="alert" v-else>
			{{p.t('lehre/noLvFound')}}
		</div>
		<mylv-semester v-bind="current"/>
	</div>
	<div class="mylv-student text-center" v-else>
		<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
	</div>`
};