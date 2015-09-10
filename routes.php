<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

Route::get('/', 'DashboardController@index');

Route::get('dashboard', 'DashboardController@dashboard');


/*
|--------------------------------------------------------------------------
| AuthController Routes
|--------------------------------------------------------------------------
*/
Route::group(
    ['prefix'=>'auth'], function () {
        Route::post('login', 'Auth\AuthController@authenticate');
        Route::post('join', 'Auth\AuthController@postJoin');
    }
);
Route::get('join/{token}', 'Auth\AuthController@getJoin');



/*
|--------------------------------------------------------------------------
| HomeController Routes
|--------------------------------------------------------------------------
*/
Route::group(
    ['prefix'=>'home', 'middleware'=>'auth'], function () {
        
        Route::get("/", 'HomeController@home');
    }
);


/*
|--------------------------------------------------------------------------
| ClientController Routes
|--------------------------------------------------------------------------
*/
Route::group(
    ['middleware'=>'auth'], function () {
        Route::get('client/select/{id}', 'ClientController@index');
        Route::get('client/clear', 'ClientController@clear');
        Route::post('client/invite', 'ClientController@invite');
        Route::get('client/invite/delete/{id}', 'ClientController@invitedelete');
        Route::resource('client', 'ClientController');
    }
);    



/*
|--------------------------------------------------------------------------
| VendorController Routes
|--------------------------------------------------------------------------
*/
Route::group(
    ['middleware'=>'auth'], function () {
        Route::get("/vendor/{id}/website", "VendorController@website");
        Route::get("/vendor/addotherbenefir","VendorController@addOtherBenefit");
        Route::get("/vendor/deleteotherbenefit","VendorController@deleteotherbenefit");
        Route::resource('vendor', 'VendorController');
    }
);


/*
|--------------------------------------------------------------------------
| BenefitController Routes
|--------------------------------------------------------------------------
*/
Route::group(
    ['middleware'=>'auth'], function () {
        Route::get("benefit/{id}/vendors", "BenefitController@vendors");
        Route::resource('benefit', 'BenefitController');
    }
);


Route::group(
    ['middleware'=>'auth'], function () {
        Route::get('benefit/{benefit_id}/document/{document_id}/download', "DocumentController@download");
        Route::resource('benefit.document', 'DocumentController');
    }
);

Route::group(
    ['middleware'=>'auth'], function () {
        Route::get("/document/{id}/download", "SystemDocumentsController@download");
        Route::post("/document/email", "SystemDocumentsController@email");
        Route::resource('document', 'SystemDocumentsController');
    }
);

Route::group(
    ['middleware'=>'auth'], function () {
        Route::post("/document/{id}/tag", "TagDocumentsController@tag");
        Route::get("/document/{id}/fetchModal", "TagDocumentsController@fetchModal");
        Route::get("/document/tag/delete/{id}", "TagDocumentsController@tagDelete");
        Route::resource('tag.document', 'TagDocumentsController');
    }
);

Route::group(
    ['middleware'=>'auth'], function () {
        Route::resource("benefitlist", "EmployeeBenefitListController");
    }
);

Route::group(
    ['middleware'=>'auth'], function () {
        Route::resource("employee.document", "EmployeeDocumentsController");
    }
);

/*
|--------------------------------------------------------------------------
| EmployeeController Routes
|--------------------------------------------------------------------------
*/
Route::group(
    ['middleware'=>'auth'], function () {
        Route::post("employee/upload", "EmployeeController@upload");
        Route::get("employee/download/csv", "EmployeeController@download");
        Route::post("employee/{id}/terminate", "EmployeeController@terminate");
        Route::post("employee/{id}/email", "EmployeeController@email");
        Route::resource('employee', 'EmployeeController');
    }
);


Route::group(
    ["middleware"=>"auth"], function () {
        Route::resource("employee.benefit", "EmployeeBenefitController");
    }
);


/*
|--------------------------------------------------------------------------
| ProfileController Routes
|--------------------------------------------------------------------------
*/
Route::group(
    ['prefix'=>'profile','middleware'=>'auth'], function () {
        Route::resource('/', 'ProfileController');
    }
);    


Route::get("/employee/benefit/modal", "EmployeeBenefitController@fetchEditModal");


/*
|--------------------------------------------------------------------------
| PasswordController Routes
|--------------------------------------------------------------------------
*/
Route::group(
    ['prefix'=>'password'], function () {
    
        Route::get('email', 'Auth\PasswordController@getEmail');
        Route::post('email', 'Auth\PasswordController@postEmail');

        // Password reset routes
        Route::get('reset/{token}', 'Auth\PasswordController@getReset');
        Route::post('reset', 'Auth\PasswordController@postReset');

    }
);
    

Route::get(
    'logout', function () {
        Auth::logout();
        if (Session::has("current_client")) {
            Session::forget("current_client"); 
        }
        return redirect('/');
    }
);
