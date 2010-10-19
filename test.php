<?php
require_once('lib/erlang_term_parser.php');


$tests = array(
    'basic' => array(
        'type'   => 'simple',
        'source' => '{[],{},[{}],[48],[a],<11.22.33>}',
        'result' => array(
            'type' => 'tuple', 
            'data' => array(
                array('type' => 'list',  'data' => array()),
                array('type' => 'tuple', 'data' => array()),
                array('type' => 'list',  'data' => array( array( 'type' => 'tuple',  'data' => array()))),
                array('type' => 'list',  'data' => array(48) ),
                array('type' => 'list',  'data' => array( array( 'type' => 'atom',   'data' => 'a'))),
                array('type' => 'pid',   'data' => '<11.22.33>'),
                ),
            ),
        ),
    'number' => array(
        'type'   => 'simple',
        'source' => '[48, -49, 1.2, 1.2e3]',
        'result' => array(
            'type' => 'list', 
            'data' => array( 48, -49, 1.2, 1.2e3 ),
            ),
        ),
    'lists' => array(
        'type'   => 'simple',
        'source' => '[[],"","012"]',
        'result' => array(
            'type' => 'list', 
            'data' => array(
                array('type' => 'list',  'data' => array()),
                array('type' => 'list',  'data' => array()),
                array('type' => 'list',  'data' => array(48,49,50)),
                ),
            ),
        ),
    'exception tuple' => array('type'   => 'exception', 'source' => '{', 'result' => 'Error while parsing term',),
    'exception list'  => array('type'   => 'exception', 'source' => '[', 'result' => 'Error while parsing term',),
    'exception list tuple' => array('type'   => 'exception', 'source' => '[{', 'result' => 'Error while parsing term',),
    'exception tuple 1' => array('type'   => 'exception', 'source' => '{"','result' => 'Error while parsing quoted list',),
    'exception tuple 2' => array('type'   => 'exception', 'source' => '{""','result' => 'Error while parsing tuple',),
    'exception tuple 3' => array('type'   => 'exception', 'source' => '{"",','result' => 'Error while parsing term',),
    'list2string' => array(
        'type'   => 'list2string',
        'source' => '{[],"0","123"}',
        'result' => array(
            'type' => 'tuple',
            'data' => array('', '0', '123')
            ) 
        ),
    );



foreach($tests as $name => $test){
    if($test['type'] === 'list2string'){
        echo "Test [$name] ... ";
        $result = erl_list2string(erl_parse_term($test['source']));
        if($test['result'] !== $result ){
            echo "failed\n";
            print_r($result);
            print_r($test['result']);
        } else {
            echo "OK\n";
        }
    } elseif($test['type'] === 'simple'){
        echo "Test [$name] ... ";
        $result = erl_parse_term($test['source']);
        if($test['result'] !== $result ){
            echo "failed\n";
            print_r($result);
            print_r($test['result']);
        } else {
            echo "OK\n";
        }
    } elseif($test['type'] === 'exception'){
        echo "Test [$name] ... ";
        try{
            $result = erl_parse_term($test['source']);
        } catch (Exception $e) {
            $result = $e->GetMessage();
        }
        if($test['result'] !== $result ){
            echo "failed\n";
            print_r($result);
            print_r($test['result']);
        } else {
            echo "OK\n";
        }
    } else {
        echo "[error] undefined test type";
    }
}



