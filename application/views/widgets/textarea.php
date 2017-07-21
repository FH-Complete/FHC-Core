<?php Widget::printStartBlock(${Widget::HTML_ARG_NAME}); ?>

	<div class="div-table">
		<div class="div-row">
			<?php
				if (isset(${Widget::HTML_ARG_NAME}[UDFWidgetTpl::LABEL]))
				{
			?>
				<div class="div-cell align-top">
					<label for="<?php echo ${Widget::HTML_ARG_NAME}[Widget::HTML_ID]; ?>">
						<?php echo ${Widget::HTML_ARG_NAME}[UDFWidgetTpl::LABEL]; ?>
					</label>
				</div>
			<?php
				}
			?>
			<div class="div-cell">
				<textarea
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, Widget::HTML_ID); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, Widget::HTML_NAME); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, TextareaWidget::ROWS); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, TextareaWidget::COLS); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidget::REQUIRED); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::PLACEHOLDER); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::REGEX); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::TITLE); ?>
				><?php echo ${TextareaWidget::TEXT}; ?></textarea>
			</div>
		</div>
	</div>

<?php Widget::printEndBlock(${Widget::HTML_ARG_NAME}); ?>