<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiDocsController extends Controller
{
    public function __invoke(Request $request)
    {
        if (! auth()->user()->canManageDeveloperSettings()) {
            Log::warning('ApiDocsController: User is not allowed to access API docs', ['user_id' => auth()->user()->id]);
            abort(403);
        }

        return view('scribe.index');
    }
}
