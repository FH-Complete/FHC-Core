export default {
    name: "BaseOffcanvas",
    props: [
    "id",
    "cOffcanvasClass",      // optional: add custom classes to offcanvas class
    "closeFunc"
    ],
    computed: {
        OffcanvasClass() {
            return this.cOffcanvasClass || 'offcanvas-end'; // default: slide in from right to left
        }
    },
    template: `
    <div class="offcanvas"
        :class="this.OffcanvasClass"
        :id="this.id"
        :aria-labelledby="this.id + '_label'"
        tabindex="-1"
        data-bs-backdrop="false">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" :id="this.id + '_label'">
                <slot name="title"></slot>
            </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" @click="closeFunc" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div>
                <slot name="body"></slot>
            </div>
        </div>
    </div>`
}
