<?php

namespace markhuot\keystone\models;

use ArrayAccess;
use Closure;
use markhuot\keystone\base\ComponentData as Data;
use markhuot\keystone\base\FieldDefinition;
use markhuot\keystone\db\ActiveRecord;
use markhuot\keystone\db\Table;
use Twig\Markup;

class ComponentData extends ActiveRecord implements ArrayAccess
{
    protected array $accessed = [];
    protected ?Closure $normalizer=null;

    public function safeAttributes()
    {
        return array_merge(parent::safeAttributes(), ['data']);
    }

    public function rules(): array
    {
        return [
            [['type'], 'required'],
        ];
    }

    public static function tableName()
    {
        return Table::COMPONENT_DATA;
    }

    public function setNormalizer(Closure $normalizer): self
    {
        $this->normalizer = $normalizer;

        return $this;
    }

    public function getAccessed()
    {
        return collect($this->accessed);
    }

    public function offsetExists(mixed $offset): bool
    {
        return true;
    }

    public function offsetGet(mixed $offset): mixed
    {
        $this->accessed[$offset] = new FieldDefinition;

        $value = $this->getAttribute('data')[$offset] ?? null;

        if ($this->normalizer) {
            return ($this->normalizer)($offset, $value);
        }

        return $value;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->merge([$offset => $value]);
    }

    public function offsetUnset(mixed $offset): void
    {
        $old = $this->getAttribute('data') ?? [];
        unset($old[$offset]);
        $this->setAttribute('data', $old);
    }

    public function merge(array $new): void
    {
        $old = $this->getAttribute('data') ?? [];
        $this->setAttribute('data', array_merge($old, $new));
    }
}
