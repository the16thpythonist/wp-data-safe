/**
 * Returns the string url, that needs to to be used to send AJAX requests to wordpress
 *
 * There is a problem with using the constants defined by wordpress itself:
 * The 'ajaxurl' is only defined for the admin area and the 'url' constant is only defined on the actual front end.
 *
 * This function checks which one is defined and returns the value of that one
 *
 * CHANGELOG
 *
 * Added 03.11.2018
 *
 * @return {*}
 */
function ajaxURL() {
    if(typeof ajaxurl === 'undefined') {
        return url;
    } else {
        return ajaxurl;
    }
}

/**
 * Sends an ajax request to the wordpress server using the url variables given by the data param and returns result
 *
 * Note that the ajax request issued inside this function is not asynchronous, thus the function will be blocking until
 * the result has been retrieved from the server or a timeout occurs after 1 second.
 *
 * CHANGELOG
 *
 * Added 31.08.2018
 *
 * @since 0.0.0.2
 *
 * @param args
 * @return {*}
 */
function ajaxDataPost(args) {
    let result, nocache, data;
    nocache = Date.now();
    data = $.extend({}, args, {nocache: nocache});

    jQuery.ajax({
        url:        ajaxURL(),
        type:       'Get',
        timeout:    1000,
        dataType:   'html',
        async:      false,
        data:       data,
        error:      function(response) {
            console.log('There was an error with DataPost Interaction over ajax');
            console.log(response);
        },
        success:    function(response) {
            result = response;
        }
    });
    return result;
}

/**
 * Reads the DataPost file by the given filename on the server and returns the string content
 *
 * CHANGELOG
 *
 * Added 31.08.2018
 *
 * @since 0.0.0.2
 *
 * @param filename  the string name of the file to be written. Has to contain the type extension.
 * @return {*}
 */
function readDataPost(filename) {
    let args = {
        action:     'read_data_file',
        filename:   filename
    };
    return ajaxDataPost(args);
}

/**
 * Writes the given data string to the DataPost file of the given name on the server
 *
 * If the file by the given name does not exist on the server, it will be created.
 *
 * CHANGELOG
 *
 * Added 31.08.2018
 *
 * @since 0.0.0.2
 *
 * @param filename  the string name of the file to be written. Has to contain the type extension.
 * @param data      the string to be written into the file.
 * @return {*}
 */
function writeDataPost(filename, data) {
    let args = {
        action:     'write_data_file',
        filename:   filename,
        data:       data
    };
    return ajaxDataPost(args);
}

/**
 * Deletes the data file post with the given filename
 *
 * CHANGELOG
 *
 * Added 04.11.2018
 *
 * @param filename
 * @return {*}
 */
function deleteDataPost(filename) {
    let args = {
        action:     'delete_data_file',
        filename:   filename
    };
    return ajaxDataPost(args);
}

/**
 * Loads the CSS file with the given url, after the page has loaded dynamically
 *
 * CHANGELOG
 *
 * Added 06.11.2018
 *
 * @param url
 */
function loadCSS(url) {
    jQuery(`<link rel="stylesheet" type="text/css" href="${url}">`).appendTo("head");
}