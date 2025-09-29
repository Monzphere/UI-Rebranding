<?php
/**
 * UI Customization - Logo Management View
 *
 * @var CView $this
 * @var array $data
 */

$form = (new CForm())
	->setId('logo-upload-form')
	->setName('logo_form')
	->setAttribute('enctype', 'multipart/form-data');

$form_grid = (new CFormGrid())
	->addItem([
		new CLabel(_('Logo Type'), 'logo_type'),
		new CFormField([
			(new CSelect('logo_type'))
				->setId('logo-type-select')
				->addOptions(CSelect::createOptionsFromArray([
					'main' => _('Main Logo (Login Screen) - 114×30 px'),
					'sidebar' => _('Sidebar Logo - 91×24 px'),
					'compact' => _('Compact Logo (Icon) - 24×24 px')
				]))
				->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
		])
	])
	->addItem([
		new CLabel(_('Logo File'), 'logo_file'),
		new CFormField([
			(new CFile('logo_file'))
				->setId('logo-file-input')
				->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
				->setAttribute('accept', 'image/svg+xml,image/png,image/jpeg,image/gif,image/webp'),
			(new CDiv())->addClass('form-input-margin'),
			(new CTag('small', true, _('Supported formats: SVG, PNG, JPG, GIF, WebP')))
				->addClass('text-muted')
		])
	])
	->addItem([
		new CLabel(_('Preview'), 'logo-preview'),
		new CFormField([
			(new CDiv())
				->setId('logo-preview-container')
				->addClass('logo-preview-box')
				->addItem(
					(new CDiv(_('Upload a file to see preview')))
						->addClass('logo-preview-placeholder')
				)
		])
	]);

// Current logos section
if (!empty($data['current_logos'])) {
	$current_logos_table = (new CTableInfo())->setHeader([
		_('Logo Type'),
		_('Current Logo'),
		_('Actions')
	]);

	foreach ($data['current_logos'] as $type => $path) {
		if (!empty($path)) {
			// Add cache bust to image URL
			$image_url = $path . (strpos($path, '?') !== false ? '&' : '?') . '_=' . time();

			$current_logos_table->addRow([
				$data['logo_dimensions'][$type]['label'] ?? ucfirst($type),
				(new CImg($image_url))
					->addClass('current-logo-preview')
					->setAttribute('style', 'max-width: 200px; max-height: 60px;'),
				(new CSimpleButton(_('Remove')))
					->addClass('js-logo-remove btn-alt')
					->setAttribute('data-logo-type', $type)
			]);
		}
	}

	$form_grid->addItem([
		new CLabel(_('Current Logos')),
		new CFormField($current_logos_table)
	]);
}

// Branding configuration
$form_grid->addItem([
	new CLabel(_('Footer Text'), 'brand_footer'),
	new CFormField([
		(new CTextBox('brand_footer', $data['brand_footer'] ?? ''))
			->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
			->setAttribute('placeholder', _('Company Name'))
	])
])
->addItem([
	new CLabel(_('Help URL'), 'brand_help_url'),
	new CFormField([
		(new CTextBox('brand_help_url', $data['brand_help_url'] ?? ''))
			->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
			->setAttribute('placeholder', 'https://example.com/help')
	])
]);

// Form buttons
$form->addItem([
	$form_grid,
	(new CFormActions(
		(new CSubmit('upload', _('Upload Logo')))
			->setId('logo-upload-button'),
		[
			(new CButton('reset', _('Reset')))
				->addClass('btn-alt')
				->setId('logo-reset-button')
		]
	))
]);

// Instructions
$instructions = (new CDiv())
	->addClass('info-box')
	->addItem([
		new CTag('h4', true, _('Instructions')),
		(new CList([
			_('Select the logo type you want to upload'),
			_('Choose an image file (recommended dimensions shown for each type)'),
			_('Preview your logo before uploading'),
			_('Click "Upload Logo" to apply changes'),
			_('Changes will be visible immediately after upload')
		]))
	]);

(new CHtmlPage())
	->setTitle(_('UI Customization'))
	->setDocUrl('manual/web_interface/frontend_sections/administration/general#modules')
	->addItem($instructions)
	->addItem($form)
	->show();