<?php
defined('INC_ROOT') || die;

// Add Markdown functionality
require_once __DIR__ . '/markdown.php';

// Get WonderCMS global instance
global $Wcms;

// Check if user is logged in as admin for all admin actions
$isAdmin = $Wcms->loggedIn;
$adminActions = ['list', 'edit', 'delete', 'settings'];

// Sanitize GET parameters
$page = isset($_GET['page']) ? $Wcms->stripTags($_GET['page']) : '';
$action = isset($_GET['action']) ? $Wcms->stripTags($_GET['action']) : '';
$slug = isset($_GET['slug']) ? $Wcms->stripTags($_GET['slug']) : '';
$tag = isset($_GET['tag']) ? $Wcms->stripTags($_GET['tag']) : '';
$shown = isset($_GET['shown']) ? (int)$_GET['shown'] : null;
$confirm = isset($_GET['confirm']) ? (int)$_GET['confirm'] : 0;

// Verify admin access for restricted actions
if (in_array($action, $adminActions) && !$isAdmin) {
    $Wcms->alert('Access denied. Please log in as administrator.', 'danger');
    return;
}

// Save Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && $page === 'simplefeed'
    && $action === 'settings'
    && isset($_POST['token'])) {
    
    // Use WonderCMS token verification
    if (!$Wcms->verifyFormToken('token')) {
        $Wcms->alert('Security check failed. Please try again.', 'danger');
    } else {
        $cfg = [
            'date_format' => $Wcms->stripTags(trim($_POST['date_format'])),
            'show_more_limit' => (int)$_POST['show_more_limit'],
            'use_thumbnails' => (int)$_POST['use_thumbnails'] === 1,
            'default_markdown' => isset($_POST['default_markdown']) ? ((int)$_POST['default_markdown'] === 1) : true
        ];
        
        if (sf_safeWriteFile(__DIR__.'/../data/settings.json', $cfg)) {
            $Wcms->alert('Settings saved successfully.', 'success');
            $Wcms->log('SimpleFeed: Settings updated', 'info');
        } else {
            $Wcms->alert('Failed to save settings.', 'danger');
            $Wcms->log('SimpleFeed: Failed to save settings', 'danger');
        }
    }
}

