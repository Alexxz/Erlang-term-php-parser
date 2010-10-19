<?php
require_once('lib/erlang_term_parser.php');


$tests = array(
    'basic test' => array(
        'source' => '{[],{},[{}],[48],[a],<11.22.33>}',
        'result' => array(
            'type' => 'tuple', 
            'data' => array(
                array('type' => 'list',  'data' => array()),
                array('type' => 'tuple', 'data' => array()),
                array('type' => 'list',  'data' => array( array( 'type' => 'tuple',  'data' => array()))),
                array('type' => 'list',  'data' => array( array( 'type' => 'number', 'data' => 48))),
                array('type' => 'list',  'data' => array( array( 'type' => 'atom',   'data' => 'a'))),
                array('type' => 'pid',   'data' => '<11.22.33>'),
                ),
            ),
        ),
    'number test' => array(
        'source' => '[48, -49, 1.2, 1.2e3]',
        'result' => array(
            'type' => 'list', 
            'data' => array(
                array('type' => 'number',  'data' => 48),
                array('type' => 'number',  'data' => -49),
                array('type' => 'number',  'data' => 1.2),
                array('type' => 'number',  'data' => 1.2e3),
                ),
            ),
        ),
    );



foreach($tests as $name => $test){
    echo "Test [$name] ... ";
    $result = erl_parse_term($test['source']);
    if($test['result'] !== $result ){
        echo "failed\n";
        print_r($result);
        print_r($test['result']);
    } else {
        echo "OK\n";
    }
}



