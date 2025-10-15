<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiDocsController extends Controller
{
    public function __invoke(Request $request)
    {
        if (! auth()->user()->canManageDeveloperSettings()) {
            abort(403);
        }

        return view('scribe.index');
    }
}
