<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->get('/up');

        $response->assertStatus(200);
    }

    public function test_root_redirects_to_products(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/products');
    }
}
