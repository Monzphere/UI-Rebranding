# UI Modify - Zabbix Frontend Module

**Version:** 1.0.0
**Compatibility:** Zabbix 6.0+
**License:** GNU AGPLv3
<img width="1528" height="933" alt="image" src="https://github.com/user-attachments/assets/2a2a4dba-7fab-45a1-bbde-cd1c66af9a90" />

## Description

Frontend module for Zabbix UI customization that allows:

- ✅ Upload and management of custom logos
- ✅ Full support for all Zabbix themes (Blue, Dark, High-Contrast Light/Dark)
- ✅ Real-time image dimension validation
- ✅ Preview before upload
- ✅ Automatic generation of `brand.conf.php` file
- ✅ Footer and help URL customization
- ✅ Responsive and user-friendly interface

## Module Structure

```
uimodify/
├── manifest.json                          # Module manifest
├── Module.php                             # Main module class
├── actions/                               # Controllers
│   ├── CControllerUIModifyLogoEdit.php   # Form display
│   ├── CControllerUIModifyLogoUpload.php # Logo upload
│   └── CControllerUIModifyLogoRemove.php # Logo removal
├── views/                                 # Views
│   └── uimodify.logo.edit.php           # Upload form
└── assets/                                # Static assets
    ├── css/
    │   └── uimodify.css                 # CSS with theme support
    └── js/
        └── uimodify.js                  # Validation JavaScript
```

## Installation

### 1. Copy the module

```bash
sudo cp -r uimodify /usr/share/zabbix/modules/
```

### 2. Create required directories

```bash
sudo mkdir -p /usr/share/zabbix/local/conf
sudo mkdir -p /usr/share/zabbix/rebranding
```

### 3. Configure permissions

**For Debian/Ubuntu (www-data):**
```bash
sudo chown -R www-data:www-data /usr/share/zabbix/modules/uimodify
sudo chown -R www-data:www-data /usr/share/zabbix/local
sudo chown -R www-data:www-data /usr/share/zabbix/rebranding

sudo chmod 755 /usr/share/zabbix/modules/uimodify
sudo chmod 755 /usr/share/zabbix/local/conf
sudo chmod 755 /usr/share/zabbix/rebranding
```

**For RHEL/CentOS/Rocky (apache):**
```bash
sudo chown -R apache:apache /usr/share/zabbix/modules/uimodify
sudo chown -R apache:apache /usr/share/zabbix/local
sudo chown -R apache:apache /usr/share/zabbix/rebranding

sudo chmod 755 /usr/share/zabbix/modules/uimodify
sudo chmod 755 /usr/share/zabbix/local/conf
sudo chmod 755 /usr/share/zabbix/rebranding
```

### 4. Enable the module

1. Access Zabbix frontend as Super Admin
2. Go to: **Administration → General → Modules**
3. Click **Scan directory**
4. Find "UI Modify" in the list
5. Click **Enable**

### 5. Access the module

After enabled, go to: **Administration → UI Customization**

## Usage

### Recommended Logo Dimensions

| Type | Dimensions | Usage |
|------|------------|-------|
| **Main Logo** | 114×30 px | Login screen |
| **Sidebar Logo** | 91×24 px | Expanded sidebar menu |
| **Compact Logo** | 24×24 px | Compact sidebar menu (icon) |

### Supported Formats

- SVG (recommended for quality at any resolution)
- PNG (with transparency)
- JPG/JPEG
- GIF
- WebP

### Upload Workflow

1. **Select logo type** (Main, Sidebar, or Compact)
2. **Choose file** (up to 5MB)
3. **Preview** with dimension validation
4. **Configure footer and help URL** (optional)
5. **Click "Upload Logo"**
6. **Reload the page (F5)** to see changes

## Theme Compatibility

The module uses CSS variables for automatic support of all themes:

### Blue Theme (Default)
- Traditional blue color scheme
- Good readability and contrast

### Dark Theme
- Dark background to reduce eye strain
- Colors adjusted automatically

### High-Contrast Light
- High contrast for accessibility
- Black borders and text

### High-Contrast Dark
- High contrast with dark background
- Ideal for visual impairments

## Generated File

