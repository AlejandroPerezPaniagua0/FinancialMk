<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\DemoServiceInterface;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Public endpoints that power the "Try the demo" flow on the landing page.
 *
 * `status`  · cheap probe so the SPA can show or hide the CTA
 * `login`   · mints a Sanctum token for the seeded demo user
 *
 * Both endpoints are gated on config('demo.enabled'). When disabled they
 * return 404 — same surface as if the routes didn't exist.
 */
class DemoController extends Controller
{
    public function __construct(private readonly DemoServiceInterface $demoService) {}

    public function status(): JsonResponse
    {
        return response()->json([
            'enabled' => $this->demoService->isEnabled(),
        ]);
    }

    public function login(): JsonResponse
    {
        if (! $this->demoService->isEnabled()) {
            throw new NotFoundHttpException();
        }

        try {
            $response = $this->demoService->loginAsDemo();
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }

        return response()->json($response->toArray(), 200);
    }
}
