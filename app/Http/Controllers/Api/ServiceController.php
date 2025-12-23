<?php

namespace App\Http\Controllers\Api;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ServiceController extends Controller
{
    /**
     * Get all active services
     * Endpoint: GET /api/services
     */
    public function index()
    {
        $services = Service::active()
            ->ordered()
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'price' => $service->price,
                    'formatted_price' => $service->formatted_price,
                    'category' => $service->category,
                    'image_url' => $service->image_url,
                    'has_quantity' => $service->has_quantity,
                    'max_quantity' => $service->max_quantity,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    /**
     * Get services by category
     * Endpoint: GET /api/services/category/{category}
     */
    public function byCategory($category)
    {
        $services = Service::active()
            ->where('category', $category)
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    /**
     * Get single service detail
     * Endpoint: GET /api/services/{id}
     */
    public function show($id)
    {
        $service = Service::active()->find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $service,
        ]);
    }
}