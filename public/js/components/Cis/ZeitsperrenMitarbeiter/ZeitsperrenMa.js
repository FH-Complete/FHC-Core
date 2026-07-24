import aktuelleZeitsperren from './Details/aktuelleZeitsperren.js';

export default {
	name: 'ZeitsperrenMa',
	components: {
		aktuelleZeitsperren,
	},
	data(){
		return {
			arrayAllMa : [],
			showAllCurrent: false,
			showAllFix: false,
			showAllLectors: false,
			showSearchOes: false,
			showAllAss: false,
			showLectorsOe: false,
		}
	},
	methods: {
		showTableAllCurrent(){
			this.showAllCurrent = !this.showAllCurrent;
			this.showAllFix = false;
			this.showAllLectors = false;
			this.showSearchOes = false;
			this.showAllAss = false;
			this.showLectorsOe = false;
		},
		showTableAllFix(){
			this.showAllFix = !this.showAllFix;
			this.showAllCurrent = false;
			this.showAllLectors = false;
			this.showSearchOes = false;
			this.showAllAss = false;
			this.showLectorsOe = false;
		},
		showTableAllLectors(){
			this.showAllLectors = !this.showAllLectors;
			this.showAllFix = false;
			this.showAllCurrent = false;
			this.showSearchOes = false;
			this.showAllAss = false;
			this.showLectorsOe = false;
		},
		showTableSearchOes(){
			this.showSearchOes = !this.showSearchOes;
			this.showAllFix = false;
			this.showAllCurrent = false;
			this.showAllLectors = false;
			this.showAllAss = false;
			this.showLectorsOe = false;
		},
		showTableAllAss(){
			this.showAllAss = !this.showAllAss;
			this.showSearchOes = false;
			this.showAllFix = false;
			this.showAllCurrent = false;
			this.showAllLectors = false;
			this.showLectorsOe = false;
		},
		showTableLectorsFromStg(){
			this.showLectorsOe = !this.showLectorsOe;
			this.showSearchOes = false;
			this.showAllFix = false;
			this.showAllCurrent = false;
			this.showAllAss = false;
			this.showAllLectors = false;
		},

	},
	template: `
		<div class="base-zeitsperren w-100 h-100">
						
			<div class="row g-1">
			  <div class="col">
				<button type="button" class="btn btn-primary w-100";" @click="showTableAllCurrent">Aktuelle Zeitsperren</button>
			  </div>
			  <div class="col">
				<button type="button" class="btn btn-secondary w-100";" @click="showTableAllFix">alle Fixangestellte</button>
			  </div>
			  <div class="col">
				<button type="button" class="btn btn-secondary w-100";" @click="showTableAllLectors">Alle fixen Lektor*innen</button>
			  </div>
			  <div class="col">
				<button type="button" class="btn btn-secondary w-100";" @click="showTableSearchOes">Nach Organisationseinheit</button>
			  </div>
			  <div class="col">
				<button type="button" class="btn btn-warning w-100";" @click="showTableAllAss">Alle Assistent*innen</button>
			  </div>
			  <div class="col">
				<button type="button" class="btn btn-success w-100";" @click="showTableLectorsFromStg">Lektoren nach Stg</button>
			  </div>
			</div>
			
			<div v-if="showAllCurrent">
				<aktuelle-zeitsperren/>
			</div>
			<div v-if="showAllFix">
				<aktuelle-zeitsperren type="fix"></>
			</div>
			<div v-if="showAllLectors">
				<aktuelle-zeitsperren type="lector"></>
			</div>
			<div v-if="showSearchOes">
				<aktuelle-zeitsperren type="oe"></>
			</div>
			<div v-if="showAllAss">
				<aktuelle-zeitsperren type="ass"></>
			</div>
			<div v-if="showLectorsOe">
				<aktuelle-zeitsperren type="lecStg"></>
			</div>
			
		</div>

	`,
}