<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Input;
use Session;
use Lang;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\models\State;
use App\models\BenefitType;
use App\models\Vendor;
use App\models\VendorBenefitType;
use App\models\Client;

class VendorController extends Controller
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
            $vendors = $client->vendors;
            if (count($vendors) > 0) {
                $data["vendors"] = $vendors; 
            }
        }

        $benefit_types = $this->getBenefitType();
        if (count($benefit_types) > 0) {
            $data["benefit_types"] = $benefit_types; 
        }

        return view('index', $data)->with('page', 'vendor');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $data = [];
       
        $states = State::all();
        if (count($states) > 0) {
            $data["states"] = $states; 
        }
         
        $benefit_types = $this->getBenefitType();

        if (count($benefit_types) > 0) {
            $data["benefit_types"] = $benefit_types; 
        }

        return view('index', $data)->with('page', 'vendor_create_edit');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validation = Vendor::validate(Input::all());
        if ($validation->fails()) {
            return back()
            ->with("errors", $validation->messages()->all())
            ->withInput(); 
        }

        $vendor = Vendor::store($request);

        if ($vendor) {
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

        return redirect('vendor');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        return $this->addEditVendorView($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        return $this->addEditVendorView($id);
    }

    private function addEditVendorView($id)
    {
        $data = array();

        $vendor = Vendor::find($id);
        if ($vendor) {
            $data["vendor"] = $vendor; 
        }

        $states = State::all();
        if (count($states) > 0) {
            $data["states"] = $states; 
        }

        $benefit_types = $this->getBenefitType();
        if (count($benefit_types) > 0) {
            $data["benefit_types"] = $benefit_types; 
        }

        return view('index', $data)->with('page', 'vendor_create_edit');
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
        $validation = Vendor::validate(Input::all());
        if ($validation->fails()) {
            return back()
            ->with("errors", $validation->messages()->all())
            ->withInput(); 
        }

        $vendor = Vendor::store($request, $id);

        if ($vendor) {
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
        $vendor = Vendor::find($id);
        if ($vendor) {
            $vendor->delete(); 
        }

        return back();
    }

    public function website($vendor_id)
    {
        $vendor = Vendor::find($vendor_id);
        if (!$vendor)
            return abort(404);

        if ($vendor->website)
            return redirect()->away($vendor->website);
        else
            return abort(404);
    }


    private function getBenefitType(){

        $current_client = \Session::get('current_client');
        return BenefitType::where('client_id',$current_client['id'])
                                    ->orWhere('client_id','0')
                                    ->get();
    }

    public function addOtherBenefit(){
        $current_client = \Session::get('current_client');
       
       if(!\Input::get('benefit_name'))
         return "fail";

        $benefit = new BenefitType;
        $benefit->name = \Input::get('benefit_name');
        $benefit->client_id = $current_client['id'];
        $benefit->save();
        return $benefit->id;
        
    }

   public function deleteotherbenefit(){

        $current_client = \Session::get('current_client');

        $benefit = BenefitType::where('id',\Input::get('benefit_id'))
                    ->where('client_id',$current_client['id'])->first();
        if($benefit){
            $benefit->delete();
            return 'success';
        }

        return 'fail';
   } 
}
