<?php Widget::printStartBlock(${Widget::HTML_ARG_NAME}); ?>

	<div class="div-table">
		<div class="div-row">
			<?php
				if (isset(${Widget::HTML_ARG_NAME}[UDFWidgetTpl::LABEL]))
				{
			?>
				<div class="div-cell align-middle">
					<label for="<?php echo ${Widget::HTML_ARG_NAME}[Widget::HTML_ID]; ?>">
						<?php echo ${Widget::HTML_ARG_NAME}[UDFWidgetTpl::LABEL]; ?>
					</label>
				</div>
			<?php
				}
			?>
			<div class="div-cell">
				<input
					type="checkbox"
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, Widget::HTML_ID); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, Widget::HTML_NAME); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidget::REQUIRED); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::REGEX); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::TITLE); ?>
					<?php
						$checked = '';
						if (${CheckboxWidget::VALUE_FIELD} == ${CheckboxWidget::CHECKED_ELEMENT})
						{
							$checked = 'checked';
						}
					?>
					<?php echo $checked; ?>
					value="<?php echo ${CheckboxWidget::VALUE_FIELD}; ?>"
				>
			</div>
			<div class="div-cell align-middle">
				<?php echo ${CheckboxWidget::VALUE_FIELD}; ?>
			</div>
		</div>
	</div>

<?php Widget::printEndBlock(${Widget::HTML_ARG_NAME}); ?>