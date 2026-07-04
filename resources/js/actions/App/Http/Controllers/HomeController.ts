import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/'
 */
const index980bb49ee7ae63891f1d891d2fbcf1c9 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
    method: 'get',
})

index980bb49ee7ae63891f1d891d2fbcf1c9.definition = {
    methods: ["get","head"],
    url: '/',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/'
 */
index980bb49ee7ae63891f1d891d2fbcf1c9.url = (options?: RouteQueryOptions) => {
    return index980bb49ee7ae63891f1d891d2fbcf1c9.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/'
 */
index980bb49ee7ae63891f1d891d2fbcf1c9.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/'
 */
index980bb49ee7ae63891f1d891d2fbcf1c9.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/'
 */
    const index980bb49ee7ae63891f1d891d2fbcf1c9Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/'
 */
        index980bb49ee7ae63891f1d891d2fbcf1c9Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/'
 */
        index980bb49ee7ae63891f1d891d2fbcf1c9Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index980bb49ee7ae63891f1d891d2fbcf1c9.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index980bb49ee7ae63891f1d891d2fbcf1c9.form = index980bb49ee7ae63891f1d891d2fbcf1c9Form
    /**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
const index74efd0608d5ade35c92b26c672e97d9f = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index74efd0608d5ade35c92b26c672e97d9f.url(args, options),
    method: 'get',
})

index74efd0608d5ade35c92b26c672e97d9f.definition = {
    methods: ["get","head"],
    url: '/toko/{tokoSlug}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
index74efd0608d5ade35c92b26c672e97d9f.url = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return index74efd0608d5ade35c92b26c672e97d9f.definition.url
            .replace('{tokoSlug}', parsedArgs.tokoSlug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
index74efd0608d5ade35c92b26c672e97d9f.get = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index74efd0608d5ade35c92b26c672e97d9f.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
index74efd0608d5ade35c92b26c672e97d9f.head = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index74efd0608d5ade35c92b26c672e97d9f.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
    const index74efd0608d5ade35c92b26c672e97d9fForm = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index74efd0608d5ade35c92b26c672e97d9f.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
        index74efd0608d5ade35c92b26c672e97d9fForm.get = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index74efd0608d5ade35c92b26c672e97d9f.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\HomeController::index
 * @see app/Http/Controllers/HomeController.php:18
 * @route '/toko/{tokoSlug}'
 */
        index74efd0608d5ade35c92b26c672e97d9fForm.head = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index74efd0608d5ade35c92b26c672e97d9f.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index74efd0608d5ade35c92b26c672e97d9f.form = index74efd0608d5ade35c92b26c672e97d9fForm

/**
* Multiple routes resolve to \App\Http\Controllers\HomeController::index, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `index['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const index = {
    '/': index980bb49ee7ae63891f1d891d2fbcf1c9,
    '/toko/{tokoSlug}': index74efd0608d5ade35c92b26c672e97d9f,
}

const HomeController = { index }

export default HomeController