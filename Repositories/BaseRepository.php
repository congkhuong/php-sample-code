<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository as BaseEloquentRepository;
use App\Entities\User;
use App\Supports\AWS\FileStorage;
use Illuminate\Filesystem\Filesystem;
use App\Entities\Traits\HasPermission;
use App\Exceptions\PVTInternalServerErrorException;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Facades\DataTables;

abstract class BaseRepository extends BaseEloquentRepository
{
    use HasPermission;

    protected $skipValidator = null;
    protected $enableRawSelect = false;

    public function getUser($hasOrFail = true)
    {
        $user = auth()->guard()->user();
        if ($hasOrFail && !$user instanceof User) {
            throw new PVTInternalServerErrorException();
        }

        return $user;
    }

    public function setEnableRawSelect($enableRawSelect)
    {
        $this->enableRawSelect = $enableRawSelect;

        return $this;
    }

    public function pushCriteria($criteria)
    {
        if (is_string($criteria)) {
            $criteria = app($criteria);
        }

        return parent::pushCriteria($criteria);
    }

    /**
     * @param $rule
     * @param array $attributes
     * @param null $id
     * @return $this
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function validate($rule, array $attributes, $id = null)
    {
        if (is_null($this->validator)) {
            $this->makeValidator();
        }
        if ($id) {
            $this->validator->setId($id);
        }
        $this->validator->with($attributes)
            ->passesOrFail($rule);

        return $this;
    }

    public function skipValidator()
    {
        $this->skipValidator = $this->validator;
        $this->validator = null;

        return $this;
    }

    public function resetValidator()
    {
        if (isset($this->skipValidator)) {
            $this->validator = $this->skipValidator;
            $this->skipValidator = null;
        }

        return $this;
    }

    /**
     * Generate data structure for DataTables Library
     * @return mixed
     * @throws \Exception
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function dataTables()
    {
        request()->offsetSet('length', request('limit', config('repository.pagination.limit')));
        request()->offsetSet('start', request('offset', 0));

        $this->applyCriteria();
        $this->applyScope();
        $model = $this->model;
        if (!$this->enableRawSelect) {
            $table = $model->getModel()->getTable();
            $model = $model->select("{$table}.*");
        }
        if (!$model instanceof Builder) {
            $model = $model->query();
        }

        $data = DataTables::of($model)
            ->setPresenter($this->presenter)
            ->make(true);

        $this->resetModel();
        $this->resetScope();

        return $data;
    }
}
