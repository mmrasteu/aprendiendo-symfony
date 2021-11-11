<?php

class Londres_Instagram_Widget extends WP_Widget {
	function __construct() {
		$widget_ops = array('classname' => 'instagram_widget', 'description' => esc_html__('Displays your latest Instagram photos.', 'londres'));
		parent::__construct(false, 'UPPER _ Instagram', $widget_ops);
	}
	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => __( 'Instagram', 'londres' ), 'username' => '', 'link' => __( 'Follow Me!', 'londres' ), 'number' => 9, 'target' => '_self' ) );
		$title = $instance['title'];
		$number = absint( $instance['number'] );
		$target = $instance['target'];
		$link = $instance['link'];
		
		if (!get_option('londres_insta_token') || get_option('londres_insta_token') == ""){
			echo '<p>Please go to Appearance > Londres Options > Social Networks > Instagram and click on Authorize Instagram to grant access to your feed.</p>';
		}
		?>
		<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'londres' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>
		<p><label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Number of photos', 'londres' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>" /></label></p>
		
		<p><label for="<?php echo esc_attr( $this->get_field_id( 'target' ) ); ?>"><?php esc_html_e( 'Open links in', 'londres' ); ?>:</label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'target' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'target' ) ); ?>" class="widefat">
				<option value="_self" <?php selected( '_self', $target ) ?>><?php esc_html_e( 'Current window (_self)', 'londres' ); ?></option>
				<option value="_blank" <?php selected( '_blank', $target ) ?>><?php esc_html_e( 'New window (_blank)', 'londres' ); ?></option>
			</select>
		</p>
		<p><label for="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>"><?php esc_html_e( 'Link text', 'londres' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'link' ) ); ?>" type="text" value="<?php echo esc_attr( $link ); ?>" /></label></p>
		<?php
	}
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = ! absint( $new_instance['number'] ) ? 9 : $new_instance['number'];
		$instance['target'] = ( ( $new_instance['target'] == '_self' || $new_instance['target'] == '_blank' ) ? $new_instance['target'] : '_self' );
		$instance['link'] = strip_tags( $new_instance['link'] );
		return $instance;
	}
		
	function widget($args, $instance) {
		$title = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', $instance['title'] );
		$limit = empty( $instance['number'] ) ? 9 : $instance['number'];
		$target = empty( $instance['target'] ) ? '_self' : $instance['target'];
		$link = empty( $instance['link'] ) ? '' : $instance['link'];

		echo '<div class="widget instagram_widget">' . wp_kses_post($args['before_widget']);

		if ( ! empty( $title ) ) { echo wp_kses_post( '<h4 class="widget_title_span">' . $title . '</h4>' ); };

		do_action( 'upper_insta_before_widget', $instance );

		$this->upper_scrape_instagram($instance);
		
		do_action( 'upper_insta_after_widget', $instance );
		echo wp_kses_post($args['after_widget']) . '</div>';
	}
	
	function upper_scrape_instagram($instance) {
		
		$title = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', $instance['title'] );
		$limit = empty( $instance['number'] ) ? 9 : $instance['number'];
		$target = empty( $instance['target'] ) ? '_self' : $instance['target'];
		$link = empty( $instance['link'] ) ? '' : $instance['link'];
		
		$access_token = get_option('londres_insta_token', false);
		$uid = get_option('londres_insta_uid', false);
		
		if (!$access_token || !$uid){
			echo 'Please authorize Instagram on Appearance > Londres Options > Social Networks > Instagram and clicking on Authorize Instagram.';
			return;
		}
		
		$url = 'https://g' . 'raph.ins' . 'tag' . 'ram.com/' . $uid . '/media?fields=media_url,thumbnail_url,caption,id,media_type,timestamp,username,comments_count,like_count,permalink,children{media_url,id,media_type,timestamp,permalink,thumbnail_url}&limit=' . $limit . '&acce' . 'ss_to' . 'ken=' . $access_token;
		
		$args = array(
			'timeout' => 60,
			'sslverify' => false
		);
		
		$response = londres_remote_get( $url, $args, "_scrapper_widget-".$instance['number'], $instance['number'] );
		
		if (isset($response['data'])){
			
			$posts = $response['data'];
			
			$ulclass = apply_filters( 'upper_insta_list_class', 'instagram-pics' );
			$liclass = apply_filters( 'upper_insta_item_class', '' );
			$aclass = apply_filters( 'upper_insta_a_class', '' );
			$imgclass = apply_filters( 'upper_insta_img_class', '' );
			$username = '';
			if (get_option('londres_enable_instagram_grayscale') == "on") $imgclass .= ' londres_grayscale ';
			echo '<ul class="'.esc_attr( $ulclass ).'">';
			foreach ($posts as $post){
				$caption = isset($post['caption']) ? $post['caption'] : '';
				$thumb = strtolower($post['media_type']) == "video" ? $post['thumbnail_url'] : $post['media_url'];
				echo '<li style="width:'. (100/$limit) .'%;" class="'. esc_attr( $liclass ) .'"><a href="'. esc_url( $post['permalink'] ) .'" target="'. esc_attr( $target ) .'"  class="'. esc_attr( $aclass ) .'"><img src="'. esc_url( $thumb ) .'"  alt="'. esc_attr( $caption ) .'" title="'. esc_attr( $caption ).'"  class="'. esc_attr( $imgclass ) .'"/></a></li>';
				$username = $post['username'];
			}
			echo '</ul>';
			
			$linkclass = apply_filters( 'upper_insta_link_class', 'clear' );
			if ( get_option('londres_instagram_link') != '' ) {
				?><p class="<?php echo esc_attr( $linkclass ); ?>"><a href="<?php echo trailingslashit( '//ins'.'tag'.'ram.com/' . $username ); ?>" rel="me" target="<?php echo esc_attr( $target ); ?>"><?php echo esc_html( $link ); ?></a></p><?php
			}

		} else {
			echo 'Something went wrong. Please re-authorize Instagram on Appearance > Londres Options > Social Networks > Instagram and clicking on Authorize Instagram.';
			return;
		}
		
	}
	
	function upper_images_only( $media_item ) {
		if ( $media_item['type'] == 'image' )
			return true;
		return false;
	}
	
}
register_widget('Londres_Instagram_Widget');

?>
