<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Verify that the Vue application shell is rendered.
     *
     * @return void
     */
    public function test_home_page_renders_vue_application()
    {
        $response = $this->get('/');

        $response
            ->assertStatus(200)
            ->assertSee('<hello-world></hello-world>', false);
    }
}
