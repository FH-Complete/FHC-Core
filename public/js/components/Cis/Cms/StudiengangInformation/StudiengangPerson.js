export default {
	props:{
		uid:String,
		vorname:String,
		nachname:String,
		titelpre:String,
		kontakt:String,	
		telefoneklappe:String,
		email:String,
		planbezeichnung:String,
		foto:String,
		displayWidget:{
			type:Boolean,
			default:false,
		}
	},
	template:/*html*/`
	<div class="card" :style="{'width':displayWidget?'12rem':'15rem'}">
		<div class="bg-dark d-flex justify-content-center">
			<img  :src="base64Image" alt="mitarbeiter_foto" style="width: 110px; height: auto; object-fir:scale-down;" class="card-img-top" >
		</div>
		<div class="card-body">
			<h6 class="text-center card-title mb-0">{{fullname}} <a v-if="profilViewLink" :href="profilViewLink"><i class="ms-2 fa fa-arrow-up-right-from-square" style="color:#00649C"></i></a></h6>
		</div>
		<hr class="my-0">
		<div class="card-body">
			
			<div class="flex flex-column gap-3">
			<div class="mb-3">
				<span>
					<i class="fa fa-phone me-2"></i>
					<a :href="phone.link">{{phone.number}}</a>
				</span>
			</div>
			<div class="mb-3">
				<span>
					<i class="fa fa-home me-2"></i>
					{{ort}}
				</span>
			</div>
			<div>
				<span>
					<i class="fa-regular fa-envelope me-2"></i>
					<a :href="email_link" v-html="formattedEmail"></a>
				</span>
			</div>
			
			</div>
		</div>
	</div>
	`,
	computed:{
		formattedEmail: function(){
			if(!this.email ) return null;
			let emailString= this.email.replace("mailto:", "");
			// when splitting a string, the letter that is used to split the string will be removed from the result
			let emailArray = emailString.split('@');
			// returns both parts of the splitted string in combination with the removed letter and a word break
			return emailArray[0] + '@<wbr>' + emailArray[1];
		},
		fullname: function () {
			if (this.titelpre && this.vorname && this.nachname) {
				return `${this.titelpre} ${this.vorname} ${this.nachname}`;
			}
			else if (this.vorname && this.nachname) {
				return `${this.vorname} ${this.nachname}`;
			}
			else if (this.nachname) {
				return this.vorname;
			}
			else {
				return null;
			}
		},
		phone: function () {
			if (this.kontakt && this.telefoneklappe) {
				return {
					link: "tel:".concat(this.kontakt).concat(" " + this.telefoneklappe),
					number: this.kontakt.concat(" " + this.telefoneklappe),
				} 
			}
			else {
				return this.kontakt ? {
					link: "tel:".concat(this.kontakt),
					number: this.kontakt,
				} : null;
			}
		},
		email_link: function () {
			return this.email ? "mailto:".concat(this.email) : null;
		},
		base64Image:function(){
			return this.foto ? 'data:image/png;base64,'.concat(this.foto) : null;
		},
		ort:function(){
			return this.planbezeichnung ?? null;
		},
		profilViewLink: function(){
			return this.uid ? FHC_JS_DATA_STORAGE_OBJECT.app_root.concat(FHC_JS_DATA_STORAGE_OBJECT.ci_router).concat("/Cis/Profil/View/").concat(this.uid): null; 
		},
	},
	
}