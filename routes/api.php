<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;


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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/greeting', function () {
    // accessing value on response directly
    $response = Http::get('https://jsonplaceholder.typicode.com/todos/1')["title"];
    return $response;
});



// 100 requests over 24 hours
Route::middleware('throttle:100,1440')->get('phone',function(Request $request){
    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
    $numberStr = $request['number'];
    $region = strtoupper($request['selected-region']);

    try {
        $geocoder = \libphonenumber\geocoding\PhoneNumberOfflineGeocoder::getInstance();
        $numberProto = $phoneUtil->parse($numberStr, $region);
        $response=array(
            "valid"=> $phoneUtil->isValidNumber($numberProto),
             "formats"=>[
                 "national"=> $phoneUtil->format($numberProto,\libphonenumber\PhoneNumberFormat::NATIONAL),
                 "E164"=> $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::E164),
                 "international"=> $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL)
             ],
             "location"=> $geocoder->getDescriptionForNumber($numberProto,"en_US")
        );

        return $response;

    } catch (\libphonenumber\NumberParseException $e) {
        return $e;
    }

});

Route::middleware('throttle:100,24')->get('supported-regions',function(){
    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
    $regions= $phoneUtil->getSupportedRegions();
    return $regions;
});

Route::middleware('throttle:50,24')->get('email',function(Request $request){
    $mailApiResponse = Http::get('http://apilayer.net/api/check', [
        'access_key'=> $_ENV['MAIL_VALIDATE_ACCESS_KEY'],
        'smtp'=>1,
        'format'=>1,
        'email'=> $request['email']
    ]);

    $response = array(
        "email"=>$mailApiResponse["email"],
        "user"=> $mailApiResponse["user"],
        "domain"=> $mailApiResponse["domain"],
        "valid_format"=>$mailApiResponse["format_valid"],
        "mx_found"=> $mailApiResponse["mx_found"],
        "deliverable"=> $mailApiResponse["smtp_check"],
        "free_to_use" => $mailApiResponse["free"],
        "score"=> $mailApiResponse["score"],
        "disposable"=> $mailApiResponse["disposable"]
    );

    return $response;
});



