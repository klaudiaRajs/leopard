<?php

namespace MyApp\Analyzer;

use MyApp\Statistics\StatKeeper;

class Tokenizer {
	/** @var token[] */
	private $tokens;
	private $statKeeper;
	private $fileName;

	public function __construct($fileContents, $fileName, StatKeeper $statKeeper) {
		$unifiedContents = str_replace("\r", '', $fileContents);
		$this->statKeeper = $statKeeper;
		$this->fileName = $fileName;
		$this->parse($unifiedContents);
	}

	public function getAll() {
		return $this->tokens;
	}

	private function parse($fileContents) {
		$additional = [
			';' => Token::SEMICOLON,
			'"' => Token::DOUBLE_QUOTE,
			'[' => Token::SQUARE_BRACKET_OPEN,
			']' => Token::SQUARE_BRACKET_CLOSE,
			'{' => Token::CURLY_BRACKET_OPEN,
			'}' => Token::CURLY_BRACKET_CLOSE,
			'(' => Token::BRACKET_OPEN,
			')' => Token::BRACKET_CLOSE,
			'=' => Token::ASSIGNMENT,
			'!' => Token::EXCLAMATION_MARK,
			'.' => Token::DOT,
			',' => Token::COMMA,
			'?' => Token::QUESTION_MARK,
			':' => Token::COLON,
            '<' => Token::SMALLER,
            '>' => Token::BIGGER,
            '/' => Token::DIVISION,
		];
		$tokens = token_get_all($fileContents);
		$lastLine = 1;

		foreach ($tokens as $idx => $token) {
			if (is_array($token)) {
				$this->tokens[] = new Token($idx, $token[0], $token[1], $token[2], token_name($token[0]), null, null, null, null, null, null );
				$lastLine = $token[2];
			} elseif (isset($additional[$token])) {
				if (strpos($token, "\n")) {
					print_r($token);
					exit;
				}
				$this->tokens[] = new Token($idx, $additional[$token], $token, $lastLine, $additional[$token], null, null, null, null, null, null );
			} else {
				print_r($token);
				exit;
			}
		}
	}

	public function getTokenMessages($tokens, $introducedProblems) {

	    $tokenAnalyzer = new TokenAnalyser($this->statKeeper, $this->fileName, $introducedProblems);
	    $structureAnalyzer = new StructureAnalyser($this->statKeeper, $this->fileName, $introducedProblems);

        $tokens = $structureAnalyzer->isTooLongStructure($tokens, 'T_FOREACH', 50);
        $tokens = $structureAnalyzer->isTooLongStructure($tokens, 'T_FOR', 50);

		for ($i = 0; $i < count($tokens); $i++){
			$tokens[$i]->tokenMessage .= $tokenAnalyzer->containsStatics($tokens[$i]);
			$tokens[$i]->tokenMessage .= $tokenAnalyzer->containsDeprecated($tokens[$i]);
			$tokens[$i]->tokenMessage .= $tokenAnalyzer->containsGlobal($tokens[$i]);
			$tokens[$i]->tokenMessage .= $tokenAnalyzer->containsUnusedVariables($i, $tokens[$i], $tokens);
			$tokens[$i]->tokenMessage .= $tokenAnalyzer->checkIfNamingConventionFollowed($tokens[$i], $tokens, $i);
            $tokens[$i]->tokenMessage .= $tokenAnalyzer->checkIfNotSingleLetterVariable($tokens[$i]);
		}

        $tokens = $structureAnalyzer->isTooLongStructure($tokens, 'T_FUNCTION', Rules::FUNCTION_LENGTH);
        $tokens = $structureAnalyzer->isTooLongStructure($tokens, 'T_CLASS', Rules::CLASS_LENGTH);
        $tokens = $structureAnalyzer->areLinesTooLong($tokens, Rules::LINE_LENGTH);
        $tokens = $structureAnalyzer->longestRepeatedTokenChain($tokens, Rules::REPEATED_STRING_THRESHOLD);
        //@TODO check correct number of params
        $tokens = $structureAnalyzer->hasFunctionTooManyParameters($tokens);
        $tokens = $structureAnalyzer->findUnusedMethods($tokens);
		//@TODO check what is appropriate length of a structure

		return $tokens;
	}
}
