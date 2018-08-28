<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 27.08.18
 * Time: 14:44
 */

namespace the16thplayer\Wordpress\Data;

// All the uses need to be hardcoded. If a new type has been created it needs to be updated here
use the16thplayer\Wordpress\Data\Type\JSONFilePost;


class DataPost
{
    public static $POST_TYPE;
    public static $REGISTRATION;

    /*
     * Here a associative array needs to be hardcoded, which assignes each "file extension" (really just a string with
     * the type name) the according class to be used in such a case. For example when a new one is to be created.
     */
    public static $TYPES = array(
        'json'          => JSONFilePost::class
    );

    public static function load(string $filename) {
        $file = static::explodeFilename($filename);
        $query = static::getWpQuery($filename);
        $post = $query->get_posts()[0];
        $post_id = $post->ID;

        // Creating a wrapper object
        return static::createWrapperObject((string) $post_id, $file['type']);
    }

    public static function create(string $filename) {
        $file = static::explodeFilename($filename);

        // Creating the wordpress post
        $post_id = static::createWordpressPost($file['name'], $file['type']);

        // Creating a wrapper object
        return static::createWrapperObject((string) $post_id, $file['type']);
    }

    private static function createWrapperObject(string $post_id, string $type) {
        // Creating a wrapper object
        $class = static::$TYPES[$type];
        $object = new $class($post_id);
        return $object;
    }

    private static function createWordpressPost(string $title, string $type) {
        $args = array(
            'post_type'         => static::$POST_TYPE,
            'post_title'        => $title,
            'post_status'       => 'publish',
            'tax_input'         => array(
                static::$REGISTRATION->getTypeTaxonomyName() => $type,
            ),
        );
        return wp_insert_post($args);
    }

    public static function exists(string $filename) {
        $query = static::getWpQuery($filename);
        return $query->post_count !== 0;
    }

    private static function getWpQuery(string $filename) {
        $file = static::explodeFilename($filename);

        /*
         * Originally there is a problem with using the WP_Query for such a request. Filtering by the taxonomy term is
         * easy using the "tax_query" parameter. But the WP_Query doesnt support filtering by the actual title.
         * Therefore the method "queryTitleFilter" of this class is a filter callback that adds this functionality to
         * WP_Query. Now the title can be searched by the string parameter "post_title_like".
         * !This functionality only gets added if the "register" method of this class is called first!
         *
         * Remember, that the name of the type taxonomy is combined from the custom name for the post type. It is
         * stored within the registration object.
         */
        $args = array(
            'post_type'             => static::$POST_TYPE,
            'post_title_like'       => $file['name'],
            'posts_per_page'        => 2,
            'tax_query'             => array(
                array(
                    'taxonomy'      => static::$REGISTRATION->getTypeTaxonomyName(),
                    'field'         => 'slug',
                    'terms'         => $file['type']
                )
            )
        );
        $query = WP_Query($args);
        return $query;
    }

    private static function explodeFilename(string $filename) {
        $explode = explode('.', $filename);
        $name = $explode[0];
        $type = strtolower($explode[1]);
        return array("name" => $name, "type" => $type);
    }

    public static function register(string $post_type) {
        add_filter('posts_where', array(static::class, 'queryTitleFilter'), 10, 2);

        static::$POST_TYPE = $post_type;

        $registration = new DataPostRegistration($post_type);
        $registration->register();

        static::$REGISTRATION = $registration;
    }

    /**
     * Wordpress filter hook in. Adds the optional argument to filter by the post_title with a WP_Query
     *
     * The Problem:
     * For the functionality of retrieving file like data posts, the wordpress posts have to be filtered by their names
     * and their types (The types are modelled as different taxonomy terms to the custom taxonomy "type"). Now with
     * wordpress the problem is that using a custom SQL query (global $wpdb) it would be easy to filter by the post
     * title, but also needing to filter by the term would make the query very complicated. Using the WP_query object
     * to search for the posts would make it easy to filter by the taxonomy term (using the "tax_query" argument), but
     * there is no parameter to filter by the actual title, only the title slug.
     *
     * This method will be used as the callback function to a wordpress filter hook, which will add a parameter to the
     * WP_query arguments, that will filter by the actual title string.
     *
     * CHANGELOG
     *
     * Added 28.08.2018
     *
     * @since 0.0.0.0
     *
     * @param $where
     * @param $wp_query
     * @return string
     */
    private static function queryTitleFilter($where, $wp_query) {
        global $wpdb;
        if ( $post_title_like = $wp_query->get('post_title_like') ) {
            $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $wpdb->esc_like( $post_title_like ) ) . '%\'';
        }
        return $where;
    }
}