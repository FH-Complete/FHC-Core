<?php Widget::printStartBlock(${Widget::HTML_ARG_NAME}); ?>

	<div class="div-table">
		<div class="div-row">
			<?php
				if (isset(${Widget::HTML_ARG_NAME}[UDFWidgetTpl::LABEL]))
				{
			?>
				<div class="div-cell-label valign-middle width-150px">
					<label
						for="<?php echo ${Widget::HTML_ARG_NAME}[Widget::HTML_ID]; ?>"
						<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidget::REQUIRED); ?>
					>
						<?php echo ${Widget::HTML_ARG_NAME}[UDFWidgetTpl::LABEL]; ?>
					</label>
				</div>
			<?php
				}
			?>
			<div class="div-cell-data width-30px">
				<input
					type="checkbox"
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, Widget::HTML_ID); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, Widget::HTML_NAME); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidget::REQUIRED); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::REGEX); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::TITLE); ?>
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

<?php Widget::printEndBlock(${Widget::HTML_ARG_NAME}); ?>