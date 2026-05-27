export default {
	props: {
		title:String,
		vertretungsList:Array,
		showBezeichnung:Boolean,
	},
	template:/*html*/`
	<div class="card mb-3 border-0">
		<div class="card-header">
			<span>{{title}}</span>
		</div>
		<div class="card-body">
			<p v-for="vertretung in vertretungsList">
				<a v-if="profilViewLink(vertretung.uid)" :href="profilViewLink(vertretung.uid)" :aria-label="$p.t('profil','profil')" :title="$p.t('profil','profil')">
					<i class="me-2 fa fa-arrow-up-right-from-square fhc-primary-color" aria-hidden="true"></i>
				</a>
				{{vertretungFormatedName(vertretung,false)}}
			</p>
		</div>
	</div>
	`,
	methods: {
		profilViewLink: function (uid) {
			return uid ? FHC_JS_DATA_STORAGE_OBJECT.app_root.concat(FHC_JS_DATA_STORAGE_OBJECT.ci_router).concat("/Cis/Profil/View/").concat(uid) : null;
		},
		vertretungFormatedName: function (vertretung) {
			if (!vertretung) return null;
			return `${vertretung.vorname ?? ''} ${vertretung.nachname ?? ''} ${vertretung.bezeichnung && this.showBezeichnung ? '('.concat(vertretung.bezeichnung.replace("(", "").replace(")", "")).concat(")") : ''}`
		},
	},

}