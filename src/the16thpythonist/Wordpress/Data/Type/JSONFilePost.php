<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 27.08.18
 * Time: 15:03
 */

namespace the16thplayer\Wordpress\Data\Type;

use the16thplayer\Wordpress\Data\FileDataPost;

/**
 * Class JSONFilePost
 *
 * Used to save JSON-able data easily as wordpress posts.
 *
 * This class does not use Encoder/Decoder objects, since the functionality of encoding/decoding JSON data formats is
 * already as easy as a single call to a PHP standard function.
 *
 * CHANGELOG
 *
 * Added 27.08.2018
 *
 * @since 0.0.0.0
 *
 * @author Jonas Teufel <jonseb1998@gmail.com>
 *
 * @package the16thplayer\Wordpress\Data\Type
 */
class JSONFilePost extends FileDataPost
{

    /**
     * Loads the JSON object from the post.
     *
     * CHANGELOG
     *
     * Added 27.08.2018
     *
     * @since 0.0.0.0
     *
     * @return object
     */
    public function load(): object
    {
        $encoded = $this->read();
        $data = json_decode($encoded);
        return $data;
    }

    /**
     * Saves the JSON object to the post.
     *
     * CHANGELOG
     *
     * Added 27.08.2018
     *
     * @since 0.0.0.0
     *
     * @param object $content
     * @return void
     */
    public function save(object $content)
    {
        $encoded = json_encode($content, JSON_PRETTY_PRINT);
        $this->write($encoded);
    }
}