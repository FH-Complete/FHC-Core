export default {
	data(){
		return{
			selected: this.mode,
			modes:{
				week:"Woche", 
				month:"Monat", 
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
	<div class="calendar-header card-header w-100" :class="classHeader">

		<div class="row justify-content-end" style="position: absolute; width: 98%; pointer-events: none;">
			<div class="col-auto" style="pointer-events: all;">
				<div role="group" aria-label="Kalender Modus">
					<button type="button" :class="{'active':mode_kurzbz == mode}" style="margin-right: 4px;" @click.prevent="$emit('updateMode',mode_kurzbz)" class="btn btn-outline-secondary" v-for="(mode_bezeichnung,mode_kurzbz) in modes">
						<i v-if="mode_kurzbz == 'week'" class="fa fa-calendar-week"></i>
						<i v-else-if="mode_kurzbz == 'month'" class="fa fa-calendar-days"></i>
					</button>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-5 d-flex justify-content-end">
				<button class="btn btn-outline-secondary border-0" :class="{'btn-sm':!this.size}" @click="$emit('prev')"><i class="fa fa-chevron-left"></i></button>
			</div>
			<div class="justify-content-center text-center col-2">
				<div class="d-flex justify-content-center align-items-center">
					<button class="btn btn-link link-secondary text-decoration-none" :class="{'btn-sm': !this.size}" @click="$emit('click')">
						{{ title }}
						<i v-if="eventsAreNull" class="fa fa-spinner fa-pulse"></i>
					</button>
				</div>
			</div>
			<div class="col-5 d-flex justify-content-start">
				<button class="btn btn-outline-secondary border-0" :class="{'btn-sm': !this.size}" @click="$emit('next')"><i class="fa fa-chevron-right"></i></button>
			</div>
		</div>
	</div>`
}