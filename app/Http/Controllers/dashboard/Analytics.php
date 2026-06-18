<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Analytics extends Controller
{
  public function index()
  {
    if (auth()->user()->esProveedor()) {
      return redirect()->route('proveedor.dashboard');
    }

    return view('content.dashboard.dashboards-analytics');
  }
}
