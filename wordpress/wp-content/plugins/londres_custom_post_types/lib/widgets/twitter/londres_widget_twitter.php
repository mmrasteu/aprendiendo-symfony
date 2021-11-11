<?php

class Londres_Twitter_Widget extends WP_Widget {
	function __construct() {
		$widget_ops = array('classname' => 'twitter_widget', 'description' => esc_html__('Show your tweets on your site.', 'londres'));
		parent::__construct(false, 'UPPER _ Twitter', $widget_ops);
	}
function form($instance) {

		if (isset($instance['title'])){
			$title = esc_attr($instance['title']); 	
		} else $title = "";
		
		if (isset($instance['twitteruser'])){
			$twitteruser = esc_attr($instance['twitteruser']);  	
		} else $twitteruser = "";
		
		if (isset($instance['ntweets'])){
			$ntweets = esc_attr($instance['ntweets']); 	
		} else $ntweets = "";
		
?>  
        
      <p><label for="<?php echo esc_attr($this->get_field_id('title')); ?>">&#8212; <?php esc_html_e('Title','londres'); ?> &#8212;<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_html($title); ?>" /></label></p> 
       <p><label for="<?php echo esc_attr($this->get_field_id('ntweets')); ?>">&#8212; <?php esc_html_e('Number Tweets to show','londres'); ?> &#8212;<input class="widefat" id="<?php echo esc_attr($this->get_field_id('ntweets')); ?>" name="<?php echo esc_attr($this->get_field_name('ntweets')); ?>" type="text" value="<?php echo esc_attr($ntweets); ?>" /><br><span class="flickr-stuff">If 0 will show the last tweet.</span></label></p>
        
<?php
	}
	
function update($new_instance, $old_instance) {
	// processes widget options to be saved
	$instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    $instance['ntweets'] = $new_instance['ntweets'];
	return $instance;
}
	
function widget($args, $instance) {
		
	extract($args);
    $title = apply_filters('widget_title', $instance['title'], $instance);
    $ntweets = $instance['ntweets'];
    if ($ntweets == "") $ntweets = 1;
    $twitter_data_is_filled = true;
	if ( (get_option("londres_twitter_username") == "") || (get_option("twitter_consumer_key") == "") || (get_option("twitter_consumer_secret") == "") || (get_option("twitter_user_token") == "") || (get_option("twitter_user_secret") == "") || (get_option("londres_twitter_number_tweets") == "") ){
		$twitter_data_is_filled = false;
	}
    if ($twitter_data_is_filled) wp_enqueue_script( 'londres-tweet', LONDRES_JS_PATH .'twitter/jquery.tweet.js', array(),'1.0',$in_footer = true);
    ?>
    
    <div class="twitter_container widget">
	    
	    <?php if ( !empty( $title ) ) { echo wp_kses_post($before_title . $title . $after_title); } ?>
	    
			<div id="twitter_update_list">
			 <?php
				 if ($twitter_data_is_filled){
					
					$londres_inline_script = '
					
						function upper_relative_time(date) {
				            var relative_to = (arguments.length > 1) ? arguments[1] : new Date();
				            var delta = parseInt((relative_to.getTime() - date) / 1000, 10);
				            var r = "";
				            if (delta < 1) {
				                r = "just now";
				            } else if (delta < 60) {
				                r = delta + " seconds ago";
				            } else if(delta < 120) {
				                r = "about a minute ago";
				            } else if(delta < (45*60)) {
				                r = "about " + (parseInt(delta / 60, 10)).toString() + " minutes ago";
				            } else if(delta < (2*60*60)) {
				                r = "about an hour ago";
				            } else if(delta < (24*60*60)) {
				                r = "about " + (parseInt(delta / 3600, 10)).toString() + " hours ago";
				            } else if(delta < (48*60*60)) {
				                r = "about a day ago";
				            } else {
				                r = "about " + (parseInt(delta / 86400, 10)).toString() + " days ago";
				            }
				            return r;
				        }
				        
				        function replacer (regex, replacement) {
				            return function() {
				                var returning = [];
				                this.each(function() {
				                    returning.push(this.replace(regex, replacement));
				                });
				                return jQuery(returning);
				            };
				        }
				
				        function escapeHTML(s) {
				            return s.replace(/</g,"&lt;").replace(/>/g,"^&gt;");
				        }
				
				        jQuery.fn.extend({
				            linkUser: replacer(/(^|[\W])@(\w+)/gi, "$1<span class=\"at\">@</span><a href=\"https://twitter.com/$2\">$2</a>"),
				            linkHash: replacer(/(?:^| )[\#]+([\w\u00c0-\u00d6\u00d8-\u00f6\u00f8-\u00ff\u0600-\u06ff]+)/gi,
				                " <a href=\"https://twitter.com/search?q=%23$1"+(("'.(is_string(get_option("londres_twitter_username"))?get_option("londres_twitter_username"):'').'" && "'.(is_string(get_option("londres_twitter_username"))?get_option("londres_twitter_username"):'').'".length == 1 && !s.list) ? "&from="+s.username.join("%2BOR%2B") : "")+"\" class=\"tweet_hashtag\">#$1</a>"),
				            makeHeart: replacer(/(&lt;)+[3]/gi, "<tt class=\"heart\">&#x2665;</tt>")
				        });
				
				        function linkURLs(text, entities) {
				            return text.replace(window.url_regexp, function(match) {
				                var url = (/^[a-z]+:/i).test(match) ? match : "http://"+match;
				                var text = match;
				                for(var i = 0; i < entities.length; ++i) {
				                    var entity = entities[i];
				                    if (entity.url == url && entity.expanded_url) {
				                        url = entity.expanded_url;
				                        text = entity.display_url;
				                        break;
				                    }
				                }
				                return "<a href=\""+escapeHTML(url)+"\">"+escapeHTML(text)+"</a>";
				            });
				        }
					
						jQuery(document).ready(function(){
							"use strict";
							
							jQuery.ajax({
								type: "POST",
								dataType: "JSON",
								url: "wp-admin/admin-ajax.php",
								data: {
									action : "call_upper_ajax_twitter",
									dataType: "JSON",
									templatepath: jQuery("#homePATH").html()
						        },
								complete: function(data){

									var response = "", resp = "", widget = jQuery(".twitter_container.widget");
							        
			                        if (data.response !== undefined){
			                            response = data.response;
			                        } else {
			                            if (data.responseText !== undefined){
			                                response = data.responseText;
			                            }
			                        }
			
			                        if (response.statuses !== undefined) {
			                            resp = response.statuses;
			                        } else {
			                            if (response.results !== undefined) {
			                                resp = response.results;
			                            } else {
			                                if (response !== undefined) {
			                                    resp = response;
			                                } else {
			                                    if (data.responseJSON !== undefined){
			                                        resp = data.responseJSON;
			                                    }
			                                }
			                            }
			                        }
			                        
			                        resp = JSON.parse(resp);
			                        resp = resp.response;
			                        var odd = true, counter = 0, maxtweets = '. ($ntweets != "" && intval($ntweets)>0 ? intval($ntweets) : "false") .';
			                        var output = "<ul class=\"tweet_list\" >";
									
									jQuery(resp).each(function(){
				                        counter++;
				                        if (maxtweets && counter > maxtweets) return;
				                        output += "<li class=\"tweet";
				                        if (odd) output += "_odd\">";
				                        else output += "_even\">";
				                        
				                        output += "<span class=\"tweet_time\"><a href=\"https://twitter.com/"+jQuery(this)[0].user.screen_name+"/status/"+jQuery(this)[0].id_str+"\">"+upper_relative_time( new Date(jQuery(this)[0].created_at) )+"</a></span>";
				                        
				                        output += "<span class=\"tweet_text\">"+jQuery([linkURLs(jQuery(this)[0].text, jQuery(this)[0].entities)]).linkUser().linkHash()[0]+"</span>";
				                        
				                        output += "</li>";
				                        odd = !odd;
			                        });
									output += "</ul>";
									jQuery(widget).find("#twitter_update_list").html(output);

								}
							});
						});
					';
					wp_add_inline_script('londres-global', $londres_inline_script, 'after');
				 } else {
					 ?>
					 <p>Please fill the fields on the <strong>Appearance > Londres Options > Twitter and Social Icons > Twitter</strong> panel with your credentials.</p>
					 <?php
				 }
			 ?>
			</div>
		</div>
    
    <?php
  
	}
}
register_widget('Londres_Twitter_Widget');

?>
