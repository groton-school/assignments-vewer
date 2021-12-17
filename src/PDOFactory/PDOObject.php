<?php

namespace GrotonSchool\PDOFactory;

use Exception;

/**
 * @method string|integer getId()
 */
abstract class PDOObject
{
    protected static $immutable = ['id'];

    /** @var string|integer */
    public $id;

    /** @var PDOFactory */
    protected $factory;

    private function __construct(PDOFactory $factory)
    {
        $this->factory = $factory;
    }

    public function update(array $data): PDOObject
    {
        return $this->factory->update($this, array_diff_key($data, array_flip(static::$immutable)));
    }

    public function delete()
    {
        return $this->factory->delete($this);
    }

    public function __call($name, $arguments): mixed
    {
        $accessor = preg_replace('/^(set|get).*/', '$1', $name);
        $prop = ltrim(strtolower(preg_replace('/([A-Z](?:[a-z])*)/', '_$1', substr($name, 3))), '_');
        if (array_key_exists($prop, get_object_vars($this))) {
            switch ($accessor) {
                case 'set':
                    if (count($arguments) === 1 && !in_array($prop, static::$immutable)) {
                        return $this->update([$prop => $arguments[0]]);
                    }
                    break;
                case 'get':
                    return $this->$prop;
            }
        }
        throw new Exception("method `$name`/prop `$prop` not found");
    }
}
