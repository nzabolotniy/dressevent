<?php if ( comments_open() ) : ?>
<div id="comments">
<?php if ( post_password_required() ) : ?>
				<p class="nopassword"><?php _e('Пожалуйста, введите пароль для просмотра комментариев.'); ?></p>
			</div><!-- #comments -->
<?php
		/* Stop the rest of comments.php from being processed,
		 * but don't kill the script entirely -- we still have
		 * to fully load the template.
		 */
		return;
	endif;
?>

<?php
	// You can start editing here -- including this comment!
?>

    <div class="comments_heading clear">
        <div class="add_comment"><a href="#respond">Ваш отзыв</a></div>
        <div class="comment_qty"><?php
			printf( _n('Один отзыв на %2$s', '%1$s отзывов на %2$s', get_comments_number()),
			number_format_i18n( get_comments_number() ), 'эту статью' );
			?></div>
    </div>

<?php if (have_comments()) : ?>

            <div class="comment_list">
                <ol>
                <?php
                    wp_list_comments( array( 'callback' => 'commentlist' ) );
                ?>
                </ol>
            </div>

<?php endif; // end have_comments() ?>

<?php if ('open' == $post->comment_status) : ?>

<div id="respond" class="clear">
    <div class="respond_meta">Ваш отзыв</div>
    <div class="comment_form">

    <?php if ( get_option('comment_registration') && !$user_ID ) : ?>
        <p class="comment_message">Вы должны <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php echo urlencode(get_permalink()); ?>">войти</a>, чтобы оставлять комментарии.</p>
    <?php else : ?>

        <form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform" onSubmit="return checkEmail();">

            <?php if ( $user_ID ) : ?>

                <p class="comment_message">Вы вошли как <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo wp_logout_url(get_permalink()); ?>" title="Выйти с этого аккаунта">Выйти &raquo;</a></p>

            <?php else : ?>
                <p class="comment_fields clear">
                    <input class="focus" type="text" name="author" id="author" onfocus="if(this.value=='name') this.value='';" onblur="if(this.value=='') this.value='name';" value="name" size="22" tabindex="1" />
                    <input class="focus" type="text" name="email" id="email" onfocus="if(this.value=='e-mail') this.value='';" onblur="if(this.value=='') this.value='e-mail';" value="e-mail" size="22" tabindex="2" />
                    <input class="focus" type="text" name="url" id="url" onfocus="if(this.value=='www') this.value='';" onblur="if(this.value=='') this.value='www';" value="www" size="22" tabindex="3" />
                </p>
            <?php endif; ?>

            <!--<p class="comment_message"><small><strong>XHTML:</strong> Вы можете использовать следующие теги: <code><?php echo allowed_tags(); ?></code></small></p>-->

            <p><textarea name="comment" class="focus" id="comment" cols="100%" rows="10" tabindex="4" onfocus="if(this.innerHTML=='Напишите свой отзыв здесь') this.innerHTML='';">Напишите свой отзыв здесь</textarea></p>

            <p class="comment_submit"><input name="submit" type="submit" id="submit" tabindex="5" value="Опубликовать" />
            <?php comment_id_fields(); ?>
            </p>
            <?php do_action('comment_form', $post->ID); ?>

        </form>

    <?php endif; // If registration required and not logged in ?>

    </div>

    <?php endif; // if you delete this the sky will fall on your head ?>

</div>
<?php endif; // end ! comments_open() ?>
<!-- #comments -->