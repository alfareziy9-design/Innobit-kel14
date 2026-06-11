<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WriterMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->canWriteArticles()) {
            abort(403, 'Halaman ini hanya untuk admin dan penulis.');
        }

        return $next($request);
    }
}
