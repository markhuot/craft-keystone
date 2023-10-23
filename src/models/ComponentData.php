<?php

namespace markhuot\keystone\models;

use ArrayAccess;
use Closure;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use markhuot\keystone\base\FieldDefinition;
use markhuot\keystone\db\ActiveRecord;
use markhuot\keystone\db\Table;

use function markhuot\keystone\helpers\data\data_forget;

class ComponentData extends ActiveRecord implements ArrayAccess
{
    protected array $accessed = [];

    protected ?Closure $normalizer = null;

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

    public function getData()
    {
        if (empty($this->getAttribute('data'))) {
            return [];
        }

        $data = $this->getAttribute('data');

        if (is_string($data)) {
            return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        }

        return $data;
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
        $this->accessed[$offset] = (new FieldDefinition)->handle($offset);

        $value = $this->getData()[$offset] ?? null;

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

    public function forget(string $key): self
    {
        $data = $this->getAttribute('data') ?? [];
        $data = data_forget($data, $key);
        $this->setAttribute('data', $data);

        return $this;
    }

    public function duplicate()
    {
        $new = new static;
        $new->type = $this->type;
        $new->data = $this->data;
        $new->dateCreated = Db::prepareDateForDb(DateTimeHelper::now());
        $new->dateUpdated = Db::prepareDateForDb(DateTimeHelper::now());
        $new->uid = StringHelper::UUID();
        $new->save();

        return $new;
    }

    public function merge(array $new): self
    {
        $attributes = collect($new['_attributes'] ?? [])
            ->map(fn ($value, $className) => class_exists($className) ? (new $className)->serialize($value) : $value)
            ->filter();

        if ($attributes->isNotEmpty()) {
            $new['_attributes'] = $attributes;
        }

        $old = $this->getData();
        //$new = collect(array_merge($old, $new))->filterRecursive()->toArray();
        $new = array_replace_recursive($old, $new);
        $this->setAttribute('data', $new);

        return $this;
    }
}
