<?php
require_once('lib/term_parser.php');
#$term_string = '{[],{},[{}],[1],[a],[1,2],[1,2,3]}';
$term_string = '{[],[48],[a],[48,49],[48,49,50]}';
list($result, $offset) = erl_parse_all($term_string, 0);
print_r(erl_list2string($result));
