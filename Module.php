<?php declare(strict_types=1);

namespace Modules\UIModify;

use Zabbix\Core\CModule;
use APP;
use CMenuItem;

class Module extends CModule {

	/**
	 * Initialize module.
	 */
	public function init(): void {
		// Register menu item in Administration section
		APP::Component()->get('menu.main')
			->findOrAdd(_('Administration'))
			->getSubmenu()
			->insertAfter(_('General'),
				(new CMenuItem(_('UI Customization')))
					->setAction('uimodify.logo.edit')
					->setAliases(['uimodify.logo.upload', 'uimodify.logo.preview', 'uimodify.config.save'])
			);
	}
}