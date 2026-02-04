<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE html>
<html lang="<?= isset($lang) ? HTML::chars($lang) : 'en' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="<?= isset($meta_description) ? HTML::chars($meta_description) : '' ?>">
    <meta name="author" content="<?= isset($meta_author) ? HTML::chars($meta_author) : '' ?>">
    
    <title>
        Artonit City <?php
            $city_name = '';
            $config = Kohana::$config->load('artonitcity_config');
            if (isset($config->city_name)) {
                $city_name = $config->city_name;
            }
            
            $page_title = isset($title) ? ' - ' . $title : '';
            
            echo HTML::chars($city_name . $page_title);
        ?>
    </title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="/city/assets/ico/favicon.png" type="image/png">

    <!-- CSS Libraries -->
    <link rel="stylesheet" href="/city/static/css/bootstrap.min.css">
    <link rel="stylesheet" href="/city/static/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="/city/static/css/theme.blue.css">
    <link rel="stylesheet" href="/city/static/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="/city/static/css/jstree-themes/blue/style.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/city/static/css/city.min.css?v=<?= time() ?>">
    
    <!-- Page-specific CSS -->
    <?php if (isset($page_css) && !empty($page_css)): ?>
        <?php foreach ((array)$page_css as $css_file): ?>
            <link rel="stylesheet" href="<?= HTML::chars($css_file) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <style>
        /* Critical fix for fixed navbar */
        body {
            padding-top: 70px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Mobile adjustments */
        @media (max-width: 768px) {
            body {
                padding-top: 50px;
            }
        }
        
        /* Main content container */
        .main-content-wrapper {
            flex: 1 0 auto;
            width: 100%;
        }
        
        /* Page header spacing */
        .page-header {
            margin-top: 0;
            padding-top: 20px;
        }
        
        .page-header h1:first-child,
        .page-header h2:first-child,
        .page-header h3:first-child {
            margin-top: 0;
        }
        
        /* Section anchors offset */
        .content-section {
            padding-top: 10px;
        }
        
        h1,
        h2,
        h3,
        h4,
        h5 {
            padding-top: 20px;
            margin-top: 0;
        }
        
        /* Back to top button */
        #backToTop {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 999;
            display: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            padding: 0;
            font-size: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            #backToTop {
                bottom: 20px;
                right: 20px;
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
            
            .container {
                padding-left: 10px;
                padding-right: 10px;
            }
        }
        
        /* Print styles */
        @media print {
            .navbar,
            #backToTop {
                display: none !important;
            }
            
            body {
                padding-top: 0 !important;
            }
            
            .page-header {
                padding-top: 0;
                margin-bottom: 10px;
            }
        }
    </style>
    
    <!-- HTML5 Shiv for IE8-9 support -->
    <!--[if lt IE 9]>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
    <!-- Top Navigation Menu -->
    <?php 
    // Buffer the menu output to prevent premature rendering
    ob_start();
    include Kohana::find_file('views', 'top_menu');
    $top_menu = ob_get_clean();
    echo $top_menu;
    ?>
    
    <!-- Main Content Wrapper -->
    <div class="main-content-wrapper">
        <div class="container">
            <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <?php foreach ($breadcrumbs as $crumb): ?>
                        <?php if (isset($crumb['active']) && $crumb['active']): ?>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?= HTML::chars($crumb['title']) ?>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item">
                                <a href="<?= HTML::chars($crumb['url']) ?>">
                                    <?= HTML::chars($crumb['title']) ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
            <?php endif; ?>
            
            <!-- Page Header -->
            <?php if (isset($page_title) || isset($page_subtitle)): ?>
            <div class="page-header content-section">
                <?php if (isset($page_title)): ?>
                    <h1 id="page-title"><?= HTML::chars($page_title) ?></h1>
                <?php endif; ?>
                <?php if (isset($page_subtitle)): ?>
                    <p class="lead text-muted"><?= HTML::chars($page_subtitle) ?></p>
                <?php endif; ?>
                <?php if (isset($page_description)): ?>
                    <p><?= HTML::chars($page_description) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Flash Messages -->
            <?php if (isset($flash_messages) && !empty($flash_messages)): ?>
                <?php foreach ($flash_messages as $message): ?>
                <div class="alert alert-<?= HTML::chars(isset($message['type']) ? $message['type'] : 'info') ?> alert-dismissible fade show" role="alert">
                    <?= HTML::chars($message['text']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Main Content -->
            <main role="main" class="content-section">
                <?php if (isset($content)): ?>
                    <?= $content ?>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <!-- Footer -->
    <?php if (isset($show_footer) && $show_footer): ?>
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <span class="text-muted">
                        &copy; <?= date('Y') ?> Artonit City
                        <?php if (isset($version)): ?>
                            | Version <?= HTML::chars($version) ?>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="col-md-6 text-right">
                    <span class="text-muted">
                        <?= __('Page generated in') ?>: <?= round(microtime(true) - KOHANA_START_TIME, 4) ?>s
                    </span>
                </div>
            </div>
        </div>
    </footer>
    <?php endif; ?>
    
    <!-- Back to Top Button -->
    <button id="backToTop" class="btn btn-primary" title="<?= __('Back to top') ?>" aria-label="<?= __('Scroll to top') ?>">
        ↑
    </button>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/city/static/js/bootstrap.min.js"></script>
    <script src="/city/static/js/moment-with-locales.min.js"></script>
    <script src="/city/static/js/bootstrap-datetimepicker.min.js"></script>
    <script src="/city/static/js/jquery.tablesorter.min.js"></script>
    <script src="/city/static/js/jquery.tablesorter.widgets.min.js"></script>
    <script src="/city/static/js/jquery.dataTables.min.js"></script>
    <script src="/city/static/js/jstree.min.js"></script>
    
    <!-- Application Script -->
    <script src="/city/static/js/app.min.js?v=<?= time() ?>"></script>
    
    <!-- Page-specific JavaScript -->
    <?php if (isset($page_js) && !empty($page_js)): ?>
        <?php foreach ((array)$page_js as $js_file): ?>
            <script src="<?= HTML::chars($js_file) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline JavaScript -->
    <script>
    (function() {
        'use strict';
        
        // Back to top functionality
        var backToTopBtn = document.getElementById('backToTop');
        
        if (backToTopBtn) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 300) {
                    backToTopBtn.style.display = 'block';
                } else {
                    backToTopBtn.style.display = 'none';
                }
            });
            
            backToTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            
            // Initial check
            backToTopBtn.style.display = window.scrollY > 300 ? 'block' : 'none';
        }
        
        // Fix anchor links offset for fixed navbar
        document.addEventListener('DOMContentLoaded', function() {
            var navbar = document.querySelector('.navbar');
            var navbarHeight = navbar ? navbar.offsetHeight : 70;
            
            // Fix existing anchor links
            var anchors = document.querySelectorAll('a[href^="#"]:not([href="#"])');
            for (var i = 0; i < anchors.length; i++) {
                anchors[i].addEventListener('click', function(e) {
                    var targetId = this.getAttribute('href');
                    var target = document.querySelector(targetId);
                    
                    if (target) {
                        e.preventDefault();
                        
                        var targetRect = target.getBoundingClientRect();
                        var targetPosition = targetRect.top + window.scrollY - navbarHeight - 20;
                        
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            }
            
            // Initialize datetime pickers
            if (typeof jQuery !== 'undefined' && jQuery.fn.datetimepicker) {
                jQuery('.datetimepicker').datetimepicker({
                    format: 'YYYY-MM-DD HH:mm',
                    locale: '<?php echo isset($lang) ? $lang : 'ru'; ?>',
                    sideBySide: true
                });
            }
            
            // Initialize tablesorter
            if (typeof jQuery !== 'undefined' && jQuery.fn.tablesorter) {
                jQuery('.tablesorter').tablesorter({
                    theme: 'blue',
                    widgets: ['zebra', 'filter']
                });
            }
            
            // Initialize DataTables
            if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable) {
                jQuery('.datatable').DataTable({
                    pageLength: 25,
                    responsive: true
                });
            }
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
                for (var i = 0; i < alerts.length; i++) {
                    alerts[i].style.transition = 'all 0.5s ease';
                    alerts[i].style.opacity = '0';
                    setTimeout(function(el) {
                        if (el.parentNode) {
                            el.parentNode.removeChild(el);
                        }
                    }.bind(null, alerts[i]), 500);
                }
            }, 5000);
        });
        
        // Fix for window resize
        window.addEventListener('resize', function() {
            // Re-check back to top button
            if (backToTopBtn) {
                backToTopBtn.style.display = window.scrollY > 300 ? 'block' : 'none';
            }
        });
    })();
    </script>
    
    <!-- Inline scripts from content -->
    <?php if (isset($inline_scripts)): ?>
        <script>
        <?= $inline_scripts ?>
        </script>
    <?php endif; ?>
</body>
</html>