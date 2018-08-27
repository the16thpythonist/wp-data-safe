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
        'JSON'          => JSONFilePost::class
    );

    public static function create(string $filename) {
        $split = explode($filename, '.');
        $name = $split[0];
        $type = strtoupper($split[1]);

        // TODO: Actually create a new post in wordpress (Also needs checking if already exists)

        // Creating a wrapper object
        $class = static::$TYPES[$type];
    }

    public static function register(string $post_type) {
        static::$POST_TYPE = $post_type;

        $registration = new DataPostRegistration($post_type);
        $registration->register();

        static::$REGISTRATION = $registration;
    }
}