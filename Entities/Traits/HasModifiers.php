<?php

namespace App\Entities\Traits;

use App\Entities\User;

trait HasModifiers
{
    /**
     * The name of the "created by" column.
     *
     * @var string
     */
    protected $createdByColumn = 'created_by';

    /**
     * The name of the "updated by" column.
     *
     * @var string
     */
    protected $updatedByColumn = 'updated_by';

    /**
     * @var string
     */
    protected $userIdKey = 'id';

    /**
     * Indicates if the model should be modified.
     *
     * @var bool
     */
    protected $modifiers = true;

    /**
     * Relationship to user who created the record
     *
     * @return mixed
     */
    public function createdBy()
    {
        if (!$this->usesModifiers()) {
            return null;
        }

        return $this->belongsTo(User::class, $this->getCreatedByColumn(), $this->userIdKey);
    }

    /**
     * Relationship to user who updated the record
     *
     * @return mixed
     */
    public function updatedBy()
    {
        if (!$this->usesModifiers()) {
            return null;
        }

        return $this->belongsTo(User::class, $this->getUpdatedByColumn(), $this->userIdKey);
    }

    /**
     * Update the creation and update modifiers.
     *
     * @return bool
     */
    protected function updateModifiers()
    {
        $user = getLoginUser()->user();
        if (!$user instanceof User || !$this->usesModifiers()) {
            return false;
        }
        $userId = $user->{$user->getKeyName()};

        if (!$this->isDirty($this->getUpdatedByColumn())) {
            $this->setUpdatedBy($userId);
        }

        if (!$this->exists && !$this->isDirty($this->getCreatedByColumn())) {
            $this->setCreatedBy($userId);
        }
    }

    /**
     * Get the name of the "created by" column.
     *
     * @return string
     */
    public function getCreatedByColumn()
    {
        return $this->createdByColumn;
    }

    /**
     * Get the name of the "updated by" column.
     *
     * @return string
     */
    public function getUpdatedByColumn()
    {
        return $this->updatedByColumn;
    }

    /**
     * Set the value of the "created by" attribute.
     *
     * @param  mixed $value
     * @return $this
     */
    public function setCreatedBy($value)
    {
        $this->{$this->getCreatedByColumn()} = $value;

        return $this;
    }

    /**
     * Set the value of the "updated by" attribute.
     *
     * @param  mixed $value
     * @return $this
     */
    public function setUpdatedBy($value)
    {
        $this->{$this->getUpdatedByColumn()} = $value;

        return $this;
    }

    /**
     * Determine if the model uses modifiers.
     *
     * @return bool
     */
    public function usesModifiers()
    {
        return $this->modifiers;
    }
}