The module automatically generates `/usr/share/zabbix/local/conf/brand.conf.php`:

```php
<?php
return [
	'BRAND_LOGO' => './rebranding/logo_main_20250929120000.svg',
	'BRAND_LOGO_SIDEBAR' => './rebranding/logo_sidebar_20250929120000.svg',
	'BRAND_LOGO_SIDEBAR_COMPACT' => './rebranding/logo_compact_20250929120000.svg',
	'BRAND_FOOTER' => 'Your Company Name',
	'BRAND_HELP_URL' => 'https://yourcompany.com/help',
];
```

## JavaScript Features

### Real-time Validation
- Checks file size (max. 5MB)
- Validates allowed extensions
- Checks image dimensions

### Alert System
- ✅ **Success:** Perfect dimensions
- ⚠️ **Warning:** Close but not exact dimensions
- ❌ **Error:** Invalid file or very different dimensions

### Instant Preview
- Preview before upload
- Display of actual vs. expected dimensions
- Support for all image formats

## Troubleshooting

### Module doesn't appear in the list

**Solution:**
```bash
sudo chown -R www-data:www-data /usr/share/zabbix/modules/uimodify
sudo chmod 755 /usr/share/zabbix/modules/uimodify
```

### Upload error

**Check permissions:**
```bash
ls -la /usr/share/zabbix/rebranding/
ls -la /usr/share/zabbix/local/conf/
```

**Fix if needed:**
```bash
sudo chown www-data:www-data /usr/share/zabbix/rebranding/
sudo chown www-data:www-data /usr/share/zabbix/local/conf/
```

### Logo doesn't appear after upload

1. Clear browser cache (Ctrl+F5 or reload page)
2. Check if file was created: `ls -la /usr/share/zabbix/rebranding/`
3. Check content: `cat /usr/share/zabbix/local/conf/brand.conf.php`
4. Ensure web server has read permission

### CSS not being applied

Check if assets were loaded correctly:
```bash
ls -la /usr/share/zabbix/modules/uimodify/assets/css/
ls -la /usr/share/zabbix/modules/uimodify/assets/js/
```

## Technical Architecture

### Controllers (MVC Pattern)

- **CControllerUIModifyLogoEdit**: Renders management form
- **CControllerUIModifyLogoUpload**: Processes upload and updates configuration
- **CControllerUIModifyLogoRemove**: Removes logo and cleans configuration

### Security Features

- ✅ Permission check (Super Admin only)
- ✅ File extension validation
- ✅ File size validation
- ✅ Input sanitization
- ✅ CSRF protection via Zabbix framework

## Development

### Adding New Logo Types

1. Edit `CControllerUIModifyLogoEdit.php`:
```php
private const LOGO_DIMENSIONS = [
    'main' => ['width' => 114, 'height' => 30, 'label' => 'Main Logo'],
    'custom' => ['width' => 200, 'height' => 50, 'label' => 'Custom Logo'] // New
];
```

2. Update JavaScript in `uimodify.js`:
```javascript
dimensions: {
    custom: { width: 200, height: 50, label: 'Custom Logo' }
}
```

3. Add to `brand.conf.php` mapping in `updateBrandConfig()`.

### Customizing Themes

Edit `/usr/share/zabbix/modules/uimodify/assets/css/uimodify.css`:

```css
[data-theme="your-custom-theme"] .logo-preview-box {
    --ui-border-color: #yourcolor;
    --ui-bg-color: #yourcolor;
}
```

## References

- [Zabbix Frontend Modules Documentation](https://www.zabbix.com/documentation/current/en/devel/modules)
- [Zabbix Rebranding Guide](https://www.zabbix.com/documentation/current/en/manual/web_interface/rebranding)
- [initMAX Zabbix UI Rebranding Skeleton](https://git.initmax.cz/initMAX-Public/Zabbix-UI-Rebranding-skeleton)

## Contributing

Contributions are welcome! Please:

1. Fork the project
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

GNU Affero General Public License v3.0

## Author

Module developed based on Zabbix best practices and inspired by:
- initMAX Zabbix UI Rebranding Skeleton
- initMAX UX MAX Modules

---

**Version:** 1.0.0 | **Date:** 2025-09-29
