<?php

class FileAnalyzer extends CI_Controller{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
    }

    function analyzeUpload($fileName)
    {
        $fileContents = file_get_contents(FCPATH . "\uploads\\" . $fileName);
        $tokenizer = new tokenizer($fileContents);

        $tokens = $tokenizer->getAll();

        $deprecatedFunctions = [
            'call_user_method' => 'call_user_func()',
            'call_user_method_array' => 'call_user_func_array()',
            'define_syslog_variables' => null,
            'dl' => null,
            'ereg' => 'preg_match()',
            'ereg_replace' => 'preg_replace()',
            'eregi' => 'preg\_match with the \'i\' modifier',
            'eregi_replace' => 'preg\_replace with the \'i\' modifier',
            'mcrypt_generic_end' => null,
            'set_magic_quotes_runtime' => null,
            'magic_quotes_runtime' => null,
            'session_register' => '$\_SESSION',
            'session_unregister' => '$\_SESSION',
            'session_is_registered' => '$\_SESSION',
            'set_socket_blocking' => 'stream\_set\_blocking',
            'split' => 'preg\_split',
            'spliti' => 'preg\_split with the \'i\' modifier',
            'sql_regcase'=> null,
            'mysql_db_query' => 'mysql\_select\_db and mysql\_query',
            'mysql_escape_string' => 'mysql\_real\_escape\_string',
            'mysql_list_dbs' => null,
            'datefmt_set_timezone_id' => 'datefmt\_set\_timezone',
            'mcrypt_cbc' => null,
            'mcrypt_cfb' => null,
            'mcrypt_ecb' => null,
            'mcrypt_ofb' => null,
            'ldap_sort' => null,
        ];
        $globals = ['$_SESSION', '$_POST', '$_GET', '$_FILES', '$_SERVER', '$_COOKIE', '$_ENV', '$_REQUEST', '$GLOBALS'];


        foreach($tokens as $key => $token){
            if( $token->tokenName == 'T_STATIC' ){
                $tokens[$key]->tokenMessage = "Shouldn't use statics if it's not absolutely necessary";
            }
            if( $token->tokenName == 'T_VARIABLE' && in_array($token->content, $globals)){
                $tokens[$key]->tokenMessage = "Shouldn't use global variables";
            }
            if( $token->tokenName == 'T_STRING' ){
                foreach( $deprecatedFunctions as $function => $solution ){
                     if( $token->content == $function){
                        if( $solution ) {
                            $tokens[$key]->tokenMessage = "This method is deprecated. Suggested: " . $solution;
                        }else{
                            $tokens[$key]->tokenMessage = "This method is deprecated.";
                        }
                    }
                }
            }
        }

        $formattedContents = TokenPresenter::getFormattedContents($tokens);
        $this->load->view('codePresenter', ['fileContents' => $formattedContents]);
    }
}



