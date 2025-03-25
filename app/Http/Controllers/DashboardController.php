<?php

// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BannerForm;
use App\Models\Project;
// Add other models as needed
use App\Models\CaseStudy;
use App\Models\IndustryReport;
use App\Models\Webinar;
use App\Models\Quotation;
use App\Models\Testimonial;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $data = [
                'total_projects' => Project::count(),
                'total_case_studies' => CaseStudy::count(),
                'total_industry_reports' => IndustryReport::count(),
                'total_webinars' => Webinar::count(),
                'total_quotations' => BannerForm::count(),

                // 'total_quotations' => Quotation::count(),
                'total_testimonials' => Testimonial::count(),
                // Optional: Additional project-specific stats
                'featured_projects' => Project::where('is_featured', true)->count(),
                'avg_project_price' => Project::avg('price'),
                'projects_by_property_for' => Project::groupBy('property_for')
                    ->selectRaw('property_for, COUNT(*) as count')
                    ->pluck('count', 'property_for')
                    ->toArray()
            ];

            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}