<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 27.08.18
 * Time: 15:03
 */

namespace the16thpythonist\Wordpress\Data\Type;

use the16thpythonist\Wordpress\Data\FileDataPost;



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
     * @return array
     */
    public function load()
    {
        $encoded = $this->read();
        $sanitized = $this->sanitizeJSON($encoded);
        //$sanitized = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $sanitized);
        //$sanitized = utf8_encode($sanitized);
        return json_decode($sanitized, TRUE);
    }

    private function sanitizeJSON(string $encoded) {
        // This will remove unwanted characters.
        // Check http://www.php.net/chr for details
        for ($i = 0; $i <= 31; ++$i) {
            $encoded = str_replace(chr($i), "", $encoded);
        }
        $encoded = str_replace(chr(127), "", $encoded);

        // This is the most common part
        // Some file begins with 'efbbbf' to mark the beginning of the file. (binary level)
        // here we detect it and we remove it, basically it's the first 3 characters
        if (0 === strpos(bin2hex($encoded), 'efbbbf')) {
            $encoded = substr($encoded, 3);
        }

        $encoded = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $encoded);
        $encoded = stripslashes($encoded);
        $encoded = utf8_encode($encoded);

        return $encoded;
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
     * @param $content
     * @return void
     */
    public function save($content)
    {

        $encoded = json_encode($content, JSON_PRETTY_PRINT);
        $this->write($encoded);
    }
}