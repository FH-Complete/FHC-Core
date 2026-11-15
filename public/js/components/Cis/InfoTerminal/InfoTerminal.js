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
import InfoTerminalLvPlan from './InfoTerminalLvPlan.js';
import InfoTerminalRoomsAvailabilityOverview from './InfoTerminalRoomsAvailabilityOverview.js';
import CmsContent from '../../Cis/Cms/Content.js';
import ApiInfoTerminal from '../../../api/factory/infoterminal.js';

export default {
	name: 'InfoTerminal',
	components: {
		InfoTerminalLvPlan,
		InfoTerminalRoomsAvailabilityOverview,
		CmsContent,
	},
	provide() {
		return {
			isMobile: false,
		};
	},
	props: {
		logoUrl: {
			type: String,
			default: () => document.getElementById('cis-header')?.dataset.logoUrl ?? '',
		},
		contentPagesData: {
			type: Array,
			default: [],
		},
	},
	data() {
		return {
			cardNumber: null,
			currentlySelectedTab: 'LVPlan',
			availableTabs: [
				{ name: 'LVPlan', label: 'myLVPlan' },
				{ name: 'AvailableRooms', label: 'freeRooms' },
			],
			ApiInfoTerminal: ApiInfoTerminal,
			languages: FHC_JS_DATA_STORAGE_OBJECT.server_languages
		};
	},
	computed: {
		selectedContentPageItem() {
			return this.$props.contentPagesData.find((item) => item.title === this.currentlySelectedTab) || null;
		},
	},
	watch: {
		'$route.query.key_input': {
			handler(newVal) {
				this.cardNumber = newVal || null;
			},
			immediate: true,
		},
	},
	methods: {
		getContentFetchURL(contentID, version, language) {
			if (!this.cardNumber) return null;
			if (!contentID) return null;

			return this.ApiInfoTerminal.getContentPage(this.cardNumber, contentID, version, language, true);
		},
		logoutTheUser() {
			window.location.replace(window.location.origin + window.location.pathname);
		},
		onChange(lang) {
			if (this.languages.some(l => l.sprache === lang)) {
				this.$p
					.setLanguage(lang)
					.then(() => {
						if (document.querySelector('[cis4Reload]'))
							window.location.reload();
						else
							this.$emit('changed', lang);
					});
			}
		}
	},
	template: /* html */ `
	<div
		class="container-fluid"
	>
		<div class="w-100 vh-100 row p-3">
			<div class="col-md-3 col-xl-2 d-flex flex-column">
				<div>
					<img :src="logoUrl" alt="Corporate Identity Logo" class="img-fluid w-75">
				</div>
				<div class="nav flex-column gap-2 justify-content-start mt-4">
					<button
						v-for="tab in availableTabs"
						:key="tab.name"
						type="button"
						class="nav-link text-start rounded-3 px-4 py-3 fs-5 border"
						:class="
							currentlySelectedTab === tab.name
								? 'bg-body-tertiary text-body fw-semibold border-secondary-subtle shadow-sm'
								: 'bg-transparent text-body-secondary border-light-subtle'
						"
						@click="currentlySelectedTab = tab.name"
					>
						{{ $p.t('info_terminal', tab.label) }}
					</button>
					<button
						v-for="contentPageItem in contentPagesData"
						:key="contentPageItem.title"
						:class="
							currentlySelectedTab === contentPageItem.title
							? 'bg-body-tertiary text-body fw-semibold border-secondary-subtle shadow-sm'
							: 'bg-transparent text-body-secondary border-light-subtle'
						"
						@click="currentlySelectedTab = contentPageItem.title"
						type="button"
						class="nav-link text-start rounded-3 px-4 py-3 fs-5 border"
					>
						{{ $p.t('info_terminal', contentPageItem.title) }}
					</button>
				</div>
			</div>
			<div class="col h-100 align-items-center justify-content-center d-flex flex-column">
				<div class="w-100 d-flex justify-content-end align-items-center gap-2">
					<div class="btn-group btn-group-sm" role="group" aria-label="Sprache wählen">
						<button 
							v-for="lang in languages"
							:key="lang.value"
							:class="[{active: $p.user_language.value == lang.sprache}]"
							:selected="$p.user_language.value == lang.sprache"
							@click.prevent="onChange(lang.sprache)" 
							type="button" 
							class="btn btn-outline-secondary">
							{{ lang.bezeichnung }}
						</button>
					</div>
					<button
						:title="$p.t('ui', 'logout')"
						@click="logoutTheUser()"
						type="button"
						class="btn btn-outline-secondary rounded-3 px-3 py-2"
						aria-label="Logout"
					>
						<i class="fa-solid fa-right-from-bracket"></i>
					</button>
				</div>
				<InfoTerminalLvPlan 
					v-if="currentlySelectedTab === 'LVPlan'"
					:cardNumber=this.cardNumber
					class="w-100 flex-grow-1"
				/>
				<InfoTerminalRoomsAvailabilityOverview
					v-else-if="currentlySelectedTab === 'AvailableRooms'"
					:cardNumber=this.cardNumber
					class="w-100 flex-grow-1"
				/>
				<div
					v-else
					class="d-flex justify-content-center align-items-center  flex-grow-1"
				>
					<CmsContent
						
						:content_id="selectedContentPageItem?.id"
						:version="selectedContentPageItem?.version"
						:sprache="selectedContentPageItem?.language"
						:fetchContentURL="
							getContentFetchURL(selectedContentPageItem?.id, selectedContentPageItem?.version, selectedContentPageItem?.language)
						"
						
					/>
				</div>
			</div>
		</div>
	</div>`,
};