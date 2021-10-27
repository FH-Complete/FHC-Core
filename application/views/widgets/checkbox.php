<?php HTMLWidget::printStartBlock(${HTMLWidget::HTML_ARG_NAME}); ?>

	<div class="div-table">
		<div class="div-row">
			<?php
				if (isset(${HTMLWidget::HTML_ARG_NAME}[HTMLWidget::LABEL]))
				{
			?>
				<div class="div-cell-label valign-middle width-150px">
					<label
						for="<?php echo ${HTMLWidget::HTML_ARG_NAME}[HTMLWidget::HTML_ID]; ?>"
						<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::REQUIRED); ?>
					>
						<?php echo ${HTMLWidget::HTML_ARG_NAME}[HTMLWidget::LABEL]; ?>
					</label>
				</div>
			<?php
				}
			?>
			<div class="div-cell-data width-30px">
				<input
					type="checkbox"
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::HTML_ID); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::HTML_NAME); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::REQUIRED); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::TITLE); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::DISABLED, false); ?>
					<?php
						$checked = '';
						if (${CheckboxWidget::VALUE_FIELD} === true)
						{
							$checked = 'checked';
						}
					?>
					<?php echo $checked; ?>
					value="<?php echo CheckboxWidget::CHECKBOX_VALUE; ?>"
				>
			</div>
		</div>
	</div>

<?php HTMLWidget::printEndBlock(${HTMLWidget::HTML_ARG_NAME}); ?>

