<?php


class message_helper_test extends TestCase{

    public function test_MarkLineWithDeprecatedMethod(){
        $expected = '<span style="color:red;">//Method is deprecated</span>';
        $this->assertEquals($expected, Messages::MarkLineWithDeprecatedMethod());
    }

    public function test_MarkLineWithStatic()
    {
        $expected = '<span style="color:red;">//Static</span>';
        $this->assertEquals($expected, Messages::MarkLineWithStatic());
    }

    public function test_MarkLineWithGlobals()
    {
        $expected = '<span style="color:red;">//Global</span>';
        $this->assertEquals($expected, Messages::MarkLineWithGlobals());
    }
}