/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
import MyLvPlan from '../../Cis/LvPlan/MyLvPlan.js';

export default {
	name: 'InfoTerminal',
	components: {
		MyLvPlan,
	},
	provide() {
		return {
			isMobile: false,
		};
	},
	props: {
		logoUrl: {
			type: String,
			default: () =>
				document.getElementById('cis-header')?.dataset.logoUrl ?? '',
		},
	},
	data() {
		return {
			currentlySelectedTab: 'LVPlan',
			availableTabs: [
				{ name: 'LVPlan', label: 'Mein LV-Plan' },
				{ name: 'AvailablePCRooms', label: 'Freie PC Räume' },
				{ name: 'AvailableLectureHalls', label: 'Freie Hörsäle' },
				{ name: 'AvailableSeminarRooms', label: 'Freie Seminarräume' },
				{ name: 'AvailableLaborRooms', label: 'Freie Laborräume' },
				{ name: 'PersonalData', label: 'Personliche daten' },
			],
		};
	},
	template: /* html */ `
	<div
		class="container-fluid"
	>
		<div class="w-100 vh-100 row p-3">
			<div class="col-md-3 col-xl-2 d-flex flex-column">
				<div>
					<img :src="logoUrl" alt="Corporate Identity Logo">
				</div>
				<div class="nav flex-column gap-2 justify-content-center flex-grow-1">
					<button
						v-for="tab in availableTabs"
						:key="tab.name"
						type="button"
						class="nav-link text-start rounded-3 px-3 py-2 border"
						:class="
							currentlySelectedTab === tab.name
								? 'bg-body-tertiary text-body fw-semibold border-secondary-subtle shadow-sm'
								: 'bg-transparent text-body-secondary border-light-subtle'
						"
						@click="currentlySelectedTab = tab.name"
					>
						{{ tab.label }}
					</button>
				</div>
			</div>
			<div class="col">
				<MyLvPlan v-if="currentlySelectedTab === 'LVPlan'" />
				<div v-else-if="currentlySelectedTab === 'AvailablePCRooms'">
					Freie PC Räume Content
				</div>
				<div v-else-if="currentlySelectedTab === 'AvailableLectureHalls'">
					Freie Hörsäle Content
				</div>
				<div v-else-if="currentlySelectedTab === 'AvailableSeminarRooms'">
					Freie Seminarräume Content
				</div>
				<div v-else-if="currentlySelectedTab === 'AvailableLaborRooms'">
					Freie Laborräume Content
				</div>
				<div v-else-if="currentlySelectedTab === 'PersonalData'">
					Personliche daten Content
				</div>
			</div>
		</div>
	</div>`,
};
