<?php

class helpers_tests extends CI_TestCase{

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        $this->load->library('unit_test');
        parent::__construct($name, $data, $dataName);
    }

    public function messageHelperTest(){
        $test = 1 + 1;

        $expected_result = 2;

        $test_name = 'Adds one plus one';

        echo $this->unit->run($test, $expected_result, $test_name);
    }
}