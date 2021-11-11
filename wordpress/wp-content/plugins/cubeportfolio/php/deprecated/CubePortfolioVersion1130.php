<?php

/**
*
*/
class CubePortfolioVersion1130
{
    // wordpress global db
    private $wpdb;

    public function __construct() {
        global $wpdb;

        // store global db instance
        $this->wpdb = $wpdb;

        $this->editCustomcss();
    }

    private function editCustomcss() {
        $table_cbp = CubePortfolioMain::$table_cbp;

        $records = $this->wpdb->get_results("SELECT id, customcss FROM $table_cbp", ARRAY_A);

        foreach ($records as $key => $value) {
            $value['customcss'] = str_replace('.cbp-filter-counter:before', '.cbp-filter-counter:after', $value['customcss']);

            $this->wpdb->update($table_cbp,
                                array('customcss' => $value['customcss']),
                                array('id' => $value['id']),
                                array('%s'),
                                array('%d')
                                );
        }
    }
}
