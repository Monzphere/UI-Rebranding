<?php declare(strict_types=1);

namespace Modules\UIModify\Actions;

use CController;
use CControllerResponseData;
use CControllerResponseFatal;
use CMessageHelper;
use CWebUser;
use Exception;

class CControllerUIModifyLogoEdit extends CController {

	private const BRAND_CONF_PATH = '/usr/share/zabbix/local/conf/brand.conf.php';
	private const REBRANDING_PATH = '/usr/share/zabbix/rebranding/';

	// Recommended logo dimensions
	private const LOGO_DIMENSIONS = [
		'main' => ['width' => 114, 'height' => 30, 'label' => 'Main Logo (Login Screen)'],
		'sidebar' => ['width' => 91, 'height' => 24, 'label' => 'Sidebar Logo'],
		'compact' => ['width' => 24, 'height' => 24, 'label' => 'Compact Logo (Icon)']
	];

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
		$current_config = $this->loadCurrentConfig();

		$data = [
			'title' => _('UI Customization'),
			'current_logos' => $current_config['logos'] ?? [],
			'brand_footer' => $current_config['footer'] ?? '',
			'brand_help_url' => $current_config['help_url'] ?? '',
			'logo_dimensions' => self::LOGO_DIMENSIONS,
			'themes' => $this->getAvailableThemes()
		];

		$response = new CControllerResponseData($data);
		$response->setTitle(_('UI Customization'));
		$this->setResponse($response);
	}

	/**
	 * Load current branding configuration
	 */
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

	/**
	 * Update brand.conf.php file
	 */
	private function updateBrandConfig(string $logo_type, string $filename, string $footer, string $help_url): void {
		$current_config = $this->loadCurrentConfig();

		// Map logo types to config keys
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

		// Update specific logo
		if (isset($logo_map[$logo_type])) {
			$config_data[$logo_map[$logo_type]] = './rebranding/' . $filename;
		}

		// Generate PHP config file
		$php_content = "<?php\nreturn [\n";
		foreach ($config_data as $key => $value) {
			if (!empty($value)) {
				$php_content .= sprintf("\t'%s' => '%s',\n", $key, addslashes($value));
			}
		}
		$php_content .= "];\n";

		// Ensure directory exists
		$conf_dir = dirname(self::BRAND_CONF_PATH);
		if (!is_dir($conf_dir)) {
			mkdir($conf_dir, 0755, true);
		}

		file_put_contents(self::BRAND_CONF_PATH, $php_content);
		chmod(self::BRAND_CONF_PATH, 0644);
	}

	/**
	 * Ensure required directories exist
	 */
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

	/**
	 * Get available Zabbix themes
	 */
	private function getAvailableThemes(): array {
		return [
			'blue-theme' => _('Blue'),
			'dark-theme' => _('Dark'),
			'hc-light' => _('High-contrast light'),
			'hc-dark' => _('High-contrast dark')
		];
	}
}