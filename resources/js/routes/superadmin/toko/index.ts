import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::store
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:105
 * @route '/superadmin/toko'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/superadmin/toko',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::store
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:105
 * @route '/superadmin/toko'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::store
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:105
 * @route '/superadmin/toko'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\SuperAdmin\TokoController::store
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:105
 * @route '/superadmin/toko'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\SuperAdmin\TokoController::store
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:105
 * @route '/superadmin/toko'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::status
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:126
 * @route '/superadmin/toko/{toko}/status'
 */
export const status = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: status.url(args, options),
    method: 'put',
})

status.definition = {
    methods: ["put"],
    url: '/superadmin/toko/{toko}/status',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::status
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:126
 * @route '/superadmin/toko/{toko}/status'
 */
status.url = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { toko: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id_toko' in args) {
            args = { toko: args.id_toko }
        }
    
    if (Array.isArray(args)) {
        args = {
                    toko: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        toko: typeof args.toko === 'object'
                ? args.toko.id_toko
                : args.toko,
                }

    return status.definition.url
            .replace('{toko}', parsedArgs.toko.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::status
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:126
 * @route '/superadmin/toko/{toko}/status'
 */
status.put = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: status.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\SuperAdmin\TokoController::status
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:126
 * @route '/superadmin/toko/{toko}/status'
 */
    const statusForm = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: status.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\SuperAdmin\TokoController::status
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:126
 * @route '/superadmin/toko/{toko}/status'
 */
        statusForm.put = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: status.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    status.form = statusForm
/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::langganan
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:143
 * @route '/superadmin/toko/{toko}/langganan'
 */
export const langganan = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: langganan.url(args, options),
    method: 'post',
})

langganan.definition = {
    methods: ["post"],
    url: '/superadmin/toko/{toko}/langganan',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::langganan
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:143
 * @route '/superadmin/toko/{toko}/langganan'
 */
langganan.url = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { toko: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id_toko' in args) {
            args = { toko: args.id_toko }
        }
    
    if (Array.isArray(args)) {
        args = {
                    toko: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        toko: typeof args.toko === 'object'
                ? args.toko.id_toko
                : args.toko,
                }

    return langganan.definition.url
            .replace('{toko}', parsedArgs.toko.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::langganan
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:143
 * @route '/superadmin/toko/{toko}/langganan'
 */
langganan.post = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: langganan.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\SuperAdmin\TokoController::langganan
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:143
 * @route '/superadmin/toko/{toko}/langganan'
 */
    const langgananForm = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: langganan.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\SuperAdmin\TokoController::langganan
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:143
 * @route '/superadmin/toko/{toko}/langganan'
 */
        langgananForm.post = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: langganan.url(args, options),
            method: 'post',
        })
    
    langganan.form = langgananForm
const toko = {
    store: Object.assign(store, store),
status: Object.assign(status, status),
langganan: Object.assign(langganan, langganan),
}

export default toko