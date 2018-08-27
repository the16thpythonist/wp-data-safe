<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 27.08.18
 * Time: 13:58
 */

namespace the16thplayer\Wordpress\Data;


/**
 * Class FileDataPost
 *
 * This is an abstract class
 *
 * This abstract class is the basis for all the FileData classes, which means all the data that can be modeled to be
 * contained within a single (encoded) string. This class implements the "FileData" Interface, which dictates, that
 * a class must implement "read", "write", "load" and "save".
 * While "load" and "save" are specific to each type of file and the way a data structure is being converted to a
 * string, the "read" and "write" methods are the same for all of them as it is always a string being saved to the
 * "post_content" attribute of a post. These methods have a general implementation here and "load"/"save" still have
 * to be implemented specifically.
 *
 * This class also defines the "name" and "data" attributes, which store the content and the name of the "file"
 *
 * CHANGELOG
 *
 * Added 27.08.2018
 *
 * @since 0.0.0.0
 *
 * @package the16thplayer\Wordpress\Data
 */
abstract class FileDataPost implements FileData
{
    public $post_id;
    public $post;

    public $name;
    public $data;

    /**
     * FileDataPost constructor.
     *
     * CHANGELOG
     *
     * Added 27.08.2018
     *
     * @since 0.0.0.0
     *
     * @param int $post_id
     */
    public function __construct(int $post_id)
    {
        $this->post_id = $post_id;
        /*
         * The post id has to be passed to the constructor. Even if a new "file" is supposed to be created. The process
         * of actually creating that post is the concern of a higher level process (meaning something before the actual
         * wrapper is called).
         * So it is safe to assume that the post object exists.
         */
        $this->post = get_post($this->post_id);
        /*
         * The name of the "file" object is the title of the wordpress post, that represents the "file"
         */
        $this->name = $this->post->post_title;
    }

    /**
     * Returns the string saved inside the post body.
     *
     * CHANGELOG
     *
     * Added 27.08.2018
     *
     * @since 0.0.0.0
     *
     * @return string
     */
    public function read(): string
    {
        /*
         * The 'get_post' method will load the current version of the post object from wordpress and replace the
         * attribute 'post' of this object with the new version. Doing this to prevent that changes on the post
         * during the time this object is created and the read method is called are ignored.
         */
        $this->get_post();
        /*
         * The content of the file is being mapped as the 'post_content' attribute of a wordpress post. So to get the
         * file content this attribute just has to be returned
         */
        $this->data = $this->post->post_content;
        return $this->data;
    }

    /**
     * Loads the wordpress WP_Post object for the post id and then replaces the post attribute with the current object
     *
     * CHANGELOG
     *
     * Added 27.08.2018
     *
     * @since 0.0.0.0
     */
    private function get_post()
    {
        /*
         * loading the *current* version of the post object from wordpress using the post id and replacing this objects
         * attribute with the new version
         */
        $post = get_post($this->post_id);
        $this->post = $post;
    }

    /**
     * Saves the given string as the wordpress post body
     *
     * CHANGELOG
     *
     * Added 27.08.2018
     *
     * @since 0.0.0.0
     *
     * @param string $content
     * @return void
     */
    public function write(string $content)
    {
        /*
         * Updating the relevant attributes of this object
         */
        $this->data = $content;
        /*
         * Actually writing the new values of the current state of the wrapper objects to the wordpress post using the
         * 'wp_update_post' function.
         */
        $this->update_post();
    }

    /**
     *
     */
    private function update_post()
    {
        $postarr = array(
            'ID'            => $this->post_id,
            'post_title'    => $this->name,
            'post_content'  => $this->data,
        );
        wp_update_post($postarr);
    }
}