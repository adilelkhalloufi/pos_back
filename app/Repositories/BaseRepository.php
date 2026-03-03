<?php

namespace App\Repositories;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of BaseModel
 */
abstract class BaseRepository
{
    private ?BaseModel $baseModel = null;

    /**
     * @param string $value
     * @param string $columnName
     *
     * @return TModel|null
     */
    public function find(string $value, string $columnName = 'id')
    {
        return $this->getQueryBuilder()
            ->where($columnName, $value)
            ->first();
    }

    /**
     * Set Connection adn create new Query
     * @return Builder
     */
    public function getQueryBuilder(): Builder
    {
        return $this->getModel()
            ->newQuery();
    }

    /**
     * @return BaseModel
     */
    private function getModel(): BaseModel
    {
        if ($this->baseModel instanceof BaseModel) {
            return $this->baseModel;
        }
        $this->baseModel = app($this->getModelClass());

        return $this->baseModel;
    }

    /**
     * @return string
     */
    abstract public function getModelClass(): string;

    /**
     * @param string $id
     * @param array $attributes
     *
     * @return bool
     */
    public function update(string $id, array $attributes): bool
    {
        return $this->getQueryBuilder()
                ->where($this->getModel()->getIdColumn(), $id)
                ->update($attributes) > 0;
    }

    /**
     * @param array $attributes
     *
     * @return TModel
     */
    public function create(array $attributes)
    {
        /** @var TModel */
        return $this->getQueryBuilder()
            ->create($attributes);
    }

    /**
     * @param array $attributes
     * @param array $values
     *
     * @return TModel
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        /** @var TModel */
        return $this->getQueryBuilder()
            ->updateOrCreate($attributes, $values);
    }

    /**
     * @param string $value
     * @param string $columnName
     *
     * @return bool
     */
    public function delete(string $value, string $columnName = 'id'): bool
    {
        return $this->getQueryBuilder()
                ->where($columnName, $value)
                ->delete() > 0;
    }

    /**
     * Used to control the select() from db
     *
     * @param string $tableName
     * @param array $selectedAttributes
     *
     * @return array
     */
    public function prefixTableName(string $tableName, array $selectedAttributes): array
    {
        return array_map(
            static fn($item) => $tableName . "." . $item,
            $selectedAttributes
        );
    }


    public function findbyfield(string $value, string $columnName)
    {
        return $this->getQueryBuilder()
            ->where($columnName, $value)
            ->get();
    }

        /**
     * Find a single model with relations.
     *
     * @param string $value
     * @param string $columnName
     * @param array|string|null $relations
     * @return BaseModel|null
     */
    public function findWith(string $value, string $columnName = 'id', array|string|null $relations = null)
    {
        $qb = $this->getQueryBuilder();
        if ($relations) {
            $qb = $qb->with($relations);
        }
        return $qb->where($columnName, $value)->first();
    }

 
}