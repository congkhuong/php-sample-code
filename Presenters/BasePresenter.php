<?php

namespace App\Presenters;

use App\Exceptions\PVTFieldException;
use App\Exceptions\PVTInvalidParameterException;
use Prettus\Repository\Presenter\FractalPresenter;
use App\Supports\FieldsParser;

abstract class BasePresenter extends FractalPresenter
{

    protected $data = [];

    /**
     * BasePresenter constructor.
     * @param array $options
     * @throws PVTFieldException
     */
    public function __construct(array $options = [])
    {
        parent::__construct();
        $fields = !empty($options['fields']) ? $options['fields'] : request('fields');
        if ($fields) {
            $fieldsParser = new FieldsParser();
            try {
                $this->data = array_merge($this->data, $fieldsParser->parse($fields));
            } catch (\Exception $ex) {
                throw new PVTInvalidParameterException(
                    [new PVTFieldException('fields', $ex->getMessage())],
                    $ex
                );
            }
        }
    }

    public function columns()
    {
        return ['*'];
    }

    /**
     * @return $this
     */
    protected function parseIncludes()
    {
        $request = app('Illuminate\Http\Request');
        $paramIncludes = config('repository.fractal.params.include', 'include');
        if ($request->has($paramIncludes)) {
            $param = $request->get($paramIncludes);
            if (is_string($param)) {
                $param = str_replace(";", ",", $param);
            }
            $this->fractal->parseIncludes($param);
        }
        return $this;
    }
}
