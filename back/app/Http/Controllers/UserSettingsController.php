<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserSettingsResource;
use App\Services\Interfaces\UserSettingsServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSettingsController extends Controller
{
    public function __construct(
        private readonly UserSettingsServiceInterface $userSettingsService,
    ) {}

    /**
     * GET /api/user/settings
     * Returns the authenticated user's settings, auto-creating defaults on first access.
     */
    public function show(Request $request): JsonResponse
    {
        $settings = $this->userSettingsService->getForUser($request->user());

        return (new UserSettingsResource($settings))->response()->setStatusCode(200);
    }

    /**
     * PUT /api/user/settings
     * Partially updates the authenticated user's settings (only provided fields change).
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'currency_id' => 'sometimes|nullable|integer|exists:currencies,id',
            'theme' => 'sometimes|string|in:light,dark,system',
            'language' => 'sometimes|string|max:5',
            'timezone' => 'sometimes|string|timezone',
            'default_chart_range' => 'sometimes|string|in:1D,1W,1M,3M,6M,1Y,MAX',
            'default_chart_interval' => 'sometimes|string|in:1min,5min,15min,30min,1h,4h,1d,1wk,1mo',
            'show_extended_metrics' => 'sometimes|boolean',
            'notifications_enabled' => 'sometimes|boolean',
            'preferences' => 'sometimes|nullable|array',
        ]);

        $settings = $this->userSettingsService->updateForUser($request->user(), $validated);

        return (new UserSettingsResource($settings))->response()->setStatusCode(200);
    }
}
