import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\LanggananController::index
 * @see app/Http/Controllers/Admin/LanggananController.php:20
 * @route '/admin/langganan'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/langganan',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\LanggananController::index
 * @see app/Http/Controllers/Admin/LanggananController.php:20
 * @route '/admin/langganan'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\LanggananController::index
 * @see app/Http/Controllers/Admin/LanggananController.php:20
 * @route '/admin/langganan'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\LanggananController::index
 * @see app/Http/Controllers/Admin/LanggananController.php:20
 * @route '/admin/langganan'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\LanggananController::index
 * @see app/Http/Controllers/Admin/LanggananController.php:20
 * @route '/admin/langganan'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\LanggananController::index
 * @see app/Http/Controllers/Admin/LanggananController.php:20
 * @route '/admin/langganan'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\LanggananController::index
 * @see app/Http/Controllers/Admin/LanggananController.php:20
 * @route '/admin/langganan'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
const LanggananController = { index }

export default LanggananController