// Handle routes
switch ($action) {
    case 'list':
        // Admin only: List View
        include __DIR__.'/../admin/list_view.php';
        break;
        
    case 'edit':
        // Admin only: Edit Form
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($slug)) {
            $file = __DIR__.'/../data/'.basename($slug).'.json';
            $post = [];
            
            if (file_exists($file)) {
                $post = sf_safeReadFile($file, true);
                // Default to Markdown if not specified
                if (!isset($post['use_markdown'])) {
                    $config = sf_getConfig();
                    $post['use_markdown'] = $config['default_markdown'] ?? true;
                }
            } else {
                // For new post: Default value for Markdown from settings
                $config = sf_getConfig();
                $post['use_markdown'] = $config['default_markdown'] ?? true;
            }
            
            include __DIR__.'/../admin/edit_form.php';
        } 
        // Admin only: Save Post
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['token'])) {
            // Use WonderCMS token verification
            if (!$Wcms->verifyFormToken('token')) {
                $Wcms->alert('Security check failed. Please try again.', 'danger');
                include __DIR__.'/../admin/edit_form.php';
                break;
            }
            
            // Sanitize input using WonderCMS functions
            $postData = [
                'title' => $Wcms->stripTags($_POST['title']),
                'date' => $Wcms->stripTags($_POST['date']),
                'short' => $Wcms->stripTags($_POST['short']),
                'image' => $Wcms->stripTags($_POST['image']),
                'author' => $Wcms->stripTags($_POST['author']),
                'content' => $_POST['content'], // Content will be processed based on Markdown setting
                'tags' => array_filter(array_map([$Wcms, 'stripTags'], array_map('trim', explode(',', $_POST['tags'])))),
                'use_markdown' => (isset($_POST['use_markdown']) && $_POST['use_markdown'] == '1')
            ];
            
            // Validate
            $errors = sf_validatePost($postData);
            if (!empty($errors)) {
                $errorMessage = "<strong>Please correct the following errors:</strong><ul>";
                foreach ($errors as $error) {
                    $errorMessage .= "<li>" . $Wcms->stripTags($error) . "</li>";
                }
                $errorMessage .= "</ul>";
                $Wcms->alert($errorMessage, 'danger');
                
                $post = $postData;
                include __DIR__.'/../admin/edit_form.php';
                break;
            }
            
            // If not using Markdown, purify HTML content
            if (!$postData['use_markdown']) {
                $postData['content'] = $Wcms->purify($postData['content']);
            }
            
            // Determine slug
            if (!empty($_POST['original_slug'])) {
                // Editing existing post - keep original slug
                $slug = $Wcms->stripTags($_POST['original_slug']);
            } else {
                // New post - generate slug from title
                $slug = sf_generateSlug($postData['title']);
            }
            
            $postData['slug'] = $slug;
            
            // Save the file
            $file = __DIR__.'/../data/'.basename($slug).'.json';
            if (sf_safeWriteFile($file, $postData)) {
                $Wcms->alert('Post saved successfully.', 'success');
                $Wcms->log('SimpleFeed: Post saved - ' . $slug, 'info');
                
                // Redirect properly using header() if headers not sent
                if (!headers_sent()) {
                    header('Location: ?page=simplefeed&action=list');
                    exit;
                } else {
                    // Fallback to JavaScript redirect
                    echo '<script>window.location.href="?page=simplefeed&action=list";</script>';
                    exit;
                }
            } else {
                $Wcms->alert('Error saving post.', 'danger');
                $Wcms->log('SimpleFeed: Failed to save post - ' . $slug, 'danger');
                
                $post = $postData;
                include __DIR__.'/../admin/edit_form.php';
            }
        } else {
            // New post form
            $config = sf_getConfig();
            $post = [
                'slug' => '',
                'title' => '',
                'date' => date('Y-m-d'),
                'short' => '',
                'image' => '',
                'author' => '',
                'content' => '',
                'tags' => [],
                'use_markdown' => $config['default_markdown'] ?? true
            ];
            include __DIR__.'/../admin/edit_form.php';
        }
        break;
        
    case 'delete':
        // Admin only: Delete Post
        if (!empty($slug)) {
            $file = __DIR__.'/../data/'.basename($slug).'.json';
            
            if (!$confirm) {
                // Using custom HTML for confirmation dialog to avoid overlay issues
                echo "<div class='confirm-delete'>";
                echo "<p>Are you sure you want to delete: <strong>" . $Wcms->stripTags($slug) . "</strong>?</p>";
                echo "<a href='?page=simplefeed&action=delete&slug=" . urlencode($slug) . "&confirm=1&token=" . $Wcms->getToken() . "' class='btn-delete'>Yes, delete</a> ";
                echo "<a href='?page=simplefeed&action=list' class='btn-cancel'>Cancel</a>";
                echo "</div>";
            } else {
                // Use WonderCMS token verification
                if (!isset($_GET['token']) || !$Wcms->verifyToken($_GET['token'])) {
                    $Wcms->alert('Security check failed. Please try again.', 'danger');
                } else if (file_exists($file)) {
                    // Verify file is within the data directory (prevent path traversal)
                    $dataPath = realpath(__DIR__ . '/../data');
                    $realFile = realpath($file);
                    
                    if ($realFile && strpos($realFile, $dataPath) === 0) {
                        if (unlink($file)) {
                            $Wcms->alert('Post deleted successfully.', 'success');
                            $Wcms->log('SimpleFeed: Post deleted - ' . $slug, 'info');
                            
                            // Redirect properly
                            if (!headers_sent()) {
                                header('Location: ?page=simplefeed&action=list');
                                exit;
                            } else {
                                echo '<script>window.location.href="?page=simplefeed&action=list";</script>';
                                exit;
                            }
                        } else {
                            $Wcms->alert('Error deleting post.', 'danger');
                            $Wcms->log('SimpleFeed: Failed to delete post - ' . $slug, 'danger');
                        }
                    } else {
                        $Wcms->alert('Security error: Invalid file path.', 'danger');
                        $Wcms->log('SimpleFeed: Security warning - attempted to delete file outside data directory', 'danger');
                    }
                }
            }
        }
        break;
        
    case 'view':
        // View Single Post (public)
        if (!empty($slug)) {
            $config = sf_getConfig();
            $posts = sf_loadPosts();
            $post = null;
            
            foreach ($posts as $p) {
                if ($p['slug'] === $slug) {
                    $post = $p;
                    break;
                }
            }
            
            if ($post) {
                // Convert Markdown to HTML if needed
                if (isset($post['use_markdown']) && $post['use_markdown']) {
                    $post['content_html'] = sf_parseMarkdown($post['content']);
                } else {
                    // HTML was already cleaned when saving
                    $post['content_html'] = $post['content'];
                }
                
                include __DIR__.'/../templates/feed_view.php';
            } else {
                $Wcms->alert('Post not found.', 'danger');
                include __DIR__.'/../templates/feed_list.php';
            }
        }
        break;
        
    case 'archive':
        // Archive View (public)
        include __DIR__.'/../templates/feed_archive.php';
        break;
        
    case 'tag':
        // Tag Filter (public)
        include __DIR__.'/../templates/feed_list.php';
        break;
        
    default:
        // Default view (settings for admin or feed for public)
        if ($isAdmin && !isset($_GET['view'], $_GET['archive'], $_GET['tag'])) {
            include __DIR__.'/../admin/panel.php';
        } else {
            // Load frontend
            $config = sf_getConfig();
            $posts = sf_loadPosts();
            
            // Convert Markdown to HTML
            foreach ($posts as &$post) {
                if (isset($post['use_markdown']) && $post['use_markdown']) {
                    $post['content_html'] = sf_parseMarkdown($post['content']);
                } else {
                    // HTML was already cleaned when saving
                    $post['content_html'] = $post['content'];
                }
            }
            
            include __DIR__.'/../templates/feed_list.php';
        }
        break;
}
