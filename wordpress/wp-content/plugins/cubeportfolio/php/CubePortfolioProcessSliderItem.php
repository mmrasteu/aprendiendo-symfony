<?php

/**
*
*/
class CubePortfolioProcessSliderItem
{
    // wordpress global db
    private $wpdb;

    private $item;

    public function __construct($item) {
        global $wpdb;

        // store global db instance
        $this->wpdb = $wpdb;

        $this->item = $item;

        if (!isset($this->item->type)) {
            $this->item->type = 'image';
        }
    }

    public function getURL() {
        return call_user_func(array($this, $this->item->type . 'URL'));
    }

    private function imageURL() {
        return wp_get_attachment_url($this->item->id);
    }

    private function youtubeURL() {
        $lastIndex = strrpos($this->item->id, 'v=') + 2;
        $videoLink = substr($this->item->id, $lastIndex);

        $videoLink = preg_replace('/\?|&/', '?', $videoLink);

        return '//www.youtube.com/embed/' . $videoLink;
    }

    private function vimeoURL() {
        $lastIndex = strrpos($this->item->id, '/') + 1;
        $videoLink = substr($this->item->id, $lastIndex);

        $videoLink = preg_replace('/\?|&/', '?', $videoLink);

        return '//player.vimeo.com/video/' . $videoLink;
    }

    private function tedURL() {
        $lastIndex = strrpos($this->item->id, '/') + 1;
        $videoLink = substr($this->item->id, $lastIndex);

        return 'http://embed.ted.com/talks/' . $videoLink . '.html';
    }

    private function soundcloudURL() {
        return $this->item->id;
    }

    private function selfhostedvideoURL() {
        $url = $this->item->id;

        if (strpos($url, '|') !== false) {
            // create new url
            $url = explode('|', $url);
        } else {
            // create new url
            $url = explode('%7C', $url);
        }

        return $url;
    }

    private function selfhostedaudioURL() {
        return $this->item->id;
    }

    public function getHTML() {
        $url = call_user_func(array($this, $this->item->type . 'URL'));

        return call_user_func(array($this, $this->item->type . 'HTML'), $url);
    }

    private function imageHTML($url) {
        return '<img src="' . $url . '" alt="">';
    }

    private function youtubeHTML($url) {
        return '<div class="cbp-misc-video"><iframe src="' . $url . '" frameborder="0" allowfullscreen scrolling="no"></iframe></div>';
    }

    private function vimeoHTML($url) {
        return '<div class="cbp-misc-video"><iframe src="' . $url . '" frameborder="0" allowfullscreen scrolling="no"></iframe></div>';
    }

    private function tedHTML($url) {
        return '<div class="cbp-misc-video"><iframe src="' . $url . '" frameborder="0" allowfullscreen scrolling="no"></iframe></div>';
    }

    private function soundcloudHTML($url) {
        return '<div class="cbp-misc-video"><iframe src="' . $url . '" frameborder="0" allowfullscreen scrolling="no"></iframe></div>';
    }

    private function selfhostedvideoHTML($url) {
        $html = '<div class="cbp-misc-video"><video controls="controls" height="auto" style="width: 100%">';

        foreach ($url as $value) {
            $type = '';

            if (preg_match('/(\.mp4)/i', $value) === 1) {
                $type = 'mp4';
            } else if (preg_match('/(/(\.ogg)|(\.ogv)/i', $value) === 1) {
                $type = 'ogg';
            } else if (preg_match('/(\.webm)/i', $value) === 1) {
                $type = 'webm';
            }

            $html .= '<source src="' . $value . '" type="video/' . $type . '">';
        }

        $html .= 'Your browser does not support the video tag.' .
            '</video></div>';

        return $html;
    }

    private function selfhostedaudioHTML($url) {
        return '<div class="cbp-misc-video"><audio controls="controls" height="auto" style="margin-top: 26%; width: 75%">' .
            '<source src="' . $url . '" type="audio/mpeg">' .
            'Your browser does not support the audio tag.' .
            '</audio></div>';
    }
}
