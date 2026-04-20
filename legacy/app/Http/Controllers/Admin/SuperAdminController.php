<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AdminAnalyticsService;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    protected AdminAnalyticsService $analyticsService;

    public function __construct(AdminAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display the super admin dashboard
     */
    public function dashboard()
    {
        $analytics = $this->analyticsService->getAnalytics();

        return view('admin.super-admin.dashboard', compact('analytics'));
    }
}
