<?php

namespace App\Transformers;

use App\Entities\Model;
use App\Exceptions\PVTFieldException;
use App\Exceptions\PVTInternalServerErrorException;
use App\Exceptions\PVTInvalidParameterException;
use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

abstract class BaseTransformer extends TransformerAbstract
{
    protected $level;
    protected $data = [];

    const MAXIMUM_RECURSIVE = 3;

    public function __construct(array $data, $level)
    {
        if (!is_int($level) || $level < 0) {
            throw new PVTInternalServerErrorException();
        }

        $this->level = $level;
        $this->data = array_merge($this->data, $data);
        if (isset($this->data['fields'])) {
            $fields = $this->fields();
            foreach ($this->data['fields'] as $field => $values) {
                if (!array_key_exists($field, $fields)) {
                    throw new PVTInvalidParameterException([
                        new PVTFieldException('fields', PVTFieldException::ERROR_ID_FIELD_INVALID)
                    ]);
                }
            }
        }
    }

    public function isPresentField($field, $isDefault = true)
    {
        if ($this->isMaximumRecursive()) {
            return false;
        }

        if (empty($this->data['fields'])) {
            return $isDefault;
        }

        return array_key_exists($field, $this->data['fields']);
    }

    public function isMaximumRecursive()
    {
        return $this->level > static::MAXIMUM_RECURSIVE;
    }

    /**
     * @param $object
     * @param bool $force : ignore null value
     * @param bool $fieldExists : ignore field not exists
     *
     * @return array
     */
    public function makeData($object, $force = false, $fieldExists = false)
    {
        $object = arrayToObject($object);
        $data = [];
        if ($fieldExists && $object instanceof Model) {
            $object = arrayToObject($object->attributesToArray());
        }
        foreach ($this->fields() as $field => $type) {
            if (!$this->isPresentField($field)) {
                continue;
            }
            if ($fieldExists && !property_exists($object, $field)) {
                continue;
            }
            if ($force && is_null($object->{$field})) {
                $data[$field] = null;
                continue;
            }
            switch ($type) {
                case 'ignore':
                    continue;
                case 'array':
                    $data[$field] = (array)$object->{$field};
                    break;
                case 'string':
                    $data[$field] = strval($object->{$field});
                    break;
                case 'integer':
                    $data[$field] = intval($object->{$field});
                    break;
                case 'float':
                    $data[$field] = floatval($object->{$field});
                    break;
                case 'double':
                    $data[$field] = doubleval($object->{$field});
                    break;
                case 'boolean':
                    $data[$field] = (bool)$object->{$field};
                    break;
                case 'timestamp':
                    $data[$field] = $this->formatDate($object->{$field}, 'timestamp');
                    break;
                default:
                    throw new PVTInternalServerErrorException();
            }
        }
        return $data;
    }

    public function formatDate($date, $format)
    {
        if (is_null($date)) {
            return null;
        }
        if (!($date instanceof Carbon)) {
            $date = new Carbon($date);
        }
        if ($format == 'timestamp') {
            return $date->toIso8601String();
        }
        return $date->format($format);
    }

    public function getData($field = null)
    {
        if (is_null($field)) {
            return $this->data;
        }
        if (!$this->isPresentField($field, false) || !is_array($this->data['fields'][$field])) {
            return [];
        }
        return $this->data['fields'][$field];
    }

    public function makeTransformer($field, $transformer, array $data = [])
    {
        if ($this->isMaximumRecursive()) {
            throw new PVTInternalServerErrorException();
        }
        if (!class_exists($transformer)) {
            throw new PVTInternalServerErrorException();
        }
        $data += $this->getData($field);
        $instance = new $transformer($data, $this->level + 1);
        if (!($instance instanceof TransformerAbstract)) {
            throw new PVTInternalServerErrorException();
        }
        return $instance;
    }

    abstract public function fields();
}
