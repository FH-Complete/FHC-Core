export default {
  props: {
    data: Object,
  },

  data() {
    return {
      originalValue: null,
      zustellKontakteCount: null,
    };
  },

  inject: ["getZustellkontakteCount"],

  methods: {
    updateValue: function (event, bind) {
      if (bind === "zustellung") {
        this.data[bind] = event.target.checked;
      } else {
        //? sets the value of a property to null when an empty string is entered to keep the isChanged function valid
        this.data[bind] = event.target.value === "" ? null : event.target.value;
      }
      this.$emit("profilUpdate", this.isChanged ? this.data : null);
      this.zustellKontakteCount = this.getZustellkontakteCount();
    },
  },

  computed: {
    showZustellKontakteWarning: function () {
	  // if the kontakt is already a zustellungskontakt when the user is editing the kontakt, then no warning is shown and the zustellung will be overwritten
	  if (JSON.parse(this.originalValue).zustellung) {
	    return false;
	  }
	  const kontakteOfSelectedType = this.zustellKontakteCount.filter((element) => {
		  return element.kontakttyp === this.data?.kontakttyp
	  });
      // if zustellKontakteCount is not 0 and the own kontakt has the flag zustellung set to true
      if (!this.zustellKontakteCount.some((element) => element.kontakt_id === this.data.kontakt_id)) {
        return this.data.zustellung && kontakteOfSelectedType.length;
      }
      return this.kontakteOfSelectedType.length >= 2 && this.data.zustellung;
    },
    isChanged: function () {
      //? returns true if the original passed data object was changed
      if (!this.data.kontakt || !this.data.kontakttyp) {
        return false;
      }
      return JSON.stringify(this.data) !== this.originalValue;
    },
  },

  created() {
    this.originalValue = JSON.stringify(this.data);
    this.zustellKontakteCount = this.getZustellkontakteCount();
  },
  
  template:
    /*html*/
    `

    <div class="gy-3 row align-items-center justify-content-center">

    <div v-if="!data.kontakt_id" class="col-12">


        <div  class="form-underline">
            <div class="form-underline-titel">{{$p.t('profilUpdate','kontaktTyp')}}</div>

            <select :value="data.kontakttyp" @change="updateValue($event,'kontakttyp')" class="form-select" aria-label="Select Kontakttyp">
                <option selected></option>
                <option value="email">{{$p.t('person','email')}}</option>
                <option value="telefon">{{$p.t('person','telefon')}}</option>
                <option value="notfallkontakt">{{$p.t('profilUpdate','notfallkontakt')}}</option>
                <option value="mobil">{{$p.t('profilUpdate','mobiltelefonnummer')}}</option>
                <option value="homepage">{{$p.t('profilUpdate','homepage')}}</option>
                <option value="fax">{{$p.t('profilUpdate','faxnummer')}}</option>

            </select>
        </div>

    </div>
    <div class="col-12">

        <!-- rendering KONTAKT emails -->


        <div class="form-underline">
        <div class="form-underline-titel">{{data.kontakttyp?data.kontakttyp:$p.t('global','kontakt')}}</div>

        <input :aria-label="data.kontakttyp" :title="data.kontakttyp" :disabled="data.kontakttyp?false:true"  class="form-control"   :value="data.kontakt" @input="updateValue($event,'kontakt')" :placeholder="data.kontakt">
        </div>

    </div>
    <div class="col-12">

    <div  class="form-underline">
    <div class="form-underline-titel">{{$p.t('global','anmerkung')}}</div>

    <input :aria-label="$p.t('global','anmerkung')" :title="$p.t('global','anmerkung')" class="form-control" :value="data.anmerkung" @input="updateValue($event,'anmerkung')" :placeholder="data.anmerkung">
    </div>

    </div>

    <!-- warning message for too many zustellungs Kontakte -->
    <div v-if="showZustellKontakteWarning" class="col-12 ">
    <div class="alert alert-warning mx-2">
      <span>{{$p.t('profilUpdate','zustell_kontakte_warning')}}</span>
    </div>
    </div>
    <!-- End of warning -->

    <div  class="d-flex flex-row justify-content-start col-12  allign-middle">
        <span style="opacity: 0.65; font-size: .85rem; " class="px-2">{{$p.t('profilUpdate','zustellungsKontakt')}}</span>

        <input :aria-label="$p.t('profilUpdate','zustellungsKontakt')" :title="$p.t('profilUpdate','zustellungsKontakt')" class="form-check-input " type="checkbox" :checked="data.zustellung" @change="updateValue($event,'zustellung')" id="flexCheckDefault">
    </div>
  </div>
    `,
};
