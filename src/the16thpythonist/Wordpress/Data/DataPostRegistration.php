<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 27.08.18
 * Time: 14:27
 */

namespace the16thpythonist\Wordpress\Data;


/**
 * Class DataPostRegistration
 *
 *
 *
 * @package the16thplayer\Wordpress\Data
 */
class DataPostRegistration
{
    public $label;
    public $post_type;

    public function __construct(string $post_type, string $label='Data')
    {
        $this->post_type = $post_type;
        $this->label = $label;
    }

    /**
     * Registers the post type and all other things associated with it in wordpress
     *
     * CHANGELOG
     *
     * Added 27.08.2018
     *
     * Changed 29.08.2018
     * Added the registration of the type taxonomy as a hookin
     *
     * Changed 31.08.2018
     * Added the registration of the two ajax methods, that will be used to read and write data files from the
     * frontend directly.
     * Added the registration of the javascript file 'function.js', which contains the JS functions, used to read and
     * write the DataPost files directly from the frontend.
     *
     * Changed 04.11.2018
     * Added the registration for a wp ajax callback, that deletes a data post
     *
     * @since 27.08.2018
     */
    public function register() {
        add_action('init', array($this, 'registerPostType'));
        add_action('init', array($this, 'registerTypeTaxonomy'));

        /*
         * These ajax functions will be used to read and write data files from the frontend directly.
         * no_priv access is not yet supported.
         */
        add_action('wp_ajax_read_data_file', array($this, 'ajaxReadData'));
        add_action('wp_ajax_nopriv_read_data_file', array($this, 'ajaxReadData'));
        add_action('wp_ajax_write_data_file', array($this, 'ajaxWriteData'));
        // 04.11.2018
        // When dealing with temporary file management on the front end, a delete function also became necessary
        add_action('wp_ajax_delete_data_file', array($this, 'ajaxDeleteData'));

        add_action('wp_enqueue_scripts', array($this, 'registerScript'));
        add_action('admin_enqueue_scripts', array($this, 'registerScript'));
    }

    /**
     * Registers the post type itself with wordpress
     *
     * CHANGELOG
     *
     * Added 27.08.2018
     *
     * Changed 29.08.2018
     * Renamed function from "register_post_type" to "registerPostType". I dont know what I thought when doing the
     * first name, that was Python style guide not PHP.
     *
     * @since 0.0.0.0
     */
    public function registerPostType() {
        $args = array(
            'label'                 => $this->label,
            'description'           => 'This post type describes a data storage for data of different data types',
            'public'                => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'show_in_nav_menus'     => false,
            'menu_position'         => 12,
            'menu_icon'             => 'dashicons-portfolio',
            'taxonomies'            => array(
                $this->getTypeTaxonomyName()
            ),
            'has_archive'           => false,
            'map_meta_cap'          => true,
            'supports'              => array(
                'title',
                'editor'
            )
        );
        register_post_type(
            $this->post_type,
            $args
        );
    }

    /**
     * Registers the new type taxonomy for the Data CPT in wordpress
     *
     * CHANGELOG
     *
     * Added 29.08.2018
     *
     * @since 0.0.0.0
     */
    public function registerTypeTaxonomy() {
        $args = array(
            'public'            => true,
            'label'             => 'Type',
            'description'       => 'The "file type" of the data posts'
        );
        register_taxonomy(
            $this->getTypeTaxonomyName(),
            $this->post_type,
            $args
        );
    }

    /**
     * Returns the name of the "Type" taxonomy for the "Data" post type.
     *
     * The name of the taxonomy is being created from the name of the taxonomy and the string "type" joined by a
     * underscore.
     *
     * CHANGELOG
     *
     * Added 27.08.2018
     *
     * @since 0.0.0.0
     *
     * @return string
     */
    public function getTypeTaxonomyName() {
        return $this->post_type . '_type';
    }

    /**
     * Registers the 'functions.js' script with wordpress, so that it is send with each html page.
     *
     * CHANGELOG
     *
     * Added 31.08.2018
     *
     * @since 0.0.0.2
     */
    public function registerScript() {
        $url = plugin_dir_url(__FILE__) . 'functions.js';
        wp_enqueue_script('data-post-function', $url);
    }

    /**
     * The method that will be hooked into the ajax response for getting data from the wp-data-safe system
     *
     * The AJAX request has to be html GET based and the filename has to be passed by the url-variable 'filename'
     *
     * CHANGELOG
     *
     * Added 31.08.2018
     *
     * Changed 29.10.2018
     * Added a wp_die() at the end. Like this the ajax doesnt return the trailing 0
     *
     * @since 0.0.0.2
     */
    public function ajaxReadData() {
        if (array_key_exists('filename', $_GET)) {
            $name = $_GET['filename'];
            $file = DataPost::load($name);
            echo $file->read();
        } else {
            echo 'ERROR: No file name has been passed';
        }
        wp_die();
    }

    /**
     * The method that will be hooked into the ajax response for writing to a DataPost
     *
     * The AJAX request has to be html GET based and contain the following url variables:
     * - filename: The string name of the file to write to. Has to contain the file extension as well
     * - data: The string data to be written into the file
     *
     * Note that, this function does not support anything else, but strings to be written into the file, so the
     * correct encoding of data structures has to be done within the front end.
     *
     * CHANGELOG
     *
     * Added 31.08.2018
     *
     * Changed 29.10.2018
     * Added a wp_die() at the end. Like this the ajax doesnt return the trailing 0
     *
     * @since 0.0.0.2
     */
    public function ajaxWriteData() {
        if (array_key_exists('filename', $_GET) && array_key_exists('data', $_GET)) {
            $name = $_GET['filename'];
            $data = $_GET['data'];
            $file = DataPost::create($name);
            $file->write($data);
        }
        wp_die();
    }

    /**
     * Ajax callback for deleting a data post
     *
     * CHANGELOG
     *
     * Added 04.11.2018
     */
    public function ajaxDeleteData() {
        // First we need to check if a filename even has been passed with the request
        if (array_key_exists('filename', $_GET)) {
            $name = $_GET['filename'];
            DataPost::delete($name);
        }
        // Prevent a malformed AJAX response
        wp_die();
    }

}