<?php
/*
    @author Alexxz
    @project_url https://github.com/Alexxz/Erlang-term-php-parser

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
// includeing library
require_once('lib/erlang_term_parser.php');

// tests
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
        'source' => '[[],"","012", [ ] ]',
        'result' => array(
            'type' => 'list', 
            'data' => array(
                array('type' => 'list',  'data' => array()),
                array('type' => 'list',  'data' => array()),
                array('type' => 'list',  'data' => array(48,49,50)),
                array('type' => 'list',  'data' => array()),
                ),
            ),
        ),
    'tuples' => array(
        'type'   => 'simple',
        'source' => '{{},{1,2} , { }}',
        'result' => array(
            'type' => 'tuple', 
            'data' => array(
                array('type' => 'tuple',  'data' => array()),
                array('type' => 'tuple',  'data' => array(1,2)),
                array('type' => 'tuple',  'data' => array()),
                ),
            ),
        ),
    'spaces' => array(
        'type'   => 'simple',
        'source' => '{ {} , [ 1 , 2 ] }',
        'result' => array(
            'type' => 'tuple', 
            'data' => array(
                array('type' => 'tuple',  'data' => array()),
                array('type' => 'list',  'data' => array(1,2)),
                ),
            ),
        ),
    'exception tuple' => array('type'   => 'exception', 'source' => '{', 'result' => 'Error while parsing term',),
    'exception list 1'  => array('type'   => 'exception', 'source' => '[',  'result' => 'Error while parsing term',),
    'exception list 2'  => array('type'   => 'exception', 'source' => '[ ', 'result' => 'Error while parsing term',),
    'exception list tuple' => array('type'   => 'exception', 'source' => '[{', 'result' => 'Error while parsing term',),
    'exception tuple 1' => array('type'   => 'exception', 'source' => '{"','result' => 'Error while parsing quoted list',),
    'exception tuple 2' => array('type'   => 'exception', 'source' => '{""','result' => 'Error while parsing tuple',),
    'exception tuple 3' => array('type'   => 'exception', 'source' => '{"",','result' => 'Error while parsing term',),
    'list2string' => array(
        'type'   => 'list2string',
        'source' => '{[],"0","123"}',
        'result' => array(
            'type' => 'tuple',
            'data' => array( array('type' => 'list', 'data' => array()), '0', '123')
            ) 
        ),
    );


// make tests
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



