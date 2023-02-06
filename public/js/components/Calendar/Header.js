export default {
	inject: [
		'eventsAreNull',
		'size'
	],
	props: {
		title: String
	},
	emits: [
		'prev',
		'next',
		'click'
	],
	template: `
	<div class="calendar-header card-header btn-group w-100">
		<button class="btn btn-outline-secondary border-0 flex-grow-0" :class="{'btn-sm': !this.size}" @click="$emit('prev')"><i class="fa fa-chevron-left"></i></button>
		<button class="btn btn-link link-secondary text-decoration-none" :class="{'btn-sm': !this.size}" @click="$emit('click')">
			{{ title }}
			<i v-if="eventsAreNull" class="fa fa-spinner fa-pulse"></i>
		</button>
		<button class="btn btn-outline-secondary border-0 flex-grow-0" :class="{'btn-sm': !this.size}" @click="$emit('next')"><i class="fa fa-chevron-right"></i></button>
	</div>`
}
