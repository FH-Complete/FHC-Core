import accessibility from "../../../directives/accessibility.js";

export default {
	directives: {
		accessibility
	},
	props: {
		student: Object
	},
	data() {
		return {
			current: this.$route.params.tab || 'details',
			tabTemplates: {
				details: 'Details',
				notizen: 'Notizen',
				kontakt: 'Kontakt'
			},
			tabs: {}
		}
	},
	computed: {
		hasNoStudent() {
			return !this.student || (Object.keys(this.student).length === 0 && this.student.constructor === Object);
		},
		currentComponent() {
			return this.tabs[this.current].component;
		}
	},
	created() {
		this.tabs = Object.fromEntries(Object.entries(this.tabTemplates).map(([key, title]) => {
			return [key, {
				title,
				component: Vue.defineAsyncComponent(() => import("./Details/" + key.charAt(0).toUpperCase() + key.slice(1) + '.js'))
			}];
		}));
	},
	template: `
	<div class="stv-details h-100 pb-3 d-flex flex-column">
		<div v-if="hasNoStudent" class="justify-content-center d-flex h-100 align-items-center">Bitte StudentIn auswählen!</div>
		<template v-else>		
			<div class="nav nav-tabs">
				<div
					v-for="({title}, key) in tabs"
					:key="comp"
					class="nav-item nav-link"
					:class="{active: key == current}"
					@click="current=key"
					:aria-current="key == current ? 'page' : ''"
					v-accessibility:tab
					>
					{{title}}
				</div>
			</div>
			<div style="flex: 1 1 0%; height: 0%" class="border-bottom border-start border-end overflow-auto p-3">
				<keep-alive>
					<suspense>
						<component :is="currentComponent" :student="student"></component>
						<template #fallback>
							Loading...
						</template>
					</suspense>
				</keep-alive>
			</div>
		</template>
	</div>`
};