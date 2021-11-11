<?php

if (!class_exists("ezTweet")){
	class ezTweet {
		/*************************************** config ***************************************/

		// Your Twitter App Consumer Key
		private $consumer_key = "";

		// Your Twitter App Consumer Secret
		private $consumer_secret = "";

		// Your Twitter App Access Token
		private $user_token = "";

		// Your Twitter App Access Token Secret
		private $user_secret = "";

		// Path to tmhOAuth libraries
		private $lib = "";

		// Enable caching
		private $cache_enabled = false;

		// Cache interval (minutes)
		private $cache_interval = 15;

		// Path to writable cache directory
		private $cache_dir = './';

		// Enable debugging
		private $debug = false;

		/**************************************************************************************/

		public function __construct() {
			// Initialize paths and etc.
			$this->consumer_key = str_replace(' ','',get_option('twitter_consumer_key'));
			$this->consumer_secret = str_replace(' ','',get_option('twitter_consumer_secret'));
			$this->user_token = str_replace(' ','',get_option('twitter_user_token'));
			$this->user_secret = str_replace(' ','',get_option('twitter_user_secret'));
			$this->lib = $_POST['templatepath'].'wp-content/plugins/londres_custom_post_types/lib/';
			$this->pathify($this->cache_dir);
			$this->pathify($this->lib);
			$this->message = '';

			// Set server-side debug params
			$this->debug = false;
		}

		public function fetch() {
			echo json_encode(
				array(
					'response' => json_decode($this->getJSON(), true),
					'message' => ($this->debug) ? $this->message : false
				)
			);
		}

		private function getJSON() {
			if($this->cache_enabled === true) {
				$CFID = $this->generateCFID();
				$cache_file = $this->cache_dir.$CFID;


				$JSONraw = $this->getTwitterJSON();
				$JSON = $JSONraw['response'];

				// Don't write a bad cache file if there was a CURL error
				if($JSONraw['errno'] != 0) {
					$this->consoleDebug($JSONraw['error']);
					return $JSON;
				}

				if($this->debug === true) {
					// Check for twitter-side errors
					$pj = json_decode($JSON, true);
					if(isset($pj['errors'])) {
						foreach($pj['errors'] as $error) {
							$message = 'Twitter Error: "'.$error['message'].'", Error Code #'.$error['code'];
							$this->consoleDebug($message);
						}
						return false;
					}
				}


				return $JSON;

			} else {
				$JSONraw = $this->getTwitterJSON();

				if($this->debug === true) {
					// Check for CURL errors
					if($JSONraw['errno'] != 0) {
						$this->consoleDebug($JSONraw['error']);
					}

					// Check for twitter-side errors
					$pj = json_decode($JSONraw['response'], true);
					if(isset($pj['errors'])) {
						foreach($pj['errors'] as $error) {
							$message = 'Twitter Error: "'.$error['message'].'", Error Code #'.$error['code'];
							$this->consoleDebug($message);
						}
						return false;
					}
				}
				return $JSONraw['response'];
			}
		}

		private function getTwitterJSON() {
			require $this->lib.'tmhOAuth.php';
			require $this->lib.'tmhUtilities.php';

			$tmhOAuth = new tmhOAuth(array(
				'consumer_key'          => $this->consumer_key,
				'consumer_secret'       => $this->consumer_secret,
				'user_token'            => $this->user_token,
				'user_secret'           => $this->user_secret,
				'curl_ssl_verifypeer'   => false
			));

			if (!isset($_POST['request'])){
				$_POST['request'] = array(
					"host" => "api.twitter.com",
					"url" => "/1.1/statuses/user_timeline.json",
					"parameters" => array(
						"include_entities" => 1,
						"screen_name" => array(get_option('londres_twitter_username')),
						"page" => 1,
						"count" => intval(get_option('londres_twitter_number_tweets')),
						"include_rts" => 1
					)
				);
			}
			
			$url = $_POST['request']['url'];
			$params = $_POST['request']['parameters'];
			
			$tmhOAuth->request('GET', $tmhOAuth->url($url), $params);
			return $tmhOAuth->response;
		}

		private function generateCFID() {
			// The unique cached filename ID
			return md5(serialize($_POST)).'.json';
		}

		private function pathify(&$path) {
			// Ensures our user-specified paths are up to snuff
			$path = realpath($path).'/';
		}

		private function consoleDebug($message) {
			if($this->debug === true) {
				$this->message .= 'tweet.js: '.$message."\n";
			}
		}
	}	
}

add_action ('wp_ajax_call_upper_ajax_twitter', 'upper_ajax_twitter') ;
add_action ('wp_ajax_nopriv_call_upper_ajax_twitter', 'upper_ajax_twitter') ;

function upper_ajax_twitter(){
	
	if (!isset($_POST)) wp_send_json_error('no direct access');
	if (!wp_verify_nonce($_POST['security'],'londres-theme-twitter')) return;
	
	include_once $_POST['templatepath'] . 'wp-content/plugins/londres_custom_post_types/lib/tmhOAuth.php';

	$cennings = new tmhOAuth(array());
	$thepath = $cennings->config['curl_capath'];
	$thepath = substr($thepath, 0, strpos($thepath, "wp-content"));
	unset($cennings);
	
	$ezTweet = new ezTweet;
	$ezTweet->fetch();
	
	wp_die();
	
}

?>