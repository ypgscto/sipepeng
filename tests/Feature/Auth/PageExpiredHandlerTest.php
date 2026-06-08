<?php

namespace Tests\Feature\Auth;

use App\Support\Http\PageExpiredResponse;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PageExpiredHandlerTest extends TestCase
{
    public function test_matches_token_mismatch_and_http_419(): void
    {
        $this->assertTrue(PageExpiredResponse::matches(new TokenMismatchException()));
        $this->assertTrue(PageExpiredResponse::matches(new HttpException(419, 'Page Expired')));
        $this->assertFalse(PageExpiredResponse::matches(new HttpException(404, 'Not Found')));
    }

    public function test_resolves_json_response_for_api_clients(): void
    {
        $request = Request::create('/admin/penelitian', 'POST');
        $request->headers->set('Accept', 'application/json');

        $response = PageExpiredResponse::resolve($request, new HttpException(419, 'Page Expired'));

        $this->assertSame(419, $response->getStatusCode());
        $this->assertStringContainsString('Sesi habis', $response->getContent());
    }
}
