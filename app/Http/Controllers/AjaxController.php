<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AjaxController extends Controller
{
    public function adminstore(Request $request)
    {
        if ($request->isMethod('post') === false) {
            return;
        }

        $request->validate([
                'check' => 'required|in:0,1',
                'create' => 'required|in:0,1',
                'id' => 'numeric'
        ]);
        
        $check = $request['check'];
        $create = $request['create'];
        $id = $request['id'];
        $label = $request['label'];

        if($check == 1) {
            //update checked_at
            $affected = DB::table('pesquisas')
              ->where('id', $id)
              ->update([
                  'checked_at' => DB::raw('NOW()')
                  ]
                );
        }
        else if($create == 1) {
            //update created_at, label
            $affected = DB::table('pesquisas')
              ->where('id', $id)
              ->update([
                  'created_at' => DB::raw('NOW()'),
                  'label' => $label,
                  'slug' => slugify($label)
                  ]
                );
        }
        return response()->json(['success'=>$affected]);
    }

    public function admindel(Request $request)
    {
        if ($request->isMethod('post') === false) {
            return;
        }

        $request->validate([
                'id' => 'numeric'
        ]);
        
        $id = $request['id'];

        $del = DB::table('pesquisas')->where('id', $id)->delete();
        
        return response()->json(['success'=>$del]);
    }

}
