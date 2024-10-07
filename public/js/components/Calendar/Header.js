export default {
	data(){
		return{
			selected: this.mode,
			modes:{
				week:"Woche", 
				weeks:"Kalendarwochen", 
				month:"Monat", 
				months:"Monate", 
				years:"Jahre",
			},
		}
	},
	inject: [
		'eventsAreNull',
		'size',
		'classHeader',
		'mode',
		'updateMode',
	],
	props: {
		title: String
	},
	emits: [
		'updateMode',
		'prev',
		'next',
		'click'
	],
	computed: {
		myClassHeader() {
			// TODO(chris): + {'btn-sm': !this.size}
			let c = this.classHeader;
			if (Array.isArray(c)) {
				if (!this.size)
					c.push('btn-sm');
			} else if (typeof c === 'string' || c instanceof String) {
				if (!this.size)
					c += ' btn-sm';
			} else {
				c['btn-sm'] = !this.size;
			}

			return c;
		}
	},
	template: `
	<div class="calendar-header card-header row w-100" :class="classHeader">
		<button class="btn btn-outline-secondary border-0 col-auto" :class="{'btn-sm':!this.size}" @click="$emit('prev')"><i class="fa fa-chevron-left"></i></button>
		<div class="col text-center">
			<div class="d-flex justify-content-center align-items-center">
				<button class="btn btn-link link-secondary text-decoration-none" :class="{'btn-sm': !this.size}" @click="$emit('click')">
					{{ title }}
					<i v-if="eventsAreNull" class="fa fa-spinner fa-pulse"></i>
				</button>
				<select class="form-select w-auto" aria-label="Default select example">
					<option :selected="mode_kurzbz == mode" @click="$emit('updateMode',mode_kurzbz)" v-for="(mode_bezeichnung,mode_kurzbz) in modes">{{mode_bezeichnung}}</option>
				</select>
			</div>
		</div>
		<button class="col-auto btn btn-outline-secondary border-0" :class="{'btn-sm': !this.size}" @click="$emit('next')"><i class="fa fa-chevron-right"></i></button>
	</div>`
}
