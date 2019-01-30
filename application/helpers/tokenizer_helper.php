<?php

class tokenizer {
	/** @var token[] */
	private $tokens;

	public function __construct($fileContents) {
		$unifiedContents = str_replace("\r", '', $fileContents);
		$this->parse($unifiedContents);
	}

	public function getAll() {
		return $this->tokens;
	}

	private function parse($fileContents) {
		$additional = [
			';' => token::SEMICOLON,
			'"' => token::DOUBLE_QUOTE,
			'[' => token::SQUARE_BRACKET_OPEN,
			']' => token::SQUARE_BRACKET_CLOSE,
			'{' => token::CURLY_BRACKET_OPEN,
			'}' => token::CURLY_BRACKET_CLOSE,
			'(' => token::BRACKET_OPEN,
			')' => token::BRACKET_CLOSE,
			'=' => token::ASSIGNMENT,
			'!' => token::EXCLAMATION_MARK,
			'.' => token::DOT,
			',' => token::COMMA,
			'?' => token::QUESTION_MARK,
			':' => token::COLON,
		];
		$tokens = token_get_all($fileContents);
		$lastLine = 1;

		foreach ($tokens as $idx => $token) {
			if (is_array($token)) {
				$this->tokens[] = new token($idx, $token[0], $token[1], $token[2], token_name($token[0]), null, null, null);
				$lastLine = $token[2];
			} elseif (isset($additional[$token])) {
				if (strpos($token, "\n")) {
					print_r($token);
					exit;
				}
				$this->tokens[] = new token($idx, $additional[$token], $token, $lastLine, $additional[$token], null, null, null);
			} else {
				print_r($token);
				exit;
			}
		}
	}

	public function getTokenMessages($tokens) {
		//@TODO przerobic na fora
		foreach ($tokens as $key => $token) {
			$tokens[$key]->tokenMessage = tokenAnalyser::containsStatics($token);
			$tokens[$key]->tokenMessage .= tokenAnalyser::containsDeprecated($token);
			$tokens[$key]->tokenMessage .= tokenAnalyser::containsGlobal($token);
			$tokens[$key]->tokenMessage .= tokenAnalyser::containsUnusedVariables($key, $token, $tokens);
			$tokens[$key]->tokenMessage .= tokenAnalyser::checkIfNamingConventionFollowed($token);
		}

		//@TODO check what is appropriate length of a structure
		$tokens = structureAnalyser::isTooLongStructure($tokens, 'T_FUNCTION', rules_helper::FUNCTION_LENGTH);
		$tokens = structureAnalyser::isTooLongStructure($tokens, 'T_CLASS', rules_helper::CLASS_LENGTH);
		$tokens = structureAnalyser::areLinesTooLong($tokens, rules_helper::LINE_LENGTH);
		$tokens = structureAnalyser::findUnusedMethods($tokens);
		//@TODO check correct number of params
		$tokens = structureAnalyser::hasFunctionTooManyParameters($tokens);
		$tokens = structureAnalyser::longestRepeatedTokenChain($tokens, rules_helper::REPEATED_STRING_THRESHOLD);
		$tokens = $this->analyzeLoops($tokens);
		return $tokens;
	}


	//@TODO
	private function analyzeLoops($tokens) {
		return $tokens;
	}
}
