<?php
if (!defined('ABSPATH')) exit;
if (post_password_required()) { ?>
<div class="b-cont">
	<div class="info-line">
		<i class="info-line-icon c-yellow">
			<svg width="24" height="24"><use xlink:href="#i__info"></use></svg>
		</i>
		<span><?php _e('This post is password protected. Enter the password to view comments.', 'apktemplates'); ?></span>
	</div>
</div>
<?php return;
} ?>

<div id="addcomment" class="b-add-comments ignore-select">
	<div class="b-cont">
		<?php
		$commenter = wp_get_current_commenter();
		$req = get_option('require_name_email');
		$aria_req = ($req ? " aria-required='true'" : '');
		$html_req = ($req ? " required" : '');
		
		$args = array(
			'fields' => apply_filters(
				'comment_form_default_fields', array(
					'author' =>'<div class="form-combo"><input placeholder="Enter your name" class="form-control" id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" ' . $aria_req . $html_req . '>',
					'email'  => '<input placeholder="Enter your email" class="form-control" id="email" name="email" type="email" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" aria-describedby="email-notes"' . $aria_req . $html_req  . '></div>')
			),
			'comment_field' => '<textarea id="comment" name="comment" placeholder="Type your comment" rows="5" minlength="10" ' . $aria_req . $html_req . ' spellcheck="false"></textarea>',
			'submit_button'	=> '<button class="btn btn-green d-flex-center" type="submit" id="submit" name="submit">Send</button>',
			'show_avatars' => true,
			'title_reply' => '',
			'cancel_reply_before' => '',
			'comment_notes_before' => ''
		);
		comment_form($args);
		?>
	</div>
</div>
<script>
const commentForm = document.getElementById('commentform');
/* Remove No validate for front end form validation (Dont't remove)*/
if (commentform) {
	commentform.removeAttribute('novalidate');
}
</script>