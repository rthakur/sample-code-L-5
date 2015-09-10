<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use Mail;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\models\Client;
use App\models\ClientUser;
use App\models\State;
use Auth;
class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($id=null)
    {
        // Set select client session		
        $this->setClientSession($id);
        $current_client = '';
        $page      = 'client';
        if(\Session::has('current_client')) {
            $current_client = \Session::get('current_client');
            $current_client = $current_client['id'];
        }

        if(Auth::user()->type=='user') {
            $clients = ClientUser::where('email', Auth::user()->email)->paginate(20);
            $page      = 'client_user';
        }
        else {
            $clients = Client::paginate(20); 
        }

        return view('index')->with('page', $page)->with('clients', $clients)
                                                 ->with('current_client', $current_client);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        if (Session::has("current_client")) 
            Session::forget("current_client");
             
        return view('index')->with('page', 'client_create_edit')->with('states', State::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(Client $table, Request $request)
    {    
        
        $inputs = \Input::except('_token','client_logo'); 
        
        foreach($inputs as $k => $v) {
            if(is_array($v))
             $inputs[$k] = json_encode($v);
        }

        $table->validate($request, $table->rules());

        $client = $table->create($inputs);

        if ($request->hasFile('client_logo')) {
            $table->uploadFile($request->file("client_logo"), $client->id);
        }

        $notification[] = array('type'=>'success',
                                'message'=>\Lang::get('notification.store_success')
                                );
                                
        \Session::flash('notification', $notification);
        
        return redirect('client');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        $this->setClientSession($id);

        if(Auth::user()->type=='user') {
            return redirect('/'); 
        }
        $clientuser = ClientUser::where('client_id', $id)
                                  ->where('user_id', Auth::id())->get();
        return view('index')->with('page', 'client_create_edit')
        ->with('client', Client::find($id))
        ->with('states', State::all())
        ->with('clientuser', $clientuser);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('index')->with('page', 'client_create_edit')
        ->with('client', Client::find($id))
        ->with('states', State::all());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int     $id
     * @return Response
     */
    public function update(Client $table, Request $request, $id)
    {
        $table->validate($request, $table->rules());
            
        $table = $table->find($id);
            
        foreach(\Input::except('_token', '_method', 'client_logo') as $k => $v) {
            if(is_array($v))
                $v = json_encode($v);

            $table->$k = $v; 
        }
                
        $table->save();

        if ($request->hasFile('client_logo')) {
            $table->uploadFile($request->file("client_logo"), $id);
        }
            
        $notification[] = array('type'=>'success',
                                'message'=>\Lang::get('notification.edit_success')
                                );
                                
        \Session::flash('notification', $notification);
       
        return redirect('client');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(Client $table,$id)
    {
        $table = $table->find($id);
        $table->delete(); 
        $notification[] = array('type'=>'success',
                                'message'=>\Lang::get('notification.delete_success')
                                );
        \Session::flash('notification', $notification);
        return redirect('client');
    }

    public function clear()
    {
        if (Session::has("current_client")) {
            Session::forget("current_client"); 
        }

        return redirect('client');
    }

    /**
     * Invite client user
     */
    public function invite(ClientUser $table,Request $request)
    {
        
        //$table->validate($request, $table->rules());
        $subject = "You're invited to manage officelink360 clients";
        $token            = Session::token();    
        
        $inputs = Input::all();

        foreach (Input::get('email') as $key => $email) {

             if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
                continue; 
            
            if($table->where('email', $email)->first()) 
                $token = '';
            $message = View('emails.invite_client_user')->with('name', $inputs['name'][$key])
                                                            ->with('client_name',Input::get('client_name'))
                                                            ->with('token', $token);
            
             $data     = array("sender"=>$email,"subject"=>$subject);
             $request = array(
                'name'      => $inputs['name'][$key],
                'email'     => $email,
                'client_id' => Input::get('client_id'),
                'token'     => $token,
                ); 

            //Create Invite
                if($table->invite($request)) {
                     Mail::raw(
                        $message, function ($msg) use ($data) {    
                            $msg->to($data["sender"])->subject($data["subject"]);
                        }
                    );
                 Utils::notifications('success',\Lang::get('notification.invite_success'));
                }
            }
        return redirect('client/'.Input::get('client_id'));
    }
    
    
    public function invitedelete(ClientUser $table,$id)
    {
        $table = $table->where('id', $id)->where('user_id', Auth::id())->first();

        if(!$table) return redirect('client');

        $client_id = $table->client_id;
        $table->delete(); 
        $notification[] = array('type'=>'success',
                                'message'=>\Lang::get('notification.delete_success')
                                );
        \Session::flash('notification', $notification);
        return redirect('client/'.$client_id);
    }

    private function setClientSession($id){

        if($getClient = Client::find($id)) {
            \Session::put('current_client', array('id'=>$id,'name'=>$getClient->name,'client_logo'=>$getClient->client_logo)); 
        }
    }
}
