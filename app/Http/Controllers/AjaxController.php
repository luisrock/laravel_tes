<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

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
        
        Artisan::call('sitemap:generate');
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

    //new (dez/2022)
    public function searchByKeywordSimilarity(Request $request)
    {
        if ($request->isMethod('post') === false) {
            return;
        }
        $keyword = $request['keyword'];

        // Get all records from the table 'pesquisas' where created_at is not null; get only label and id fields
        $records = DB::table('pesquisas')
            ->select('label', 'id')
            ->whereNotNull('created_at')
            ->get();

        $results = [];

        // Loop through all records
        foreach ($records as $record) {
            //save $record->label to a variable and remove ' and " and - and spaces from it
            $label = str_replace([' ', '-', '"', "'"], '', $record->label);
            //uncapitalize label
            $label = strtolower($label);
            //do the same with $keyword
            $keyword = str_replace([' ', '-', '"', "'"], '', $keyword);
            $keyword = strtolower($keyword);
            // Get the similarity score of the record with the keyword
            $similarityScore = similar_text($keyword, $label, $percentage);

            // Check if the similarity score is at least 70%.
            if ($percentage >= 70) {
                //add round percentage to record
                $record->percentage = round($percentage);

                

                // Push the record to the results array
                array_push($results, $record);
            }
        }
        return response()->json(['success' => $results]);
    }

}
