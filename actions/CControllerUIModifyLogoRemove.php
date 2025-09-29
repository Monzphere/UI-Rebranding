<?php declare(strict_types=1);

namespace Modules\UIModify\Actions;

use CController;
use CControllerResponseData;
use CWebUser;
use Exception;

class CControllerUIModifyLogoRemove extends CController {

	private const BRAND_CONF_PATH = '/usr/share/zabbix/local/conf/brand.conf.php';

	protected function init(): void {
		$this->disableCsrfValidation();
	}

	protected function checkInput(): bool {
		$fields = [
			'logo_type' => 'string'
		];

		$ret = $this->validateInput($fields);

		if (!$ret) {
			$this->setResponse(new CControllerResponseData([
				'success' => false,
				'error' => 'Invalid input'
			]));
		}

		return $ret;
	}

	protected function checkPermissions(): bool {
		return CWebUser::getType() == USER_TYPE_SUPER_ADMIN;
	}

	protected function doAction(): void {
		try {
			$logo_type = $this->getInput('logo_type', '');

			if (empty($logo_type)) {
				throw new Exception('Logo type is required');
			}

			$config = $this->loadCurrentConfig();

			$logo_map = [
				'main' => 'BRAND_LOGO',
				'sidebar' => 'BRAND_LOGO_SIDEBAR',
				'compact' => 'BRAND_LOGO_SIDEBAR_COMPACT'
			];

			if (!isset($logo_map[$logo_type])) {
				throw new Exception('Invalid logo type');
			}

			if (isset($config[$logo_map[$logo_type]])) {
				unset($config[$logo_map[$logo_type]]);
			}

			if (empty($config)) {
				if (file_exists(self::BRAND_CONF_PATH)) {
					unlink(self::BRAND_CONF_PATH);
				}
			} else {
				$this->saveBrandConfig($config);
			}

			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'message' => _('Logo removed successfully')
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

		return include self::BRAND_CONF_PATH;
	}

	private function saveBrandConfig(array $config_data): void {
		$conf_dir = dirname(self::BRAND_CONF_PATH);
		if (!is_dir($conf_dir)) {
			mkdir($conf_dir, 0755, true);
		}

		// Generate PHP config file
		$php_content = "<?php\nreturn [\n";
		foreach ($config_data as $key => $value) {
			if (!empty($value)) {
				$php_content .= sprintf("\t'%s' => '%s',\n", $key, addslashes($value));
			}
		}
		$php_content .= "];\n";

		file_put_contents(self::BRAND_CONF_PATH, $php_content);
		chmod(self::BRAND_CONF_PATH, 0644);
	}
}