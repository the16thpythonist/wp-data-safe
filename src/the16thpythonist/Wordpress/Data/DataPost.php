<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 27.08.18
 * Time: 14:44
 */

namespace the16thpythonist\Wordpress\Data;

// All the uses need to be hardcoded. If a new type has been created it needs to be updated here
use the16thpythonist\Wordpress\Data\Type\JSONFilePost;

/**
 * Class DataPost
 *
 * The DataPost system:
 * The idea is to have a custom post type, that can be used to save different kinds of data, just like in files.
 * For example a "prices.json" file-post could be used to store a set of fruit prices in JSON format and a.
 * "authors.matrix" could be used to store author names in a custom matrix data type.
 *
 * One might be wondering why not just use files then? There are two reasons.
 * 1. One could be using a distributed system like Openshift to host the wordpress website. This enables the site to be
 * scalable and react dynamically to traffic by creating and deleting more VM instances of wordpress. This means however
 * that when one instance creates a new file in some folder it will not be copied back to all other instances. There
 * are only two things that are common and shared among the instances one special folder, which is most of the time the
 * media folder and the database containing the posts etc. This way uploading a new image or creating a post will indeed
 * be seen amongst all instances. By using the posts as data storage files, there doesnt have to be trouble of finding
 * out which folder is persistent among all VM instances.
 * 2. Creating a new file type wrapper with this system makes it possible to write custom encoder and decoders for the
 * data structure. That means when the data type has been defined once, a custom graph data structure could be saved
 * and retrieved to the system by just a single method call.
 *
 * This class acts as the main interface between the DataPost system and the user/developer. It offers the following
 * STATIC methods:
 * - exists: returns whether or not a file with that filename exists
 * - load: Loads a data post by the file name
 * - create: Creates a new file with given name and type. returns the wrapper object <- use it to save the content in p
 *
 * There is no "folder" system. Names have to be unique! Use prefixing in the name string to indicate some sort of
 * hierarchy instead. Also note that only filenames of files with the same type have to be unique.
 * Having a "prices.json" and a "prices.matrix" is ok.
 *
 * CHANGELOG
 *
 * Added 28.08.2018
 *
 * @since 0.0.0.0
 *
 * @package the16thplayer\Wordpress\Data
 */
class DataPost
{
    /**
     * After the "register" method was called to register the new post type with wordpress, the actual slug of that
     * new custom post type is stored in this field.
     *
     * @var string
     */
    public static $POST_TYPE;

    /**
     * After the register method was called on this class, this field stores the DataPostRegistration object, that was
     * used for the registration process. It contains information such as the exact slug of the type taxonomy.
     *
     * @var DataPostRegistration
     */
    public static $REGISTRATION;

    /*
     * Here an associative array needs to be hardcoded, which assignes each "file extension" (really just a string with
     * the type name) the according class to be used in such a case. For example when a new one is to be created.
     */
    /**
     * This array contains the string names of different types (the ones being used as file extensions) as keys and the
     * according CLASSES for the wrapper class representing that type (having the correct encoder and decoder for the
     * data structure).
     * The type is case insensitive, thus the type names are all in lower case.
     *
     * @var array
     */
    public static $TYPES = array(
        'json'          => JSONFilePost::class
    );

    /**
     * Based on the given filename, loads that data post and returns the according post wrapper object.
     *
     * The objects returned by this method implement the "FileData" interface, which means the data structure, that
     * is saved within the post can be extracted from it by calling the "load" method and a new one can be saved using
     * the "save" method. Beware the types though!
     *
     * CHANGELOG
     *
     * Added 28.08.2018
     *
     * @since 0.0.0.0
     *
     * @param string $filename
     * @return mixed
     */
    public static function load(string $filename) {
        $file = static::explodeFilename($filename);
        $query = static::getWpQuery($filename);
        $post = $query->get_posts()[0];
        $post_id = $post->ID;

        // Creating a wrapper object
        return static::createWrapperObject((string) $post_id, $file['type']);
    }

