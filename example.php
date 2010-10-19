<?php
require_once('lib/erlang_term_parser.php');

$source_string = '{[],123,<11.22.33>,"123"}';

$term = erl_parse_term($source_string);
print_r($term);

$string_term = erl_list2string($term);
print_r($string_term);
