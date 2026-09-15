export default {
	name: 'HorizontalSplit',
	props: {
		defaultRatio: {
			type: Array,
			default: () => [50, 50]
		}
	},
	data: function () {
		return {
			availWidth: 0,
			leftwidth: 0,
			rightwidth: 0,
			mousePosX: 0,
			resize: false,
			hsplitterOffset: 0,
			selfOffsetLeft: 0
		};
	},
	template: `
    <div ref="horizontalsplit" class="horizontalsplit-container">
        <div ref="leftpanel" class="horizontalsplitted"
             :style="{ width: this.leftwidthcss }">
            <slot name="left">
                <p>Left Panel</p>
            </slot>
        </div>
        <div ref="hsplitter" class="horizontalsplitter"
             :class="this.leftOrRightClass" @mousedown="this.dragStart">
            <div class="splitactions horizontal" :class="this.leftOrRightClass">
            	<span @click="this.collapseRight" class="splitaction">
                    <i class="fas fa-angle-right text-muted"></i>
                </span>
                <span @dblclick="this.showBoth" class="splitaction resize">
                    <i class="fas fa-grip-lines-vertical text-muted"></i>
                </span>
                <span @click="this.collapseLeft" class="splitaction">
                    <i class="fas fa-angle-left text-muted"></i>
                </span>
            </div>
        </div>
        <div ref="rightpanel" class="horizontalsplitted"
             :style="{ width: this.rightwidthcss }">
            <slot name="right">
                <p/>
            </slot>
        </div>
    </div>
    `,
	mounted: function () {
		this.calcWidths();
		this.trackHorizontalSplitterOffsetLeft();
		window.addEventListener('resize', this.calcWidths);
	},
	updated: function () {
		this.trackHorizontalSplitterOffsetLeft();
	},
	beforeDestroy: function () {
		window.removeEventListener('resize', this.calcWidths);
	},
	methods: {
		calcWidths: function () {
			var oldavailWidth = this.availWidth;
			this.selfOffsetLeft = this.$refs.horizontalsplit.offsetLeft;
			this.availWidth = this.$refs.horizontalsplit.offsetWidth - this.$refs.hsplitter.offsetWidth;

			if ((this.leftwidth === 0 && this.rightwidth === 0) || oldavailWidth === 0) {
				this.leftwidth = Math.floor(this.availWidth * (this.defaultRatio[0] / 100));
			} else {
				this.leftwidth = Math.floor(((this.leftwidth * 100) / oldavailWidth) / 100 * this.availWidth);
			}
			this.rightwidth = this.availWidth - this.leftwidth;
		},
		collapseLeft: function () {
			this.calcWidths();
			this.leftwidth = 0;
			this.rightwidth = this.availWidth;
		},
		collapseRight: function () {
			this.calcWidths();
			this.leftwidth = this.availWidth;
			this.rightwidth = 0;
		},
		showBoth: function () {
			this.leftwidth = Math.floor(this.availWidth * (this.defaultRatio[0] / 100));
			this.rightwidth = this.availWidth - this.leftwidth;
		},
		isCollapsed: function () {
			if (this.leftwidth === 0) {
				return 'left';
			} else if (this.rightwidth === 0) {
				return 'right';
			} else {
				return false;
			}
		},
		dragStart: function (e) {
			e.preventDefault();
			e.stopPropagation();
			window.addEventListener('mouseup', this.dragEnd);
			window.addEventListener('mousemove', this.drag);
			this.resize = true;
			this.mousePosX = e.clientX;
		},
		drag: function (e) {
			if (!this.resize) {
				return;
			}
			e.preventDefault();
			e.stopPropagation();
			var offsetX = e.clientX - this.mousePosX;
			this.leftwidth = this.leftwidth + offsetX;
			if (this.leftwidth < 0) {
				this.leftwidth = 0;
			}
			if (this.leftwidth > this.availWidth) {
				this.leftwidth = this.availWidth;
			}
			this.rightwidth = this.availWidth - this.leftwidth;
			this.mousePosX = e.clientX;
		},
		dragEnd: function (e) {
			e.preventDefault();
			e.stopPropagation();
			window.removeEventListener('mousemove', this.drag);
			window.removeEventListener('mouseup', this.dragEnd);
			this.resize = false;
			this.mousePosX = e.clientX;
		},
		trackHorizontalSplitterOffsetLeft: function () {
			this.hsplitterOffset = this.$refs.hsplitter.offsetLeft;
		}
	},
	computed: {
		leftOrRightClass: function () {
			return ((this.hsplitterOffset - this.selfOffsetLeft) <= Math.floor(this.availWidth / 2))
				? 'left'
				: 'right';
		},
		leftwidthcss: function () {
			return this.leftwidth + 'px';
		},
		rightwidthcss: function () {
			return this.rightwidth + 'px';
		}
	}
};