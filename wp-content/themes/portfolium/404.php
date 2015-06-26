<?php get_header(); ?>

<div class="page_meta clear">
    <div class="rss">
        <a href="<?php bloginfo('rss2_url'); ?>">Подписка на RSS</a>
    </div>
    <div class="heading">
        <h3>Ошибка 404. Не найдено</h3>
    </div>
    <?php if(function_exists('catlist')) { catlist(); } ?> 
    <?php get_search_form(); ?>
</div>

<?php get_footer(); ?>
