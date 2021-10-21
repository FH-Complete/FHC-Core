<?php HTMLWidget::printStartBlock(${HTMLWidget::HTML_ARG_NAME}); ?>

	<div class="div-table">
		<div class="div-row">
			<?php
				if (isset(${HTMLWidget::HTML_ARG_NAME}[HTMLWidget::LABEL]))
				{
			?>
				<div class="div-cell width-150px valign-middle">
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
			<div class="div-cell width-150px">
				<input
					type="text"
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::HTML_ID); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::HTML_NAME); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, TextfieldWidget::SIZE); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::PLACEHOLDER); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::REQUIRED); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::MIN_VALUE); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::MAX_VALUE); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::MIN_LENGTH); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::MAX_LENGTH); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::REGEX); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::TITLE); ?>
					<?php HTMLWidget::printAttribute(${HTMLWidget::HTML_ARG_NAME}, HTMLWidget::DISABLED, false); ?>
					value="<?php echo ${TextfieldWidget::VALUE}; ?>"
				>
			</div>
		</div>
	</div>

<?php HTMLWidget::printEndBlock(${HTMLWidget::HTML_ARG_NAME}); ?>

