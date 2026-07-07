import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::index
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:31
 * @route '/superadmin/toko'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/superadmin/toko',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::index
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:31
 * @route '/superadmin/toko'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::index
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:31
 * @route '/superadmin/toko'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::index
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:31
 * @route '/superadmin/toko'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\SuperAdmin\TokoController::index
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:31
 * @route '/superadmin/toko'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\SuperAdmin\TokoController::index
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:31
 * @route '/superadmin/toko'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\SuperAdmin\TokoController::index
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:31
 * @route '/superadmin/toko'
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
* @see \App\Http\Controllers\SuperAdmin\TokoController::updateStatus
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:126
 * @route '/superadmin/toko/{toko}/status'
 */
export const updateStatus = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateStatus.url(args, options),
    method: 'put',
})

updateStatus.definition = {
    methods: ["put"],
    url: '/superadmin/toko/{toko}/status',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::updateStatus
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:126
 * @route '/superadmin/toko/{toko}/status'
 */
updateStatus.url = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions) => {
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

    return updateStatus.definition.url
            .replace('{toko}', parsedArgs.toko.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::updateStatus
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:126
 * @route '/superadmin/toko/{toko}/status'
 */
updateStatus.put = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateStatus.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\SuperAdmin\TokoController::updateStatus
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:126
 * @route '/superadmin/toko/{toko}/status'
 */
    const updateStatusForm = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateStatus.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\SuperAdmin\TokoController::updateStatus
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:126
 * @route '/superadmin/toko/{toko}/status'
 */
        updateStatusForm.put = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateStatus.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateStatus.form = updateStatusForm
/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::perpanjang
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:143
 * @route '/superadmin/toko/{toko}/langganan'
 */
export const perpanjang = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: perpanjang.url(args, options),
    method: 'post',
})

perpanjang.definition = {
    methods: ["post"],
    url: '/superadmin/toko/{toko}/langganan',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::perpanjang
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:143
 * @route '/superadmin/toko/{toko}/langganan'
 */
perpanjang.url = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions) => {
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

    return perpanjang.definition.url
            .replace('{toko}', parsedArgs.toko.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SuperAdmin\TokoController::perpanjang
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:143
 * @route '/superadmin/toko/{toko}/langganan'
 */
perpanjang.post = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: perpanjang.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\SuperAdmin\TokoController::perpanjang
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:143
 * @route '/superadmin/toko/{toko}/langganan'
 */
    const perpanjangForm = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: perpanjang.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\SuperAdmin\TokoController::perpanjang
 * @see app/Http/Controllers/SuperAdmin/TokoController.php:143
 * @route '/superadmin/toko/{toko}/langganan'
 */
        perpanjangForm.post = (args: { toko: number | { id_toko: number } } | [toko: number | { id_toko: number } ] | number | { id_toko: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: perpanjang.url(args, options),
            method: 'post',
        })
    
    perpanjang.form = perpanjangForm
const TokoController = { index, store, updateStatus, perpanjang }

export default TokoController