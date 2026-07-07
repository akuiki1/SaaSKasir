import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import tokoC9586a from './toko'
import adminsE6ba9a from './admins'
/**
* @see \App\Http\Controllers\SuperAdmin\DashboardController::dashboard
 * @see app/Http/Controllers/SuperAdmin/DashboardController.php:25
 * @route '/superadmin/dashboard'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/superadmin/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SuperAdmin\DashboardController::dashboard
 * @see app/Http/Controllers/SuperAdmin/DashboardController.php:25
 * @route '/superadmin/dashboard'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SuperAdmin\DashboardController::dashboard
 * @see app/Http/Controllers/SuperAdmin/DashboardController.php:25
 * @route '/superadmin/dashboard'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SuperAdmin\DashboardController::dashboard
 * @see app/Http/Controllers/SuperAdmin/DashboardController.php:25
 * @route '/superadmin/dashboard'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\SuperAdmin\DashboardController::dashboard
 * @see app/Http/Controllers/SuperAdmin/DashboardController.php:25
 * @route '/superadmin/dashboard'
 */
    const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\SuperAdmin\DashboardController::dashboard
 * @see app/Http/Controllers/SuperAdmin/DashboardController.php:25
 * @route '/superadmin/dashboard'
 */
        dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\SuperAdmin\DashboardController::dashboard
 * @see app/Http/Controllers/SuperAdmin/DashboardController.php:25
 * @route '/superadmin/dashboard'
 */
        dashboardForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dashboard.form = dashboardForm
/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::toko
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:31
 * @route '/superadmin/toko'
 */
export const toko = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: toko.url(options),
    method: 'get',
})

toko.definition = {
    methods: ["get","head"],
    url: '/superadmin/toko',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::toko
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:31
 * @route '/superadmin/toko'
 */
toko.url = (options?: RouteQueryOptions) => {
    return toko.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::toko
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:31
 * @route '/superadmin/toko'
 */
toko.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: toko.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::toko
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:31
 * @route '/superadmin/toko'
 */
toko.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: toko.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\SuperAdmin\TokoController::toko
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:31
 * @route '/superadmin/toko'
 */
    const tokoForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: toko.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\SuperAdmin\TokoController::toko
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:31
 * @route '/superadmin/toko'
 */
        tokoForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: toko.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\SuperAdmin\TokoController::toko
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:31
 * @route '/superadmin/toko'
 */
        tokoForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: toko.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    toko.form = tokoForm
/**
* @see \App\Http\Controllers\SuperAdmin\AdminController::admins
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:27
 * @route '/superadmin/admins'
 */
export const admins = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: admins.url(options),
    method: 'get',
})

admins.definition = {
    methods: ["get","head"],
    url: '/superadmin/admins',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SuperAdmin\AdminController::admins
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:27
 * @route '/superadmin/admins'
 */
admins.url = (options?: RouteQueryOptions) => {
    return admins.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SuperAdmin\AdminController::admins
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:27
 * @route '/superadmin/admins'
 */
admins.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: admins.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SuperAdmin\AdminController::admins
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:27
 * @route '/superadmin/admins'
 */
admins.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: admins.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\SuperAdmin\AdminController::admins
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:27
 * @route '/superadmin/admins'
 */
    const adminsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: admins.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\SuperAdmin\AdminController::admins
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:27
 * @route '/superadmin/admins'
 */
        adminsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: admins.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\SuperAdmin\AdminController::admins
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:27
 * @route '/superadmin/admins'
 */
        adminsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: admins.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    admins.form = adminsForm
const superadmin = {
    dashboard: Object.assign(dashboard, dashboard),
toko: Object.assign(toko, tokoC9586a),
admins: Object.assign(admins, adminsE6ba9a),
}

export default superadmin