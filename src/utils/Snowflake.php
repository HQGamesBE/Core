<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\utils;
/**
 * Class Snowflake
 * @package HQGames\utils
 * @author Jan Sohn / xxAROX
 * @date 23. July, 2022 - 20:01
 * @ide PhpStorm
 * @project Core
 */
class Snowflake{
	/**
	 * Function generate
	 * @param null|int $dataCenterId
	 * @param null|int $workerId
	 * @return string
	 */
	static function generate(?int $dataCenterId = null, ?int $workerId = null): string{
		return (new Snowflake($dataCenterId ?? ($pid = getmypid()) !== false ? $pid : 0, $workerId))->id();
	}

	private const MAX_TIMESTAMP_LENGTH  = 41;
	private const MAX_DATACENTER_LENGTH = 5;
	private const MAX_WORKID_LENGTH     = 5;
	private const MAX_SEQUENCE_LENGTH   = 12;
	private const MAX_FIRST_LENGTH      = 1;
	/**
	 * The data center id.
	 */
	protected ?int $datacenter = null;
	/**
	 * The worker id.
	 */
	protected ?int $workerid = null;
	/**
	 * The Sequence Resolver instance.
	 */
	protected ?SequenceResolver $sequence = null;
	/**
	 * The start timestamp.
	 */
	protected int $startTime = -1;
	/**
	 * Default sequence resolver.
	 */
	protected ?SequenceResolver $defaultSequenceResolver = null;

	/**
	 * Build Snowflake Instance.
	 *
	 * @param null|int $datacenter
	 * @param null|int $workerid
	 */
	public function __construct(int $datacenter = null, int $workerid = null){
		$maxDataCenter = -1 ^ (-1 << self::MAX_DATACENTER_LENGTH);
		$maxWorkId = -1 ^ (-1 << self::MAX_WORKID_LENGTH);
		// If not set datacenter or workid, we will set a default value to use.
		$this->datacenter = $datacenter > $maxDataCenter || $datacenter < 0 ? mt_rand(0, 31) : $datacenter;
		$this->workerid = $workerid > $maxWorkId || $workerid < 0 ? mt_rand(0, 31) : $workerid;
	}

	/**
	 * Get snowflake id.
	 * @return string
	 */
	public function id(): string{
		$currentTime = $this->getCurrentMicrotime();
		while (($sequence = $this->callResolver($currentTime)) > (-1 ^ (-1 << self::MAX_SEQUENCE_LENGTH))) {
			usleep(1);
			$currentTime = $this->getCurrentMicrotime();
		}
		$workerLeftMoveLength = self::MAX_SEQUENCE_LENGTH;
		$datacenterLeftMoveLength = self::MAX_WORKID_LENGTH + $workerLeftMoveLength;
		$timestampLeftMoveLength = self::MAX_DATACENTER_LENGTH + $datacenterLeftMoveLength;
		return (string)((($currentTime - $this->getStartTimeStamp()) << $timestampLeftMoveLength) | ($this->datacenter << $datacenterLeftMoveLength) | ($this->workerid << $workerLeftMoveLength) | ($sequence));
	}

	/**
	 * Parse snowflake id.
	 * @param string $id
	 * @param bool $transform
	 * @return array
	 */
	public function parseId(string $id, bool $transform = false): array{
		$id = decbin(intval($id));
		$data = [
			'timestamp'  => substr($id, 0, -22),
			'sequence'   => substr($id, -12),
			'workerid'   => substr($id, -17, 5),
			'datacenter' => substr($id, -22, 5),
		];
		return $transform ? array_map(function ($value){
			return bindec($value);
		}, $data) : $data;
	}

	/**
	 * Get current microtime timestamp.
	 *
	 * @return int
	 */
	public function getCurrentMicrotime(): int{
		return floor(microtime(true) * 1000) | 0;
	}

	/**
	 * Set start time (millisecond).
	 */
	public function setStartTimeStamp(int $startTime): static{
		$missTime = $this->getCurrentMicrotime() - $startTime;
		if ($missTime < 0) {
			throw new \Exception('The start time cannot be greater than the current time');
		}
		$maxTimeDiff = -1 ^ (-1 << self::MAX_TIMESTAMP_LENGTH);
		if ($missTime > $maxTimeDiff) {
			throw new \Exception(sprintf('The current microtime - starttime is not allowed to exceed -1 ^ (-1 << %d), You can reset the start time to fix this', self::MAX_TIMESTAMP_LENGTH));
		}
		$this->startTime = $startTime;
		return $this;
	}

	/**
	 * Get start timestamp (millisecond), If not set default to 2019-08-08 08:08:08.
	 *
	 * @return int
	 */
	public function getStartTimeStamp(): float|int{
		if ($this->startTime > 0) {
			return $this->startTime;
		}
		// We set a default start time if you not set.
		$defaultTime = '2021-01-01 00:00:01';
		return strtotime($defaultTime) * 1000;
	}

	/**
	 * Set Sequence Resolver.
	 *
	 * @param SequenceResolver|callable $sequence
	 * @return Snowflake
	 */
	public function setSequenceResolver($sequence): static{
		$this->sequence = $sequence;
		return $this;
	}

	/**
	 * Get Sequence Resolver.
	 *
	 * @return SequenceResolver|callable|null
	 */
	public function getSequenceResolver(): callable|SequenceResolver|null{
		return $this->sequence;
	}

	/**
	 * Get Default Sequence Resolver.
	 *
	 * @return SequenceResolver
	 */
	public function getDefaultSequenceResolver(): SequenceResolver{
		return $this->defaultSequenceResolver ?: $this->defaultSequenceResolver = new RandomSequenceResolver();
	}

	/**
	 */
	protected function callResolver($currentTime): int{
		/**
		 * @param callable|SequenceResolver $resolver
		 * @param int $maxSequence
		 */
		$resolver = $this->getSequenceResolver();
		if (is_callable($resolver)) {
			return $resolver($currentTime);
		}
		return !($resolver instanceof SequenceResolver) ? $this->getDefaultSequenceResolver()->sequence($currentTime) : $resolver->sequence($currentTime);
	}
}

/**
 * Interface SequenceResolver
 * @package xxAROX
 */
interface SequenceResolver{
	/**
	 * The snowflake.
	 *
	 * @param int|string $currentTime current request ms
	 *
	 * @return int
	 */
	public function sequence(int $currentTime);
}

/**
 * Class RandomSequenceResolver
 * @package xxAROX
 * @author xxAROX
 * @date 10.01.2021 - 18:07
 * @project Cloud
 */
class RandomSequenceResolver implements SequenceResolver{
	/**
	 * The las ttimestamp.
	 *
	 * @var null
	 */
	protected $lastTimeStamp = -1;
	/**
	 * The sequence.
	 *
	 * @var int
	 */
	protected $sequence = 0;

	/**
	 * Function sequence
	 * @param int $currentTime
	 * @return int
	 */
	public function sequence(int $currentTime): int{
		if ($this->lastTimeStamp === $currentTime) {
			++$this->sequence;
			$this->lastTimeStamp = $currentTime;
			return $this->sequence;
		}
		$this->sequence = 0;
		$this->lastTimeStamp = $currentTime;
		return 0;
	}
}
