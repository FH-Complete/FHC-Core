import ApiProfil from '../../../../api/factory/profil.js';
import ImageUpload from '../../Profil/ProfilModal/EditProfilComponents/ImageUpload.js';

export default {
	props: {
		title: {
			type: String,
		},
		data: {
			type: Object,
		},
		fotoStatus:{
			type: Boolean,
			default: true
		}
	},
	components:{
		ImageUpload,
	},
	data() {
		return {
			FotoSperre: this.data.foto_sperre,
		};
	},
	emits: ["showEditProfilModal"],
	inject:["isEditable"],

	methods: {
		showModal(){
			this.$refs.imageUpload.show();
		},
		sperre_foto_function() {
			//TODO: refactor
			if (!this.data) {
				return;
			}
			this.$api
				.call(ApiProfil.fotoSperre(!this.FotoSperre))
				.then(res => {
					this.FotoSperre = res.data.foto_sperre;
				});
		}
	},
	computed: {
		get_image_base64_src: function () {
			if (!this.data.foto) {
				return "";
			}
			return "data:image/jpeg;base64," + this.data.foto;
		},
		name: function () {
			return {vorname: this.data.Vorname, nachname: this.data.Nachname};
		},
		profilInfo: function () {
			let res = {};
			let notIncludedProperties = [
				"Vorname",
				"Nachname",
				"foto_sperre",
				"foto",
			];
			Object.keys(this.data).forEach((key) => {
				if (!notIncludedProperties.includes(key)) {
					res[key] = this.data[key];
				}
			});
			return res;
		},
	},
	template: /*html*/ `

<div class="card h-100">
	<image-upload ref="imageUpload" :titel="$p.t('profilUpdate','profilBild')"></image-upload>
    <div class="card-header">
        <div class="row">
            <div v-if="isEditable" @click="$emit('showEditProfilModal','Personen_Informationen')" class="col-auto" type="button">
                <i class="fa fa-edit"></i>
            </div>
            <div class="col">
                <span>{{title}}</span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div  class="gy-3 row justify-content-center align-items-center">
            <!-- SQUEEZING THE IMAGE INSIDE THE FIRST INFORMATION COLUMN -->
            <!-- START OF THE FIRST ROW WITH THE PROFIL IMAGE -->
            <div class="col-12 col-sm-6 mb-2">
                <div class="row justify-content-center">
                    <div class="col-auto profil-image" style="position:relative">
                        <img alt="profile picture" class=" img-thumbnail " style=" max-height:150px; "  :src="get_image_base64_src"/>
                        <!-- LOCKING IMAGE FUNCTIONALITY -->
                        <div v-if="isEditable" role="button" @click.prevent="sperre_foto_function" class="image-lock">
                            <i :class="{'fa':true, ...(FotoSperre?{'fa-lock':true}:{'fa-lock-open':true})} "></i>
                        </div>
						<div v-if="!fotoStatus" role="button" @click.prevent="showModal" class="image-upload">
                            <i class="fa fa-upload"></i>
                        </div>
                    </div>
                </div>
            <!-- END OF THE ROW WITH THE IMAGE -->
            </div>
            <!-- END OF SQUEEZE -->
            <!-- COLUMNS WITH MULTIPLE ROWS NEXT TO PROFIL PICTURE -->
            <div class="col-12 col-sm-6">
                <div class="row gy-4">
                    <div class="col-12">
                        <div class="form-underline ">
                            <div class="form-underline-titel">{{$p.t('profilUpdate','vorname')}}</div>
                            <span class="form-underline-content">{{name.vorname}} </span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-underline ">
                            <div class="form-underline-titel">{{$p.t('profilUpdate','nachname')}}</div>
                            <span class="form-underline-content">{{name.nachname}} </span>
                        </div>
                    </div>
                </div>
            </div>
            <div v-for="(wert,bez) in profilInfo" class="col-md-6 col-sm-12">
                <div class="form-underline">
                    <div class="form-underline-titel">{{$p.t('profil',bez)}}</div>
                    <span class="form-underline-content">{{wert?wert:'-'}} </span>
                </div>
            </div>
        </div>
    </div>
</div>
`,
};
