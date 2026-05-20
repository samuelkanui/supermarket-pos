<?php

namespace App\Http\Responses\Concerns;

use Illuminate\Support\Facades\URL;

trait RedirectsToCurrentTeam
{
    protected function redirectPathForCurrentTeam($request, string $redirect): string
    {
        $team = $this->currentTeam($request);
        $user = $request->user();
        $tenant = $user?->tenant;

        URL::defaults(['current_team' => $team->slug]);

        if ($tenant) {
            $host = $request->getHost();
            $port = $request->getPort();
            $centralDomain = env('APP_CENTRAL_DOMAIN', 'supermarket-pos.test');

            // If not already on the correct subdomain, redirect to it
            if (!str_starts_with($host, $tenant->subdomain . '.')) {
                $scheme = $request->isSecure() ? 'https' : 'http';
                $portStr = ($port && !in_array($port, [80, 443])) ? ':' . $port : '';

                return "{$scheme}://{$tenant->subdomain}.{$centralDomain}{$portStr}/{$team->slug}{$redirect}";
            }
        }

        return "/{$team->slug}{$redirect}";
    }

    protected function currentTeam($request)
    {
        $user = $request->user();
        $team = $user?->currentTeam ?? $user?->personalTeam();

        if (! $team) {
            abort(403);
        }

        return $team;
    }
}
