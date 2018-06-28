<?php

class Breathalyzer
{
	private $words = [], $vocabulary = [];

	public function __construct(string $words, string $vocabulary)
	{
		//Setting words as vocabulary
		$this->setWords($words);
		$this->setVocabulary($vocabulary);
	}

	/**
	 * Setting up a vocabulary and preparing it to use
	 * @param string $vocabulary
	 * @throws \Exception
	 */
	private function setVocabulary(string $vocabulary)
	{
		if (!$vocabulary) {
			throw new \Exception('Vocabulary string is empty!');
		}

		$vocabularyArray = explode("\n", $vocabulary);
		$vocabularySorted = [];
		//Sorting array by word length
		foreach ($vocabularyArray as $word) {
			//Continue if word is empty
			if(empty($word)) {
				continue;
			}
			//Check if we already have the same word
			if (isset($this->words[$word])) {
				//Unset, because distance will be 0
				unset($this->words[$word]);
			}
			$vocabularySorted[strlen($word)][] = $word;
		}
		$this->vocabulary = $vocabularySorted;
	}

	/**
	 * Setting up words and preparing it to use
	 * @param string $words
	 * @throws \Exception
	 */
	private function setWords(string $words)
	{
		if (!$words) {
			throw new \Exception('Words string is empty!');
		}
		//Taking only words from string
		$matches = [];
		preg_match_all('/\w+/', $words, $matches);
		$wordsArray = $matches[0];
		if(!$wordsArray) {
			throw new \Exception('No words in the string!');
		}

		$wordsUnique = [];
		//Making words unique
		foreach ($wordsArray as $word) {
			//Make word uppercase
			$word = strtoupper($word);
			if(isset($wordsUnique[$word])) {
				//Increment words count
				$wordsUnique[$word]++;
			} else {
				$wordsUnique[$word] = 1;
			}
		}

		//Setting words
		$this->words = $wordsUnique;
	}

	/**
	 * Get summary of minimal distance with vocabulary for all set words
	 * @return int
	 * @throws Exception
	 */
	public function getDistanceSummary(): int
	{
		//Checking class properties
		if (!$this->vocabulary || !is_array($this->vocabulary)) {
			throw new \Exception('No vocabulary set');
		}

		if (!$this->words || !is_array($this->words)) {
			throw new \Exception('No words set');
		}

		$summary = 0;
		//Go through words and calculating distance
		foreach ($this->words as $word => $count) {
			$distance = $this->getWordMinimalDistance($word, $count);
			$summary += $distance;
		}

		return $summary;

	}

	/**
	 * Get minimal distance for word
	 * @param string $word
	 * @param int    $count
	 *
	 * @return int
	 */
	private function getWordMinimalDistance(string $word, int $count): int
	{
		//Take word length as minimal
		$min = $length = strlen($word);

		//Going through steps
		for ($step = 0; $min > $step; $step++) {
			$distance = $this->searchInStep($word, $step, $length);
			if ($distance == 1) {
				return $count;
			} elseif ($distance < $min) {
				$min = $distance;
			}
		}

		return $min * $count;
	}

	/**
	 * Calculating minimal distance from zero step (when we take vocabulary part by length), and if step > 0, then from -step and +step
	 * @param string $word
	 * @param int    $step
	 * @param int    $length
	 *
	 * @return int
	 */
	private function searchInStep(string $word, int $step, int $length): int
	{
		//Take length as minimal
		$min = $length;

		//Take minimal distance from +step and -step length
		if (isset($this->vocabulary[$length+$step]) && $this->vocabulary[$length+$step]) {
			$distance = $this->searchInVocabularyPart($word, $length, $this->vocabulary[$length+$step]);
			if ($distance < $min) {
				$min = $distance;
			}
		}

		if ($step > 0) {
			if (isset($this->vocabulary[$length-$step]) && $this->vocabulary[$length-$step]) {
				$distance = $this->searchInVocabularyPart($word, $length, $this->vocabulary[$length-$step]);
				if ($distance < $min) {
					$min = $distance;
				}
			}
		}

		return $min;
	}

	/**
	 * Calculating minimal Levenstein distance for $word in vocabulary part array
	 *
	 * @param string $word // Word for what we find Levenstein distance
	 * @param int    $length //Current word length
	 * @param array  $vocabularyPart // Array of words for what we find Levenstein distance
	 *
	 * @return int
	 */
	private function searchInVocabularyPart(string $word, int $length, array $vocabularyPart): int
	{
		$min = $length;

		foreach ($vocabularyPart as $vocabularyWord) {
			//Calculating Levenshtein distance between two strings
			$distance = levenshtein($word, $vocabularyWord);
			//If distance == 1 then return, because it's our minimal distance
			if ($distance == 1) {
				return 1;
			} elseif ($distance < $min) {
				$min = $distance;
			}
		}

		return $min;
	}

}