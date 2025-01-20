export default {
	data(){
		return{
			selected: this.mode,
			modes:{
				week: { mode_bezeichnung: "Woche", icon: "fa-calendar-week", condition: !this.noWeekView }, 
				month: { mode_bezeichnung: "Monat", icon: "fa-calendar-days", condition: !this.noMonthView }, 
				day: { mode_bezeichnung: "Tag", icon: "fa-sun" , condition:true}, 
			},
			headerPadding:null,
		}
	},
	inject: [
		'eventsAreNull',
		'size',
		'classHeader',
		'mode',
		'noWeekView',
		'noMonthView',
		'widget'
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
		getHeaderOffsetClass() {
			return 'col offset-0' + (this.widget ? '' : ' offset-md-3')
		},
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
	template: /*html*/`
	<div class="calendar-header card-header w-100" :class="classHeader">
		<div class="row align-items-center ">
			<div :class="getHeaderOffsetClass" :style="{'padding-left':headerPadding}">
				<div class="row align-items-center justify-content-center">
					<div class="col-auto ">
						<button class="btn btn-outline-secondary border-0" :class="{'btn-sm':!this.size}" @click="$emit('prev')"><i class="fa fa-chevron-left"></i></button>
					</div>
					<div class="justify-content-center text-center col-auto">
						<div class="d-flex justify-content-center align-items-center">
							<button class="btn btn-link link-secondary text-decoration-none" :class="{'btn-sm': !this.size}" @click="$emit('click')">
								{{ title }}
								<i v-if="eventsAreNull" class="fa fa-spinner fa-pulse"></i>
							</button>
						</div>
					</div>
					<div class="col-auto ">
						<button class="btn btn-outline-secondary border-0" :class="{'btn-sm': !this.size}" @click="$emit('next')"><i class="fa fa-chevron-right"></i></button>
					</div>
				</div>
			</div>
			<div ref="viewButtons" v-if="!noWeekView && !noMonthView" class=" col-12 col-md-3 d-flex justify-content-center justify-content-md-end align-items-center" style="pointer-events: none;">
				<div  style="pointer-events: all;">
					<div  role="group" aria-label="Kalender Modus">
						<button type="button" :class="{'active':mode_kurzbz === mode}" style="margin-right: 4px;" @click.prevent="$emit('updateMode',mode_kurzbz)" class="btn btn-outline-secondary" v-for="({mode_bezeichnung,icon,condition},mode_kurzbz) in modes">
							<i v-if="condition" class="fa" :class="icon" ></i>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>`
}