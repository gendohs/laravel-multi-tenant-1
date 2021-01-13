<?php

namespace EJLab\Laravel\MultiTenant\Middleware;

use App\Models\System\Tenant;
use EJLab\Laravel\MultiTenant\DatabaseManager;

use Closure;
use DB;

class HandleTenantConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $parameters = $request->route()->parameters();
        if (isset($parameters[config('elmt.tenant-id-parameter', 'domain')])) {
            $tenant = Tenant::where(config('elmt.tenant-id-column', 'domain'), $parameters[config('elmt.tenant-id-parameter', 'domain')])->get()->first();
            if ($tenant) {
                $manager = new DatabaseManager();
                $manager->setConnection($tenant);
                DB::setDefaultConnection($manager->tenantConnectionName);
                
                return $next($request);
            }
        }

        return abort(404);
    }
}
