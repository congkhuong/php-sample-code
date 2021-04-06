<?php
namespace App\Http\Controllers\Api\Admin;

use App\Criterias\PVTCriteria;
use App\Entities\Company;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\CompanyRepository;
use App\Presenters\CompanyPresenter;
use Prettus\Repository\Criteria\RequestCriteria;

class CompanyController extends Controller
{
    protected $companyRepo;

    public function __construct(CompanyRepository $companyRepo)
    {
        $this->companyRepo = $companyRepo;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = $this->companyRepo
            ->pushCriteria(RequestCriteria::class)
            ->pushCriteria(PVTCriteria::class)
            ->setPresenter(CompanyPresenter::class)
            ->datatables();
        return $this->respond($companies);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request Request Params for Create
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $company = $this->companyRepo->create($request->all());
        return $this->respond($company);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $company = $this->companyRepo
            ->setEnableRawSelect(true)
            ->pushCriteria(RequestCriteria::class)
            ->setPresenter(CompanyPresenter::class)
            ->find($id);
        return $this->respond($company['data']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request Request Parameters Update
     *
     * @param int $id Id Company
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->companyRepo->update($request->all(), $id);
        return $this->responseNoContent();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        $this->companyRepo->delete($company);
        return $this->responseNoContent();
    }
}
