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
					type="text"
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, Widget::HTML_ID); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, Widget::HTML_NAME); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, TextfieldWidget::SIZE); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidget::REQUIRED); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::PLACEHOLDER); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::REGEX); ?>
					<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::TITLE); ?>
					value="<?php echo ${TextfieldWidget::VALUE}; ?>"
				>
			</div>
		</div>
	</div>

<?php Widget::printEndBlock(${Widget::HTML_ARG_NAME}); ?>