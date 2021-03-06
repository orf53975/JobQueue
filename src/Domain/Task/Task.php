<?php

namespace JobQueue\Domain\Task;

use JobQueue\Domain\Job\ExecutableJob;

final class Task implements \Serializable, \JsonSerializable
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
     * @var ExecutableJob
     */
    private $job;

    /**
     *
     * @var int
     */
    private $createdAt;

    /**

     * @var ParameterBag
     */
    private $parameters;

    /**

     * @var TagBag
     */
    private $tags;

    /**
     *
     * @param Profile       $profile
     * @param ExecutableJob $job
     * @param array         $parameters
     * @param array         $tags
     */
    public function __construct(Profile $profile, ExecutableJob $job, array $parameters = [], array $tags = [])
    {
        $this->identifier = new Identifier;
        $this->status = new Status(Status::WAITING);
        $this->profile = $profile;
        $this->job = $job;
        $this->createdAt = time();
        $this->parameters = new ParameterBag($parameters);
        $this->tags = new TagBag($tags);
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
    public function updateStatus(Status $status)
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
        return $this->job;
    }

    /**
     *
     * @param bool $humanReadable
     * @return string
     */
    public function getJobName(bool $humanReadable = false): string
    {
        $jobName = get_class($this->job);

        if ($humanReadable) {
            $name = explode('\\', $jobName);
            $name = array_pop($name);

            // Convert CamelCase to snake_case
            preg_match_all('/([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)/', $name, $matches);
            foreach ($matches[0] as &$match) {
                $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
            }

            if ('job' !== $lmatch = array_pop($matches[0])) {
                $matches[0] = $lmatch;
            }

            return implode('_', $matches[0]);
        }

        return $jobName;
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
     * @param string $name
     * @return bool
     */
    public function hasParameter(string $name): bool
    {
        return $this->parameters->has($name);
    }

    /**
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters->__toArray();
    }

    /**
     *
     * @param string $name
     * @return mixed
     */
    public function getParameter(string $name)
    {
        return $this->parameters->get($name);
    }

    /**
     *
     * @param string $name
     * @return bool
     */
    public function hasTag(string $name): bool
    {
        return $this->tags->has($name);
    }

    /**
     *
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags->__toArray();
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
            get_class($this->job),
            $this->createdAt,
            $this->parameters->__toArray(),
            $this->tags->__toArray(),
        ]);
    }

    /**
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $array = unserialize($serialized);

        $this->identifier = new Identifier($array[0]);
        $this->status = new Status($array[1]);
        $this->profile = new Profile($array[2]);
        $this->job = new $array[3];
        $this->createdAt = $array[4];
        $this->parameters = new ParameterBag($array[5]);
        $this->tags = new TagBag($array[6]);
    }

    /**
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'identifier' => (string) $this->identifier,
            'status'     => (string) $this->status,
            'profile'    => (string) $this->profile,
            'job'        => get_class($this->job),
            'date'       => $this->getCreatedAt('r'),
            'parameters' => $this->parameters->__toArray(),
            'tags'       => $this->tags->__toArray(),
        ];
    }

    /**
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->identifier;
    }

    /**
     * Try to free memory
     *
     */
    public function __destruct()
    {
        unset(
            $this->identifier,
            $this->status,
            $this->profile,
            $this->job,
            $this->createdAt,
            $this->parameters,
            $this->tags
        );
    }
}
