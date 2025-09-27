<?php
if (!defined('ABSPATH')) exit;
function apktemplates_an1_comments($comment, $args, $depth) {
	$default_logo_url = get_avatar_url(array('size' => 96));
	$comment_author_logo = get_avatar_url($comment, array('size' => 96, 'class' => 'circle'));

	$logo_url = !empty($comment_author_logo) ? $comment_author_logo : $default_logo_url;

	$author = get_comment_author() ? get_comment_author() : 'User';
?>
<article id="comment-<?php comment_ID(); ?>" class="comments-tree-item" itemprop="comment" itemscope="" itemtype="https://schema.org/Comment">
	<div id="comment-id-<?php comment_ID(); ?>">
		<div class="comment">
			<footer class="comment__header">
				<div class="comment__avatar">
					<span class="rand_photo">
						<img alt="<?php echo $author; ?>" src="<?php echo $logo_url; ?>" class="avatar avatar-55 photo muralazy" height="55" width="55" loading="lazy" decoding="async">
					</span>
				</div>
				<div class="comment__info">
					<div class="comment__info-left">
						<div class="author" itemprop="author" itemscope="" itemtype="https://schema.org/Person">
							<span itemprop="name"><?php echo $author; ?></span>
						</div>
						<span>
							<time itemprop="dateCreated" datetime="<?php echo get_comment_date( 'c' ); ?>"><?php printf( _x( '%s ago', '%s = human-readable time difference', 'apktemplates' ), human_time_diff( get_comment_time( 'U' ), current_time( 'timestamp' ) ) ); ?> </time>
						</span>
					</div>
				</div>
			</footer>
			<div class="comment__footer">
				<div class="text">
					<?php if ($comment->comment_approved == '0') : ?>
					<span style="color:red; font-size: 16px; font-weight: 700;"><strong><?php _e('Your comment is wait for aproval...', 'apktemplates'); ?></strong></span> 
					<?php endif; ?>
					<div itemprop="text" id="comment-id-<?php comment_ID(); ?>"><?php comment_text(); ?></div>
				</div>
				<div class="comment__moderation">
					<div class="reply">
						<?php comment_reply_link(array_merge($args, array('depth' => $depth, 'max_depth' => $args['max_depth'] ))); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</article>
<?php }

function apktemplates_enqueue_comment_reply() {
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) { 
        wp_enqueue_script( 'comment-reply' ); 
    }
}
add_action( 'wp_enqueue_scripts', 'apktemplates_enqueue_comment_reply' );

function change_cancel_reply_text($html, $link, $text) {
    $new_text = 'Cancel';
    return str_replace($text, $new_text, $html);
}
add_filter('cancel_comment_reply_link', 'change_cancel_reply_text', 10, 3);

add_filter( 'comment_form_logged_in', '__return_empty_string' );