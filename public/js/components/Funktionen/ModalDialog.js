import { usePhrasen } from '../../mixins/Phrasen.js';

export const ModalDialog = {
	name: 'ModalDialog',
    props: {
        type: String,
        title: String,
    },
    expose: ['show', 'hide'],
    setup(props, { emit }) {
       
        let modalConfirmEle = Vue.ref(null);
        let thisModalObj;
        let _resolve;
        let _reject;
        const { t } = usePhrasen();

        Vue.onMounted(() => {
            thisModalObj = new bootstrap.Modal(modalConfirmEle.value);
        });
        
        const show = async () => {
            thisModalObj.show();
            return new Promise(function (resolve, reject) {
              _resolve = resolve;
              _reject = reject;
            });
        }

        function hide() {
            thisModalObj.hide();
        }

        const ok = () => {
            _resolve(true);
          }
          
        const cancel = () =>  {
            _resolve(false);
        }        

        return { modalConfirmEle, show, hide, ok, cancel, t };
    },

    template:`
    <div class="modal fade " id="customModalDialog" tabindex="-1" aria-labelledby=""
        aria-hidden="true" ref="modalConfirmEle">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customModalDialogLabel">{{ title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <slot name="body" />
                </div>
                <div class="modal-footer">
                    <slot name="footer"></slot>
                    <button type="button" class="btn btn-secondary" @click="cancel" data-bs-dismiss="modal">
                      {{ t('ui','abbrechen') }}
                    </button>
                    <button type="button" class="btn btn-primary" @click="ok" data-bs-dismiss="modal">
                        {{ t('ui','ok') }}
                    </button>
                </div>
            </div>
        </div>
    </div>`
};
