import DetailsDetails from './Details/Details.js';
import DetailsNotizen from './Details/Notizen.js';

export default {
	components: {
		DetailsDetails,
		DetailsNotizen
	},
	props: {
		student: Object
	},
	data() {
		return {
			component: 'DetailsDetails',
			tabs: {
				DetailsDetails: 'Details',
				DetailsNotizen: 'Notizen'
			}
		}
	},
	computed: {
		hasNoStudent() {
			return !this.student || (Object.keys(this.student).length === 0 && this.student.constructor === Object);
		}
	},
	template: `
	<div class="stv-details h-100 pb-3">
		<div v-if="hasNoStudent" class="justify-content-center d-flex h-100 align-items-center">Bitte StudentIn auswählen!</div>
		<template v-else>		
			<ul class="nav nav-tabs">
				<li v-for="(title, comp) in tabs" class="nav-item" :key="comp">
					<a class="nav-link" :class="{active: comp == component}" :aria-current="comp == component ? 'page' : ''" href="#" @click="component=comp">{{title}}</a>
				</li>
			</ul>
			<component :is="component" :student="student"></component>
		</template>
	</div>`
};