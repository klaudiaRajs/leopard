<?php

class tokenAnalyser {

	public static function containsStatics($token) {
		if ($token->tokenIdentifier == T_STATIC) {
			return rules_helper::STATIC_WARNING;
		}
		return null;
	}

	public static function containsDeprecated($token) {
		$message = null;
		if ($token->tokenIdentifier == T_STRING) {
			foreach (rules_helper::deprecated() as $function => $solution) {
				if ($token->content == $function) {
					if ($solution) {
						$message = rules_helper::METHOD_DEPRECATED_WITH_SUGGEST_WARNING . $solution;
					} else {
						$message = rules_helper::METHOD_DEPRECATED_WARNING;
					}
				}
			}
		}
		return $message;
	}

	public static function containsGlobal($token) {
		if ($token->tokenIdentifier == T_VARIABLE && in_array($token->content, rules_helper::globals())) {
			return rules_helper::GLOBALS_WARNING;
		}
		return null;
	}

	public static function containsUnusedVariables($key, $token_, $tokens) {
		$message = null;
		if ($token_->tokenIdentifier == T_VARIABLE && in_array($token_->content, rules_helper::reservedVariableNames())) {
			return $message;
		}
		if ($token_->tokenIdentifier == T_VARIABLE && self::containsGlobal($token_) == null) {
			$variable = $token_->content;
			foreach ($tokens as $tokenKey => $token) {
				$message = rules_helper::UNUSED_VARIABLE_WARNING;
				if ($token_->tokenIdentifier == T_VARIABLE && $token->content == $variable && $tokenKey !== $key) {
					$message = null;
					break;
				}
			}
		}
		return $message;
	}

	public static function checkIfNamingConventionFollowed($token) {
		if (self::isNative($token) || self::containsGlobal($token) || $token->tokenIdentifier == T_CONSTANT_ENCAPSED_STRING || $token->tokenIdentifier == T_DOC_COMMENT || $token->tokenIdentifier == T_COMMENT) {
			return null;
		}
		if (rules_helper::nameConvention() == 'camelCase') {
			if (self::checkIfCamelCaseConventionFollowed($token)) {
				return null;
			}
			return rules_helper::CAMEL_CASE_WARNING;
		}
		if (rules_helper::nameConvention() == 'Pascal') {
			if (self::checkIfPascalConventionFollowed($token)) {
				return null;
			}
			return rules_helper::PASCAL_CONVENTION_WARNING;
		}

		if (rules_helper::nameConvention() == 'underscore') {
			if (self::checkIfUnderscoreConventionFollowed($token)) {
				return null;
			}
			return rules_helper::UNDERSCORE_CONVENTION_WARNING;
		}
	}

	public static function isNative($token) {
		if (in_array($token->content, rules_helper::keyNames())) {
			return true;
		}
		if (in_array($token->content, get_defined_functions()['internal'])) {
			return true;
		}
		if (array_key_exists($token->content, rules_helper::deprecated())) {
			return true;
		}
		return false;
	}

	private static function checkIfPascalConventionFollowed($token) {
		if (in_array($token->tokenName, rules_helper::TOKENS_CONTAINING_NAMING)) {
			if ($token->tokenIdentifier == T_VARIABLE) {
				$firstCharacter = mb_substr($token->content, 1, 1, "UTF-8");
				$isUpper = ctype_upper($firstCharacter);
			} else {
				$firstCharacter = mb_substr($token->content, 0, 1, "UTF-8");
				$isUpper = ctype_upper($firstCharacter);
			}
			return ((strpos($token->content, '_') === false) && $isUpper);
		}
		return true;
	}

	private static function checkIfUnderscoreConventionFollowed($token) {
		if (in_array($token->tokenName, rules_helper::TOKENS_CONTAINING_NAMING)) {
			$word = str_replace('$', '', $token->content);
			$parts = explode("_", $word);
			foreach ($parts as $part) {
				$stringArr = str_split($part);
				foreach ($stringArr as $char) {
					if (ctype_alpha($char) && !ctype_lower($char)) {
						return false;
					}
				}
			}
		}
		return true;
	}

	private static function checkIfCamelCaseConventionFollowed($token) {
		if (in_array($token->tokenName, rules_helper::TOKENS_CONTAINING_NAMING)) {
			$word = str_replace('$', '', $token->content);
			if (strpos($token->content, '_') !== false) {
				return false;
			}
			$firstCharacter = mb_substr($word, 0, 1, "UTF-8");

			if (!ctype_lower($firstCharacter)) {
				return false;
			}
		}
		return true;
	}
}
