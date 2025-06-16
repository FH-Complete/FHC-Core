import LvInfo from "../../Mylv/LvInfo.js"
import LvMenu from "../../Mylv/LvMenu.js"

export default {
	name: 'CisStundenplanEventEvent',
	components: {
		LvMenu,
		LvInfo
	},
	props: {
		event: Object
	},
	data() {
		return {
			lvMenu: null
		};
	},
	watch: {
		event() {
			this.loadMenu();
		}
	},
	methods: {
		loadMenu() {
			if (this.event && this.event.type == 'lehreinheit') {
				this.$fhcApi
					.factory.stundenplan.getLehreinheitStudiensemester(this.event.lehreinheit_id[0])
					.then(res => res.data)
					.then(studiensemester_kurzbz => {
						this.$fhcApi
							.factory.addons.getLvMenu(this.event.lehrveranstaltung_id, studiensemester_kurzbz)
							.then(res => {
								if (res.data) {
									this.lvMenu = res.data;
								}
							});
					});
			}
		}
	},
	created() {
		this.loadMenu();
	},
	template: `
	<div class="cis-stundenplan-event-event p-4 border-start h-100 overflow-auto">
		<template v-if="!event">
			<h3>{{ $p.t('lehre/noLvFound') }}</h3>
		</template>
		<template v-else>
			<h3>{{ $p.t('lvinfo','lehrveranstaltungsinformationen') }}</h3>
			<div class="w-100">
				<lv-info :event="event" />
			</div>
			<h3 v-if="lvMenu">{{ $p.t('lehre','lehrveranstaltungsmenue') }}</h3>
			<lv-menu
				v-if="lvMenu"
				:menu="lvMenu"
				:containerStyles="['p-0']"
				:rowStyles="['m-0']"
			/>
		</template>
	</div>
	`
}
