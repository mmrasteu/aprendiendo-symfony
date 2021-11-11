<?php
/**
 * @package WordPress
 * @subpackage Londres
 */
?><!DOCTYPE html>
<!--[if IE 9 ]><html <?php language_attributes(); ?> class="ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html <?php language_attributes(); ?>> <!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1" name="viewport">
	<?php wp_head(); ?>
</head>

<?php
	if (get_option('londres_enable_under_construction') === "on" && get_option('londres_under_construction_page') != "0" && !is_user_logged_in()){
		add_action('template_redirect', londres_under_construction());
	}
?>

<body <?php body_class(); if (get_option("londres_body_type") == "body_boxed") echo esc_attr('id=boxed_layout');?>>
	
	<?php
		if (get_option("londres_sliding_panel") == "on" && !is_page_template( 'template-under-construction.php') ){
			?>
			<div class="londres-push-sidebar londres-push-sidebar-right">
			    <i class="overlay-menu-close font-icon icon-icon_close"></i>
			    <div class="display-table">
			        <div id="londres-push-sidebar-content" class="londres-push-sidebar-content ajaxable">
				        <?php
					        if (function_exists('dynamic_sidebar')){
						        dynamic_sidebar('sliding_panel_sidebar');
					        }
				        ?>
			        </div>
			    </div>
			</div>
			<?php
		}
	?>	
	
	
	<div id="main">
	<?php
		$loader = ((is_page_template() && get_post_meta(get_the_ID(), 'londres_enable_custom_header_options_value', true) == "yes") || (is_single() && get_post_meta(get_the_ID(), 'londres_enable_custom_header_options_value', true) == "yes")) ? get_post_meta(get_the_ID(), 'londres_enable_website_loading_value', true) : get_option("londres_enable_website_loader");
		if ($loader == "on"){
			?>
			<div id="londres_website_load">
			  	<div class="spinner">
				  	<?php
					  	$divs = "";
					  	$howMany = 0;
					  	$spinner = get_option("londres_website_loader");
					  	switch($spinner){
							case "ball-clip-rotate": case "square-spin": case "ball-rotate": case "ball-scale": case "ball-scale-ripple":
								$howMany = 1;
							break;
							case "ball-clip-rotate-pulse": case "ball-clip-rotate-multiple": case "cube-transition": case "ball-zig-zag":
								$howMany = 2;
							break;
							case "ball-pulse": case "ball-triangle-path": case "ball-scale-multiple": case "ball-pulse-sync": case "ball-beat": case "ball-scale-ripple-multiple":
								$howMany = 3;
							break;
							case "line-scale-party":
								$howMany = 4;
							break;
							case "ball-pulse-rise": case "line-scale": case "line-scale-pulse-out": case "line-scale-pulse-out-rapid": case "pacman":
								$howMany = 5;
							break;
							case "ball-spin-fade-loader": case "line-spin-fade-loader":
								$howMany = 8;
							break;
							case "ball-grid-pulse":
								$howMany = 9;
							break;
						}
						for ($i=0; $i<$howMany; $i++) $divs .= "<div class='loader_build_helper-{$i}'></div>";
						
						if ($spinner == "load2" || $spinner == "load3" || $spinner == "load6"){
							echo wp_kses_post('<div class="loaders-style-box '.$spinner.'"><div class="loader"></div></div>');
						} else {
							echo wp_kses_post('<div class="loaders-style-box loader-inner '.$spinner.'">'.$divs.'</div>');
						}
				  	?>
				</div>
				<?php
					if (get_option("londres_enable_website_loader_percentage") == "on"){
						?>
						<div class="percentage">0%</div>
						<?php
					}
				?>
		  	</div>
			<?php
		}
	?>
	
	<?php
		if (get_option("londres_body_type") == "body_boxed"){
			?>
			<div class="boxed_layout">
			<?php
		}
	?>