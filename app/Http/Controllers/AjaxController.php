<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class AjaxController extends Controller
{

    //make a public function to get the id from the table 'pesquisas' by the keyword
    public function getidbykeyword(Request $request)
    {
        if ($request->isMethod('get') === false) {
            return;
        }

        $request->validate([
            'keyword' => 'required'
        ]);

        $keyword = $request['keyword'];
        $id = DB::table('pesquisas')->where('keyword', $keyword)->value('id');

        return response()->json(['success' => $id]);
    }

    public function adminstore(Request $request)
    {
        if ($request->isMethod('post') === false) {
            return;
        }

        $request->validate([
            'create' => 'required|in:0,1',
            'id' => 'numeric'
        ]);

        $create = $request['create'];
        $id = $request['id'];
        $label = $request['label'];

        if ($create == 1) {
            // Ao criar uma página, preenchemos tanto created_at quanto checked_at
            // Isso porque ao criar, o admin já está implicitamente verificando/aprovando
            // a página para aparecer publicamente em /temas e na API
            $affected = DB::table('pesquisas')
                ->where('id', $id)
                ->update(
                    [
                        'created_at' => DB::raw('NOW()'),
                        'checked_at' => DB::raw('NOW()'),
                        'label' => $label,
                        'slug' => slugify($label)
                    ]
                );
        }

        // Artisan::call('sitemap:generate'); //substituído por cronjob uma vez ao dia, às 06 da manhã: php8.1 /home/forge/tesesesumulas.com.br/artisan sitemap:generate
        return response()->json(['success' => $affected]);
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

        return response()->json(['success' => $del]);
    }

    //new (dez/2022)
    public function searchByKeywordSimilarity(Request $request)
    {
        if ($request->isMethod('post') === false) {
            return;
        }
        $keywordSearched = $request['keywordSearched'];
        $label = $request['label'];
        $typeToCompare = $request['typeToCompare'];
        $percentage_requested = intval($request['percentage']);
        $termToCompare = $keywordSearched;
        if ($typeToCompare == 'label') {
            $termToCompare = $label;
            $termToCompare = str_replace([' ', '-', '"', "'"], '', $termToCompare);
            $termToCompare = strtolower($termToCompare);
        }


        // Get all records from the table 'pesquisas' where created_at is not null; get only label and id fields
        $records = DB::table('pesquisas')
            ->select('id', 'keyword', 'label', 'results')
            ->whereNotNull('created_at')
            ->get();

        $results = [];

        // Loop through all records
        foreach ($records as $record) {
            if ($typeToCompare == 'label') {
                $termFromDB = $record->label;
                $termFromDB = str_replace([' ', '-', '"', "'"], '', $termFromDB);
                $termFromDB = strtolower($termFromDB);
            } else {
                $termFromDB = $record->keyword;
            }


            // Get the similarity score of the record with the keyword
            $similarityScore = similar_text($termToCompare, $termFromDB, $percentage);

            // Check if the similarity score is at least 70%.
            if ($percentage >= $percentage_requested) {
                //add round percentage to record
                $record->percentage = round($percentage);
                $record->criteria = $typeToCompare;
                $record->termCompared = $termToCompare;
                // Push the record to the results array
                array_push($results, $record);
            }
        }
        return response()->json(['success' => $results]);
    }

}