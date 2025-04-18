# SimpleFeed Plugin Installation Guide

This guide will help you install and set up the SimpleFeed plugin for WonderCMS.

## Requirements

- WonderCMS 3.0.0 or newer
- PHP 7.2 or newer
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

SimpleFeed requires the Parsedown library for full Markdown support:

1. Download Parsedown.php from https://github.com/erusev/parsedown/blob/master/Parsedown.php
2. Create a `lib` directory inside the SimpleFeed plugin directory
3. Place the Parsedown.php file in the `lib` directory

**Note:** SimpleFeed will still work without Parsedown, but with limited Markdown functionality.

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
           ├── assets/
           ├── data/
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

## Feature Configuration

### Enabling Search

To add the search functionality to your theme:

1. Edit your theme file (usually `theme.php` in your active theme directory)
2. Add the following code where you want the search box to appear:
   ```php
   <?php include __DIR__ . '/plugins/simplefeed/templates/search.php'; ?>
   ```
3. Save the file

This will display a search form that allows visitors to search through your posts.

## Troubleshooting

If you encounter issues:

1. **Markdown doesn't work properly**
   - Make sure Parsedown.php is correctly placed in the `lib` directory
   - Check PHP error logs for specific error messages
   - If Parsedown is missing, SimpleFeed will use basic Markdown conversion

2. **Permission errors**
   - Make sure the data directory is writable by your web server
   - Check that file permissions are set correctly

3. **Plugin doesn't appear**
   - Check that all files are correctly uploaded
   - Verify that your WonderCMS version is compatible

4. **Links not working correctly**
   - Make sure your .htaccess file is properly configured for WonderCMS
   - Try using the plugin with default WonderCMS URL settings

5. **Search not working**
   - Ensure that the path to the search.php file is correct in your theme
   - Check for JavaScript errors in your browser console

## Updating

To update the plugin:

1. Backup your `data` directory
2. Replace all plugin files with the new version
3. Copy your `data` directory back to the plugin directory

## Support

If you need help with installation or encounter any issues, please open an issue on the GitHub repository.
