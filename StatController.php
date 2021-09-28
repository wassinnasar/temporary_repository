<?php

namespace App\Http\Controllers\Web;

use App\Helpers\Utilities;
use App\Http\Controllers\Controller;
use App\Model\Application;
use App\Model\Day;
use App\Model\Hit;
use App\Model\Logger;
use App\Model\DefinedVersion;
use App\Model\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StatController extends Controller
{

    private $i = 0;
    private $ip_array = [];
    public function endpoint(Request $request)
    {

        Log::info('INFO Request:' . json_encode($request->all()));


        $deffield = ' ';
        $actualversion = 861;
        $actualuuid = '';
        $setting = Setting::all()->first();
        if($setting) {
            $actualversion = $setting->actualversion;
            $actualuuid = $setting->versionname;
        }

        if ($request->lang == 'en' || $request->lang == 'ru') {
            $prefix = $request->lang;
            switch ($request->ver) {
                case 761:
                    $field = 'unact_' . $prefix;// мобильная версия
                    if($actualuuid == $request->installid){
                    $deffield = 'defined_unact_' . $prefix;
                    }
                    break;
                case $actualversion:
                    $field = 'act_' . $prefix;// актуальная версия
                    
                    break;
                default:
                    $field = 'undef_' . $prefix;// неизвестная версия
                    if($actualuuid == $request->installid){
                     $deffield = 'defined_act_' . $prefix;   
                     }
            }
        } else {
            $field = 'undefinite';// без языковой принадлежности, может быть вообще любой запрос
        }

        $ndate = date("dmY");
        $day = Day::where('date', $ndate)->first();
        $definedversion = DefinedVersion::whereDate('created_at', Carbon::today())->first();

        if ($day) {
            $day->date = $ndate;
            $day->$field += 1;
            $day->save();
        } else {
            $day = new Day();
            $day->date = $ndate;
            $day->$field = 1;
            $day->save();
        }
   
       //=========== Праверка на уникальность по uuid

       if(isset($request->installid) && strlen($request->installid) == 32) {

            $application = Application::where('uuid', $request->installid)->first();
            if(!$application){

                $application = new Application();
                $application->uuid =  $request->installid;
                $application->version =  $request->ver;
                if(isset($request->lang)){
                    if($request->lang == 'en' || $request->lang == 'ru') {
                        $application->lang = $request->lang;
                    }
                }
                $application->save();
            }
             $hit = Hit::where('application_id', $application->id)
             ->whereDate('created_at', Carbon::today())
             ->first();

             if(!$hit){

               if (!$definedversion) {
                 $definedversion = new DefinedVersion();
                 $definedversion->created_at = Carbon::today();
                 $definedversion->defined_act_ru = 0;
                 if($request->ver == 880){
                       $definedversion->defined_unact_ru = 0;
                    } 
              } 
                 $definedversion->defined_act_ru += 1;
                 $definedversion->save();
                 if($request->ver == 880){
                       $definedversion->defined_unact_ru += 1;
                       $definedversion->save();
                    }
             }
        }

      //===========

        $hit = new Hit();
        $hit->log = json_encode($request->all(), true);
        $ip = Utilities::get_ip();
        $hit->ip = $ip;


        if(isset($request->lang)){
            if($request->lang == 'en' || $request->lang == 'ru') {
                $hit->lang = $request->lang;
            }
        }

        if(isset($request->installid)) {
            $application = Application::where('uuid', $request->installid)->first();
            if(!$application){
                $application = new Application();
                $application->uuid =  $request->installid;
                $application->version =  $request->ver;
                if(isset($request->lang)){
                    if($request->lang == 'en' || $request->lang == 'ru') {
                        $application->lang = $request->lang;
                    }
                }
                $application->save();
            }
            $hit->application_id = $application->id;
        }
        $hit->save();

        $log = new Logger();
        $log->text = $ip . ' : ' . json_encode($request->all(), true) ;
        $log->save();
    }
}
