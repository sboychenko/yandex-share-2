<?php
/**
 * Plugin Name: Yandex Share 2
 * Description: Add a Yandex Share block to your WordPress posts and pages.
 * Author: Sergey Boychenko
 * Version: 0.1
 * License: GPLv2
 * Text Domain: yandex-share-2
 * Domain Path: /languages
 */

class Yandex_Share_2_Plugin {
    public $services;
    public $direction;
    public $size;
    public $limit;
    public $token;
    public $counter;
    public $bare;
    public $before;

    function __construct() {
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_init', array( $this, 'admin_init' ) );
    }

    function init() {
        $this -> options = array_merge( array(
            'services' => 'vkontakte,facebook,gplus,twitter,lj,moimir,odnoklassniki,skype,telegram,viber,,whatsapp',
            '$direction' => 'horizontal',
            '$size' => 'm',
            '$limit' => '0'
        ), (array) get_option( 'yandex-share-2', array() ) );

        load_plugin_textdomain( 'yandex-share-2', false, basename( dirname( __FILE__ ) ) . '/languages' );

        $this->services = array(
            'vkontakte' => __('Vkontakte', 'yandex-share-2'),
            'facebook' => __('Facebook', 'yandex-share-2'),
            'odnoklassniki' => __('Odnoklassniki', 'yandex-share-2'),
            'gplus' => __('Google+', 'yandex-share-2'),
            'twitter' => __('Twitter', 'yandex-share-2'),
            'moimir' => __('Moy Mir', 'yandex-share-2'),
            'lj' => __('LiveJournal', 'yandex-share-2'),
            'linkedin' => __('LinkedIn', 'yandex-share-2'),

            'pinterest' => __('Pinterest', 'yandex-share-2'),
            'tumblr' => __('Tumblr', 'yandex-share-2'),

            'skype' => __('Skype', 'yandex-share-2'),
            'viber' => __('Viber', 'yandex-share-2'),
            'whatsapp' => __('WhatsApp', 'yandex-share-2'),
            'telegram' => __('Telegram', 'yandex-share-2'),
            'blogger' => __('Blogger', 'yandex-share-2'),

            'evernote' => __('Evernote', 'yandex-share-2'),
            'pocket' => __('Pocket', 'yandex-share-2'),
            'collections' => __('Ya', 'yandex-share-2'),
            'delicious' => __('Delicious', 'yandex-share-2'),
            'digg' => __('Digg', 'yandex-share-2'),

            'qzone' => __('Qzone', 'yandex-share-2'),
            'reddit' => __('Reddit', 'yandex-share-2'),
            'renren' => __('Renren', 'yandex-share-2'),
            'sinaWeibo' => __('Sina Weibo', 'yandex-share-2'),
            'surfingbird' => __('Surfingbird', 'yandex-share-2'),
            'tencentWeibo' => __('Tencent Weibo', 'yandex-share-2'),
        );

        $this->direction = array(
                'horizontal' => __('Horizontal','yandex-share-2'),
                'vertical' => __('Vertical','yandex-share-2'),
        );

        $this->size = array(
            'm' => __('Big','yandex-share-2'),
            's' => __('Little','yandex-share-2'),
        );

        //$this->limit = 0;
        //$this->token = '';
        //$this->counter = false;
        //$this->bare = false;
        //$this->before = '';

        if ( ! empty( $this->options['services'] ) ) {
            add_filter( 'the_content', array( $this, 'the_content' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        }
    }

    function enqueue_scripts() {
        // add script to footer
        wp_register_script( 'yandex-share-2', '//yastatic.net/share2/share.js', array(), false, true );
    }

    function the_content( $content ) {

        if( !is_single() ) {
            return $content;
        }

        $share = $this->options['before'];
        $share .= sprintf('<div class="ya-share2" data-lang="ru" data-services="%s" data-direction="%s" data-size="%s" data-title="%s" data-url="%s" ',
            esc_attr($this->options['services']),
            esc_attr($this->options['direction']),
            esc_attr($this->options['size']),
            esc_attr(get_the_title()),
            esc_url(get_permalink())
        );

        if ($this->options['limit'] != 0) {
            $share .= ' data-limit="' . $this->options['limit'] . '"';
        }
        if ($this->options['token'] != '') {
            $share .= ' data-access-token="' . $this->options['token'] . '"';
        }
        if ($this->options['counter']) {
            $share .= ' data-counter';
        }
        if ($this->options['bare']) {
            $share .= ' bare';
        }

        $share .= '></div>';

        static $enqueued = false;
        if (!$enqueued) {
            wp_enqueue_script('yandex-share-2');
            $enqueued = true;
        }

        return $content . "\n\n".$share;
    }

    function admin_init() {
        register_setting( 'yandex-share-2', 'yandex-share-2', array( $this, 'sanitize' ) );
        add_settings_section( 'general', '', '', 'yandex-share-2' );

        add_settings_field( 'services', __( 'Services', 'yandex-share-2' ), array( $this, 'field_services' ), 'yandex-share-2', 'general' );
        add_settings_field( 'before', __( 'HTML before', 'yandex-share-2' ), array( $this, 'field_before' ), 'yandex-share-2', 'general' );
        add_settings_field( 'size', __( 'Size', 'yandex-share-2' ), array( $this, 'field_size' ), 'yandex-share-2', 'general' );
        add_settings_field( 'direction', __( 'Direction', 'yandex-share-2' ), array( $this, 'field_direction' ), 'yandex-share-2', 'general' );
        add_settings_field( 'limit', __( 'Limit', 'yandex-share-2' ), array( $this, 'field_limit' ), 'yandex-share-2', 'general' );
        add_settings_field( 'token', __( 'Token', 'yandex-share-2' ), array( $this, 'field_token' ), 'yandex-share-2', 'general' );
        add_settings_field( 'counter', __( 'Counter', 'yandex-share-2' ), array( $this, 'field_counter' ), 'yandex-share-2', 'general' );
        add_settings_field( 'bare', __( 'Bare', 'yandex-share-2' ), array( $this, 'field_bare' ), 'yandex-share-2', 'general' );
    }

    function sanitize( $input ) {
        $output = $this->options;

        if ( isset( $input['services-submit'] ) && empty( $input['services'] ) )
            $output['services'] = '';

        if ( isset( $input['services-submit'] ) && ! empty( $input['services'] ) ) {
            $services = array();
            foreach ( $this->services as $key => $value )
                if ( ! empty( $input['services'][ $key ] ) )
                    $services[] = $key;

            $output['services'] = implode( ',', $services );
        }

        if ( isset( $input['direction'] ) && array_key_exists( $input['direction'], $this->direction ) )
            $output['direction'] = $input['direction'];

        if ( isset( $input['size'] ) && array_key_exists( $input['size'], $this->size ) )
            $output['size'] = $input['size'];

        if ( !isset( $input['counter'] ) )
            $output['counter'] = false;

        if ( isset( $input['counter'] ) )
            $output['counter'] = true;

        if ( !isset( $input['bare'] ) )
            $output['bare'] = false;

        if ( isset( $input['bare'] ) )
            $output['bare'] = true;

        if ( isset( $input['token'] ) )
            $output['token'] = $input['token'];

        if ( isset( $input['limit'] ) )
            $output['limit'] = $input['limit'];

        if ( isset( $input['before'] ) )
            $output['before'] = $input['before'];

        return $output;
    }

    function field_services() {
        $selected_services = explode( ',', $this->options['services'] );
        ?>
        <input type="hidden" name="yandex-share-2[services-submit]" value="1" />
        <?php foreach ( $this->services as $key => $label ) : ?>
            <label style="width: 150px; display: inline-block;"><input
                        type="checkbox"
                        name="yandex-share-2[services][<?php echo esc_attr( $key ); ?>]"
                        value="1"
                    <?php checked( in_array( $key, $selected_services ) ); ?>
                /> <?php echo esc_html( $label ); ?></label>
        <?php endforeach; ?>
        <?php
    }

    function field_direction() {
        ?>
        <select name="yandex-share-2[direction]">
            <?php foreach ( $this->direction as $value => $label ) : ?>
                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $this->options['direction'] ); ?>><?php echo esc_html( $label ); ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    function field_size() {
        ?>
        <select name="yandex-share-2[size]">
            <?php foreach ( $this->size as $value => $label ) : ?>
                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $this->options['size'] ); ?>><?php echo esc_html( $label ); ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    function field_limit() {
        ?>
            <input name="yandex-share-2[limit]" value="<?php echo esc_attr( $this->options['limit'] ); ?>" />
        <?php
    }

    function field_token() {
        ?>
        <input name="yandex-share-2[token]" value="<?php echo esc_attr( $this->options['token'] ); ?>" />
        <?php
    }

    function field_before() {
        ?>
        <textarea name="yandex-share-2[before]" ><?php echo $this->options['before']; ?></textarea>
        <?php
    }

    function field_counter() {
        ?>
        <label><input
                    type="checkbox"
                    name="yandex-share-2[counter]"
                    value="1"
                <?php checked( $this->options['counter'] ); ?>
            /> </label>
        <?php
    }

    function field_bare() {
        ?>
        <label><input
                    type="checkbox"
                    name="yandex-share-2[bare]"
                    value="1"
                <?php checked( $this->options['bare'] ); ?>
            /> </label>
        <?php
    }

    function admin_menu() {
        add_options_page( __( 'Yandex Share 2', 'yandex-share-2' ), __( 'Yandex Share 2', 'yandex-share-2' ), 'manage_options', 'yandex-share-2', array( $this, 'render_options' ) );
    }

    function render_options() {
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2><?php _e( 'Yandex Share 2', 'yandex-share-2' ); ?></h2>
            <p><?php _e( 'Allow add share Yandex buttons', 'yandex-share-2' ); ?>
            <form action="options.php" method="POST">
                <?php settings_fields( 'yandex-share-2' ); ?>
                <?php do_settings_sections( 'yandex-share-2' ); ?>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
$GLOBALS['yandex_share_2_plugin'] = new Yandex_Share_2_Plugin;