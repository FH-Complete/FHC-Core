export default {
	data() {
		return {}
	},
	props: {
		data: {
			type: Object,
		},
		title: {
			type: String,
		}
	},
	inject: {
		// inject info that should not be displayed
		'studiengang_kz': {
			from: 'studiengang_kz',
			default: false
		},
	},
	computed: {
		getLinkGruppeListe() {
			if(this.studiengang_kz === false) {
				return '';
			}
			return this.data.gruppe?.value && this.data.verband?.value && this.data.semester?.value ? FHC_JS_DATA_STORAGE_OBJECT.app_root 
				+ 'cis/private/stud_in_grp.php?kz='+this.studiengang_kz+'&sem=' + this.data.semester.value
				+ '&verband=' + this.data.verband.value + '&grp=' + this.data.gruppe.value : ''
		},
		getLinkVerbandListe() {
			if(this.studiengang_kz === false) {
				return '';
			}
			return this.data.verband?.value && this.data.semester?.value ? FHC_JS_DATA_STORAGE_OBJECT.app_root 
				+ 'cis/private/stud_in_grp.php?kz='+this.studiengang_kz+'&sem=' + this.data.semester.value
				+ '&verband=' + this.data.verband.value : ''
		},
		getLinkSemesterListe() {
			if(this.studiengang_kz === false) {
				return '';
			}
			return this.data.semester?.value ? FHC_JS_DATA_STORAGE_OBJECT.app_root 
				+ 'cis/private/stud_in_grp.php?kz='+this.studiengang_kz+'&sem=' + this.data.semester.value : ''
		}
	},
	created() {
		//TODO: check if data.Telefon is a valid telefon number to call before using it as a tel: link
	},
	template: `
    <div class="card">     
        <div class="card-header">{{title}}</div>
        <div class="card-body">
            <div class="gy-3 row">
				<div v-for="(entry, key) in data" class="col-md-6 col-sm-12 ">
					
					<div class="form-underline">
						<div class="form-underline-titel">{{entry.label }}</div>
			
						<!-- print Telefon link -->
						<a  v-if="key == 'telefon'" :href="entry.value ?'tel:'+entry.value:null" :class="{'form-underline-content':true,'text-decoration-none':!entry.value,'text-body':!entry.value}">{{entry.value ?? '-'}}</a>
						
						<!-- print semester link -->
						<span v-else-if="key == 'semester' && entry.value"  class="form-underline-content">
							{{ entry.value }}
							<a :aria-label="$p.t('profil','semesterLink')" class="ms-auto mb-2" target="_blank" :href="getLinkSemesterListe">
								<i aria-hidden="true" class="fa fa-arrow-up-right-from-square me-1 fhc-link-color"></i>
							</a>
						</span>
						
						<!-- print verband link -->
						<span v-else-if="key =='verband' && entry.value"  class="form-underline-content">
							{{ entry.value }}
							<a :aria-label="$p.t('profil','verbandLink')" class="ms-auto mb-2" target="_blank" :href="getLinkVerbandListe">
								<i aria-hidden="true" class="fa fa-arrow-up-right-from-square me-1 fhc-link-color"></i>
							</a>
						</span>
						
						<!-- print gruppe link -->
						<span v-else-if="key == 'gruppe' && entry.value"  class="form-underline-content">
							{{ entry.value }}
							<a :aria-label="$p.t('profil','gruppenLink')" class="ms-auto mb-2" target="_blank" :href="getLinkGruppeListe">
								<i aria-hidden="true" class="fa fa-arrow-up-right-from-square me-1 fhc-link-color"></i>
							</a>
						</span>
						
						<!-- else print information -->
						<span v-else class="form-underline-content">{{ entry.value ?? '-'}}</span>
					</div>
				</div>
			</div>
		</div>
	</div>`
};