<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantIdentify
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $parts = explode('.', $host);

        $subdomain = null;
        
        $centralDomains = [
            'localhost',
            '127.0.0.1',
            env('APP_CENTRAL_DOMAIN', 'supermarket-pos.test'),
            'yourpos.com',
        ];

        // If the host is not one of our base central domains, we resolve the tenant
        if (!in_array($host, $centralDomains)) {
            // Check if it's a subdomain of any central domain
            foreach ($centralDomains as $centralDomain) {
                if (str_ends_with($host, '.' . $centralDomain)) {
                    $subdomain = str_replace('.' . $centralDomain, '', $host);
                    break;
                }
            }

            // If it's a custom main domain (e.g. naivaspos.com) instead of a subdomain
            if (!$subdomain) {
                $tenant = Tenant::where('domain', $host)->first();
                if ($tenant) {
                    app()->instance('tenant', $tenant);
                }
            }
        }

        if ($subdomain) {
            $tenant = Tenant::where('subdomain', $subdomain)->first();
            
            if (!$tenant) {
                abort(404, 'Supermarket store not found.');
            }

            // Bind the active tenant into the container
            app()->instance('tenant', $tenant);
        }

        return $next($request);
    }
}
