import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\SikasirController::landing
 * @see app/Http/Controllers/SikasirController.php:21
 * @route '/'
 */
export const landing = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: landing.url(options),
    method: 'get',
})

landing.definition = {
    methods: ["get","head"],
    url: '/',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SikasirController::landing
 * @see app/Http/Controllers/SikasirController.php:21
 * @route '/'
 */
landing.url = (options?: RouteQueryOptions) => {
    return landing.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SikasirController::landing
 * @see app/Http/Controllers/SikasirController.php:21
 * @route '/'
 */
landing.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: landing.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SikasirController::landing
 * @see app/Http/Controllers/SikasirController.php:21
 * @route '/'
 */
landing.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: landing.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\SikasirController::landing
 * @see app/Http/Controllers/SikasirController.php:21
 * @route '/'
 */
    const landingForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: landing.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\SikasirController::landing
 * @see app/Http/Controllers/SikasirController.php:21
 * @route '/'
 */
        landingForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: landing.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\SikasirController::landing
 * @see app/Http/Controllers/SikasirController.php:21
 * @route '/'
 */
        landingForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: landing.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    landing.form = landingForm
const SikasirController = { landing }

export default SikasirController