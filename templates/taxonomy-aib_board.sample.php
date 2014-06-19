<?php
	$paged = get_query_var('aib-page') ? intval(get_query_var('aib-page')) : 1;
	$term = get_queried_object();
	$args = array(
		'post_type' => $aib->post_type,
		'tax_query' => array(
			array(
				'taxonomy' => $aib->taxonomy,
				'field' => 'slug',
				'terms' => $term->slug
			)
		),
		'posts_per_page' => 50,
		'paged' => $paged
	);
	$query = new WP_Query($args);

	$term_link = get_term_link($term);
	if ($paged > 1) {
		if ($paged == 2) {
			$prev_link = $term_link;
		} else {
			$prev_link = user_trailingslashit(trailingslashit($term_link) . 'page/' . ($paged - 1));
		}
	} else {
		$prev_link = false;
	}

	if ($paged < $query->max_num_pages) {
		$next_link = user_trailingslashit(trailingslashit($term_link) . 'page/' . ($paged + 1));
	} else {
		$next_link = false;
	}
?>

<?php get_header(); ?>
<section id="primary" class="content-area aib">
	<div id="content" class="site-content" role="main">
		<?php $aib->render_top_navigation(); ?>
		<header class="entry-header">
			<h1 class="entry-title">
				<?php echo $term->name; ?>
			</h1>
		</header>
		<div class="entry-content">
			<?php
				$subject = isset($_POST['aib-subject']) ? $_POST['aib-subject'] : '';
				$comment = isset($_POST['aib-comment']) ? $_POST['aib-comment'] : '';
				$captcha = $aib->get_captcha();
				if (isset($_POST['aib-submit'])) {
					$wrong_captcha = !$aib->validate_captcha();
				} else {
					$wrong_captcha = false;
				}
			?>
			<div id="respond" class="comment-respond">
				<h3 id="reply-title" class="comment-reply-title">
					<?php /*_e('Create a Thread');*/ ?>
				</h3>
				<form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" id="aib-comment-form" class="comment-form" novalidate="" enctype="multipart/form-data">
					<p>
						<input type="text" id="aib-subject" name="aib-subject" value="<?php echo $subject; ?>" placeholder="New thread title" />
						<input type="submit" name="<?php echo $aib->aib_submit; ?>" id="submit" value="<?php _e('Submit'); ?>" class="aib-submit">
					</p>
					<p>
						<textarea id="aib-comment" name="aib-comment" cols="45" rows="1" aria-required="true" <?php if(!$comment) echo 'placeholder="Your message"' ;?>><?php echo $comment; ?></textarea>
					</p>
					<div class="aib-last-row-wrapper">
						<div class="aib-attachment-wrapper">
							<input type="file" id="aib-attachment" name="aib-attachment" accept="image/jpeg,image/png,image/gif" />
						</div>
							<?php if ($captcha) : ?>
								<div class="aib-captcha-wrapper">
									<?php echo $captcha; ?>
								</div>
							<?php endif; ?>
					</div>
					<p class="form-submit">
						<?php $visibility = $aib->validate() ? ' style="display: none;"' : ''?>
						<span class="aib-validation-message"<?php echo $visibility; ?>><?php _e('Please fill at least one of the fields above'); ?></span>
						<?php if ($captcha) : ?>
								<?php if ($wrong_captcha) : ?>
											<span class="aib-wrong-captcha"><?php _e('Entered wrong captcha code'); ?></span>
								<?php endif; ?>
						<?php endif; ?>		
					</p>
					<input type="hidden" name="aib-parent" id="aib-parent" value="0" />
					<input type="hidden" name="aib-board" id="aib-board" value="<?php echo get_queried_object()->term_id; ?>" />
				</form>
			</div>
		</div>
		<nav class="navigation paging-navigation" role="navigation">
			<div class="pagination loop-pagination">
				<?php if ($prev_link) : ?>
					<a class="prev page-numbers" href="<?php echo $prev_link; ?>">← <?php _e('Back to the future'); ?></a> <?php if ($next_link) echo '|'; ?>
				<?php endif; ?>
				<?php if ($next_link) : ?>
					<a class="next page-numbers" href="<?php echo $next_link; ?>"><?php _e('Forward to the past'); ?> →</a>
				<?php endif; ?>
			</div>
		</nav>
		<?php
			if ($query->have_posts()) {
				while ($query->have_posts()) {
					$query->the_post();
					$thread_id = get_the_ID();
					$title = get_the_title();
					$reply_count = $aib->get_reply_count($post->ID);
					if (!$title) $title = 'thread #'.$thread_id;
					?>
					<div id="aib<?php the_ID(); ?>" class="aib-post">
						<h2>
							<a href="<?php the_permalink(); ?>"><?php echo $title; ?></a> / <?php echo $reply_count; ?>
						</h2>
						<div class="aib-content">
							<?php $thumbnail_id = get_post_thumbnail_id(); ?>
							<?php $full_image = is_wp_error($thumbnail_id) ? false : wp_get_attachment_image_src($thumbnail_id, 'full'); ?>
							<?php if ($full_image) : ?>
								<?php $image_file_size = filesize(get_attached_file($thumbnail_id)); ?>
								<div class="aib-image">
									<a href="<?php echo $full_image[0]; ?>" rel="prettyPhoto[aib]">
										<?php the_post_thumbnail('thumbnail'); ?>
									</a>
									<div class="aib-image-size">
										<?php echo sprintf('%d %s', $image_file_size / 1024, __('Kb')); ?>
									</div>
								</div>
							<?php endif; ?>
							<div class="aib-text">
							<?php the_content(); ?>
							</div>
						</div>
						<div class="aib-post-footer">
							#<?php the_ID(); ?>
							|
							<?php the_time('F j, Y | g:i a'); ?>
							<?php if (current_user_can('manage_options')) : ?>
								|
								<a href="<?php echo get_edit_post_link($thread_id, ''); ?>"><?php _e('edit'); ?></a>
							<?php endif; ?>
							<?php if (current_user_can('manage_options') || $aib->get_current_person() == get_post_meta(get_the_ID(), 'aib-impersonation-cookie', true)) : ?>
								|
								<a href="#" class="delete-aib-post" data-id="<?php echo get_the_ID(); ?>"><?php _e('delete'); ?></a>
							<?php endif; ?>
						</div>
					</div>
					<?php
				}
			} else {
				?>
				<div class="aib-no-posts"><?php _e('No threads on this board yet...'); ?></div>
				<?php
			}
			wp_reset_postdata();
		?>
		<nav class="navigation paging-navigation" role="navigation">
			<div class="pagination loop-pagination">
				<?php if ($prev_link) : ?>
					<a class="prev page-numbers" href="<?php echo $prev_link; ?>">← <?php _e('Back to the future'); ?></a> <?php if ($next_link) echo '|'; ?>
				<?php endif; ?>
				<?php if ($next_link) : ?>
					<a class="next page-numbers" href="<?php echo $next_link; ?>"><?php _e('Forward to the past'); ?> →</a>
				<?php endif; ?>
			</div>
		</nav>
	</div>
</section>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$("a[rel^='prettyPhoto']").prettyPhoto({
			show_title: false,
			gallery_markup: '',
			social_tools: ''
		});

		$('#aib-comment-form #submit').click(function(e) {
			var allowSubmit = $('#aib-subject').val() || $('#aib-comment').val() || $('#aib-attachment').val();
			if (allowSubmit) {
				$('.aib-validation-message').fadeOut(1500);
			} else {
				$('.aib-validation-message').fadeIn(500);
				e.preventDefault();
			}
		});
	});
</script>
<?php
//get_sidebar('content');
//get_sidebar();
get_footer();
