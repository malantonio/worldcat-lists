# worldcat lists #
Currently, OCLC has not provided an API to access user-generated lists in [WorldCat][wc]. This is a small set of functions (and a command-line script) that scrape a user's lists (or specific list via ID) and return the results.

## usage ##

### programmatically ###
`lib/wc-lists.php` provides two functions that will fetch a list (or lists) and return select data in an associative array:

#### array get_list_items(string $username, mixed $listID) ####
Returns an array of associative arrays for each item in the specified list. Each uses these keys:

     key      |     value
--------------|-----------------
`title`       | Title of book
`author`      | Author
`format`      | Item type
`oclc_number` | Item's OCLC number
`notes`       | User-provided notes
`date_added`  | Date item was added to the list

#### array get_lists(string $username[, bool $show_items = false]) ####
Returns an array of associative arrays for each list. The optional `$show_items` parameter will use `get_list_items` to retrieve items from each list. These keys are used:

     key      |     value
--------------|-----------------
`name`        | User-provided name for the list
`id`          | The list's ID
`description` | User-provided description for list
`items`       | If `$show_items` is `true`, will contain an array of items (returned from `get_list_items`), otherwise will be `null`


### command line ###
to-do

## license ##
MIT

[wc]: http://worldcat.org