import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Ceo\DashboardController::dashboard
 * @see app/Http/Controllers/Ceo/DashboardController.php:28
 * @route '/ceo/dashboard'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/ceo/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Ceo\DashboardController::dashboard
 * @see app/Http/Controllers/Ceo/DashboardController.php:28
 * @route '/ceo/dashboard'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Ceo\DashboardController::dashboard
 * @see app/Http/Controllers/Ceo/DashboardController.php:28
 * @route '/ceo/dashboard'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Ceo\DashboardController::dashboard
 * @see app/Http/Controllers/Ceo/DashboardController.php:28
 * @route '/ceo/dashboard'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Ceo\DashboardController::dashboard
 * @see app/Http/Controllers/Ceo/DashboardController.php:28
 * @route '/ceo/dashboard'
 */
    const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Ceo\DashboardController::dashboard
 * @see app/Http/Controllers/Ceo/DashboardController.php:28
 * @route '/ceo/dashboard'
 */
        dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Ceo\DashboardController::dashboard
 * @see app/Http/Controllers/Ceo/DashboardController.php:28
 * @route '/ceo/dashboard'
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
const ceo = {
    dashboard: Object.assign(dashboard, dashboard),
}

export default ceo