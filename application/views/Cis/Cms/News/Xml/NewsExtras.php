<stg_extras>
	<stg_kz><?= $studiengang->studiengang_kz; ?></stg_kz>
	<stg_ltg_name><![CDATA[<?= $this->p->t('global', 'studiengangsleitung'); ?>]]></stg_ltg_name>';
	<?php foreach ($stg_ltg as $item) { ?>
		<stg_ltg> <?php $this->view('Cis/Cms/News/Xml/Address/Detailed', ['obj' => $item]); ?></stg_ltg>
	<?php } ?>
	<gf_ltg_name><![CDATA[<?= $this->p->t('global', 'geschaeftsfuehrendeltg'); ?>]]></gf_ltg_name>';
	<?php foreach ($gf_ltg as $item) { ?>
		<gf_ltg> <?php $this->view('Cis/Cms/News/Xml/Address/Detailed', ['obj' => $item]); ?></gf_ltg>
	<?php } ?>
	<stv_ltg_name><![CDATA[<?= $this->p->t('global', 'stellvertreter'); ?>]]></stv_ltg_name>';
	<?php foreach ($stv_ltg as $item) { ?>
		<stv_ltg> <?php $this->view('Cis/Cms/News/Xml/Address/Detailed', ['obj' => $item]); ?></stv_ltg>
	<?php } ?>
	<ass_name><![CDATA[<?= $this->p->t('global', 'sekretariat'); ?>]]></ass_name>';
	<?php foreach ($ass as $item) { ?>
		<ass> <?php $this->view('Cis/Cms/News/Xml/Address/Detailed', ['obj' => $item]); ?></ass>
	<?php } ?>

	<zusatzinfo><![CDATA[<?= $studiengang->zusatzinfo_html; ?>]]></zusatzinfo>
	
	<hochschulvertr_name><![CDATA[<?= $this->p->t('global', 'hochschulvertretung'); ?>]]></hochschulvertr_name>';
	<?php foreach ($hochschulvertr as $item) { ?>
		<hochschulvertr> <?php $this->view('Cis/Cms/News/Xml/Address/Short', ['obj' => $item]); ?></hochschulvertr>
	<?php } ?>

	<stdv_name><![CDATA[<?= $this->p->t('global', 'studentenvertreter'); ?> <?= strtoupper($studiengang->oe_kurzbz); ?>]]></stdv_name>';
	<?php foreach ($stdv as $item) { ?>
		<stdv> <?php $this->view('Cis/Cms/News/Xml/Address/Short', ['obj' => $item]); ?></stdv>
	<?php } ?>

	<jahrgangsvertr_name><![CDATA[<?= $this->p->t('global', 'jahrgangsvertretung'); ?><?= $semester; ?> <?= $this->p->t('global', 'semester'); ?>]]></jahrgangsvertr_name>';
	<?php foreach ($jahrgangsvertr as $item) { ?>
		<jahrgangsvertr> <?php $this->view('Cis/Cms/News/Xml/Address/Short', ['obj' => $item]); ?></jahrgangsvertr>
	<?php } ?>

	<?php if (defined("CIS_EXT_MENU") && CIS_EXT_MENU) { ?>
		<cis_ext_menu>
			<download_name><![CDATA[<?= $this->p->t('global', 'allgemeinerdownload'); ?>]]></download_name>
			<kurzbz><![CDATA[<?= strtolower($studiengang->typ . $studiengang->kurzbz); ?>]]></kurzbz>
			<kurzbzlang><![CDATA[<?= strtolower($studiengang->kurzbzlang); ?>]]></kurzbzlang>
			<stg_kz><![CDATA[<?= $studiengang->studiengang_kz; ?>]]></stg_kz>
		</cis_ext_menu>';
	<?php } ?>

</stg_extras>
