<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Application;
use App\Model\CityRequest;
use App\Model\Day;
use App\Model\Hit;
use App\Model\Logger;
use App\Model\DefinedVersion;
use App\Model\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MainController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $days = Day::all();
        $definedversions = DefinedVersion::all();
        $filter = null;
        if(isset($request->filter)) {
            $filter = $request->filter;
        }
        $datas = [];


        $datas = [];
        foreach ($days as $day) {
            $arr = [];
            $day->date = (string)$day->date;
            if(strlen($day->date) == 7) {
                $day->date =  '0' . $day->date;
            }

//            dd($day->date[1]);
//            $newDate = date("d-m-Y", strtotime($day->date));
//            dd($newDate);
//            $redat = explode('-', $newDate);
//            $nmonth = (int)$redat[1] - 1;
//            $newformDate = $redat[0] . ', ' . $nmonth . ', ' . $redat[2];
            foreach ($definedversions as $defver) {
                if($day->created_at->format('Y,m,d') == $defver->created_at->format('Y,m,d')){
                    $arr['defunact'] = $defver->defined_act_ru + $defver->defined_act_en;
                    $arr['defact'] = $defver->defined_unact_ru + $defver->defined_unact_en;
                } else {
                    $arr['defunact'] = 0;
                    $arr['defact'] = 0;
                }
            }

//            dd($day);
            $arr['id'] = $day->id;
            $arr['realdat'] = $day->date;
            $arr['realdata'] = $day->date[4].$day->date[5].$day->date[6].$day->date[7].', '. ((int)($day->date[2].$day->date[3]) - 1) .', '.$day->date[0].$day->date[1];
            $arr['act'] = !$filter ? $day->act_ru + $day->act_en : ($filter == 'ru' ? $day->act_ru : $day->act_en);
            $arr['unact'] = !$filter ? $day->unact_ru + $day->unact_en : ($filter == 'ru' ? $day->unact_ru : $day->unact_en);
            $arr['undef'] = !$filter ? $day->undef_ru + $day->undef_en : ($filter == 'ru' ? $day->undef_ru : $day->undef_en);
            if($day->undefinite != null) {
                $arr['undef'] += $day->undefinite;
            }
            $datas[] = $arr;
        }

        $version = 0;
        $setting = Setting::all()->first();
        if($setting) {
            $version = $setting->actualversion;
        }

        $hits = Hit::orderBy('id', 'DESC')->paginate(20);

       $q = DB::table('hits')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as views'))
           ->where('application_id', '<>', null)
            ->groupBy('application_id', 'date');
//            ->get();
        if($filter != null) {
            $q->where('lang', $filter);
        }

        $hits_count = $q->get();
        $hs = Hit::all()->count();
        $applications = DB::table('applications')
//            ->select(DB::raw('DATE(hits.created_at) as date'), '*')
//            ->join('hits', 'applications.id', '=', 'hits.application_id')
//            ->groupBy('applications.id', 'date')
            ->get();

//       dd($hits_count, $hs, $applications);
        $h_count = [];
       foreach ( $hits_count as $item) {
//           $item->date = date('dmY', strtotime($item->date));
//           if(isset()) {
//
//           }
           $h_count[(string)date('dmY', strtotime($item->date))] = isset($h_count[(string)date('dmY', strtotime($item->date))]) ? ($h_count[(string)date('dmY', strtotime($item->date))] + 1) : 1;
       }

       foreach ($datas as &$item) {
           $item['uniqueid'] = 0;
            if(isset($h_count[$item['realdat']])) {
                $item['uniqueid']  =  $h_count[$item['realdat']];
            }
       }
       unset($item);
       if(isset($request->test)){
        dd($hits_count,$h_count, $datas );
       }


        $topTen = Application::with('hits')
//        $topTen = Application::where('version','<>',761)->with('hits')
            ->get()
            ->sortByDesc(function($application){
                return $application->hits()->whereDate('created_at', Carbon::today())->count();
            })->take(50);


        if(isset($request->test1)) {
            $mobils = Application::where('version', 761)->get();
            $count = 0;
            foreach ($mobils as &$mobil){
                $count += $mobil->hits()->whereDate('created_at', Carbon::today())->count();
                $mobil->count += $mobil->hits()->whereDate('created_at', Carbon::today())->count();
            }

            dd($count, $mobils);
        }


        $vers = '';
        $sett = Setting::all()->first();
        if($sett) {
            $vers = $sett->versionname;
        }
        $apps = Application::where('uuid', $vers)->get();
        $cou = 0;
        foreach ($apps as &$app){
            if($app->hits()->whereDate('created_at', Carbon::today())->count()){
                $cou += 1;
            }
        }

        return view('admin.sections.main.home', [
            'title' => 'График',
            'datas' => $datas,
            'version' => $version,
            'count' => $cou,
            'hits' => $hits,
            'topten' => $topTen,
        ]);
    }


    public function logger(Request $request)
    {
        $filter = false;
        if(isset($request->filter) && $request->filter == 'installid') {
            $hits = Hit::whereNotNull('application_id')->orderBy('id', 'DESC')->paginate(50);
            $filter = true;
        } else {
            $hits = Hit::orderBy('id', 'DESC')->paginate(50);
        }
//        dd($filter, $request->filter);
        return view('admin.sections.main.logger', [
            'title' => 'Логи',
            'hits' => $hits,
            'filter' => $filter,
        ]);
    }


    public function topten(Request $request)
    {
        $topTen = Application::with('hits')
            ->get()
            ->sortByDesc(function($application){
                return $application->hits()->whereDate('created_at', Carbon::today())->count();
            })->take(10);

//        foreach ($topTen as $item) {
//            $item->()->whereDate('created_at', Carbon::today())->count();
//        }




        return view('admin.sections.main.topten', [
            'title' => 'Горячая Десятка!',
            'topten' => $topTen,
        ]);
    }
    // public function version()
    // {
       
      
    //     $hits = Hit::where('log->installid', 'indubala.ru')->get();
    //     $hitDates = [];
    //     $dat = Hit::where('log->installid', 'indubala.ru')->value('created_at')->format('Y, m, d');
    //     $coun = 0;
    //     $ind = 0;
    //     foreach ($hits as $hit) {
    //         $newDate = $hit->created_at->format('Y, m, d');
    //         if($newDate != $dat){
                
    //            $hitDates[$ind] = $coun;
    //            $coun = 0;
    //            $dat = $newDate;
    //            $ind += 1;
    //         } 
    //          $coun += 1;
    //     }
    //     dd($hitDates);
    // }

    public function city_requests(Request $request)
    {
        $crs = CityRequest::all();

        foreach ($crs as $cr) {

            $cr->date = (string)$cr->date;
            if(strlen($cr->date) == 7) {
                $cr->date =  '0' . $cr->date;
            }

            $cr->realdata = $cr->date[4].$cr->date[5].$cr->date[6].$cr->date[7].', '.((int)($cr->date[2].$cr->date[3] -1)).', '.$cr->date[0].$cr->date[1];
        }
//        dd($cr);
        return view('admin.sections.main.city_requests', [
            'title' => 'График запросов городов',
            'crs' => $crs,
        ]);
    }
}
