<?php
/**
 * Template Name: Wellness Studio Calendar
 *
 * @package Wellness Studio Calendar
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>
            </header>

            <div class="entry-content">
                <div id="wsc-frontend-app">
                    <!-- Vue.js application will be mounted here -->
                    <div class="wsc-loading">
                        <?php _e("Loading calendar...", "wellness-studio-calendar"); ?>
                    </div>
                </div>
            </div>
        </article>
    </main>
</div>

<?php
get_footer();