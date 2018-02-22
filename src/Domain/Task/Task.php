<?php

namespace JobQueue\Domain\Task;

use JobQueue\Domain\Job\ExecutableJob;

final class Task implements \Serializable
{
    /**
     *
     * @var string
     */
    private $identifier;

    /**
     *
     * @var Status
     */
    private $status;

    /**
     *
     * @var Profile
     */
    private $profile;

    /**
     *
     * @var string
     */
    private $jobName;

    /**
     *
     * @var int
     */
    private $createdAt;

    /**
     *
     * @var array
     */
    private $parameters;

    /**
     *
     * @param Profile       $profile
     * @param ExecutableJob $job
     * @param array         $parameters
     */
    public function __construct(Profile $profile, ExecutableJob $job, array $parameters = [])
    {
        $this->identifier = new Identifier;
        $this->status = new Status(Status::WAITING);
        $this->profile = $profile;
        $this->jobName = get_class($job);
        $this->createdAt = time();
        $this->parameters = $parameters;
    }

    /**
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     *
     * @return Status
     */
    public function getStatus(): Status
    {
        return $this->status;
    }

    /**
     *
     * @param Status $status
     */
    public function updateStatus(Status $status): void
    {
        $this->status = $status;
    }

    /**
     *
     * @return Profile
     */
    public function getProfile(): Profile
    {
        return $this->profile;
    }

    /**
     *
     * @return ExecutableJob
     */
    public function getJob(): ExecutableJob
    {
        return new $this->jobName;
    }

    /**
     *
     * @return string
     */
    public function getJobName(bool $humanReadable = false): string
    {
        if ($humanReadable) {
            $name = explode('\\', $this->jobName);
            $name = array_pop($name);

            // Convert CamelCase to snake_case
            preg_match_all('/([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)/', $name, $matches);
            foreach ($matches[0] as &$match) {
                $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
            }

            return implode('_', $matches[0]);
        }

        return $this->jobName;
    }

    /**
     *
     * @param string|null $format
     * @return mixed
     */
    public function getCreatedAt(string $format = null)
    {
        return $format
            ? date($format, $this->createdAt)
            : $this->createdAt;
    }

    /**
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     *
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function getParameter(string $name, $default = null)
    {
        return isset($this->parameters[$name])
            ? $this->parameters[$name]
            : $default;
    }

    /**
     *
     * @return string
     */
    public function serialize(): string
    {
        return serialize([
            (string) $this->identifier,
            (string) $this->status,
            (string) $this->profile,
            $this->jobName,
            $this->createdAt,
            $this->parameters,
        ]);
    }

    /**
     *
     * @param string $serialized
     */
    public function unserialize($serialized): void
    {
        $array = unserialize($serialized);

        $this->identifier = new Identifier($array[0]);
        $this->status = new Status($array[1]);
        $this->profile = new Profile($array[2]);
        $this->jobName = $array[3];
        $this->createdAt = $array[4];
        $this->parameters = $array[5];
    }

    /**
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->identifier;
    }
}
