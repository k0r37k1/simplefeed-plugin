# SimpleFeed Plugin for WonderCMS

A minimalist feed/blog plugin with tags and navigation for WonderCMS.

## Features

- Simple blog functionality for your WonderCMS website
- Support for tags and categories
- Markdown or HTML content editing
- Responsive design
- Admin panel integration
- Image thumbnails
- Archive view

## Installation

1. Download the ZIP file or clone this repository
2. Extract the files to your WonderCMS plugins directory: `YOUR-SITE/plugins/simplefeed/`
3. Log in to your WonderCMS admin panel
4. The SimpleFeed menu item will appear in your navigation
5. Click on the SimpleFeed button in the admin panel to manage your posts

## Usage

### Creating Posts

1. Navigate to SimpleFeed in the admin panel
2. Click "Create new post"
3. Fill in the required fields (Title and Date)
4. Choose between Markdown or HTML formatting
5. Add tags separated by commas
6. Save your post

### Managing Posts

- View all posts from the admin panel
- Edit or delete existing posts
- Configure settings like date format and thumbnail display

### Frontend Views

- Main feed page: Lists all posts with pagination
- Single post view: Displays the full content of a post
- Tag filter: Shows posts with a specific tag
- Archive: Displays all posts organized by year

## Configuration

You can configure the following settings:

- **Date Format**: PHP date format for displaying dates (default: d.m.Y)
- **Show More Limit**: Number of posts to show before "Show more" button (default: 4)
- **Use Thumbnails**: Enable/disable image thumbnails in post lists
- **Default to Markdown**: Set Markdown as the default format for new posts

## Requirements

- WonderCMS 3.0.0 or newer
- PHP 7.4 or newer

## License

This plugin is licensed under the MIT License - see the LICENSE file for details.
