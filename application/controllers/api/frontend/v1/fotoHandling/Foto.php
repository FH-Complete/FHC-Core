<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Foto extends  FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'uploadFoto' => ['admin:r', 'assistenz:r'],
			'deleteFoto' => ['admin:r', 'assistenz:r'],
		]);

		//Load Models and Libraries
		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->model("crm/Akte_model", "AkteModel");
		$this->load->model('person/Fotostatusperson_model', 'FotostatusPersonModel');

		$this->loadPhrases([
			'ui',
			'header'
		]);
	}

	public function uploadFoto($person_id)
	{
		if(!$person_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Person_id']), self::ERROR_TYPE_GENERAL);
		}

		$data = json_decode(file_get_contents("php://input"), true);

		if (!empty($data['image']))
		{
			$base64 = $data['image'];
			$resizedImage1 = $this->_resize($base64, 827, 1063);

			if (is_null($resizedImage1))
				return $this->terminateWithError($this->p->t('header', 'error_fotoupload'), self::ERROR_TYPE_GENERAL);

			$akte = $this->AkteModel->loadWhere(array('person_id' => $person_id, 'dokument_kurzbz' => 'Lichtbil'));

			$akteUpdateData = array(
				'dokument_kurzbz' => 'Lichtbil',
				'person_id' => $person_id,
				'inhalt' => $resizedImage1,
				'mimetype' => 'image/jpg',
				'erstelltam' => date('c'),
				'gedruckt' => false,
				'titel' => 'Lichtbild_' . $person_id . '.jpg',
				'bezeichnung' => 'Lichtbild gross',
				'insertamum' => date('c'),
				'insertvon' => getAuthUID(),
			);

			if (hasData($akte)) {
				$akte_id = getData($akte)[0]->akte_id;

				$akteUpdateData['updateamum'] = date('c');
				$akteUpdateData['updatevon'] = getAuthUID();
				$akteResult = $this->AkteModel->update(array('akte_id' => $akte_id), $akteUpdateData);
			} else {
				$akteResult = $this->AkteModel->insert($akteUpdateData);
			}

			if (isError($akteResult)) {
				return $this->terminateWithError(getError($akteResult), self::ERROR_TYPE_GENERAL);
			}

			$resizedImage2 = $this->_resize($base64, 101, 130);

			if (is_null($resizedImage2))
				return $this->terminateWithError($this->p->t('header', 'error_fotoupload'), self::ERROR_TYPE_GENERAL);

			$result = $this->_updateFoto($person_id, $resizedImage2);

			if (!isError($result)) {
				$this->FotostatusPersonModel->insert(array(
					'person_id' => $person_id,
					'fotostatus_kurzbz' => 'hochgeladen',
					'datum' => date('Y-m-d'),
					'updateamum' => date('c'),
					'updatevon' => getAuthUID(),
					'insertamum' => date('c'),
					'insertvon' => getAuthUID(),
				));

				return $this->terminateWithSuccess($base64);
			}
		}
		else
		{
			$this->terminateWithError($this->p->t('header', 'error_noPhoto'), self::ERROR_TYPE_GENERAL);
		}
	}

	public function deleteFoto($person_id)
	{
		if(!$person_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Person_id']), self::ERROR_TYPE_GENERAL);
		}

		$result = $this->_deleteFoto($person_id);

		if (isError($result))
		{
			return $this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess($result);
	}

	private function _resize($imageData, $maxwidth, $maxheight, $quality = 90)
	{
		$meta = getimagesize($imageData);
		if (!$meta)
		{
			return null;
		}

		$src_width = $meta[0];
		$src_height = $meta[1];
		$mime = $meta['mime'];

		switch ($mime) {
			case 'image/jpeg':
			case 'image/jpg':
				$imagecreated = imagecreatefromjpeg($imageData);
				break;
			case 'image/png':
				$imagecreated = imagecreatefrompng($imageData);
				break;
			case 'image/gif':
				$imagecreated = imagecreatefromgif($imageData);
				break;
			default:
				return null;
		}


		if (!$imagecreated)
		{
			return null;
		}

		$src_aspect_ratio = $src_width / $src_height;
		$thu_aspect_ratio = $maxwidth / $maxheight;

		if ($src_width <= $maxwidth && $src_height <= $maxheight)
		{
			$thu_width = $src_width;
			$thu_height = $src_height;
		}
		elseif ($thu_aspect_ratio > $src_aspect_ratio)
		{
			$thu_width = (int) ($maxheight * $src_aspect_ratio);
			$thu_height = $maxheight;
		}
		else
		{
			$thu_width = $maxwidth;
			$thu_height = (int) ($maxwidth / $src_aspect_ratio);
		}

		$imageScaled = imagecreatetruecolor($thu_width, $thu_height);

		if ($mime === 'image/png')
		{
			$background = imagecolorallocate($imageScaled , 0, 0, 0);
			imagecolortransparent($imageScaled, $background);
			imagealphablending($imageScaled, false);
			imagesavealpha($imageScaled, true);
		}

		imagecopyresampled($imageScaled, $imagecreated, 0, 0, 0, 0, $thu_width, $thu_height, $src_width, $src_height);

		if ($mime === "image/gif")
		{
			$background = imagecolorallocate($imageScaled, 0, 0, 0);
			imagecolortransparent($imageScaled, $background);
		}

		if (!empty($imageScaled))
		{
			ob_start();

			if ($mime == 'image/png')
				imagepng($imageScaled, NULL);
			else if ($mime === 'image/gif')
				imagegif($imageScaled, NULL);
			else
				imagejpeg($imageScaled, NULL, $quality);

			$resizedImageData = ob_get_contents();
			ob_end_clean();
			@imagedestroy($imagecreated);
			@imagedestroy($imageScaled);


			if (!empty($resizedImageData))
			{
				return base64_encode($resizedImageData);
			}
			return null;
		}
		return null;
	}

	private function _updateFoto($person_id, $foto)
	{
		$personJson['foto'] = $foto;
		$result = $this->PersonModel->update($person_id, $personJson);

		if (isError($result))
		{
			return error($result->msg, EXIT_ERROR);
		}

		return $result;
	}

	private function _deleteFoto($person_id)
	{
		$personJson['foto'] = null;
		$result = $this->PersonModel->update($person_id, $personJson);

		if (isError($result))
		{
			return error($result->msg, EXIT_ERROR);
		}

		return $result;
	}
}
