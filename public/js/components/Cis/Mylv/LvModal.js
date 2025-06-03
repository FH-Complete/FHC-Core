import BsModal from "../../Bootstrap/Modal.js";
import Alert from "../../Bootstrap/Alert.js";
import LvMenu from "./LvMenu.js"

import ApiStundenplan from '../../../api/factory/stundenplan.js';
import ApiAddons from '../../../api/factory/addons.js';

export default {
	components: {
		BsModal,
		Alert,
		LvMenu,
	},
	mixins: [BsModal],
	props: {
		event:Object,
		title:{
			type:String,
			default:"title"
		},
		showMenu:{
			type:Boolean,
			default:true,
		},
	/*
	* NOTE(chris):
	* Hack to expose in "emits" declared events to $props which we use
	* in the v-bind directive to forward all events.
	* @see: https://github.com/vuejs/core/issues/3432
	*/
		onHideBsModal: Function,
		onHiddenBsModal: Function,
		onHidePreventedBsModal: Function,
		onShowBsModal: Function,
		onShownBsModal: Function,
	},
	data() {
		return {
			menu: null,
			result: false,
			info: null,
		};
	},
	methods:{
		onHideModal: function(){
			this.menu = null;
		},
		onModalShow: function()
		{
			// do not load the menu if the menu is not getting rendered
			if(!this.showMenu) return;

			if (this.event.type == 'lehreinheit') {
				this.$api
					.call(ApiStundenplan.getLehreinheitStudiensemester(this.event.lehreinheit_id[0]))
					.then(res => res.data)
					.then(studiensemester_kurzbz => this.$api.call(
						ApiAddons.getLvMenu(
							this.event.lehrveranstaltung_id,
							studiensemester_kurzbz
						)
					))
					.then(res => {
						if (res.data) {
							this.menu = res.data;
						}
					});
			}
		}
	},
	mounted() {
		this.modal = this.$refs.modalContainer.modal;
	},
	popup(options) {
		return BsModal.popup.bind(this)(null, options);
	},
	template: /*html*/ `
	<bs-modal ref="modalContainer" @showBsModal="onModalShow" @hideBsModal="onHideModal" v-bind="$props" :bodyClass="''" dialogClass='modal-lg' class="bootstrap-alert" :backdrop="false" >
		<template v-slot:title>
			<slot name="modalTitle"></slot>
		</template>
		<template v-slot:default>
			<slot name="modalContent"></slot>
			
			<template v-if="showMenu && this.menu">
				<h3>{{$p.t('lehre','lehrveranstaltungsmenue')}}</h3>
				<lv-menu :menu="menu"></lv-menu>
			</template>
		</template>
		<!-- optional footer -->
		<template  v-slot:footer >
			<button class="btn btn-outline-secondary " @click="hide">{{$p.t('ui','cancel')}}</button>    
		</template>
		<!-- end of optional footer --> 
	</bs-modal>`,
};
