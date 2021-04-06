<?php

namespace App\Presenters;

use App\Transformers\CompanyTransformer;

class CompanyPresenter extends BasePresenter
{
    public function getTransformer()
    {
        return new CompanyTransformer($this->data, 0);
    }
}
