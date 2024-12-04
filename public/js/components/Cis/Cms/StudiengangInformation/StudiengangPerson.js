export default {
	data(){

	},
	props:{
		person_data:
		{
			type: Object,
			required: true
		}
	},
	computed:{
		formattedEmail: function(){
			if(!this.person_data ) return null;
			let emailString= this.person_data.email.replace("mailto:", "");
			// when splitting a string, the letter that is used to split the string will be removed from the result
			let emailArray = emailString.split('@');
			// returns both parts of the splitted string in combination with the removed letter and a word break
			return emailArray[0] + '@<wbr>' + emailArray[1];
		},
	},
	template:/*html*/`
	<div class="card" style="width: 15rem;">
		<div class="bg-dark d-flex justify-content-center">
			<img  :src="person_data.foto" alt="person_dataFoto" style="width: 110px; height: auto; object-fir:scale-down;" class="card-img-top" >
		</div>
		<div class="card-body">
			<h5 class="text-center card-title mb-0">{{person_data.fullname}}</h5>
		</div>
		<hr class="my-0">
		<div class="card-body">
			
			<div class="flex flex-column gap-3">
			<div class="mb-3">
				<span>
					<i class="fa fa-phone me-2"></i>
					<a :href="person_data.telefone">{{person_data.telefone?.replace("tel:","")}}</a>
				</span>
			</div>
			<div class="mb-3">
				<span>
					<i class="fa fa-home me-2"></i>
					{{person_data.ort}}
				</span>
			</div>
			<div>
				<span>
					<i class="fa-regular fa-envelope me-2"></i>
					<a :href="person_data.email" v-html="formattedEmail"></a>
				</span>
			</div>
			
			</div>
		</div>
	</div>
	<!--<div class="flex flex-column gap-3">
		<div class="mb-3">
			<span>
				<i class="fa fa-user me-2"></i>
				{{person_data.fullname}}
			</span>
		</div>
		<div class="mb-3">
			<span>
				<i class="fa fa-phone me-2"></i>
				<a :href="person_data.telefone">{{person_data.telefone?.replace("tel:","")}}</a>
			</span>
		</div>
		<div class="mb-3">
			<span>
				<i class="fa fa-home me-2"></i>
				{{person_data.ort}}
			</span>
		</div>
		<div class="mb-3">
			<span>
				<i class="fa-regular fa-envelope me-2"></i>
				<a :href="person_data.email"> {{person_data.email.replace("mailto:","")}}</a>
			</span>
		</div>
		<div class="mb-3">
			<img :src="person_data.foto" alt="person_dataFoto" style="width: 100px; height: auto; object-fir:scale-down;">
		</div>
	</div>-->`,
}