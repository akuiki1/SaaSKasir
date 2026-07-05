import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
export const index = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/toko/{tokoSlug}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
index.url = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { tokoSlug: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    tokoSlug: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        tokoSlug: args.tokoSlug,
                }

    return index.definition.url
            .replace('{tokoSlug}', parsedArgs.tokoSlug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
index.get = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
index.head = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
    const indexForm = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
        indexForm.get = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
        indexForm.head = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
const HomeController = { index }

export default HomeController