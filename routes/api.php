<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::get('create-tenant', function (Request $request) {
//    $request->validate([
//        'tenant' => ['required', 'string', 'unique:tenants,name'],
//    ]);
//    $tenant = App\Models\Tenant::create(['name' => request('tenant'), 'user_id' => auth()->id()]);
//    $tenant->domains()->create(['domain' => request('tenant').'.host.apimultitenant.php8.gainhq.com']);
//    return ['success' => true, 'domain' => request('tenant').'.host.apimultitenant.php8.gainhq.com'];
//});

Route::get('/', function () {
    return ['success' => true, 'action' => 'home page'];
});

Route::post('login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);
    if (Auth::attempt($credentials)) {
//            $request->session()->regenerate();

        $token = auth()->user()->createToken('device-1');

        return response()->json(['success' => true, 'token' => $token->plainTextToken, 'type' => auth()->id() == 1 ? 'landlord' : 'tenant'], 200);
    }

    return response()->json(['success' => false, 'message' => 'Credentials not matched'], 422);
});

Route::post('register', function (Request $request) {
    $credentials = $request->validate([
        'name' => ['required', 'string'],
        'email' => ['required', 'email', 'unique:users,email'],
        'password' => ['required', 'confirmed'],
    ]);

    \App\Models\User::create([
        'name' => request('name'),
        'email' => request('email'),
        'password' => bcrypt(request('password')),
    ]);

    if (Auth::attempt($credentials)) {
        $token = auth()->user()->createToken('device-1');

        return response()->json(['success' => true, 'token' => $token->plainTextToken, 'type' => auth()->id() == 1 ? 'landlord' : 'tenant'], 200);
    }

    return response()->json(['success' => false, 'message' => 'Credentials not matched'], 422);
});



Route::middleware([
    'auth:sanctum'
])->group(function () {
//    Route::get('/', function () {
//        return ['success' => true, 'action' => 'landlod logged in'];
//    });

    Route::get('/user', function (Request $request) {
        return $request->user()->load('tenant', 'tenant.domains');
    });

    Route::get('/subscribers', function (Request $request) {
        if(auth()->id() == 1) {
            return response()->json(['success' => true, 'data' => \App\Models\User::query()->whereHas('tenant')->with('tenant', 'tenant.domains')->get()]);
        }
        return response()->json(['success' => false, 'message' => 'Unauthorised access.'], 401);
    });

    Route::post('create-tenant', function (Request $request) {
        $request->validate([
            'tenant' => ['required', 'string', 'unique:tenants,name'],
        ]);
        $tenant = App\Models\Tenant::create(['name' => request('tenant'), 'user_id' => auth()->id()]);
        $tenant->domains()->create(['domain' => request('tenant').'.host.apimultitenant.php8.gainhq.com']);
        return ['success' => true, 'domain' => request('tenant').'.host.apimultitenant.php8.gainhq.com'];
    });

//    Route::get('delete-tenant', function () {
//        return App\Models\Tenant::find(request('tenant'))->delete();
//    });
});
