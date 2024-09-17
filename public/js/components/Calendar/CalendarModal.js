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
				<h3>{{$p.t('lvinfo','lehrveranstaltungsinformationen')}}</h3>
				<table class="table table-hover mb-4">
					<tbody>
						<tr>
							<th>{{
								$p.t('global','datum')?
								$p.t('global','datum')+':'
								:''
							}}</th>
							<td>{{event.datum}}</td>
						</tr>
						<tr>
							<th>{{
								$p.t('global','raum')?
								$p.t('global','raum')+':'
								:''
							}}</th>
							<td>{{event.ort_kurzbz}}</td>
						</tr>
						<tr>
							<th>{{
								$p.t('lehre','lehrveranstaltung')?
								$p.t('lehre','lehrveranstaltung')+':'
								:''
							}}</th>
							<td>{{'('+event.lehrform+') ' + event.lehrfach_bez}}</td>
						</tr>
						<tr>
							<th>{{
								$p.t('lehre','lektor')?
								$p.t('lehre','lektor')+':'
								:''
							}}</th>
							<td>{{event.lektor.map(lektor=>lektor.kurzbz).join("/")}}</td>
						</tr>
						<tr>
							<th>{{
								$p.t('ui','zeitraum')?
								$p.t('ui','zeitraum')+':'
								:''
							}}</th>
							<td>{{start_time + ' - ' + end_time}}</td>
						</tr>
						<tr>
							<th>{{
								$p.t('lehre','organisationseinheit')?
								$p.t('lehre','organisationseinheit')+':'
								:''
							}}</th>
							<td>{{event.organisationseinheit}}</td>
						</tr>
					</tbody>
				</table>
				<h3>Lehrveranstaltungs Menu</h3>
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
