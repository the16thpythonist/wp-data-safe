# Description


# Changelog

### 0.0.0.0 - 29.09.2018

Initial version

### 0.0.0.1 - 29.09.2018

- Changed the "create" Method on the DataPost fassade, so that the load method is called, when the post already exists.

### 0.0.0.2 - 31.08.2018

- The post type registration now adds two ajax callbacks, that can be called from the frontend to  read and write the 
string content of a data post directly.
- The post type registration now enqueues a JS script, which contains functions, that can be used to read and write 
data posts from JS directly using AJAX GET requests.
- Added file 'functions.js'
