<?php

class Londres_Newsletter_Widget extends WP_Widget {
	function __construct() {
		$widget_ops = array('classname' => 'newsletter_widget', 'description' => esc_html__('Subscribe the Newsletter.', 'londres'));
		parent::__construct(false, 'UPPER _ Newsletter', $widget_ops);
	}
function form($instance) {	

	if (isset($instance['title'])){
		$title = esc_attr($instance['title']); 
	} else $title = "";
	
?>  
     <p><label for="<?php echo esc_attr($this->get_field_id('title')); ?>">&#8212; <?php esc_html_e('Title', 'londres'); ?> &#8212;<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_html($title); ?>" /></label></p> 
       <p>This widget will display the subscription form to the newsletter with the information you inserted on the <strong>Appearance</strong> > <strong>Londres Options</strong> > <strong>Newsletter</strong>.</p>
        
<?php
	}
function update($new_instance, $old_instance) {
	// processes widget options to be saved
	$instance = $old_instance;
    $instance['title'] = $new_instance['title'];
		return $instance;
	}
	
function widget($args, $instance) {
		
	extract($args);
    $title = $instance['title'];
   
    ?> 
    <div class="widget widget-newsletter">
	    <?php if (!empty($title)) { echo "<h4>$title</h4><hr>"; } ?>
	    <div class="mail-box">
			<div class="mail-news">
				<div class="news-l">
					<div class="banner">
						<h3><?php
							if (function_exists('icl_t')){
								echo icl_t( 'londres', 'Subscribe our <span class=text_color>Newsletter</span>', get_option('londres_newsletter_text'));
							} else {
								echo get_option("londres_newsletter_text");
							}
						?></h3>
						<p><?php
							if (function_exists('icl_t')){
								echo stripslashes(icl_t( 'londres', 'Subscribe to our newsletter to receive news, cool free stuff updates and new released products (no spam!)', get_option('londres_newsletter_stext')));
							} else {
								echo stripslashes(get_option("londres_newsletter_stext"));
							}
						?></p>
					</div>
					<div class="form">
						<?php
							$code = str_replace('&', '&amp;', get_option("londres_mailchimp_code"));
							echo stripslashes($code);
						?>
					</div>
				</div>
			</div>
		</div>
    </div>
    <?php
	}
}
register_widget('Londres_Newsletter_Widget');

?>
