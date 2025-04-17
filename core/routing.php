<?php
defined('INC_ROOT') || die;

// Ensure we have settings and helper functions
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/helpers.php';

// Start session for CSRF protection
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Process admin actions
if (isset($_GET['page']) && $_GET['page'] === 'simplefeed') {
    // Admin authentication check (assuming WonderCMS handles authentication)
    if (!isset($Wcms) || !$Wcms->loggedIn) {
        echo '<div class="alert alert-danger">You need to be logged in to access admin features.</div>';
        return;
    }
    
    // Save settings
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['date_format'], $_POST['csrf_token'])) {
        // Verify CSRF token
        if (!sf_verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            echo '<div class="alert alert-danger">Invalid security token. Please try again.</div>';
        } else {
            $cfg = [
                'date_format' => trim($_POST['date_format']),
                'show_more_limit' => max(1, (int)$_POST['show_more_limit']),
                'use_thumbnails' => ($_POST['use_thumbnails'] == '1')
            ];
            
            $dataDir = __DIR__ . '/../data';
            if (!is_dir($dataDir)) {
                mkdir($dataDir, 0755, true);
            }
            
            $result = file_put_contents(__DIR__ . '/../data/settings.json', 
                json_encode($cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                
            if ($result !== false) {
                echo '<div class="alert alert-success">Settings saved successfully.</div>';
            } else {
                echo '<div class="alert alert-danger">Failed to save settings. Check permissions.</div>';
            }
        }
    }
    
    // Save post data
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['csrf_token'])) {
        // Verify CSRF token
        if (!sf_verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $errors = ['Invalid security token. Please try again.'];
            include __DIR__ . '/../admin/edit_form.php';
            return;
        }
        
        $originalSlug = $_POST['original_slug'] ?? '';
        
        // Prepare post data
        $postData = [
            'slug' => $originalSlug, // Preserve original slug for edits
            'title' => $_POST['title'],
            'date' => $_POST['date'],
            'short' => $_POST['short'],
            'image' => $_POST['image'],
            'author' => $_POST['author'],
            'content' => sf_sanitizeHTML($_POST['content']), // Sanitize HTML content
            'tags' => $_POST['tags']
        ];
        
        // Save post and handle result
        $result = sf_savePost($postData);
        
        if (empty($result['errors'])) {
            // Success - redirect to list
            header('Location: ?page=simplefeed&list=1&success=1');
            exit;
        } else {
            // Show form with errors
            $post = $result['post'];
            $errors = $result['errors'];
            include __DIR__ . '/../admin/edit_form.php';
            return;
        }
    }
    
    // Route to appropriate view based on GET parameters
    
    // Default admin panel
    if (!isset($_GET['list'], $_GET['edit'], $_GET['delete'], $_GET['view'], 
               $_GET['archive'], $_GET['tag'])) {
        include __DIR__ . '/../admin/panel.php';
        return;
    }
    
    // List view
    if (isset($_GET['list'])) {
        // Show success message if redirected from save operation
        if (isset($_GET['success'])) {
            echo '<div class="alert alert-success">Post saved successfully.</div>';
        }
        
        include __DIR__ . '/../admin/list_view.php';
        return;
    }
    
    // Edit form display
    if (isset($_GET['edit']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $slug = $_GET['edit'];
        
        // Security: validate slug format
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            echo '<div class="alert alert-danger">Invalid post identifier.</div>';
            include __DIR__ . '/../admin/list_view.php';
            return;
        }
        
        $file = __DIR__ . '/../data/' . $slug . '.json';
        
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $post = json_decode($content, true);
            
            if (!$post) {
                echo '<div class="alert alert-danger">Invalid post data.</div>';
                include __DIR__ . '/../admin/list_view.php';
                return;
            }
        }
        
        include __DIR__ . '/../admin/edit_form.php';
        return;
    }
    
    // Delete post
    if (isset($_GET['delete'])) {
        $slug = $_GET['delete'];
        
        // Security: validate slug format
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            echo '<div class="alert alert-danger">Invalid post identifier.</div>';
            include __DIR__ . '/../admin/list_view.php';
            return;
        }
        
        // Confirmation step
        if (!isset($_GET['confirm'])) {
            $csrf = sf_getCSRFToken();
            echo '<div class="alert alert-warning">';
            echo 'Are you sure you want to delete <strong>' . htmlspecialchars($slug, ENT_QUOTES) . '</strong>? ';
            echo '<a href="?page=simplefeed&delete=' . urlencode($slug) . 
                 '&confirm=1&csrf_token=' . urlencode($csrf) . '">Yes</a> | ';
            echo '<a href="?page=simplefeed&list=1">No</a>';
            echo '</div>';
            include __DIR__ . '/../admin/list_view.php';
            return;
        }
        
        // CSRF verification for deletion
        if (!sf_verifyCSRFToken($_GET['csrf_token'] ?? '')) {
            echo '<div class="alert alert-danger">Invalid security token. Please try again.</div>';
            include __DIR__ . '/../admin/list_view.php';
            return;
        }
        
        // Perform deletion
        if (sf_deletePost($slug)) {
            echo '<div class="alert alert-success">Post deleted successfully.</div>';
        } else {
            echo '<div class="alert alert-danger">Failed to delete post.</div>';
        }
        
        include __DIR__ . '/../admin/list_view.php';
        return;
    }
    
    // Frontend views
    $config = sf_getConfig();
    $posts = sf_loadPosts();
    
    // Single post view
    if (isset($_GET['view'])) {
        $slug = $_GET['view'];
        
        // Security: validate slug format
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            echo '<div class="alert alert-danger">Invalid post identifier.</div>';
            include __DIR__ . '/../templates/feed_list.php';
            return;
        }
        
        // Find the requested post
        $post = null;
        foreach ($posts as $p) {
            if ($p['slug'] == $slug) {
                $post = $p;
                break;
            }
        }
        
        if (!$post) {
            echo '<div class="alert alert-danger">Post not found.</div>';
            include __DIR__ . '/../templates/feed_list.php';
            return;
        }
        
        include __DIR__ . '/../templates/feed_view.php';
        return;
    }
    
    // Archive view
    if (isset($_GET['archive'])) {
        include __DIR__ . '/../templates/feed_archive.php';
        return;
    }
    
    // Tag filter view
    if (isset($_GET['tag'])) {
        $tag = $_GET['tag'];
        
        // Security: validate tag format 
        if (!preg_match('/^[a-zA-Z0-9 _-]+$/', $tag)) {
            echo '<div class="alert alert-danger">Invalid tag format.</div>';
            $tag = null;
        }
        
        include __DIR__ . '/../templates/feed_list.php';
        return;
    }
    
    // Default to list view if no specific route matched
    include __DIR__ . '/../templates/feed_list.php';
}
