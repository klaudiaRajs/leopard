<?php

namespace MyApp\Analyzer;

class Rules {
    private static $convention;

	const MAX_PARAMS = 3;
	const FUNCTION_LENGTH = 30;
	const CLASS_LENGTH = 250;
	const LINE_LENGTH = 150;
	const REPEATED_STRING_THRESHOLD = 15;
	const LOOP_LENGTH = 50;
	const SIMILARITY_THRESHOLD = 30;

	const METHOD_DEPRECATED_WITH_SUGGEST_WARNING = "This method is deprecated. Suggested: ";
	const METHOD_DEPRECATED_WARNING = "This method is deprecated. ";
	const GLOBALS_WARNING = "Shouldn't use global variables. ";
	const STATIC_WARNING = "Shouldn't use statics if it's not absolutely necessary. ";
	const UNUSED_VARIABLE_WARNING = "This is unused variables. ";
	const TOO_LENGTHY_STRUCTURE = "This structure is too long. ";
	const TOO_LONG_LINE = "This line is too long. ";
	const UNUSED_METHOD_WARNING = "This method seems not to be used. ";
	const REPEATED_CHUNK_OF_CODE_WARNING = "This is repeated chunk of code. Try to abstract it out to function. ";
	const TOO_MANY_PARAMS_WARNING = "This function has too many params. ";
	const CAMEL_CASE_WARNING = "You should use camelCase. ";
	const PASCAL_CONVENTION_WARNING = "You should use PascalCase convention. ";
	const UNDERSCORE_CONVENTION_WARNING = "You should use underscore convention. ";
	const SINGLE_LETTER_VARIABLE_WARNING = "Single letter variable names might mean unclear name, ensure the name conveys the intention. ";
    const CONST_NAMING_CONVENTION_WARNING = "It is recommended to call constants with all capital letters.";
    const SIMILAR_CHUNK_OF_CODE_WARNING = "Similarity with: ";
    const METHOD_NAMING_CONVENTION = "Method should follow PSR-1, which for method names uses camelCase. ";

	const TOKENS_CONTAINING_NAMING = [
		'T_STRING', 'T_VARIABLE'
	];

	public static function deprecated()
    {
        return [
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
            'sql_regcase' => null,
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
    }

    public static function globals(){
        return ['$_SESSION', '$_POST', '$_GET', '$_FILES', '$_SERVER', '$_COOKIE', '$_ENV', '$_REQUEST', '$GLOBALS'];
    }

    public static function setNamingConvention($naming){
        self::$convention = $naming;
    }

    public static function nameConvention(){
	    return self::$convention;
	}

	public static function keyNames(){
    	return [
    		'__construct', 'self', 'false', 'true', 'null', '$this'
		];
	}

	public static function reservedVariableNames(){
    	return [
    		'$this'
		];
	}
}
