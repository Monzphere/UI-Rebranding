<?php declare(strict_types=1);

namespace Modules\UIModify\Actions;

use CController;
use CControllerResponseData;
use CMessageHelper;
use CWebUser;
use Exception;

class CControllerUIModifyConfigSave extends CController {

	private const BRAND_CONF_PATH = '/usr/share/zabbix/local/conf/brand.conf.php';

	protected function init(): void {
		$this->disableCsrfValidation();
	}

	protected function checkInput(): bool {
		$fields = [
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
			$brand_footer = $this->getInput('brand_footer', '');
			$brand_help_url = $this->getInput('brand_help_url', '');

			$current_config = $this->loadCurrentConfig();

			$config_data = [
				'BRAND_LOGO' => $current_config['BRAND_LOGO'] ?? '',
				'BRAND_LOGO_SIDEBAR' => $current_config['BRAND_LOGO_SIDEBAR'] ?? '',
				'BRAND_LOGO_SIDEBAR_COMPACT' => $current_config['BRAND_LOGO_SIDEBAR_COMPACT'] ?? '',
				'BRAND_FOOTER' => $brand_footer,
				'BRAND_HELP_URL' => $brand_help_url
			];

			$this->saveBrandConfig($config_data);

			CMessageHelper::setSuccessTitle(_('Configuration saved successfully'));

			$this->setResponse(new CControllerResponseData([
				'success' => true
			]));

		} catch (Exception $e) {
			CMessageHelper::setErrorTitle($e->getMessage());
			$this->setResponse(new CControllerResponseData([
				'success' => false,
				'error' => $e->getMessage()
			]));
		}
	}

	private function loadCurrentConfig(): array {
		if (!file_exists(self::BRAND_CONF_PATH)) {
			return [];
		}

		return include self::BRAND_CONF_PATH;
	}

	private function saveBrandConfig(array $config_data): void {
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
}