<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $temas = DB::table('pesquisas')
                ->select('*')
                ->orderBy(DB::raw("REPLACE(keyword, '\"', '')")) //GET RID OFF QUOTES ONLY FOR ALPHABETICAL ORDER
                ->get();

        return view('admin', compact('temas'));
    }
}
