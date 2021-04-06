<?php
namespace App\Repositories;

use App\Entities\Company;
use App\Validators\CompanyValidator;
use DB;

class CompanyRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name' => 'like',
        'kana_name' => 'like',
    ];

    public function validator()
    {
        return CompanyValidator::class;
    }

    public function model()
    {
        return Company::class;
    }

    public function delete($company)
    {
        DB::beginTransaction();
        try {
            $company->delete();
            $company->brands()->each(function ($brand) {
                $brand->delete();
                $brand->templates()->detach();
            });
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
