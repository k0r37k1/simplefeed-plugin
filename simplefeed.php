<?php
/**
 * Plugin Name: SimpleFeed
 * Description: A minimalist feed/blog plugin with tags and navigation for WonderCMS.
 * Version: 1.1.0
 * Author: k0r37k1
 * Author URL: https://github.com/k0r37k1
 * License: MIT
 * 
 * A minimalist feed/blog plugin with tags and navigation for WonderCMS.
 * With integrated Markdown support and security features.
 */
defined('INC_ROOT') || die;
global $Wcms;

try {
    // Load core functions
    require_once __DIR__ . '/core/settings.php';
    require_once __DIR__ . '/core/functions.php';
    require_once __DIR__ . '/core/markdown.php';

    // Load settings
    $config = sf_getConfig();

    // Create data directory if it doesn't exist
    $dataDir = sf_getDataPath();
    if (!is_dir($dataDir)) {
        if (!mkdir($dataDir, 0755, true)) {
            $Wcms->alert('danger', 'Failed to create data directory. Please check folder permissions.');
            $Wcms->log('SimpleFeed: Failed to create data directory', 'danger');
            return;
        }
    }

    // Create lib directory if it doesn't exist (for Parsedown)
    $libDir = __DIR__ . '/lib';
    if (!is_dir($libDir)) {
        if (!mkdir($libDir, 0755, true)) {
            $Wcms->alert('danger', 'Failed to create lib directory. Please check folder permissions.');
            $Wcms->log('SimpleFeed: Failed to create lib directory', 'danger');
            return;
        }
    }

    // Check if Parsedown is available, if not show warning
    if (!file_exists(__DIR__ . '/lib/Parsedown.php')) {
        if ($Wcms->loggedIn) {
            $Wcms->alert('warning', 'Parsedown library not found. Markdown features will work with limited functionality. Please download Parsedown.php and place it in the lib directory for full Markdown support.', 'warning');
        }
    }

    /**
     * Verify CSRF token consistently across plugin
     * 
     * @param string $tokenField Name of the token field
     * @param bool $isRequest Whether to check $_REQUEST (true) or $_POST (false)
     * @return bool True if token is valid
     */
    function sf_verifyToken(string $tokenField = 'token', bool $isRequest = false): bool {
        global $Wcms;
        
        if (!$Wcms->loggedIn) {
            return false;
        }
        
        $tokenValue = $isRequest 
            ? ($_REQUEST[$tokenField] ?? null) 
            : ($_POST[$tokenField] ?? null);
            
        if (empty($tokenValue)) {
            return false;
        }
        
        return $Wcms->verifyToken($tokenValue);
    }

    /**
     * Set a feedback message to be displayed on the next page load
     *
     * @param string $message The message to display
     * @param string $type Message type (success, warning, danger)
     * @return void
     */
    function sf_setFeedback(string $message, string $type = 'success'): void {
        $_SESSION['feedbackMessage'] = $message;
        $_SESSION['feedbackType'] = $type;
    }

    // Add menu item
    $Wcms->addListener('menu', function(array $menu) use ($Wcms) {
        // Publicly visible - no login required
        $menu[] = ['slug' => 'simplefeed', 'name' => 'SimpleFeed'];
        return $menu;
    });

    // Add admin panel entry when logged in
    $Wcms->addListener('adminPanel', function(array $args) use ($Wcms) {
        // Only if admin is logged in
        if ($Wcms->loggedIn) {
            $args[0] .= '<a href="' . $Wcms->url('?page=simplefeed') . '" class="sf-btn sf-btn-info marginTop5">SimpleFeed</a>';
        }
        return $args;
    });

    // Include CSS with $Wcms->url for correct paths
    $Wcms->addListener('css', function(array $css) use ($Wcms) {
        $css[] = $Wcms->url('plugins/simplefeed/assets/simplefeed.css');
        return $css;
    });

    // Include JavaScript with $Wcms->url for correct paths
    $Wcms->addListener('js', function(array $js) use ($Wcms) {
        $js[] = $Wcms->url('plugins/simplefeed/assets/simplefeed.js');
        return $js;
    });

    // Add search integration
    $Wcms->addListener('search', function($args) use ($Wcms) {
        $query = $args[0] ?? '';
        $results = $args[1] ?? [];
        
        if (empty($query)) {
            return [$query, $results];
        }
        
        // Load all posts
        $posts = sf_loadPosts();
        
        // Search in posts
        foreach ($posts as $post) {
            $title = $post['title'] ?? '';
            $content = $post['content'] ?? '';
            $short = $post['short'] ?? '';
            $searchableContent = $title . ' ' . $short . ' ' . strip_tags($content);
            
            // If post matches search query
            if (stripos($searchableContent, $query) !== false) {
                // Create excerpt
                $excerpt = substr(strip_tags($content), 0, 150);
                if (strlen(strip_tags($content)) > 150) {
                    $excerpt .= '...';
                }
                
                // Add to results
                $results[] = [
                    'title' => $post['title'],
                    'url' => $Wcms->url('?page=simplefeed&action=post&slug=' . $post['slug']),
                    'excerpt' => $excerpt
                ];
            }
        }
        
        return [$query, $results];
    });

    // Page listener for routing all simplefeed pages
    $Wcms->addListener('page', function(array $page) use ($Wcms) {
        $pg = $_GET['page'] ?? '';
        if ($pg !== 'simplefeed') return $page;

        // Get route parameters
        $action = isset($_GET['action']) ? $Wcms->stripTags($_GET['action']) : '';
        $slug = isset($_GET['slug']) ? $Wcms->stripTags($_GET['slug']) : '';
        $tag = isset($_GET['tag']) ? $Wcms->stripTags($_GET['tag']) : '';
        $shown = isset($_GET['shown']) ? (int)$_GET['shown'] : null;
        $confirm = isset($_GET['confirm']) ? (int)$_GET['confirm'] : 0;

        // Check if user is logged in as admin for admin actions
        $isAdmin = $Wcms->loggedIn;
        $adminActions = ['list', 'edit', 'delete', 'settings'];

        // Verify admin access for restricted actions
        if (in_array($action, $adminActions) && !$isAdmin) {
            $Wcms->alert('danger', 'Access denied. Please log in as administrator.');
            sf_setFeedback('Access denied. Please log in as administrator.', 'danger');
            $action = ''; // Reset to default view
        }

        // Handle POST requests for settings
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && $action === 'settings'
            && sf_verifyToken()) {

            $cfg = [
                'date_format' => $Wcms->stripTags(trim($_POST['date_format'])),
                'show_more_limit' => (int)$_POST['show_more_limit'],
                'use_thumbnails' => (int)$_POST['use_thumbnails'] === 1,
                'default_markdown' => isset($_POST['default_markdown']) ? ((int)$_POST['default_markdown'] === 1) : true
            ];

            $settingsPath = sf_getDataPath() . '/settings.json';
            if (sf_safeWriteFile($settingsPath, $cfg)) {
                $Wcms->alert('success', 'Settings saved successfully.');
                $Wcms->log('SimpleFeed: Settings updated', 'info');
                sf_setFeedback('Settings saved successfully.', 'success');
            } else {
                $Wcms->alert('danger', 'Failed to save settings.');
                $Wcms->log('SimpleFeed: Failed to save settings', 'danger');
                sf_setFeedback('Failed to save settings.', 'danger');
            }
            
            $Wcms->redirect();
        }

        // Handle POST requests for editing posts
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && $action === 'edit'
            && sf_verifyToken()) {

            // Sanitize input using WonderCMS functions
            $postData = [
                'title' => $Wcms->stripTags($_POST['title']),
                'date' => $Wcms->stripTags($_POST['date']),
                'short' => $Wcms->stripTags($_POST['short']),
                'image' => $Wcms->stripTags($_POST['image']),
                'author' => $Wcms->stripTags($_POST['author']),
                'content' => $_POST['content'], // Content will be processed based on Markdown setting
                'tags' => array_filter(array_map([$Wcms, 'stripTags'], array_map('trim', explode(',', $_POST['tags'] ?? '')))),
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
                $Wcms->alert('danger', $errorMessage);
                sf_setFeedback($errorMessage, 'danger');

                $post = $postData;
            } else {
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
                    
                    // Check if slug already exists and create a unique one
                    $allPosts = sf_loadPosts();
                    $existingSlugs = array_column($allPosts, 'slug');
                    if (in_array($slug, $existingSlugs)) {
                        $slug = $slug . '-' . time();
                    }
                }

                $postData['slug'] = $slug;

                // Save the file
                $file = sf_getDataPath() . '/' . basename($slug) . '.json';
                if (sf_safeWriteFile($file, $postData)) {
                    $Wcms->alert('success', 'Post saved successfully.');
                    $Wcms->log('SimpleFeed: Post saved - ' . $slug, 'info');
                    sf_setFeedback('Post saved successfully.', 'success');

                    // Redirect to list view
                    if (!headers_sent()) {
                        header('Location: ' . $Wcms->url('?page=simplefeed&action=list'));
                        exit;
                    } else {
                        echo '<script>window.location.href="' . $Wcms->url('?page=simplefeed&action=list') . '";</script>';
                        exit;
                    }
                } else {
                    $Wcms->alert('danger', 'Error saving post.');
                    $Wcms->log('SimpleFeed: Failed to save post - ' . $slug, 'danger');
                    sf_setFeedback('Error saving post.', 'danger');
                }
            }
        }

        // Handle delete actions
        if ($action === 'delete' && !empty($slug) && sf_verifyToken('token', true)) {
            $file = sf_getDataPath() . '/' . basename($slug) . '.json';

            if (!$confirm) {
                // Using custom HTML for confirmation dialog to avoid overlay issues
                ob_start();
                echo "<div class='sf-confirm-delete'>";
                echo "<p>Are you sure you want to delete: <strong>" . $Wcms->stripTags($slug) . "</strong>?</p>";
                echo "<a href='" . $Wcms->url('?page=simplefeed&action=delete&slug=' . urlencode($slug) . '&confirm=1&token=' . $Wcms->getToken()) . "' class='sf-btn sf-btn-danger'>Yes, delete</a> ";
                echo "<a href='" . $Wcms->url('?page=simplefeed&action=list') . "' class='sf-btn sf-btn-secondary'>Cancel</a>";
                echo "</div>";
                $page['content'] = ob_get_clean();
                return $page;
            } else {
                if (file_exists($file)) {
                    // Verify file is within the data directory (prevent path traversal)
                    $dataPath = realpath(sf_getDataPath());
                    $realFile = realpath($file);

                    if ($realFile && strpos($realFile, $dataPath) === 0) {
                        if (unlink($file)) {
                            $Wcms->alert('success', 'Post deleted successfully.');
                            $Wcms->log('SimpleFeed: Post deleted - ' . $slug, 'info');
                            sf_setFeedback('Post deleted successfully.', 'success');

                            // Redirect to list view
                            if (!headers_sent()) {
                                header('Location: ' . $Wcms->url('?page=simplefeed&action=list'));
                                exit;
                            } else {
                                echo '<script>window.location.href="' . $Wcms->url('?page=simplefeed&action=list') . '";</script>';
                                exit;
                            }
                        } else {
                            $Wcms->alert('danger', 'Error deleting post.');
                            $Wcms->log('SimpleFeed: Failed to delete post - ' . $slug, 'danger');
                            sf_setFeedback('Error deleting post.', 'danger');
                        }
                    } else {
                        $Wcms->alert('danger', 'Security error: Invalid file path.');
                        $Wcms->log('SimpleFeed: Security warning - attempted to delete file outside data directory', 'danger');
                        sf_setFeedback('Security error: Invalid file path.', 'danger');
                    }
                }
            }
        }

        // Load common data
        $config = sf_getConfig();
        $posts = sf_loadPosts();
        
        // Make path utilities available to templates
        $pluginUrl = $Wcms->url('plugins/simplefeed');

        // Convert Markdown to HTML for posts when needed
        foreach ($posts as &$post) {
            if (isset($post['use_markdown']) && $post['use_markdown']) {
                $post['content_html'] = sf_parseMarkdown($post['content']);
            } else {
                // HTML was already cleaned when saving
                $post['content_html'] = $post['content'];
            }
        }

        // Content based on action
        ob_start();

        switch ($action) {
            case 'list':
                // Admin only: List View
                include __DIR__ . '/admin/list.php';
                break;

            case 'edit':
                // Admin only: Edit Form
                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    $post = [];

                    if (!empty($slug)) {
                        $file = sf_getDataPath() . '/' . basename($slug) . '.json';
                        if (file_exists($file)) {
                            $post = sf_safeReadFile($file, true);
                        }
                    }

                    // Default values for new post if needed
                    if (empty($post)) {
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
                    }

                    // Default to Markdown if not specified
                    if (!isset($post['use_markdown'])) {
                        $post['use_markdown'] = $config['default_markdown'] ?? true;
                    }

                    include __DIR__ . '/admin/edit.php';
                }
                break;

            case 'post':
                // View Single Post (public) - changed from 'view' to 'post'
                if (!empty($slug)) {
                    $found = false;
                    foreach ($posts as $p) {
                        if ($p['slug'] === $slug) {
                            $post = $p;
                            $found = true;
                            break;
                        }
                    }

                    if ($found) {
                        include __DIR__ . '/templates/post.php'; // Changed from view.php to post.php
                    } else {
                        $Wcms->alert('danger', 'Post not found.');
                        sf_setFeedback('Post not found.', 'danger');
                        include __DIR__ . '/templates/list.php';
                    }
                }
                break;

            case 'archive':
                // Archive View (public)
                include __DIR__ . '/templates/archive.php';
                break;
                
            case 'search':
                // Search Results View (public)
                include __DIR__ . '/templates/results.php';
                break;

            case 'tag':
                // Tag Filter (public)
                include __DIR__ . '/templates/list.php';
                break;

            default:
                // Default view (settings for admin or feed for public)
                if ($isAdmin && !isset($_GET['view'], $_GET['archive'], $_GET['tag'])) {
                    include __DIR__ . '/admin/panel.php';
                } else {
                    include __DIR__ . '/templates/list.php';
                }
                break;
        }

        $page['content'] = ob_get_clean();
        $page['title'] = 'SimpleFeed';
        return $page;
    });

} catch (Exception $e) {
    // Log error with WonderCMS functions and display friendly message
    if (method_exists($Wcms, 'log')) {
        $Wcms->log('SimpleFeed Plugin Error: ' . $e->getMessage(), 'danger');
    } else {
        error_log('SimpleFeed Plugin Error: ' . $e->getMessage());
    }

    if (method_exists($Wcms, 'alert')) {
        $Wcms->alert('danger', 'SimpleFeed Plugin Error: ' . $Wcms->stripTags($e->getMessage()), 'danger');
    } else {
        echo '<div class="sf-alert sf-alert-danger">SimpleFeed Plugin Error: ' . $Wcms->stripTags($e->getMessage()) . '</div>';
    }
}
