<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function generateReport(Request $request)
    {
        $data = $request->all();

        $html = View::make('report.template',compact('data'))->render();

        return Response::make($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'attachment; filename="search-results.html"',
        ]);
    }
}
