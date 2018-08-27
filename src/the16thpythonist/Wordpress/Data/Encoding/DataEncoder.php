<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 27.08.18
 * Time: 13:17
 */


/**
 * Interface DataEncoder
 *
 * A DataEncoder will be used to encode a given data structure into a string, so that the string can be saved in a file
 * like data storage post.
 *
 * A DataEncoder class is supposed to be a state based worker class, which means, the object is being created during
 * the construction of the actual file post wrapper and should be saved as a field. Which means, that the file saving
 * utility "knows a worker, it will have to contact once an encoding issue will arise in the future". When a data
 * structure is supposed to be saved "the worker will be contacted and he will be handed his assignment" (passing the
 * data structure to the "set" method) then he will get to work and return his result upon calling the "encode" method.
 * Then he'll wait for his next assignment.
 *
 * This class mainly exists for SOC design reasons. It would be unidiomatic to let the save method of the sepcific file
 * types contain all the encoding code, when its main concern should be managing the actual "saving".
 */
interface DataEncoder
{
    /**
     * With this method, the worker can be given a new assignment to work on. All following actions (methods) of the
     * encoder worker will be based on this new object.
     *
     * @param object $data
     * @return void
     */
    public function set($data);

    /**
     * Calling the this method will trigger the worker to start working on its new assignment. This means all the
     * methods for encoding the given data structure are being executed.
     * The resulting string will be returned
     *
     * @return string
     */
    public function encode(): string;
}