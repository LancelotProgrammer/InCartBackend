<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiDocsController extends Controller
{
    public function __invoke(Request $request)
    {
        if (!auth()->user()->canManageDeveloperSettings()) {
            abort(403);
        }
        return view('scribe.index');
    }
}
