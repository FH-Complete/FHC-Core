import drop from '../../../directives/drop.js';
import dragClick from '../../../directives/dragClick.js';

import ApiStvGroups from '../../../api/factory/stv/group.js';
import ApiStvDetails from '../../../api/factory/stv/details.js';

export default {
	components: {
		PvTreetable: primevue.treetable,
		PvColumn: primevue.column
	},
	directives: {
		drop,
		dragClick
	},
	inject: {
		$reloadList: {
			from: '$reloadList',
			default: () => {}
		},
		currentSemester: {
			from: 'currentSemester',
			required: true
		},
		appConfig: {
			from: 'appConfig',
			default: {
				number_displayed_past_studiensemester: 5
			}
		}
	},
	emits: [
		'selectVerband'
	],
	props: {
		endpoint: {
			type: Object,
			required: true,
		},
		preselectedKey: {
			type: String,
			default: null
		}
	},
	data() {
		return {
			loading: true,
			nodes: [],
			selectedKey: [],
			expandedKeys: {},
			filters: {}, // TODO(chris): filter only 1st level?
			favorites: {on: false, list: []}
		}
	},
	computed: {
		filteredNodes() {
			if (this.favorites.on)
				return this.nodes.filter(node => this.favorites.list.includes(node.key));

			return this.nodes;
		},
		noSemReloadNodes() {
			return this.nodes.reduce(this.mapNodesToNoSemReloadNodes, []);
		}
	},
	watch: {
		'preselectedKey': function (newVal, oldVal) {
			if (newVal !== oldVal) {
				this.setPreselection();
			}
		},
		'appConfig.number_displayed_past_studiensemester'(newVal, oldVal) {
			if (oldVal !== undefined) {
				this.noSemReloadNodes.forEach(node => {
					delete node.children;
					this.onExpandTreeNode(node);
				});
			}
		}
	},
	methods: {
		findNodeByKey(key, arr) {
			if (!arr)
				arr = this.nodes;
			let res = arr.filter(n => n.key == key);
			if (res.length)
				return res.pop();
			res = arr.map(n => n.children ? this.findNodeByKey(key, n.children) : null).filter(a => a);
			if (res.length)
				return res.pop();
			return null;
		},
		async onExpandTreeNode(node) {
			if (!node.children) {
				if (node.data.link) {
					let activeEl = null;
					this.$nextTick(() => {
						this.$nextTick(() => {
							activeEl = document.activeElement;
						});
					});
					this.loading = true;
					
					return this.$api
						.call(this.endpoint.get(node.data.link))
						.then(result => result.data)
						.then(result => {
							const subNodes = result.map(this.mapResultToTreeData);
							const realNode = this.findNodeByKey(node.key);
							if (realNode)
								realNode.children = subNodes;
							else
								node.children = subNodes; // NOTE(chris): fallback should never be the case

							let treeitem = this.$refs.tree.$el.querySelector('[data-tree-item-key="' + node.key + '"]');
							treeitem = treeitem.closest('[role="row"]');

							this.$nextTick(() => {
								if (activeEl == document.activeElement)
									treeitem.dispatchEvent(new KeyboardEvent('keydown', {
										code: 'ArrowDown',
										key: 'ArrowDown'
									}));
							});

							this.loading = false;
						})
						.catch(this.$fhcAlert.handleSystemError);
				}
			}
		},
		onSelectTreeNode(node) {
			if (node.data.link)
				this.$emit('selectVerband', {link: node.data.link, studiengang_kz: node.data.stg_kz, semester: node.data.semester, orgform_kurzbz: node.data.orgform_kurzbz});
		},
		mapNodesToNoSemReloadNodes(result, node) {
			if (node.data.no_sem_reload)
				result.push(node);
			if (node.children)
				result = node.children.reduce(this.mapNodesToNoSemReloadNodes, result);
			return result;
		},
		mapResultToTreeData(el) {
			const cp = {
				key: ("" + el.link).replace('/', '-'),
				data: el,
				label: el.name
			};

			if (el.children)
				cp.children = el.children.map(this.mapResultToTreeData);
			else
				cp.leaf = el.leaf || false;

			return cp;
		},
		filterFav() {
			this.favorites.on = !this.favorites.on;
			this.$api
				.call(this.endpoint.favorites.set(
					JSON.stringify(this.favorites)
				));
		},
		markFav(key) {
			let index = this.favorites.list.indexOf(key.data.link + '');

			if (index != -1) {
				this.favorites.list.splice(index, 1);
			} else {
				this.favorites.list.push(key.data.link + '');
			}

			this.$api
				.call(this.endpoint.favorites.set(
					JSON.stringify(this.favorites)
				));
		},
		unsetFavFocus(e) {
			if (e.target.dataset?.linkFavAdd !== undefined) {
				e.target.tabIndex = -1;
			} else {
				let items = e.target.querySelectorAll('[data-link-fav-add]:not([tabindex="-1"])');
				items.forEach(el => el.tabIndex = document.activeElement == el ? 0 : -1);
			}
		},
		setFavFocus(e) {
			if (e.target.dataset?.linkFavAdd !== undefined) {
				e.target.tabIndex = 0;
			} else {
				let items = e.target.querySelectorAll('[data-link-fav-add][tabindex="-1"]');
				items.forEach(el => el.tabIndex = 0);
			}
		},
		async setPreselection()
		{
			if (!this.preselectedKey)
			{
				this.selectedKey = null;
				return;
			}

			let rawKey = this.preselectedKey

			if (!rawKey || typeof rawKey !== 'string')
				return;

			const parts = this.preselectedKey.split('/');
			let currentKey = parts[0];
			let currentNode = this.findNodeByKey(currentKey);

			if (!currentNode)
				return;

			if(this.selectedKey)
			{
				const currentSelectedKey = Object.keys(this.selectedKey).find(Boolean);
				if (currentSelectedKey) {
					if (currentSelectedKey == currentKey)
						return;
					/**
					 * Do not select a new entry if the current is a child of the new one.
					 * This happens if a child entry of a new stg is selected and the router
					 * tries to select the stg root entry (because subtrees do not have
					 * routes yet)
					 */
					const isChild = this.findNodeByKey(
						currentSelectedKey,
						currentNode.children || []
					);
					if (isChild)
						return;
				}
			}

			for (let i = 1; i < parts.length; i++)
			{
				this.expandedKeys[currentNode.key] = true;

				await this.onExpandTreeNode(currentNode);

				currentKey += '-' + parts[i];
				currentNode = this.findNodeByKey(currentKey);

				if (!currentNode)
				{
					return;
				}
			}

			this.selectedKey = {[currentNode.key]: true};
			this.onSelectTreeNode(currentNode);
		},
		async toggleTreeNode(node) {
			if (this.expandedKeys[node.key]) {
				delete this.expandedKeys[node.key];
			} else if (!node.leaf) {
				await this.onExpandTreeNode(node);
				this.expandedKeys[node.key] = true;
			}
		},
		getStudentAjaxId(student) {
			let res = student.id;
			if (student.vorname && student.nachname)
				res += ' (' + student.vorname + ' ' + student.nachname + ')';
			return res;
		},
		dropStudents(node, students) {
			const data = node.data;
			
			let endpoint;
			if (data.gruppe_kurzbz) {
				endpoint = students.map(student => [
					this.getStudentAjaxId(student),
					ApiStvGroups.add(
						student.id,
						data.gruppe_kurzbz,
						this.currentSemester
					)
				]);
			} else {
				const { semester, verband, gruppe } = data;
				const params = { semester, verband, gruppe };
				endpoint = students.map(student => [
					this.getStudentAjaxId(student),
					ApiStvDetails.saveStudent(
						student.id,
						this.currentSemester,
						params
					)
				]);
			}

			return this.$api
				.call(endpoint)
				.then(this.$reloadList)
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
	mounted() {
		this.$api
			.call(this.endpoint.get())
			.then(result => {
				this.nodes = result.data.map(el => {
					el.root = true;
					return this.mapResultToTreeData(el);
				});
				this.setPreselection();
				this.loading = false;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(this.endpoint.favorites.get())
			.then(result => {
				if (result.data) {
					this.favorites = JSON.parse(result.data);
				}
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: /* html */`
	<div class="overflow-auto" tabindex="-1">
		<pv-treetable
			ref="tree"
			class="stv-verband p-treetable-sm"
			:value="filteredNodes"
			@node-expand="onExpandTreeNode"
			selection-mode="single"
			v-model:expanded-keys="expandedKeys"
			v-model:selection-keys="selectedKey"
			@node-select="onSelectTreeNode"
			scrollable
			scroll-height="flex"
			@focusin="setFavFocus"
			@focusout="unsetFavFocus"
			:filters="filters"
		>
			<pv-column
				field="name"
				expander
				class="text-break"
			>
				<template #header>
					<div class="text-right">
						<div class="p-input-icon-left">
							<i class="pi pi-search"></i>
							<input
								type="text"
								v-model="filters['global']"
								class="form-control ps-5"
								placeholder="Search"
							>
						</div>
					</div>
				</template>
				<template #body="{ node }">
					<span
						v-if="['semester', 'verband', 'gruppe', 'gruppe_kurzbz'].some(key => node.data.hasOwnProperty(key))"
						:data-tree-item-key="node.key"
						:title="node.data.studiengang_kz"
						v-drag-click="() => toggleTreeNode(node)"
						v-drop:link-strict.student-collection="(evt, students) => dropStudents(node, students)"
					>
						{{ node.data.name }}
					</span>
					<span
						v-else
						:data-tree-item-key="node.key"
						:title="node.data.studiengang_kz"
						v-drag-click="() => toggleTreeNode(node)"
					>
						{{ node.data.name }}
					</span>
				</template>
			</pv-column>
			<pv-column
				field="fav"
				class="flex-shrink-0 flex-grow-0"
				header-class="flex-shrink-0 flex-grow-0"
			>
				<template #header>
					<a
						v-if="favorites.on || favorites.list.length"
						href="#"
						@click.prevent="filterFav"
					>
						<i
							:class="favorites.on ? 'fa-solid' : 'fa-regular'"
							class="fa-star"
						></i>
					</a>
				</template>
				<template #body="{ node }">
					<a
						v-if="node.data.root"
						href="#"
						tabindex="-1"
						data-link-fav-add
						@click.prevent="markFav(node)"
						@keydown.enter.stop.prevent="markFav(node)"
					>
						<i
							:class="favorites.list.includes(node.data.link + '') ? 'fa-solid' : 'fa-regular'"
							class="fa-star"
						></i>
					</a>
				</template>
			</pv-column>
			<pv-column field="studiengang_kz" class="d-none"></pv-column>
		</pv-treetable>
	</div>`
};
