<?php

namespace markhuot\keystone\models;

use ArrayAccess;
use Closure;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use Illuminate\Support\Collection;
use markhuot\keystone\base\Attribute;
use markhuot\keystone\base\FieldDefinition;
use markhuot\keystone\db\ActiveRecord;
use markhuot\keystone\db\Table;

use function markhuot\keystone\helpers\base\throw_if;
use function markhuot\keystone\helpers\data\data_forget;

/**
 * @property int $id
 * @property string $type
 * @property array<mixed> $data
 *
 * @implements ArrayAccess<array-key, mixed>
 */
class ComponentData extends ActiveRecord implements ArrayAccess
{
    /** @var array<FieldDefinition> */
    protected array $accessed = [];

    protected ?Closure $normalizer = null;

    /**
     * This is included to make PHPStan happy. You can't safely call `new static` if child
     * classes can override the constructor. So, we'll mark the constructor as final so
     * that child classes can not change the signature.
     *
     * @param  array<mixed>  $config
     */
    final public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function safeAttributes()
    {
        return array_merge(parent::safeAttributes(), ['data']);
    }

    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            [['type'], 'required'],
        ];
    }

    public static function tableName(): string
    {
        return Table::COMPONENT_DATA;
    }

    public function setNormalizer(Closure $normalizer): self
    {
        $this->normalizer = $normalizer;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getData(): array
    {
        if (empty($this->getAttribute('data'))) {
            return [];
        }

        /** @var string|array<mixed> $data */
        $data = $this->getAttribute('data');

        if (is_string($data)) {
            $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            throw_if(! is_array($data), 'Could not decode component data');
        }

        return $data;
    }

    /**
     * @return array<mixed>
     */
    public function getDataAttributes(): array
    {
        if (empty($this->getData()['_attributes'])) {
            return [];
        }

        throw_if(! is_array($this->getData()['_attributes']), '_attributes should always be an array of attributes => attribute values');

        return $this->getData()['_attributes'];
    }

    /**
     * @return Collection<array-key, FieldDefinition>
     */
    public function getAccessed(): Collection
    {
        return collect($this->accessed);
    }

    public function offsetExists(mixed $offset): bool
    {
        return true;
    }

    public function get(string $offset, mixed $default = null): mixed
    {
        if ($this->isRelationPopulated($offset)) {
            return $this->getRelatedRecords()[$offset];
        }

        $value = $this->getRaw($offset, $default);

        if ($this->normalizer) {
            return ($this->normalizer)($value, $offset);
        }

        return $value;
    }

    public function getRaw(string $offset, mixed $default = null): mixed
    {
        if ($this->hasAttribute($offset)) {
            return $this->getAttribute($offset);
        }

        $this->accessed[$offset] = (new FieldDefinition)->handle($offset);

        return $this->getData()[$offset] ?? $default;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->merge([$offset => $value]);
    }

    public function offsetUnset(mixed $offset): void
    {
        $old = $this->getData();
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

    public function duplicate(): self
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

    /**
     * @param  array<mixed>  $new
     */
    public function merge(array $new): self
    {
        $new = $this->serializeAttributes($new);

        $old = $this->getData();
        $oldAttributes = $old['_attributes'] ?? [];
        $newAttributes = $new['_attributes'] ?? [];

        throw_if(! is_array($oldAttributes), 'Old attributes must be an array to merge');
        throw_if(! is_array($newAttributes), 'new Attributes must be an array to merge');

        // Replace out attributes first
        $new['_attributes'] = array_replace($oldAttributes, $newAttributes);
        if (empty($new['_attributes'])) {
            unset($new['_attributes']);
        }

        // This used to be array_replace_recursive, which seems nice. Then we could pass
        // in a very sparse fieldset and retain existing data and only update what we
        // actually want to change. But a good chunk of Craft fields don't work that
        // way and actually want to pass in NULL to remove values. E.g. the Craft condition
        // builder removes array elements by unsetting them.
        //
        // Because of that we only merge the top level keys, anything deeper must be passed
        // in full if you want to retain existing data.
        $new = array_replace($old, $new);

        $this->setAttribute('data', $new);

        return $this;
    }

    /**
     * @param  array<mixed>  $new
     * @return array<mixed>
     */
    public function serializeAttributes(array $new): array
    {
        if (empty($new['_attributes'])) {
            return $new;
        }

        throw_if(! is_array($new['_attributes']), '_attributes must be an array');

        /** @var array<class-string<Attribute>, mixed> $attributes */
        $attributes = $new['_attributes'];

        $new['_attributes'] = collect($attributes)
            ->map(fn (mixed $value, string $className) => class_exists($className) ? (new $className)->serialize($value) : $value)
            ->toArray();

        return $new;
    }
}
