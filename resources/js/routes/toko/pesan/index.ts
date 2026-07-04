import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\PesananPublikController::store
 * @see app/Http/Controllers/PesananPublikController.php:27
 * @route '/toko/{tokoSlug}/pesan'
 */
export const store = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/toko/{tokoSlug}/pesan',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PesananPublikController::store
 * @see app/Http/Controllers/PesananPublikController.php:27
 * @route '/toko/{tokoSlug}/pesan'
 */
store.url = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return store.definition.url
            .replace('{tokoSlug}', parsedArgs.tokoSlug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PesananPublikController::store
 * @see app/Http/Controllers/PesananPublikController.php:27
 * @route '/toko/{tokoSlug}/pesan'
 */
store.post = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\PesananPublikController::store
 * @see app/Http/Controllers/PesananPublikController.php:27
 * @route '/toko/{tokoSlug}/pesan'
 */
    const storeForm = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\PesananPublikController::store
 * @see app/Http/Controllers/PesananPublikController.php:27
 * @route '/toko/{tokoSlug}/pesan'
 */
        storeForm.post = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(args, options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\PesananPublikController::lacak
 * @see app/Http/Controllers/PesananPublikController.php:72
 * @route '/toko/{tokoSlug}/lacak-pesanan'
 */
export const lacak = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: lacak.url(args, options),
    method: 'post',
})

lacak.definition = {
    methods: ["post"],
    url: '/toko/{tokoSlug}/lacak-pesanan',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PesananPublikController::lacak
 * @see app/Http/Controllers/PesananPublikController.php:72
 * @route '/toko/{tokoSlug}/lacak-pesanan'
 */
lacak.url = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return lacak.definition.url
            .replace('{tokoSlug}', parsedArgs.tokoSlug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PesananPublikController::lacak
 * @see app/Http/Controllers/PesananPublikController.php:72
 * @route '/toko/{tokoSlug}/lacak-pesanan'
 */
lacak.post = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: lacak.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\PesananPublikController::lacak
 * @see app/Http/Controllers/PesananPublikController.php:72
 * @route '/toko/{tokoSlug}/lacak-pesanan'
 */
    const lacakForm = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: lacak.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\PesananPublikController::lacak
 * @see app/Http/Controllers/PesananPublikController.php:72
 * @route '/toko/{tokoSlug}/lacak-pesanan'
 */
        lacakForm.post = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: lacak.url(args, options),
            method: 'post',
        })
    
    lacak.form = lacakForm
const pesan = {
    store: Object.assign(store, store),
lacak: Object.assign(lacak, lacak),
}

export default pesan