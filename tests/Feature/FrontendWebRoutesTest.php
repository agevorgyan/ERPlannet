<?php

namespace Tests\Feature;

use Tests\TestCase;

class FrontendWebRoutesTest extends TestCase
{
    public function test_frontend_root_is_accessible_on_same_port(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('ERPlannet');
        $response->assertSee('/css/app.css');
        $response->assertSee('/js/app.js');
        $response->assertSee('SERVER_INITIAL_DATA');
    }

    public function test_frontend_subroutes_are_accessible_on_same_port(): void
    {
        $views = [
            'dashboard',
            'pos',
            'inventory',
            'manufacturing',
            'quality',
            'delivery',
            'users',
            'roles',
            'billing',
            'settings',
            'api-console',
        ];

        foreach ($views as $view) {
            $response = $this->get("/{$view}");
            $response->assertStatus(200);
            $response->assertSee('ERPlannet');
        }
    }

    public function test_api_routes_remain_functional_and_distinct(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'healthy',
        ]);
    }
}
