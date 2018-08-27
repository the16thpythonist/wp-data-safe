<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 27.08.18
 * Time: 13:48
 */

/**
 * Interface DataDecoder
 *
 * A DataDecoder will be used to decode a string into a data structure.
 *
 * A DataDecoder class is supposed to be a state based worker class, which means, the object is being created during
 * the construction of the actual file post wrapper and should be saved as a field. Which means, that the file saving
 * utility "knows a worker, it will have to contact once a decoding issue will arise in the future". When a data
 * structure is supposed to be loaded "the worker will be contacted and he will be handed his assignment" (passing the
 * data structure to the "set" method) then he will get to work and return his result upon calling the "decode" method.
 * Then he'll wait for his next assignment.
 *
 * This class mainly exists for SOC design reasons. It would be unidiomatic to let the load method of the specific file
 * types contain all the decoding code, when its main concern should be managing the actual "loading".
 */
interface DataDecoder
{
    /**
     * This method will be used to give the worker its next assignment. All following actions(methods) will be executed
     * on this new string.
     *
     * @param string $data The string loaded from the post content
     * @return mixed
     */
    public function set(string $data);

    /**
     * Calling this method will trigger the worker to start working on its new assignment. The finished product
     * (data structure) will be returned.
     *
     * @return mixed
     */
    public function decode();
}