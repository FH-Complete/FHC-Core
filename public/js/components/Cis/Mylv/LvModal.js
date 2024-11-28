import BsModal from "../../Bootstrap/Modal.js";
import Alert from "../../Bootstrap/Alert.js";
import LvMenu from "./LvMenu.js"
import LvInfo from "./LvInfo.js"

export default {
	components: {
		BsModal,
		Alert,
		LvMenu,
		LvInfo,
	},
	mixins: [BsModal],
	props: {
		event:Object,
		title:{
			type:String,
			default:"title"
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
			menu: [],
			result: false,
			info: null,
		};
	},
	methods:{
		onModalShow: function()
		{
			if (this.event.type == 'lehreinheit') {
				this.$fhcApi.factory.stundenplan.getLehreinheitStudiensemester(this.event.lehreinheit_id[0]).then(
					res=>res.data
				).then(
					studiensemester_kurzbz =>{
						this.$fhcApi.factory.addons.getLvMenu(this.event.lehrveranstaltung_id, studiensemester_kurzbz).then(res => {
							if (res.data) {
								this.menu = res.data;
							}
						});
					}
				)
			}
		},
	},
	mounted() {
		this.modal = this.$refs.modalContainer.modal;
	},
	popup(options) {
		return BsModal.popup.bind(this)(null, options);
	},
	template: /*html*/ `
	<bs-modal ref="modalContainer" @showBsModal="onModalShow" @hideBsModal="onModalHide" v-bind="$props" :bodyClass="''" dialogClass='modal-lg' class="bootstrap-alert" backdrop="false" >
		<template v-slot:title>
			<template v-if="event.titel">{{ event.titel + ' - ' + event.lehrfach_bez + ' [' + event.ort_kurzbz+']'}}</template>
			<template v-else>{{ event.lehrfach_bez + ' [' + event.ort_kurzbz+']'}}</template>
		</template>
		<template v-slot:default>
			<h3 >{{$p.t('lvinfo','lehrveranstaltungsinformationen')}}</h3>
			<lv-info :event="event"></lv-info>
			<h3 >Lehrveranstaltungs Menu</h3>
			<lv-menu :menu="menu"></lv-menu>
		</template>
		<!-- optional footer -->
		<template  v-slot:footer >
			<button class="btn btn-outline-secondary " @click="hide">{{$p.t('ui','cancel')}}</button>    
		</template>
		<!-- end of optional footer --> 
	</bs-modal>`,
};