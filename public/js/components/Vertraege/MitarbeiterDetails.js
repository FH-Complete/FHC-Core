export default {
	data() {
		return {
			headerData: {},
			departmentData: {},
			leitungData: {},
		};
	},
	inject: {
		domain: {
			from: 'configDomain',
			default: 'technikum-wien.at'
		},
	},
	props: {
		person_id: Number
	},
	computed: {
		appRoot() {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root;
		},
	},
	created(){
		this.getHeader(this.person_id);

		// Ensure loadDepartmentData is awaited
		this.loadDepartmentData(this.person_id)
			.then(() => {
				// Call getLeitungOrg only after departmentData is loaded
				this.getLeitungOrg(this.departmentData.oe_kurzbz);
			})
			.catch((error) => {
				console.error("Error loading department data:", error);
			});
	},
	watch: {
		person_id: {
			handler(newVal) {
				if (newVal) {
					this.getHeader(this.person_id);
					this.loadDepartmentData(this.person_id).
					then(() => {
						this.getLeitungOrg(this.departmentData.oe_kurzbz);
					});
				}
			},
			deep: true,
		},
	},
	methods: {
		getHeader(person_id) {
			return this.$fhcApi.factory.vertraege.person
				.getHeader(person_id)
				.then(result => {
					this.headerData = result.data[0];
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		loadDepartmentData(person_id) {
			return this.$fhcApi.factory.vertraege.person
				.getPersonAbteilung(person_id)
				.then(result => {
					this.departmentData = result.data[0];
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		getLeitungOrg(oekurzbz){
			return this.$fhcApi.factory.vertraege.person
				.getLeitungOrg(oekurzbz)
				.then(result => {
					this.leitungData = result.data[0];
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
	template: `
		<div class="core-mitarbeiter-details">
			<!--show Picture-->
			<div class="row">
				<div class="col-md-2 d-flex justify-content-start align-items-center w-30 pb-3 gap-3" 
						style="max-height: 8rem; max-width: 6rem; overflow: hidden;">
					<img 
						class="d-block h-100 rounded" 
						alt="profilepicture" 
						style="object-fit: cover; max-width: 100%;" 
						:src="appRoot + 'cis/public/bild.php?src=person&person_id=' + person_id">
				</div>
			
				<!--show Ma-Details-->
				<div class="col-md-10">
					<h5>{{headerData.titelpre}} {{headerData.vorname}} {{headerData.nachname}} {{headerData.titelpost}} </h5>
					<strong class="text-muted">{{departmentData.organisationseinheittyp_kurzbz}}</strong> {{departmentData.bezeichnung}} <span v-if="leitungData.uid"> | </span><strong v-if="leitungData.uid" class="text-muted">Vorgesetzte/e </strong>{{leitungData.titelpre}} {{leitungData.vorname}} {{leitungData.nachname}} {{leitungData.titelpost}}  
					<p>
						<strong class="text-muted">Email </strong>
						 <span v-if="!headerData?.alias">
							<a :href="'mailto:'+headerData?.uid+'@'+domain">{{  headerData.uid }}@{{ domain }}</a>
						</span>
						<span v-if="headerData?.alias">
							<a :href="'mailto:'+headerData?.alias+'@'+domain">{{  headerData.alias }}@{{ domain }}</a>
						</span>
						<span v-if="headerData?.telefonklappe" class="mb-2"> | <strong class="text-muted">DW</strong> {{  headerData?.telefonklappe }}</span>
					</p>
		
				</div>
			</div>
		</div>

`
};