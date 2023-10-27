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

        throw_if(! is_array($this->data['_attributes']), '_attributes should always be an array of attributes => attribute values');

        return $this->data['_attributes'];
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

    public function get(mixed $offset): mixed
    {
        return $this->offsetGet($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if ($this->hasAttribute($offset)) {
            return $this->getAttribute($offset);
        }

        $this->accessed[$offset] = (new FieldDefinition)->handle($offset);

        $value = $this->getData()[$offset] ?? null;

        if ($this->normalizer) {
            return ($this->normalizer)($value, $offset);
        }

        return $value;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->merge([$offset => $value]);
    }

    public function offsetUnset(mixed $offset): void
    {
        $old = $this->data ?? [];
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
        $new = array_replace_recursive($old, $new);
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
