<?php

namespace OlaHub\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class ValidationServiceProvider extends ServiceProvider
{

    public function boot(){
        Validator::extend('olahub_unique', function ($attribute, $value, $parameters, $validator) {

            $request = \Illuminate\Http\Request::capture();
            $urlParamter = $request->segments();
            if(isset($parameters[0]) && isset($parameters[1])){
                $table = $parameters[0];
                $column = $parameters[1];
                if($urlParamter[2]=="update"){
                    if(isset($urlParamter[3]) && $urlParamter[3] > 0){
                        $count = \DB::table($table)->where($column,$value)->where('id','!=',$urlParamter[3])->count();
                        if($count){
                            return false;
                        }
                    }
                }
                $count = \DB::table($table)->where($column,$value)->count();
                if($count){
                    return false;
                }
            }
            return true;
        });
    }
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
