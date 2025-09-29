<?php declare(strict_types=1);

namespace Modules\UIModify\Actions;

use CController;
use CControllerResponseData;
use CWebUser;

class CControllerUIModifyLogoPreview extends CController {

	protected function init(): void {
		$this->disableCsrfValidation();
	}

	protected function checkInput(): bool {
		return true;
	}

	protected function checkPermissions(): bool {
		return CWebUser::getType() == USER_TYPE_SUPER_ADMIN;
	}

	protected function doAction(): void {
		if (!isset($_FILES['logo_file']) || $_FILES['logo_file']['error'] !== UPLOAD_ERR_OK) {
			$this->setResponse(new CControllerResponseData([
				'success' => false,
				'error' => _('No file uploaded')
			]));
			return;
		}

		$file = $_FILES['logo_file'];
		$file_data = file_get_contents($file['tmp_name']);
		$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

		$mime_types = [
			'svg' => 'image/svg+xml',
			'png' => 'image/png',
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'gif' => 'image/gif',
			'webp' => 'image/webp'
		];

		$mime_type = $mime_types[$file_extension] ?? 'image/png';
		$base64_data = base64_encode($file_data);

		$image_size = @getimagesize($file['tmp_name']);

		$this->setResponse(new CControllerResponseData([
			'success' => true,
			'preview' => sprintf('data:%s;base64,%s', $mime_type, $base64_data),
			'size' => [
				'width' => $image_size[0] ?? 0,
				'height' => $image_size[1] ?? 0
			]
		]));
	}
}