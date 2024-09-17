import BsModal from "../Bootstrap/Modal.js";
import Alert from "../Bootstrap/Alert.js";
import LvMenu from "../Cis/Mylv/LvMenu.js"

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
			isMenuSelected:false,
		};
	},
	computed: {
		start_time: function(){
			if(!this.event.start) return 'N/A';
			return this.event.start.getHours() + ":" + this.event.start.getMinutes();
		},
		end_time: function(){
			if (!this.event.end) return 'N/A';
			return this.event.end.getHours() + ":" + this.event.end.getMinutes();
		}
	},
	methods:{
		onModalShow: function(){
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
		onModalHide:function(){
			this.isMenuSelected = false;
		}
	},
	mounted() {
		this.modal = this.$refs.modalContainer.modal;
	},
	popup(options) {
		return BsModal.popup.bind(this)(null, options);
	},
	template: /*html*/ `
	<bs-modal ref="modalContainer" @showBsModal="onModalShow" @hideBsModal="onModalHide" v-bind="$props" :bodyClass="''" :dialogClass="{'modal-lg': !isMenuSelected, 'modal-fullscreen':isMenuSelected}" class="bootstrap-alert" backdrop="false" >
		<template v-slot:title>
			<template v-if="event.titel">{{ event.titel + ' - ' + event.lehrfach_bez + ' [' + event.ort_kurzbz+']'}}</template>
			<template v-else>{{ event.lehrfach_bez + ' [' + event.ort_kurzbz+']'}}</template>
		</template>
		<template v-slot:default>
			<template v-if="!isMenuSelected">
				<div class="row">
					<div class="col">
						<h3>{{$p.t('lvinfo','lehrveranstaltungsinformationen')}}</h3>
					</div>
				</div>

				<div class="row">
					<div class="offset-3 col-4"><span>Datum:</span></div>
					<div class=" col"><span>{{event.datum}}</span></div>
				</div>
				<div class="row">
					<div class="offset-3 col-4"><span>Raum:</span></div>
					<div class=" col"><span>{{event.ort_kurzbz}}</span></div>
				</div>
				<div class="row">
					<div class="offset-3 col-4"><span>LV:</span></div>
					<div class=" col"><span>{{'('+event.lehrform+') ' + event.lehrfach_bez}}</span></div>
				</div>
				<div class="row">
					<div class="offset-3 col-4"><span>Lektor:</span></div>
					<div class=" col"><span>{{event.lektor.map(lektor=>lektor.kurzbz).join("/")}}</span></div>
				</div>
				<div class="row">
					<div class="offset-3 col-4"><span>Zeitraum:</span></div>
					<div class=" col"><span>{{start_time + ' - ' + end_time}}</span></div>
				</div>
				<hr class="my-5">
				<div v-if="menu" class="row">
					<div class="col">
						<h3>Lehrveranstaltungs Menu</h3>
					</div>
				</div>
			</template>
			<lv-menu v-model:isMenuSelected="isMenuSelected" :menu="menu"></lv-menu>
		</template>
		<!-- optional footer -->
		<template  v-slot:footer >
			<button class="btn btn-outline-secondary " @click="hide">{{$p.t('ui','cancel')}}</button>    
		</template>
		<!-- end of optional footer --> 
	</bs-modal>`,
};
