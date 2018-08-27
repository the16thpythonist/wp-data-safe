<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 27.08.18
 * Time: 11:38
 */

namespace the16thplayer\Wordpress\Data;

/**
 * Interface FileData
 *
 * A post wrapper, that implements the FileData interface will describe a wordpress post object, given by its name
 * (post title) the data content will be stored as a single string (post content) and this string will have to be
 * written and read as it is using the "read" and "write" methods. But since most data is structured differently than
 * just a string the data structure will have to be encoded into the string. The "load" method will be used to get the
 * actual data structure and the save method will be used to take a data structure, encode it and then save it.
 */
interface FileData
{
    /**
     * This function will be used to read the complete plain string of the data.
     *
     * @return string
     */
    public function read(): string;

    /**
     * This function will be used to save a new string as the data content.
     *
     * @param string $content
     * @return mixed
     */
    public function write(string $content);

    /**
     * If the data saved is not a primitive string data structure. This method will decode the string into its original
     * data structure and return that.
     *
     * @return object
     */
    public function load(): object;

    /**
     * Saves a given data structure, by encoding it into a string.
     *
     * @param object $content
     * @return mixed
     */
    public function save(object $content);
}