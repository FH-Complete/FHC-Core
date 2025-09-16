import Dms from "../../../../Form/Upload/Dms.js";

import ApiProfil from '../../../../../api/factory/profil.js';

export default {
  components: {
    AutoComplete: primevue.autocomplete,
	Dms: Dms
  },

  props: {
    data: Object,
    isMitarbeiter: {
      type: Boolean,
      default: false,
    },
    files: {
      type: Array,
      default: []
    },
  },

  inject: ["getZustelladressenCount", "updateFileID"],

  data() {
    return {
      gemeinden: [],
      ortschaftnamen: [],
      selectedNation: null,
      nationenList: [],
      originalValue: null,
      zustellAdressenCount: null,
	  dmsData: [],
	  fileschanged: false
    };
  },

  watch: {
    "data.gemeinde": function (newValue, oldValue) {
      this.$emit("profilUpdate", this.isChanged ? this.data : null);
    },
    "data.ort": function (newValue, oldValue) {
      this.$emit("profilUpdate", this.isChanged ? this.data : null);
    },
  },

  methods: {

    autocompleteSearchGemeinden: function (event) {
      this.gemeinden = this.gemeinden.map((gemeinde) => gemeinde);
    },

    autocompleteSearchOrtschaftsnamen: function (event) {
      this.ortschaftnamen = this.ortschaftnamen.map((ortschaft) => ortschaft);
    },

    getGemeinde: function () {
      //? only query the gemeinde is the nation is Austria and the PLZ is greater than 999 and less than 32000
      if (
        this.data.nation &&
        this.data.nation === "A" &&
        this.data.plz &&
        this.data.plz > 999 &&
        this.data.plz < 32000
      ) {
        this.$api
          .call(ApiProfil.getGemeinden(this.data.nation, this.data.plz))
          .then((res) => {
            if (res.data.length) {
              this.gemeinden = [
                ...new Set(
                  res.data.map((element) => {
                    return element.name;
                  })
                ),
              ];
              this.ortschaftnamen = [
                ...new Set(
                  res.data.map((element) => {
                    return element.ortschaftsname;
                  })
                ),
              ];
            }
          });
      } else {
        this.gemeinden = [];
      }
    },

    updateValue: function (event, bind) {
      //? sets the value of a property to null when an empty string is entered to keep the isChanged function valid
      if (bind === "zustelladresse") {
        this.data[bind] = event.target.checked;
	  } else if(bind === 'files') {
		  if(this.dmsData.length > 0 && this.dmsData[0].type !== 'application/x.fhc-dms+json') {
			this.fileschanged = true;
		  }
		  this.updateFileID(this.dmsData);
      } else {
        this.data[bind] = event.target.value === "" ? null : event.target.value;
      }

      this.$emit("profilUpdate", this.isChanged ? this.data : null);
      // update the zustellAdressen count
      this.zustellAdressenCount = this.getZustelladressenCount();
    },

	deleteDmsData: function() {
		this.dmsData = [];
		this.updateValue(null, 'files');
	}
  },

  computed: {
    showZustellAdressenWarning: function () {

	  // if the address was already a zustellungsadresse when editing the address, then the warning will not be shown and the zustellungsadresse will just be overwritten
	  if (JSON.parse(this.originalValue).zustelladresse){
		return false;
	  }
      // if zustellAdressenCount is not 0 and the own kontakt has the flag zustellung set to true
      if (!this.zustellAdressenCount.includes(this.data.adresse_id)) {
        return this.data.zustelladresse && this.zustellAdressenCount.length;
      }
      return this.zustellAdressenCount.length >= 2 && this.data.zustelladresse;
    },
    isChanged: function () {
      if (
        !this.data.strasse ||
        !this.data.plz ||
        !this.data.ort ||
        !this.data.typ ||
        this.dmsData.length === 0
      ) {
        return false;
      }

      const datachanged = this.originalValue !== JSON.stringify(this.data);
      return datachanged || this.fileschanged;
    },
  },

  created() {
    // get all available nationen
    this.$api
      .call(ApiProfil.getAllNationen())
      .then(res => {
        this.nationenList = res.data;
        this.getGemeinde();
      });
   
    this.originalValue = JSON.stringify(this.data);
    this.zustellAdressenCount = this.getZustelladressenCount();
  },

  mounted() {
    if (this.files) {
      this.dmsData = this.files;
    }
  },

  template: /*html*/ `
<div class="gy-3 row justify-content-center align-items-center">
  <!-- warning message for too many zustellungs Adressen -->
  <div v-if="showZustellAdressenWarning" class="col-12 ">
    <div class="card bg-danger mx-2">
      <div class="card-body text-white ">
	  <span>{{$p.t('profilUpdate','zustell_adressen_warning')}}</span>
      </div>
    </div>
  </div>
  <!-- End of warning -->


  <div class="col-12 ">
    <div class="form-check mb-2">
      <input class="form-check-input" type="checkbox" @change="updateValue($event,'zustelladresse')" :checked="data.zustelladresse" id="flexCheckDefault">
      <label class="form-check-label" for="flexCheckDefault">
      {{$p.t('person','zustelladresse')}}
      </label>
    </div>
  </div>

  <!-- NATION -->
  <div class="col-8">
    <div class="form-underline ">
      <div class="form-underline-titel">{{$p.t('person','nation')}}*</div>
      <select  :value="data.nation" @change="updateValue($event,'nation')" @change="getGemeinde" class="form-select" aria-label="Select Kontakttyp">
        <option selected></option>
        <option :value="nation.code" v-for="nation in nationenList">{{nation.langtext}}</option>
      </select>
    </div>
  </div>

  <!-- PLZ -->
  <div class=" col-4">
    <div class="form-underline">
      <div class="form-underline-titel">{{$p.t('person','plz')}}*</div>
      <input class="form-control" :value="data.plz" :aria-label="$p.t('person','plz')" :title="$p.t('person','plz')" @input="updateValue($event,'plz')" @input="getGemeinde" :placeholder="data.plz">
    </div>
  </div>

  <!-- GEMEINDE -->
  <div class="col-lg-6">
    <div class="form-underline ">
      <div class="form-underline-titel">{{$p.t('person','gemeinde')}}*</div>
      <auto-complete :aria-label="$p.t('person','gemeinde')" class="w-100" v-model="data.gemeinde" dropdown :forceSelection="data.nation ==='A'?true:false" :suggestions="gemeinden" @complete="autocompleteSearchGemeinden" ></auto-complete>
    </div>
  </div>

  <!-- ORT -->
  <div  class="col-lg-6" >
    <div class="form-underline ">
      <div class="form-underline-titel">{{$p.t('person','ort')}}*</div>
      <auto-complete :aria-label="$p.t('person','ort')" class="w-100" v-model="data.ort" dropdown :forceSelection="data.nation ==='A'?true:false" :suggestions="ortschaftnamen" @complete="autocompleteSearchOrtschaftsnamen" ></auto-complete>
    </div>
  </div>

  <!-- STRASSE -->
  <div  class="col-lg-8">
    <div class="form-underline ">
      <div class="form-underline-titel">{{$p.t('person','strasse')}}*</div>
      <input :aria-label="$p.t('person','strasse')" class="form-control" :value="data.strasse" @input="updateValue($event,'strasse')" :placeholder="data.strasse">
    </div>
  </div>
  
  <!-- ADRESSEN TYP -->
  <div class="col-lg-4">
    <div  class="form-underline">
      <div class="form-underline-titel">{{$p.t('profilUpdate','kontaktTyp')}}*</div>
      <select  :value="data.typ" @change="updateValue($event,'typ')" class="form-select" aria-label="Select Kontakttyp">
        <option selected></option>
        <option value="Nebenwohnsitz">{{$p.t('profilUpdate','nebenwohnsitz')}}</option>
        <option value="Hauptwohnsitz">{{$p.t('profilUpdate','hauptwohnsitz')}}</option>
        <option v-if="isMitarbeiter" value="Homeoffice">{{$p.t('profilUpdate','homeoffice')}}</option>
        <option v-if="isMitarbeiter" value="Rechnungsadresse">{{$p.t('profilUpdate','rechnungsadresse')}}</option>
      </select>    
    </div>
  </div>

	<div class="row g-2">
		<div class="col">
			<dms
				ref="update"
				id="files"
				name="files"
				:multiple="false"
				v-model="dmsData"
				@update:model-value="updateValue($event,'files')"
			></dms>
		</div>
		<div class="col-auto">
			<button
					@click="deleteDmsData"
					class="btn btn-danger"
					:aria-label="$p.t('profilUpdate','deleteAttachment')"
					:title="$p.t('profilUpdate','deleteAttachment')"
			><i style="color:white" class="fa fa-trash" aria-hidden="true"></i></button>
		</div>
	</div>

</div>
    `,
};
