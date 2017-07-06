<?php Widget::printStartBlock(${Widget::HTML_ARG_NAME}); ?>
<?php
	if (isset(${Widget::HTML_ARG_NAME}[UDFWidgetTpl::LABEL]))
	{
?>
	<label for="<?php echo ${Widget::HTML_ARG_NAME}[Widget::HTML_ID]; ?>">
		<?php echo ${Widget::HTML_ARG_NAME}[UDFWidgetTpl::LABEL]; ?>
	</label>
<?php
	}
?>
<input
	type="checkbox"
	<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, Widget::HTML_ID); ?>
	<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, Widget::HTML_NAME); ?>
	<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::REQUIRED); ?>
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
<?php echo ${CheckboxWidget::VALUE_FIELD}; ?>
<?php Widget::printEndBlock(${Widget::HTML_ARG_NAME}); ?>