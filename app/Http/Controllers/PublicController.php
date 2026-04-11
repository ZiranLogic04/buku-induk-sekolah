<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;

class PublicController extends Controller
{
    public function tahunAjaran()
    {
        return response()->json(TahunAjaran::orderByDesc('id')->get());
    }
}
