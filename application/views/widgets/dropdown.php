<?php HTMLWidget::printStartBlock(${HTMLWidget::HTML_ARG_NAME}); ?>

	<div class="div-table">
		<div class="div-row">
			<?php
				if (isset(${HTMLWidget::HTML_ARG_NAME}[HTMLWidget::LABEL]))
				{
					$align = "valign-middle";
					if (isset(${HTMLWidget::HTML_ARG_NAME}[DropdownWidget::MULTIPLE]))
					{
						$align = "valign-top";
					}
			?>
				<div class="div-cell <?php echo $align; ?>">
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
			<div class="div-cell">
				<select
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::HTML_ID); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::HTML_NAME); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, DropdownWidget::SIZE); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, DropdownWidget::MULTIPLE, false); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::REQUIRED); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::MIN_VALUE); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::MAX_VALUE); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::REGEX); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::TITLE); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::DISABLED, false); ?>
				>
					<?php
						$elements = ${DropdownWidget::WIDGET_DATA_ELEMENTS_ARRAY_NAME};
						$selectedElements = ${DropdownWidget::SELECTED_ELEMENT};

						foreach($elements as $element)
						{
							$selected = '';

							if (is_array($selectedElements))
							{
								foreach($selectedElements as $selectedElement)
								{
									if ($element->{DropdownWidget::ID_FIELD} == $selectedElement)
									{
										$selected = 'selected';
									}
								}
							}
							else
							{
								if ($element->{DropdownWidget::ID_FIELD} == $selectedElements)
								{
									$selected = 'selected';
								}
							}
					?>
						<option value="<?php echo $element->{DropdownWidget::ID_FIELD}; ?>" <?php echo $selected; ?>>
							<?php echo $element->{DropdownWidget::DESCRIPTION_FIELD}; ?>
						</option>
					<?php
						}
					?>
				</select>
			</div>
		</div>
	</div>

<?php HTMLWidget::printEndBlock(${HTMLWidget::HTML_ARG_NAME}); ?>

