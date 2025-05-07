import BsModal from "../../Bootstrap/Modal.js";
import LvMenu from "./LvMenu.js";

import ApiAddons from '../../../api/factory/addons.js';

export default  {
  
    props:{
        event:{
            type:Object,
            required:true,
            default:null,
        },
		studiensemester: {
			type: String,
			required: false,
			default: null,
		},
		titel: {
			type: String,
			required: false,
			default: null,
		},
		// prop used to preselect a menu item and skip the grid overview
		preselectedMenu: {
			type: Object,
			required: false,
			default: null,
		}
    },
    data(){
        return {
            result: false,
            menu: [],
			isMenuSelected:false,
			hasLvStundenplanEintraege: true,
			lvEvaluierungMessage: "",
        }
    },
    mixins:[BsModal],
    components:{
        BsModal,
		LvMenu,
    },
	inject: ["studium_studiensemester"],
    methods:{
        
        hiddenModal: function(){
			this.isMenuSelected = false;
        },
        showModal: function(){
			if (!this.preselectedMenu) {
                this.$api
					.call(ApiAddons.getLvMenu(this.event.lehrveranstaltung_id, (this.studiensemester ?? this.event.studiensemester_kurzbz)))
                    .then(res => {
    					if (res.data) {
    						this.menu = res.data;
    					}
    				});
			} else {
				this.isMenuSelected = true;
			}

			// check lv evaluierung info
			if (this.studium_studiensemester) {
				this.$fhcApi.factory.studium.getLvEvaluierungInfo(this.studium_studiensemester, this.event.lehreinheit_id ?? this.event.lehrveranstaltung_id)
					.then(data => data.data)
					.then(res => {
						this.lvEvaluierungMessage = res.message;
					})
			}

			// check if the lv has stundenplan entries for this studiensemester
			if (this.studiensemester && this.event) {
				return this.$fhcApi.factory.studium.getLvStundenplanForStudiensemester(this.studiensemester, this.event.lehreinheit_id ?? this.event.lehrveranstaltung_id)
					.then(data => data.data)
					.then(res => {
						if (Array.isArray(res) && res.length > 0) {
							this.hasLvStundenplanEintraege = true;
						} else {
							this.hasLvStundenplanEintraege = false;
						}
					});
			}
			
        },
    },
	mounted(){
        this.modal = this.$refs.modalContainer;
    },
	beforeUnmount(){
		this.$refs.modalContainer.hide();
	},
    template:/*html*/`
    <bs-modal :bodyClass="isMenuSelected ? '' : 'px-4 py-5'" @showBsModal="showModal" @hiddenBsModal="hiddenModal" ref="modalContainer" :dialogClass="{'modal-lg': !isMenuSelected, 'modal-fullscreen':isMenuSelected}">

		<template #title>
            <template v-if="titel">
				<span>{{titel}}</span>
			</template>
			<template v-else>
				<span v-if="event?.lehrfach_bez ">{{event?.lehrfach_bez + (event?.stg_kurzbzlang?' / ' + event?.stg_kurzbzlang:'')}}</span>
				<span v-else>Lehrveranstaltungs Übersicht</span>
			</template>

        </template>
        <template #default>
			<div class="mb-4" v-if="lvEvaluierungMessage" v-html="lvEvaluierungMessage"></div>
			<slot name="content"></slot>
			<lv-menu v-model:isMenuSelected="isMenuSelected" :hasLvStundenplanEintraege="hasLvStundenplanEintraege" :preselectedMenu="preselectedMenu" :menu="menu" @hideModal="hide"></lv-menu>
        </template>
        
    </bs-modal>

    
    `,
};