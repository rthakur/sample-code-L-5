<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Session;

use App\models\BenefitTypeDocument;

use App\Http\Controllers\Utils;

class Benefit extends Model
{
    protected $table = "benefits";

    protected $guarded = array();

    public static function validate($input)
    {
        $rules = array(
        "carrier_name" => "required|string",
        "address1" => "string",
        "address2" => "string",
        "city" => "string",
        "state" => "string",
        "zipcode" => "digits:5",
        "phone" => "regex:/^\d{3}-\d{3}-\d{4}$/",
        "phone_alt" => "regex:/^\d{3}-\d{3}-\d{4}$/",
        "email_onboard" => "email",
        "email_termination" => "email",
        "carrier_website" => "string",
        "plan_name" => "required|string",
		
        );

        return Validator::make($input, $rules);
    }

    public static function allWithType()
    {
        $benefits = Benefit::select("benefits.*", "benefit_types.name as benefit_type")
        ->join("benefit_types", "benefits.benefit_type_id", "=", "benefit_types.id")
        ->get();

        return $benefits;
    }

    public static function store($request, $id = null)
    {
        $benefit = null;

        if ($id == null) {
            $benefit = new Benefit; 
        }
        else {
            $benefit = Benefit::find($id); 
        }

        $client = Session::get('current_client');

        if ($client) {
            $benefit->client_id = $client["id"];
        }

        $benefit->vendor_id = $request->input("vendor");
        $benefit->benefit_type_id = $request->input("benefit_type");

        $benefit->carrier_name = $request->input("carrier_name", "");
        $benefit->carrier_address1 = $request->input("address1", "");
        $benefit->carrier_address2 = $request->input("address2", "");
        $benefit->carrier_city = $request->input("city", "");
        $benefit->carrier_state = $request->input("state", "");
        $benefit->carrier_zipcode = $request->input("zipcode", "");
        $benefit->carrier_phone1 = $request->input("phone", "");
        $benefit->carrier_phone2 = $request->input("phone_alt", "");
        $benefit->carrier_onboarding_email = $request->input("email_onboard", "");
        $benefit->carrier_termination_email = $request->input("email_termination", "");
        $benefit->onboarding_broker_approval = $request->input("onboard_email_approval", "");
        $benefit->termination_broker_approval = $request->input("email_termination_approval", "");
        $benefit->group_number = $request->input("group_num", "");
        $benefit->account_number = $request->input("acct_num", "");
        $benefit->start_date = Utils::convertToDateFormat($request->input("policy_start_date", ""));
        $benefit->renewal_date = Utils::convertToDateFormat($request->input("policy_renewal_date", ""));
        $benefit->new_hire_waiting_period = $request->input("waiting_period", "");
        $benefit->plan_name = $request->input("plan_name", "");
        $benefit->carrier_website = $request->input("carrier_website", "");

        $benefit->save();

        return $benefit;
    }

    public function saveDocument($request)
    {
        return BenefitTypeDocument::store($request, $this->id);
    }

    public function documents()
    {
        return $this->hasMany("App\models\BenefitTypeDocument", "benefit_id");
    }

    public function vendorEmail()
    {
        $vendor = Vendor::find($this->vendor_id);

        if ($vendor && $vendor->email) {
            return $vendor->email; 
        }
        else {
            return null; 
        }
    }
}
