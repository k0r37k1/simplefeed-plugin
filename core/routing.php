<?php
defined('INC_ROOT') || die;

// Füge Markdown-Funktionalität hinzu
require_once __DIR__ . '/markdown.php';

// Get WonderCMS global instance
global $Wcms;

// Check if user is logged in as admin for all admin actions
$isAdmin = $Wcms->loggedIn;
$adminActions = ['list', 'edit', 'delete', 'settings'];

// Sanitize GET parameters
$page = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
$action = isset($_GET['action']) ? filter_var($_GET['action'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
$slug = isset($_GET['slug']) ? filter_var($_GET['slug'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
$tag = isset($_GET['tag']) ? filter_var($_GET['tag'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
$shown = isset($_GET['shown']) ? filter_var($_GET['shown'], FILTER_VALIDATE_INT) : null;
$confirm = isset($_GET['confirm']) ? filter_var($_GET['confirm'], FILTER_VALIDATE_INT) : 0;

// Verify admin access for restricted actions
if (in_array($action, $adminActions) && !$isAdmin) {
    echo "<div class='error'>Access denied. Please log in as administrator.</div>";
    return;
}

// Settings speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && $page === 'simplefeed'
    && $action === 'settings'
    && isset($_POST['token'])) {
    
    // Use WonderCMS token verification instead of our own
    if (!$Wcms->verifyFormToken('token')) {
        echo "<div class='error'>Security check failed. Please try again.</div>";
    } else {
        $cfg = [
            'date_format' => filter_var(trim($_POST['date_format']), FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'show_more_limit' => filter_var($_POST['show_more_limit'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'default' => 4]]),
            'use_thumbnails' => (filter_var($_POST['use_thumbnails'], FILTER_VALIDATE_INT) === 1),
            'default_markdown' => isset($_POST['default_markdown']) ? (filter_var($_POST['default_markdown'], FILTER_VALIDATE_INT) === 1) : true
        ];
        
        if (sf_safeWriteFile(__DIR__.'/../data/settings.json', $cfg)) {
            echo "<div class='success'>Einstellungen gespeichert.</div>";
        } else {
            echo "<div class='error'>Fehler beim Speichern der Einstellungen.</div>";
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
                // Für neuen Post: Default-Wert für Markdown aus den Einstellungen holen
                $config = sf_getConfig();
                $post['use_markdown'] = $config['default_markdown'] ?? true;
            }
            
            include __DIR__.'/../admin/edit_form.php';
        } 
        // Admin only: Save Post
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['token'])) {
            // Use WonderCMS token verification
            if (!$Wcms->verifyFormToken('token')) {
                echo "<div class='error'>Security check failed. Please try again.</div>";
                include __DIR__.'/../admin/edit_form.php';
                break;
            }
            
            // Sanitize input
            $postData = [
                'title' => filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
                'date' => filter_var($_POST['date'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
                'short' => filter_var($_POST['short'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
                'image' => filter_var($_POST['image'], FILTER_SANITIZE_URL),
                'author' => filter_var($_POST['author'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
                'content' => $_POST['content'], // Content wird später je nach Markdown/HTML-Einstellung verarbeitet
                'tags' => array_filter(array_map('trim', explode(',', $_POST['tags']))),
                'use_markdown' => (isset($_POST['use_markdown']) && $_POST['use_markdown'] == '1')
            ];
            
            // Validate
            $errors = sf_validatePost($postData);
            if (!empty($errors)) {
                echo "<div class='error'><strong>Please correct the following errors:</strong><ul>";
                foreach ($errors as $error) {
                    echo "<li>".htmlspecialchars($error, ENT_QUOTES)."</li>";
                }
                echo "</ul></div>";
                $post = $postData;
                include __DIR__.'/../admin/edit_form.php';
                break;
            }
            
            // Wenn Markdown nicht verwendet wird, dann HTML-Content sanitizen
            if (!$postData['use_markdown']) {
                $postData['content'] = sf_sanitizeHTML($postData['content']);
            }
            
            // Determine slug
            if (!empty($_POST['original_slug'])) {
                // Editing existing post - keep original slug
                $slug = filter_var($_POST['original_slug'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            } else {
                // New post - generate slug from title
                $slug = sf_generateSlug($postData['title']);
            }
            
            $postData['slug'] = $slug;
            
            // Save the file
            $file = __DIR__.'/../data/'.basename($slug).'.json';
            if (sf_safeWriteFile($file, $postData)) {
                echo "<div class='success'>Post saved successfully.</div>";
                header('Location: ?page=simplefeed&action=list');
                exit;
            } else {
                echo "<div class='error'>Error saving post.</div>";
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
                echo "<div class='confirm-delete'>
                      <p>Are you sure you want to delete: <strong>".htmlspecialchars($slug, ENT_QUOTES)."</strong>?</p>
                      <a href='?page=simplefeed&action=delete&slug=".urlencode($slug)."&confirm=1&token=".$Wcms->getToken()."' class='btn-delete'>Yes, delete</a>
                      <a href='?page=simplefeed&action=list' class='btn-cancel'>Cancel</a>
                      </div>";
            } else {
                // Use WonderCMS token verification
                if (!isset($_GET['token']) || !$Wcms->verifyToken($_GET['token'])) {
                    echo "<div class='error'>Security check failed. Please try again.</div>";
                } else if (file_exists($file)) {
                    if (unlink($file)) {
                        echo "<div class='success'>Post deleted successfully.</div>";
                        header('Location: ?page=simplefeed&action=list');
                        exit;
                    } else {
                        echo "<div class='error'>Error deleting post.</div>";
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
                // Konvertiere Markdown zu HTML falls nötig
                if (isset($post['use_markdown']) && $post['use_markdown']) {
                    $post['content_html'] = sf_parseMarkdown($post['content']);
                } else {
                    // HTML wurde bereits beim Speichern gesäubert
                    $post['content_html'] = $post['content'];
                }
                
                include __DIR__.'/../templates/feed_view.php';
            } else {
                echo "<div class='error'>Post not found.</div>";
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
            // Frontend laden (public)
            $config = sf_getConfig();
            $posts = sf_loadPosts();
            
            // Markdown zu HTML konvertieren
            foreach ($posts as &$post) {
                if (isset($post['use_markdown']) && $post['use_markdown']) {
                    $post['content_html'] = sf_parseMarkdown($post['content']);
                } else {
                    // HTML wurde bereits beim Speichern gesäubert
                    $post['content_html'] = $post['content'];
                }
            }
            
            include __DIR__.'/../templates/feed_list.php';
        }
        break;
}
