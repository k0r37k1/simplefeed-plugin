<?php
/**
 * SimpleFeed Plugin for WonderCMS
 *
 * A minimalist feed/blog plugin with tags and navigation for WonderCMS.
 * With integrated Markdown support and security features.
 *
 * @version 1.1.0
 * @author k0r37k1
 * @license MIT
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
    $dataDir = __DIR__ . '/data';
    if (!is_dir($dataDir)) {
        if (!mkdir($dataDir, 0755, true)) {
            throw new Exception('Failed to create data directory');
        }
    }

    // Create lib directory if it doesn't exist (for Parsedown)
    $libDir = __DIR__ . '/lib';
    if (!is_dir($libDir)) {
        if (!mkdir($libDir, 0755, true)) {
            throw new Exception('Failed to create lib directory');
        }
    }

    // Check if Parsedown is available, if not show warning
    if (!file_exists(__DIR__ . '/lib/Parsedown.php')) {
        if ($Wcms->loggedIn) {
            $Wcms->alert('Parsedown library not found. Markdown features will not work correctly. Please download Parsedown.php and place it in the lib directory.', 'warning');
        }
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
            $args[0] .= '<a href="' . $Wcms->url('?page=simplefeed') . '" class="btn btn-info marginTop5">SimpleFeed</a>';
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
            $Wcms->alert('Access denied. Please log in as administrator.', 'danger');
            $action = ''; // Reset to default view
        }

        // Handle POST requests for settings
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && $action === 'settings'
            && isset($_POST['token'])
            && $isAdmin) {

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

                if (sf_safeWriteFile(__DIR__.'/data/settings.json', $cfg)) {
                    $Wcms->alert('Settings saved successfully.', 'success');
                    $Wcms->log('SimpleFeed: Settings updated', 'info');
                } else {
                    $Wcms->alert('Failed to save settings.', 'danger');
                    $Wcms->log('SimpleFeed: Failed to save settings', 'danger');
                }
            }
        }

        // Handle POST requests for editing posts
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && $action === 'edit'
            && isset($_POST['title'], $_POST['token'])
            && $isAdmin) {

            // Use WonderCMS token verification
            if (!$Wcms->verifyFormToken('token')) {
                $Wcms->alert('Security check failed. Please try again.', 'danger');
            } else {
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
                    }

                    $postData['slug'] = $slug;

                    // Save the file
                    $file = __DIR__.'/data/'.basename($slug).'.json';
                    if (sf_safeWriteFile($file, $postData)) {
                        $Wcms->alert('Post saved successfully.', 'success');
                        $Wcms->log('SimpleFeed: Post saved - ' . $slug, 'info');

                        // Redirect to list view
                        if (!headers_sent()) {
                            header('Location: ?page=simplefeed&action=list');
                            exit;
                        } else {
                            echo '<script>window.location.href="?page=simplefeed&action=list";</script>';
                            exit;
                        }
                    } else {
                        $Wcms->alert('Error saving post.', 'danger');
                        $Wcms->log('SimpleFeed: Failed to save post - ' . $slug, 'danger');
                    }
                }
            }
        }

        // Handle delete actions
        if ($action === 'delete' && !empty($slug) && $isAdmin) {
            $file = __DIR__.'/data/'.basename($slug).'.json';

            if (!$confirm) {
                // Using custom HTML for confirmation dialog to avoid overlay issues
                ob_start();
                echo "<div class='confirm-delete'>";
                echo "<p>Are you sure you want to delete: <strong>" . $Wcms->stripTags($slug) . "</strong>?</p>";
                echo "<a href='?page=simplefeed&action=delete&slug=" . urlencode($slug) . "&confirm=1&token=" . $Wcms->getToken() . "' class='btn-delete'>Yes, delete</a> ";
                echo "<a href='?page=simplefeed&action=list' class='btn-cancel'>Cancel</a>";
                echo "</div>";
                $page['content'] = ob_get_clean();
                return $page;
            } else {
                // Use WonderCMS token verification
                if (!isset($_GET['token']) || !$Wcms->verifyToken($_GET['token'])) {
                    $Wcms->alert('Security check failed. Please try again.', 'danger');
                } else if (file_exists($file)) {
                    // Verify file is within the data directory (prevent path traversal)
                    $dataPath = realpath(__DIR__ . '/data');
                    $realFile = realpath($file);

                    if ($realFile && strpos($realFile, $dataPath) === 0) {
                        if (unlink($file)) {
                            $Wcms->alert('Post deleted successfully.', 'success');
                            $Wcms->log('SimpleFeed: Post deleted - ' . $slug, 'info');

                            // Redirect to list view
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

        // Load common data
        $config = sf_getConfig();
        $posts = sf_loadPosts();

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
                include __DIR__.'/admin/list.php';
                break;

            case 'edit':
                // Admin only: Edit Form
                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    $post = [];

                    if (!empty($slug)) {
                        $file = __DIR__.'/data/'.basename($slug).'.json';
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

                    include __DIR__.'/admin/edit.php';
                }
                break;

            case 'view':
                // View Single Post (public)
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
                        include __DIR__.'/templates/view.php';
                    } else {
                        $Wcms->alert('Post not found.', 'danger');
                        include __DIR__.'/templates/list.php';
                    }
                }
                break;

            case 'archive':
                // Archive View (public)
                include __DIR__.'/templates/archive.php';
                break;

            case 'tag':
                // Tag Filter (public)
                include __DIR__.'/templates/list.php';
                break;

            default:
                // Default view (settings for admin or feed for public)
                if ($isAdmin && !isset($_GET['view'], $_GET['archive'], $_GET['tag'])) {
                    include __DIR__.'/admin/panel.php';
                } else {
                    include __DIR__.'/templates/list.php';
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
        $Wcms->alert('SimpleFeed Plugin Error: ' . $Wcms->stripTags($e->getMessage()), 'danger');
    } else {
        echo '<div class="alert alert-danger">SimpleFeed Plugin Error: ' . $Wcms->stripTags($e->getMessage()) . '</div>';
    }
}