    /**
     * Creates a new "file" data post by the given file name & returns the wrapper object to the post
     *
     * Based on the type given in the type extension, different classes are used to create an instance. But all those
     * classes implement the "FileData" interface, which means, the data structure, that is saved inside of them can
     * be retrieved by using the "load" method and new content can be saved by using the "save" method.
     *
     * When using this class it is important to be sure, that based on the file extension the correct data type is being
     * assumed when loading and saving.
     *
     * CHANGELOG
     *
     * Added 28.08.2018
     *
     * Changed 29.08.2018
     * Added an if condition, that checks if the post already exists and only creates a new one in case it doesnt
     *
     * @since 0.0.0.0
     *
     * @param string $filename
     * @return mixed
     */
    public static function create(string $filename) {
        $file = static::explodeFilename($filename);

        if (!static::exists($filename)) {
            // Creating the wordpress post
            $post_id = static::createWordpressPost($file['name'], $file['type']);

            // Creating a wrapper object
            return static::createWrapperObject((string) $post_id, $file['type']);
        }
    }

    /**
     * Instantiates and returns a wrapper object of the class corresponding to the given type, using the given post id
     *
     * CHANGELOG
     *
     * Added 28.08.2018
     *
     * @since 0.0.0.0
     *
     * @param string $post_id
     * @param string $type
     * @return mixed
     */
    private static function createWrapperObject(string $post_id, string $type) {
        // Creating a wrapper object
        $class = static::$TYPES[$type];
        $object = new $class($post_id);
        return $object;
    }

    /**
     * Calls the wordpress function, that actually inserts a post with the given title & type tax. term, returns post ID
     *
     * CHANGELOG
     *
     * Added 28.08.2018
     *
     * @since 0.0.0.0
     *
     * @param string $title
     * @param string $type
     * @return mixed
     */
    private static function createWordpressPost(string $title, string $type) {
        $args = array(
            'post_type'         => static::$POST_TYPE,
            'post_title'        => $title,
            'post_status'       => 'publish',
            'tax_input'         => array(
                static::$REGISTRATION->getTypeTaxonomyName() => $type,
            ),
        );
        return \wp_insert_post($args);
    }

    /**
     * Boolean return of whether or not a file with the given filename already exists.
     *
     * CHANGELOG
     *
     * Added 28.08.2018
     *
     * @since 0.0.0.0
     *
     * @param string $filename
     * @return bool
     */
    public static function exists(string $filename) {
        $query = static::getWpQuery($filename);
        return $query->post_count !== 0;
    }

    /**
     * Returns the WP_Query object, which creates an SQL query based on the name and type in the given filename.
     *
     * CHANGELOG
     *
     * Added 28.08.2018
     *
     * @since 0.0.0.0
     *
     * @param string $filename
     * @return mixed
     */
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
        $query = new \WP_Query($args);
        return $query;
    }

    /**
     * Returns an assoc array with two elements, them being the file name and type (extension) if given a "filename"
     *
     * The DataPost system works on the basis of mapping "filename" to wordpress posts. These file names are supposed
     * to be like "normal" file names, in having a name and a type extension seperated by a period. For example
     * 'prices.json' would be such a name.
     * Often in the code the type and name string are needed separately. This method returns an array containing the
     * actual name and the type string. For example: array('name' => 'prices', 'type' => 'json').
     * The type will be represented in lower letters only, because the type string is case insensitive for all
     * references throughout the code.
     *
     * CHANGELOG
     *
     * Added 28.08.2018
     *
     * @since 0.0.0.0
     *
     * @param string $filename
     * @return array
     */
    private static function explodeFilename(string $filename) {
        $explode = explode('.', $filename);
        $name = $explode[0];
        $type = strtolower($explode[1]);
        return array("name" => $name, "type" => $type);
    }

    /**
     * Registers the new CPT in wordpress
     *
     * CHANGELOG
     *
     * Added 28.08.2018
     *
     * @since 0.0.0.0
     *
     * @param string $post_type
     */
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
    public static function queryTitleFilter($where, $wp_query) {
        global $wpdb;
        if ( $post_title_like = $wp_query->get('post_title_like') ) {
            $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $wpdb->esc_like( $post_title_like ) ) . '%\'';
        }
        return $where;
    }
}