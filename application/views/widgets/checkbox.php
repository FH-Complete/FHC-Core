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
		$values = ${CheckboxWidget::WIDGET_DATA_VALUES_ARRAY_NAME};
		$checkedValue = ${CheckboxWidget::CHECKED_VALUE};
		
		foreach($values as $value)
		{
			$checked = '';
			
			if ($value == $checkedValue)
			{
				$checked = 'checked';
			}
	?>
		<input
			type="checkbox"
			<?php Widget::printAttribute(${Widget::HTML_ARG_NAME}, Widget::HTML_NAME); ?>
			value="<?php echo $value; ?>"
			<?php echo $checked; ?>
		>
		
	<?php
		}
	?>
</fieldset>