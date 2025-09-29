<?php declare(strict_types=1);

namespace Modules\UIModify\Actions;

use CController;
use CControllerResponseData;
use CControllerResponseFatal;
use CWebUser;
use Exception;

class CControllerUIModifyLogoUpload extends CController {

	private const BRAND_CONF_PATH = '/usr/share/zabbix/local/conf/brand.conf.php';
	private const REBRANDING_PATH = '/usr/share/zabbix/rebranding/';

	protected function init(): void {
		$this->disableCsrfValidation();
	}

	protected function checkInput(): bool {
		$fields = [
			'logo_type' => 'string',
			'brand_footer' => 'string',
			'brand_help_url' => 'string'
		];

		$ret = $this->validateInput($fields);

		if (!$ret) {
			$this->setResponse(new CControllerResponseData(['main_block' => '']));
		}

		return $ret;
	}

	protected function checkPermissions(): bool {
		return CWebUser::getType() == USER_TYPE_SUPER_ADMIN;
	}

	protected function doAction(): void {
		try {
			$logo_type = $this->getInput('logo_type', 'main');
			$brand_footer = $this->getInput('brand_footer', '');
			$brand_help_url = $this->getInput('brand_help_url', '');

			if (!isset($_FILES['logo_file'])) {
				throw new Exception(_('No file uploaded'));
			}

			if ($_FILES['logo_file']['error'] !== UPLOAD_ERR_OK) {
				throw new Exception(_('File upload failed') . ' (Error: ' . $_FILES['logo_file']['error'] . ')');
			}

			$file = $_FILES['logo_file'];
			$allowed_extensions = ['svg', 'png', 'jpg', 'jpeg', 'gif', 'webp'];
			$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

			if (!in_array($file_extension, $allowed_extensions)) {
				throw new Exception(_('Invalid file format. Allowed: SVG, PNG, JPG, GIF, WebP'));
			}

			if ($file_extension !== 'svg') {
				$image_info = getimagesize($file['tmp_name']);
				if ($image_info === false) {
					throw new Exception(_('Invalid image file'));
				}
			}

			$this->ensureDirectoriesExist();

			$new_filename = sprintf(
				'logo_%s_%s.%s',
				$logo_type,
				date('YmdHis'),
				$file_extension
			);

			$destination = self::REBRANDING_PATH . $new_filename;

			if (!move_uploaded_file($file['tmp_name'], $destination)) {
				throw new Exception(_('Failed to save uploaded file'));
			}

			$this->updateBrandConfig($logo_type, $new_filename, $brand_footer, $brand_help_url);

			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'message' => _('Logo uploaded successfully'),
				'filename' => $new_filename,
				'path' => './rebranding/' . $new_filename
			]);
			exit;

		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode([
				'success' => false,
				'error' => $e->getMessage()
			]);
			exit;
		}
	}

	private function loadCurrentConfig(): array {
		if (!file_exists(self::BRAND_CONF_PATH)) {
			return [];
		}

		$config = include self::BRAND_CONF_PATH;

		return [
			'logos' => [
				'main' => $config['BRAND_LOGO'] ?? '',
				'sidebar' => $config['BRAND_LOGO_SIDEBAR'] ?? '',
				'compact' => $config['BRAND_LOGO_SIDEBAR_COMPACT'] ?? ''
			],
			'footer' => $config['BRAND_FOOTER'] ?? '',
			'help_url' => $config['BRAND_HELP_URL'] ?? ''
		];
	}

	private function updateBrandConfig(string $logo_type, string $filename, string $footer, string $help_url): void {
		$current_config = $this->loadCurrentConfig();

		$logo_map = [
			'main' => 'BRAND_LOGO',
			'sidebar' => 'BRAND_LOGO_SIDEBAR',
			'compact' => 'BRAND_LOGO_SIDEBAR_COMPACT'
		];

		$config_data = [
			'BRAND_LOGO' => $current_config['logos']['main'] ?? '',
			'BRAND_LOGO_SIDEBAR' => $current_config['logos']['sidebar'] ?? '',
			'BRAND_LOGO_SIDEBAR_COMPACT' => $current_config['logos']['compact'] ?? '',
			'BRAND_FOOTER' => $footer ?: ($current_config['footer'] ?? ''),
			'BRAND_HELP_URL' => $help_url ?: ($current_config['help_url'] ?? '')
		];

		if (isset($logo_map[$logo_type])) {
			$config_data[$logo_map[$logo_type]] = './rebranding/' . $filename;
		}

		$php_content = "<?php\nreturn [\n";
		foreach ($config_data as $key => $value) {
			if (!empty($value)) {
				$php_content .= sprintf("\t'%s' => '%s',\n", $key, addslashes($value));
			}
		}
		$php_content .= "];\n";

		$conf_dir = dirname(self::BRAND_CONF_PATH);
		if (!is_dir($conf_dir)) {
			mkdir($conf_dir, 0755, true);
		}

		file_put_contents(self::BRAND_CONF_PATH, $php_content);
		chmod(self::BRAND_CONF_PATH, 0644);
	}

	private function ensureDirectoriesExist(): void {
		$directories = [
			self::REBRANDING_PATH,
			dirname(self::BRAND_CONF_PATH)
		];

		foreach ($directories as $dir) {
			if (!is_dir($dir)) {
				mkdir($dir, 0755, true);
			}
		}
	}
}