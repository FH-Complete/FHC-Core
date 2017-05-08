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
<fieldset
	<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, Widget::HTML_ID); ?>
	<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::REQUIRED); ?>
	<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::REGEX); ?>
	<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, UDFWidgetTpl::TITLE); ?>
>
	<?php
		$elements = ${CheckboxWidget::WIDGET_DATA_ELEMENTS_ARRAY_NAME};
		$checkedElement = ${CheckboxWidget::CHECKED_ELEMENT};
		
		foreach($elements as $element)
		{
			$checked = '';
			
			if ($element->{CheckboxWidget::VALUE_FIELD} == $checkedElement)
			{
				$checked = 'checked';
			}
	?>
		<input
			type="checkbox"
			<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, Widget::HTML_NAME); ?>
			value="<?php echo $element->{CheckboxWidget::VALUE_FIELD}; ?>"
			<?php echo $checked; ?>
		>
		<?php echo $element->{CheckboxWidget::DESCRIPTION_FIELD}; ?>
	<?php
		}
	?>
</fieldset>