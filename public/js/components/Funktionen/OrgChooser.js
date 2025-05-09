import {CoreRESTClient} from '../../../js/RESTClient';

export const OrgChooser = {
	name: 'OrgChooser',
    props: {
      placeholder: String,
      customClass: String,
      oe: String,
    },
    emits: ["orgSelected"],
    setup(props, { emit }) {   

      const orgList = Vue.ref([]);
      const isFetching = Vue.ref(false);
      const oeRef = Vue.toRefs(props).oe
      const selected = Vue.ref();

      const fetchHead = async () => {
        isFetching.value = true
        try {
          const res = await CoreRESTClient.get(
            'extensions/FHC-Core-Personalverwaltung/api/frontend/v1/OrgAPI/getOrgHeads');
          orgList.value = CoreRESTClient.getData(res.data);
          if (orgList.value.length > 0)  {
            //orgList.value.reverse();
            if (props.oe == undefined || (props.oe != null && props.oe == '')) {
              selected.value = orgList.value[0].oe_kurzbz;
            }
            emit("orgSelected", selected.value);
          }
          isFetching.value = false          
        } catch (error) {
          console.log(error)
          isFetching.value = false
        }
      }

      Vue.onMounted(() => {
        fetchHead();
      })

      const orgSelected = (e) => {
        emit("orgSelected", e.target.value);
      }

      Vue.watch(
        oeRef,
        (val, old) => {
          console.log('prop value changed', val);
          selected.value = val;
        }
      )

      return   {
        orgList, selected,

        orgSelected
      }


    },
    template: `
    <select  id="orgHeadChooser" v-model="selected" @change="orgSelected" class="form-control-sm" aria-label=".form-select-sm " >
        <option v-for="(item, index) in orgList" :value="item.oe_kurzbz"  :key="item.oe_kurzbz">
            {{ item.bezeichnung }}
        </option>         
    </select>
    `
}    
