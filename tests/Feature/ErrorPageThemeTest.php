<?php

namespace Tests\Feature;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ErrorPageThemeTest extends TestCase
{
    public function test_error_pages_render_without_database_queries_or_built_assets(): void
    {
        \Illuminate\Support\Facades\DB::shouldReceive('connection')->never();
        foreach ([403, 404, 419, 429, 500, 503] as $code) {
            $this->view('errors.'.$code)->assertSee((string) $code)->assertSee('FanHub Plus')
                ->assertSee('Back to home')->assertSee('data-theme="dark"', false)->assertDontSee('/build/assets/');
        }
        $this->view('errors.4xx', ['exception' => new HttpException(405)])->assertSee('405');
        $this->view('errors.5xx', ['exception' => new HttpException(502)])->assertSee('502');
        $this->view('errors.503', ['message' => '<script>unsafe</script> Maintenance window'])
            ->assertSee('Maintenance window')->assertDontSee('<script>unsafe</script>', false);
    }
}
