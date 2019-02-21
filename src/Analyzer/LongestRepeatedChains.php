<?php

namespace MyApp\Analyzer;

class LongestRepeatedChains
{
	//Implementation of suffix tree - code repetition detection
	public function findAll(array $tokens, $threshold)
	{
		if (empty($tokens)) {
			return [];
		}

		$repeatedTokens = [];
		$unifiedTokens = $this->getTokensWithoutWhiteSpacesAndComments($tokens);
		$sortedSignificantTokens = $this->getSuffixTree($unifiedTokens);
		$securityCountdown = 9999;
		do {
			$result = $this->getLongestRepeatedChain($sortedSignificantTokens, $threshold);
			if (empty($result)) {
				continue;
			}

			if (count($result) > $threshold) {
				$repeatedTokens[] = $result;
			}
			//find the first and the last token
			$lowest = null;
			$highest = null;
			foreach($result as $token) {
				if (is_null($lowest) || $token->tokenHash < $lowest) {
					$lowest = $token->tokenHash;
				}
				if (is_null($highest) || $token->tokenHash > $highest) {
					$highest = $token->tokenHash;
				}
			}

			//repetition is removed from significant tokens
			foreach($unifiedTokens as $key => $token) {
				if ($token->tokenHash >= $lowest && $token->tokenHash <= $highest) {
					unset($unifiedTokens[$key]);
				}
			}
			$sortedSignificantTokens = $this->getSuffixTree($unifiedTokens);
		} while (!empty($result) || $securityCountdown-- < 0);
		return $repeatedTokens;
	}

	/**
	 * @param token[] $tokens
	 * @param $threshold
	 * @return token[]
	 */
	private function getLongestRepeatedChain(array $tokens, $threshold)
	{
		$count = count($tokens);

		$result = [];
		for ($i = 0; $i < $count - 1; $i++) {
			$longestCommonChain = $this->longestCommonChain($tokens[$i], $tokens[$i + 1]);
			if (!empty($longestCommonChain) && count($longestCommonChain) > $threshold && count($result) < count($longestCommonChain)) {
				$result = $longestCommonChain;
			}
		}

		return $result;
	}

	/**
	 * @param token[] $tokens
	 * @return array of token[]
	 */
	private function getSuffixTree(array $tokens)
	{
		$count = count($tokens);
		$subList = [];
		$strIdxMap = [];
		for ($i = 0; $i < $count; $i++) {
			$subList[$i] = array_slice($tokens, $i);
			$strIdxMap[$i] = $this->convertTokensToString($subList[$i]);
		}
		asort($strIdxMap);

		$orderedSubList = [];
		foreach (array_keys($strIdxMap) as $idx) {
			$orderedSubList[] = $subList[$idx];
		}
		return $orderedSubList;
	}

	/**
	 * @param token[] $tokens
	 * @return string
	 */
	private function convertTokensToString(array $tokens)
	{
		$str = '';
		foreach ($tokens as $token) {
			$str .= $token->content;
		}
		return $str;
	}

	/**
	 * @param token[] $a
	 * @param token[] $b
	 * @return token[]
	 */
	private static function longestCommonChain(array $a, array $b)
	{
		$count = min(count($a), count($b));
		$result = [];

		for ($i = 0; $i < $count; $i++) {
			if ($a[$i]->content == $b[$i]->content)
				$result[] = $a[$i];
			else
				break;
		}

		return $result;
	}

	/**
	 * @param token[] $tokens
	 * @return token[]
	 */
	private static function getTokensWithoutWhiteSpacesAndComments(array $tokens)
	{
		$result = [];
		foreach ($tokens as $token) {
			if (in_array($token->tokenIdentifier, [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT])) {
				continue;
			}
			$token = clone $token;
			$token->content = str_replace("\n", '', $token->content);
			$result[] = $token;
		}
		return $result;
	}
}
