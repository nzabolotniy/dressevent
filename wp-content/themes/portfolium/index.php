<?php get_header(); ?>

<div class="page_meta clear">
    <div class="rss">
        <a href="<?php bloginfo('rss2_url'); ?>">Подписка на RSS</a>
    </div>
    <div class="heading">
        <h3>Новое на сайте</h3>
    </div>
    <?php if(function_exists('catlist')) { catlist(); } ?>
    <?php get_search_form(); ?>
</div>

<div class="posts">

<?php get_template_part('loop');  // Loop template (loop.php) ?>

<?php get_template_part('pagination');  // Pagination template for WP-PageNavi support (pagination.php) ?>

</div>

<?php get_footer(); ?>