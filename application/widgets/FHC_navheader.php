<?php


class FHC_navheader extends Widget
{
	public function display($data)
	{
		if (!isset($data['items']))
		{
			//default nav header
			$data = array('headertext' => 'Infocenter', 'headertextlink' => base_url('index.ci.php/system/infocenter/InfoCenter'));
			$data['items'] = array(
				'messages' =>
					array(
						'icon' => 'envelope', 'showall' => array('showalllink' => '#', 'showalltext' => 'Alle Nachrichten anzeigen'), 'children' =>
							array(array('link' => '#', 'html' => '
								<div>
									<strong>Maximillion Pegasus</strong>
									<span class="pull-right text-muted">
												<em>Gestern</em>
											</span>
								</div>
								<div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque eleifend...</div>
								'), array('link' => '#', 'html' => '
														<div>
									<strong>Yugi Muto</strong>
									<span class="pull-right text-muted">
												<em>Vorgestern</em>
											</span>
								</div>
								<div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque eleifend...</div>
								'))
					),
				'alerts' =>
					array(
						'icon' => 'bell', 'showall' => array('showalllink' => '#', 'showalltext' => 'Alle Aktionen anzeigen'), 'children' =>
							array(array('link' => '#', 'html' => '
							<div>
								<i class="fa fa-upload fa-fw"></i> Dokument hochgeladen
								<span class="pull-right text-muted small">vor 2 Minuten</span>
							</div>'), array('link' => '#', 'html' => '
							<div>
								<i class="fa fa-envelope fa-fw"></i> Nachricht versandt
								<span class="pull-right text-muted small">vor 4 Minuten</span>
							</div>'), array('link' => '#', 'html' => '
							<div>
								<i class="fa fa-share fa-fw"></i> InteressentIn freigegeben
								<span class="pull-right text-muted small">vor 5 Minuten</span>
							</div>'))
					)
			);
		}

		$this->view('widgets/fhcnavheader', $data);
	}
}