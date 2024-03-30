<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Akaunting\Module\Facade as Module;
use Illuminate\Support\Facades\File;
use ZipArchive;

class AppsController extends Controller
{
    public function index(){
        dd('Currently developing');
        }

    public function remove($alias){
        if (!auth()->user()->hasRole('admin') || strlen($alias)<2 || (config('settings.is_demo') || config('settings.is_demo'))) {
            abort(404);
        }
        $destination=Module::get($alias)->getPath();
        if(File::exists($destination)){
            File::deleteDirectory($destination);
            return redirect()->route('apps.index')->withStatus(__('Removed'));
        }else{
            abort(404);
        }
    }

    public function store(Request $request){
       
        $path=$request->appupload->storeAs('appupload', $request->appupload->getClientOriginalName());
        
        $fullPath = storage_path('app/'.$path);
        $zip = new ZipArchive;

        if ($zip->open($fullPath)) {

            //Modules folder - for plugins
            $destination=public_path('../modules');
            $message=__('App is installed');

            //If it is language pack
            if (strpos($fullPath, '_lang') !== false) {
                $destination=public_path('../resources/lang');
                $message=__('Language pack is installed');
            }


            // Extract file
            $zip->extractTo($destination);
            
            // Close ZipArchive     
            $zip->close();
            return redirect()->route('apps.index')->withStatus($message);
        }else{
            return redirect(route('apps.index'))->withError(__('There was an error on app install. Please try manual install'));
        }
    }
}
