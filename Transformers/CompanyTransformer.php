<?php

namespace App\Transformers;

use App\Entities\Company;

class CompanyTransformer extends BaseTransformer
{
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $availableIncludes = [
        'createdBy',
    ];

    public function transform(Company $company)
    {
        return $this->makeData($company);
    }

    public function fields()
    {
        return [
            'id' => 'integer',
            'id_no' => 'string',
            'name' => 'string',
            'kana_name' => 'string',
            'logo' => 'string',
            'memo' => 'string',
            'brands_count' => 'integer',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ];
    }

    /**
     * Include CreatedBy
     *
     * @return \League\Fractal\Resource\Item|void
     */
    public function includeCreatedBy(Company $company)
    {
        $user = $company->createdBy;
        if ($user) {
            $userTransformer = $this->makeTransformer('createdBy', UserTransformer::class);
            return $this->item($user, $userTransformer, 'include');
        }
    }
}
