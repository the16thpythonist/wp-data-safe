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
     * @since 27.08.2018
     */
    public function register() {
        add_action('init', array($this, 'registerPostType'));
        add_action('init', array($this, 'registerTypeTaxonomy'));
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
}