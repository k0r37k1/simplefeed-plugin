# SimpleFeed Plugin Installation Guide

This guide will help you install and set up the SimpleFeed plugin for WonderCMS.

## Requirements

- WonderCMS 3.0.0 or newer
- PHP 7.4 or newer
- Write permissions for the plugin directory

## Installation Steps

### 1. Download the Plugin

There are two ways to download the plugin:

**Option A: Download ZIP**
- Download the ZIP file from the repository
- Extract the ZIP to a temporary location

**Option B: Clone Repository**
```
git clone https://github.com/yourusername/wondercms-simplefeed.git
```

### 2. Install Parsedown

SimpleFeed requires the Parsedown library for Markdown support:

1. Download Parsedown.php from https://github.com/erusev/parsedown/blob/master/Parsedown.php
2. Create a `lib` directory inside the SimpleFeed plugin directory
3. Place the Parsedown.php file in the `lib` directory

### 3. Upload to WonderCMS

1. Create a directory called `simplefeed` in your WonderCMS plugins directory
2. Upload all the plugin files to the `simplefeed` directory
3. The structure should look like this:
   ```
   your-wondercms-site/
   └── plugins/
       └── simplefeed/
           ├── admin/
           ├── core/
           ├── css/
           ├── data/
           ├── js/
           ├── lib/
           │   └── Parsedown.php
           ├── templates/
           ├── LICENSE
           ├── README.md
           ├── INSTALL.md
           ├── config.php
           └── simplefeed.php
   ```

### 4. Set Permissions

Ensure the following directories have write permissions:
- `plugins/simplefeed/data/`
- `plugins/simplefeed/lib/`

On most Linux/Unix systems you can use:
```
chmod 755 plugins/simplefeed/data/
chmod 755 plugins/simplefeed/lib/
```

### 5. Verify Installation

1. Log in to your WonderCMS admin panel
2. You should see a SimpleFeed link in your top navigation
3. You should also see a SimpleFeed button in your admin panel

## Troubleshooting

If you encounter issues:

1. **Markdown doesn't work**
   - Make sure Parsedown.php is correctly placed in the `lib` directory
   - Check PHP error logs for specific error messages

2. **Permission errors**
   - Make sure the data directory is writable by your web server

3. **Plugin doesn't appear**
   - Check that all files are correctly uploaded
   - Verify that your WonderCMS version is compatible

## Updating

To update the plugin:

1. Backup your `data` directory
2. Replace all plugin files with the new version
3. Copy your `data` directory back to the plugin directory

## Support

If you need help with installation or encounter any issues, please open an issue on the GitHub repository.
