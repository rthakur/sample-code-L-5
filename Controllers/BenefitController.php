<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use Input;
use Lang;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\models\BenefitType;
use App\models\Vendor;
use App\models\State;
use App\models\Benefit;
use App\models\Client;

class BenefitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $data = array();

        $client = Client::getFromSession();

        if ($client != null) {
            $benefits = $client->benefitsWithType();
            if (count($benefits) > 0) {
                $data["benefits"] = $benefits; 
            }
        }

        

        return view('index', $data)->with('page', 'benefit');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $data = array();

        $benefit_type = BenefitType::all();
        if (count($benefit_type) > 0) {
            $data["benefit_type"] = $benefit_type; 
        }

        $vendors = Vendor::all();
        if (count($vendors) > 0) {
            $data["vendors"] = $vendors; 
        }

        $state = State::all();
        if (count($state) > 0) {
            $data["states"] = $state; 
        }

        return view('index', $data)->with('page', 'benefit_create_edit');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validation = Benefit::validate(Input::all());
        if ($validation->fails()) {
            return back()->withInput()
            ->with("errors", $validation->messages()->all()); 
        }

        $benefit = Benefit::store($request);

        if ($benefit) {
            $notification[] = array(
            'type'=>'success',
            'message'=> Lang::get('notification.store_success')
            );
        } else {
            $notification[] = array(
            'type'=>'failure',
            'message'=> Lang::get('notification.store_failure')
            );
        }

        Session::flash('notification', $notification);

        return redirect('/benefit');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        return $this->addEditView($id);
    }

    private function addEditView($id)
    {
        $data = array();

        $benefit_type = BenefitType::all();
        if (count($benefit_type) > 0) {
            $data["benefit_type"] = $benefit_type; 
        }

        $vendors = Vendor::all();
        if (count($vendors) > 0) {
            $data["vendors"] = $vendors; 
        }

        $state = State::all();
        if (count($state) > 0) {
            $data["states"] = $state; 
        }

        $benefit = Benefit::find($id);
        if ($benefit) {
            $data["benefit"] = $benefit;

            $documents = $benefit->documents;
            if (count($documents) > 0) {
                $data["documents"] = $documents; 
            }
        }

        return view('index', $data)->with('page', 'benefit_create_edit');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        return $this->addEditView($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int     $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $validation = Benefit::validate(Input::all());
        if ($validation->fails()) {
            return back()->withInput()
            ->with("errors", $validation->messages()->all()); 
        }

        $benefit = Benefit::store($request, $id);

        if ($benefit) {
            $notification[] = array(
            'type'=>'success',
            'message'=> Lang::get('notification.edit_success')
            );
        } else {
            $notification[] = array(
            'type'=>'failure',
            'message'=> Lang::get('notification.edit_failure')
            );
        }

        Session::flash('notification', $notification);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        $benefit = Benefit::find($id);
        if ($benefit) {
            $benefit->delete(); 
        }

        return back();
    }

    public function vendors($id)
    {
        $benefit = BenefitType::find($id);

        $vendors = $benefit->vendors;

        return view('benefit.includes.vendors_options')
        ->with("vendors", $vendors);
    }
}
