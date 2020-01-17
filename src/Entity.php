<?php

namespace FlexPHP\Entities;

/**
 * Class Entity
 * @package FlexPHP\Entities
 */
abstract class Entity implements EntityInterface
{
    /**
     * Save attribute names hydrate on instance
     *
     * @var array<string>
     */
    private $attributesHydrated = [];

    /**
     * Entity constructor.
     * @param array<string> $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->hydrate($attributes);
    }

    /**
     * @return array<string>
     */
    public function toArray(): array
    {
        $toArray = [];

        foreach ($this->attributesHydrated as $index => $attribute) {
            if (\property_exists($this, $attribute)) {
                $toArray[$this->camelCase($attribute)] = $this->{$attribute};
            }
        }

        return $toArray;
    }

    /**
     * @param  array<string> $attributes
     * @return $this
     */
    private function hydrate(array $attributes): self
    {
        foreach ($attributes as $attribute => $value) {
            $this->{$this->snakeCase($attribute)} = $value;
        }

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function __set($name, $value)
    {
        $this->{$name} = $value;

        $this->attributesHydrated[] = $name;

        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->{$name} ?? null;
    }

    /**
     * @param string $name
     * @param array<int> $arguments
     * @return $this|mixed
     */
    public function __call(string $name, array $arguments)
    {
        $attribute = $this->snakeCase($name);

        if (\count($arguments) > 0) {
            return $this->__set($attribute, $arguments[0]);
        }

        return $this->__get($attribute);
    }

    /**
     * Entity to json string
     *
     * @return string
     */
    public function __toString()
    {
        return (string)\json_encode($this->toArray(), JSON_ERROR_NONE);
    }

    /**
     * Change to snake_case attribute name
     *
     * @param string $attribute
     * @return string
     */
    private function snakeCase(string $attribute): string
    {
        return \mb_strtolower(\preg_replace('~(?<=\\w)([A-Z])~', '_$1', $attribute) ?? $attribute);
    }

    /**
     * Change to camelCase attribute name
     *
     * @param string $attribute
     * @return string
     */
    private function camelCase(string $attribute): string
    {
        $string = \preg_replace_callback('/_(.?)/', function ($matches) {
            return \ucfirst($matches[1]);
        }, $attribute);

        return $string ?? $attribute;
    }
}
