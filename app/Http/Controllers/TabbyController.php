<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TabbyController extends Controller
{
    public function success(Request $request)
    {
        return view('tabby.success');
    }

    public function cancel(Request $request)
    {
        return view('tabby.cancel');
    }

    public function failure(Request $request)
    {
        return view('tabby.failure');
    }
} 