<?php

namespace App\Http\Controllers;

use App\Models\TestType;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    public function index()
    {
        $services = TestType::active()->orderBy('id')->get();

        return view('pages.services', compact('services'));
    }

    public function show(Request $request, TestType $service)
    {
        abort_unless($service->is_active, 404);

        return view('pages.service-detail', compact('service'));
    }
}
