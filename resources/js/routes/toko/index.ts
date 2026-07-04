import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
import pesan from './pesan'
/**
* @see \App\Http\Controllers\HomeController::home
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
export const home = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: home.url(args, options),
    method: 'get',
})

home.definition = {
    methods: ["get","head"],
    url: '/toko/{tokoSlug}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\HomeController::home
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
home.url = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return home.definition.url
            .replace('{tokoSlug}', parsedArgs.tokoSlug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\HomeController::home
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
home.get = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: home.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\HomeController::home
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
home.head = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: home.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\HomeController::home
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
    const homeForm = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: home.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\HomeController::home
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
        homeForm.get = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: home.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\HomeController::home
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
        homeForm.head = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: home.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    home.form = homeForm
const toko = {
    home: Object.assign(home, home),
pesan: Object.assign(pesan, pesan),
}

export default toko