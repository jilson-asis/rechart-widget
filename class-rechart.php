<?php

class Rechart
{
    private $table_name = 'rechart_data';

    private $api_namespace = 'rechart';

    private $api_version = 'v1';

    private $api_endpoint = '/get_data';

    public function __construct()
    {
        global $wpdb;

        $this->table_name = $wpdb->prefix . $this->table_name;
    }

    public function init()
    {
        $this->create_db();
        $this->generate_random_data();
        $this->register_dashboard_widget();
        $this->register_scripts();
        $this->register_api();
    }

    private function create_db()
    {
        global $wpdb;

        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$this->table_name}'" ) !== $this->table_name ) {
            $charset_collate = $wpdb->get_charset_collate();

            $query = "
                CREATE TABLE {$this->table_name} (
                    id INT AUTO_INCREMENT,
                    data_date DATE,
                    value INT,
                    PRIMARY KEY(id)
                ){$charset_collate};";

            if ( ! function_exists( 'dbDelta' ) ) {
                require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            }

            dbDelta($query);
        }
    }

    private function generate_random_data()
    {
        global $wpdb;

        if ( $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name}" ) === "0" ) {
            // generate 1month
            $start_date = strtotime( date( 'Y-m-d' ) . ' -1 month' );
            $end_date = strtotime( date('Y-m-d') );
            $one_day = 60 * 60 * 24;
            $days = round( ( $end_date - $start_date ) / $one_day );

            foreach ( range( 0, $days - 1 ) as $day ) {
                $date = date( 'Y-m-d', strtotime( date( 'Y-m-d', $start_date ) . " +{$day} day" ) );
                $wpdb->insert( $this->table_name, array( 'value' => rand( 0, 100 ), 'data_date' => $date ) );
            }
        }
    }

    public function load_scripts()
    {
        $asset = require_once RECHART_DIR_PATH . '/build/index.asset.php';

        wp_enqueue_script( 'rechart', RECHART_URL . '/build/index.js', $asset['dependencies'], $asset['version'] );

        wp_localize_script( 'rechart', 'rechart', array( 'apiUrl' => home_url('/wp-json') ) );
    }

    private function register_scripts()
    {
        add_action( 'admin_enqueue_scripts', [$this, 'load_scripts'] );
    }

    public function render_dashboard_widget()
    {
        echo "<div id='rechart-container' style='height:400px;'></div>";
    }

    public function create_dashboard_widget()
    {
        wp_add_dashboard_widget( 'dashboard_widget', 'Rechart Dashboard Widget', [$this, 'render_dashboard_widget'] );
    }

    private function register_dashboard_widget()
    {
        add_action( 'wp_dashboard_setup', [$this, 'create_dashboard_widget'] );
    }

    private function register_api()
    {
        add_action( 'rest_api_init', [$this, 'register_routes'] );
    }

    public function register_routes()
    {
        register_rest_route(
            "{$this->api_namespace}/{$this->api_version}",
            $this->api_endpoint,
            array(
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [$this, 'api_get_data'],
                'permission_callback' => function() {
                    return true;
                }
            )
        );
    }

    public function api_get_data(WP_REST_Request $data)
    {
        global $wpdb;

        $end_date = date('Y-m-d');
        $range = $data->get_param('range');

        switch ($range) {
            case "7days":
                $start_date = date( 'Y-m-d', strtotime( "{$end_date} -7 days" ) );
                break;
            case "15days":
                $start_date = date( 'Y-m-d', strtotime( "{$end_date} -15 days" ) );
                break;
            default:
                $start_date = date( 'Y-m-d', strtotime( "{$end_date} -1 month" ) );
                break;
        }

        $query_response = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE data_date BETWEEN '{$start_date}' AND '{$end_date}'"
            )
        );

        $data = array();

        foreach ( $query_response as $row ) {
            $data[] = array(
                'date' => $row->data_date,
                'value' => $row->value,
            );
        }

        return new WP_REST_Response( $data, 200 );
    }

}
