export default {
    name: "BaseModal",
    props: [
        "id",
        "cModalClass",      // optional: add custom classes to modal class
        "cModalDialogClass" // optional: add custom classes to modal-dialog class
    ],
    onMounted()
    {
        const modal = new bootstrap.Modal(this.$refs.modal, {})
        modal.show();
    },
    template: `
    <div class="modal fade"
        ref="modal"
        :class="this.cModalClass"
        :id="this.id"
        :aria-labelledby="this.id + '_label'" aria-hidden="true" tabindex="-1">       
            <div class="modal-dialog" :class="this.cModalDialogClass">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" :id="this.id + '_label'">
                        <slot name="title"></slot>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <slot name="body"></slot>
                </div>
                <div class="modal-footer">
                    <slot name="footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Abbrechen</button><!-- default -->
                    </slot>
                </div>
            </div>
        </div>
    </div>`
}
