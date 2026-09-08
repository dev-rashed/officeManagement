<?php

namespace App\Http\Controllers\Public;

use App\Models\Service;
use Illuminate\Routing\Controller;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::published()->sorted()->get();
        return view('pages.public.services', compact('services'));
    }

    public function show($slug)
    {
        $service = Service::where('slug', $slug)->published()->firstOrFail();

        // Lets TrackPageView attribute the view to this service.
        request()->attributes->set('trackable', $service);

        return view('pages.public.services.detail', ['service' => $service, 'seoModel' => $service]);
    }
}
