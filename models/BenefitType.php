<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Session;

use App\models\Client;

class BenefitType extends Model
{
    protected $table = "benefit_types";

    protected $fillable = ["name","client_id"];

    public function benefit()
    {
        $client_info = Session::get("current_client");
        $client_id = $client_info["id"];

        return $this->hasMany("App\models\Benefit")->where("client_id", $client_id);
    }

    public function vendors()
    {
        $client = Client::getFromSession();

        return $this->belongsToMany("App\models\Vendor", "vendor_benefit_types")
            ->where("client_id", $client->id);
    }
}

?>
