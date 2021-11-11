<?php

class CubePortfolioFrontend {
    function __construct($data, $id) {
        $this->jsondata = json_decode($data['jsondata']);

        // generate html to return to shortcode
        $this->html = $this->generateHTML($data);

        $this->options = $data['options'];

        $this->style = "<style type='text/css'>" . implode('', json_decode($data['customcss'], true)) . "</style>";

        $this->googleFonts = json_decode($data['googlefonts']);

        $this->script = '<script type="text/javascript">this.initCubePortfolio =  this.initCubePortfolio || []; this.initCubePortfolio.push({id: ' . $id . ', options: ' . $data['options'] . '});' .  '</script>';
    }

    private function generateHTML($data) {
        $items = '';

        foreach ($data['items'] as $value) {
            $items .= $value['items'];
        }

        $data['template'] = str_replace('{{filtersContent}}', $data['filtershtml'], $data['template']);
        $data['template'] = str_replace('{{gridContent}}', $items, $data['template']);
        $data['template'] = str_replace('{{loadMoreContent}}', $data['loadMorehtml'], $data['template']);

        $customCls = '';
        if ($this->jsondata && (isset($this->jsondata->customCls) && $this->jsondata->customCls)) {
            $customCls = ' ' . $this->jsondata->customCls;
        }
        $data['template'] = str_replace('{{customCls}}', $customCls, $data['template']);

        if ($this->jsondata && (isset($this->jsondata->forceFullWidth) && $this->jsondata->forceFullWidth)) {
            $data['template'] = '<div class="cbpw-fullWidth-force">' . $data['template'] . '</div>';
        }

        return $data['template'];
    }
}

