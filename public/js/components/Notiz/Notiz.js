export default {
	props: ['titel', 'text', 'von', 'bis', 'action', 'document', 'erledigt', 'verfasser', 'bearbeiter'],
	computed: {
		intTitel: {
			get() {
				return this.titel;
			},
			set(value) {
				this.$emit('update:titel', value);
			}
		},
		intText: {
			get() {
				return this.text;
			},
			set(value) {
				this.$emit('update:text', value);
			}
		},
		intVon: {
			get() {
				return this.von;
			},
			set(value) {
				this.$emit('update:von', value);
			}
		},
		intBis: {
			get() {
				return this.bis;
			},
			set(value) {
				this.$emit('update:bis', value);
			}
		},
		intDocument: {
			get() {
				return this.document;
			},
			set(value) {
				this.$emit('update:document', value);
			}
		},
		intErledigt: {
			get() {
				return this.erledigt;
			},
			set(value) {
				this.$emit('update:erledigt', value);
			}
		},
		intVerfasser: {
			get() {
				return this.verfasser;
			},
			set(value) {
				this.$emit('update:verfasser', value);
			}
		},
		intBearbeiter: {
			get() {
				return this.bearbeiter;
			},
			set(value) {
				this.$emit('update:bearbeiter', value);
			}
		},
	},
	methods: {

	},
	template: `
<div>
<!--{{intTitel}} {{intText}} {{intVon}}| {{titel}} {{text}} {{action}} {{von}} {{bis}} {{document}} {{erledigt}} {{verfasser}} {{bearbeiter}}-->
	<form class="row">
		<div class="notizAction row mb-3">
			<b>{{action}}</b>
		</div>
		<div class="notizTitle row mb-3">
			<label for="titel" class="form-label col-sm-2">Titel</label>
			<div class="col-sm-6">
				<input type="text" v-model="intTitel" class="form-control">
			</div>	
		</div>
		
		<div class="notizText row mb-3">
			<label for="text" class="form-label col-sm-2">Text</label>
			<div class="col-sm-6">
				<textarea rows="5" cols="75" v-model="intText" class="form-control"></textarea>
			</div>
		</div>
		
		<div class="notizDoc row mb-3">
			<label for="text" class="form-label col-sm-2">Dokument</label>
			<div class="col-sm-6">
				<input type="text" v-model="intDocument" class="form-control">
			</div>
		</div>
		
		<div class="notizVon row mb-3">
			<label for="von" class="form-label col-sm-2">von</label>
			<div class="col-sm-6" >
				<input type="text" v-model="intVon" class="form-control">	
			</div>
		</div>
		<div class="notizBis row mb-3">
			<label for="bis" class="form-label col-sm-2">bis</label>
			<div class="col-sm-6">
				<input type="text" v-model="intBis" class="form-control">	
			</div>
		</div>
		
		<div class="notizErledigt row mb-3">
			<label for="bis" class="form-label col-sm-2">erledigt</label>
			<div class="col-sm-6"> 
				<input type="checkbox" v-model="intErledigt">	
			</div>
		</div>
		
		<div class="notizVerfasser row mb-3">
			<label for="bis" class="form-label col-sm-2">VerfasserIn</label>
			<div class="col-sm-6">
				<input type="text" v-model="intVerfasser" class="form-control">	
				{{uid}}
			</div>
		</div>
		
		<div class="notizBearbeiter row mb-3">
			<label for="bis" class="form-label col-sm-2">BearbeiterIn</label>
			<div class="col-sm-6">
				<input type="text" v-model="intBearbeiter" class="form-control">	
			</div>
		</div>
		
	</form>
</div>`
}

