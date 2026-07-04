import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\PesananPublikController::store
 * @see app/Http/Controllers/PesananPublikController.php:27
 * @route '/pesan'
 */
const storec5f8345b47743370610b9475cd933680 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storec5f8345b47743370610b9475cd933680.url(options),
    method: 'post',
})

storec5f8345b47743370610b9475cd933680.definition = {
    methods: ["post"],
    url: '/pesan',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PesananPublikController::store
 * @see app/Http/Controllers/PesananPublikController.php:27
 * @route '/pesan'
 */
storec5f8345b47743370610b9475cd933680.url = (options?: RouteQueryOptions) => {
    return storec5f8345b47743370610b9475cd933680.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PesananPublikController::store
 * @see app/Http/Controllers/PesananPublikController.php:27
 * @route '/pesan'
 */
storec5f8345b47743370610b9475cd933680.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storec5f8345b47743370610b9475cd933680.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\PesananPublikController::store
 * @see app/Http/Controllers/PesananPublikController.php:27
 * @route '/pesan'
 */
    const storec5f8345b47743370610b9475cd933680Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storec5f8345b47743370610b9475cd933680.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\PesananPublikController::store
 * @see app/Http/Controllers/PesananPublikController.php:27
 * @route '/pesan'
 */
        storec5f8345b47743370610b9475cd933680Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storec5f8345b47743370610b9475cd933680.url(options),
            method: 'post',
        })
    
    storec5f8345b47743370610b9475cd933680.form = storec5f8345b47743370610b9475cd933680Form
    /**
* @see \App\Http\Controllers\PesananPublikController::store
 * @see app/Http/Controllers/PesananPublikController.php:27
 * @route '/toko/{tokoSlug}/pesan'
 */
const store9af5f1b26d3e35677a9cfa25928c3da7 = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store9af5f1b26d3e35677a9cfa25928c3da7.url(args, options),
    method: 'post',
})

store9af5f1b26d3e35677a9cfa25928c3da7.definition = {
    methods: ["post"],
    url: '/toko/{tokoSlug}/pesan',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PesananPublikController::store
 * @see app/Http/Controllers/PesananPublikController.php:27
 * @route '/toko/{tokoSlug}/pesan'
 */
store9af5f1b26d3e35677a9cfa25928c3da7.url = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return store9af5f1b26d3e35677a9cfa25928c3da7.definition.url
            .replace('{tokoSlug}', parsedArgs.tokoSlug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PesananPublikController::store
 * @see app/Http/Controllers/PesananPublikController.php:27
 * @route '/toko/{tokoSlug}/pesan'
 */
store9af5f1b26d3e35677a9cfa25928c3da7.post = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store9af5f1b26d3e35677a9cfa25928c3da7.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\PesananPublikController::store
 * @see app/Http/Controllers/PesananPublikController.php:27
 * @route '/toko/{tokoSlug}/pesan'
 */
    const store9af5f1b26d3e35677a9cfa25928c3da7Form = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store9af5f1b26d3e35677a9cfa25928c3da7.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\PesananPublikController::store
 * @see app/Http/Controllers/PesananPublikController.php:27
 * @route '/toko/{tokoSlug}/pesan'
 */
        store9af5f1b26d3e35677a9cfa25928c3da7Form.post = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store9af5f1b26d3e35677a9cfa25928c3da7.url(args, options),
            method: 'post',
        })
    
    store9af5f1b26d3e35677a9cfa25928c3da7.form = store9af5f1b26d3e35677a9cfa25928c3da7Form

/**
* Multiple routes resolve to \App\Http\Controllers\PesananPublikController::store, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `store['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const store = {
    '/pesan': storec5f8345b47743370610b9475cd933680,
    '/toko/{tokoSlug}/pesan': store9af5f1b26d3e35677a9cfa25928c3da7,
}

/**
* @see \App\Http\Controllers\PesananPublikController::lacak
 * @see app/Http/Controllers/PesananPublikController.php:72
 * @route '/lacak-pesanan'
 */
const lacak5b97058098ca4ae0e5267d44c45540c4 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: lacak5b97058098ca4ae0e5267d44c45540c4.url(options),
    method: 'post',
})

lacak5b97058098ca4ae0e5267d44c45540c4.definition = {
    methods: ["post"],
    url: '/lacak-pesanan',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PesananPublikController::lacak
 * @see app/Http/Controllers/PesananPublikController.php:72
 * @route '/lacak-pesanan'
 */
lacak5b97058098ca4ae0e5267d44c45540c4.url = (options?: RouteQueryOptions) => {
    return lacak5b97058098ca4ae0e5267d44c45540c4.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PesananPublikController::lacak
 * @see app/Http/Controllers/PesananPublikController.php:72
 * @route '/lacak-pesanan'
 */
lacak5b97058098ca4ae0e5267d44c45540c4.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: lacak5b97058098ca4ae0e5267d44c45540c4.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\PesananPublikController::lacak
 * @see app/Http/Controllers/PesananPublikController.php:72
 * @route '/lacak-pesanan'
 */
    const lacak5b97058098ca4ae0e5267d44c45540c4Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: lacak5b97058098ca4ae0e5267d44c45540c4.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\PesananPublikController::lacak
 * @see app/Http/Controllers/PesananPublikController.php:72
 * @route '/lacak-pesanan'
 */
        lacak5b97058098ca4ae0e5267d44c45540c4Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: lacak5b97058098ca4ae0e5267d44c45540c4.url(options),
            method: 'post',
        })
    
    lacak5b97058098ca4ae0e5267d44c45540c4.form = lacak5b97058098ca4ae0e5267d44c45540c4Form
    /**
* @see \App\Http\Controllers\PesananPublikController::lacak
 * @see app/Http/Controllers/PesananPublikController.php:72
 * @route '/toko/{tokoSlug}/lacak-pesanan'
 */
const lacakd91795e98041f8d41e770950c6fded80 = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: lacakd91795e98041f8d41e770950c6fded80.url(args, options),
    method: 'post',
})

lacakd91795e98041f8d41e770950c6fded80.definition = {
    methods: ["post"],
    url: '/toko/{tokoSlug}/lacak-pesanan',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PesananPublikController::lacak
 * @see app/Http/Controllers/PesananPublikController.php:72
 * @route '/toko/{tokoSlug}/lacak-pesanan'
 */
lacakd91795e98041f8d41e770950c6fded80.url = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return lacakd91795e98041f8d41e770950c6fded80.definition.url
            .replace('{tokoSlug}', parsedArgs.tokoSlug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PesananPublikController::lacak
 * @see app/Http/Controllers/PesananPublikController.php:72
 * @route '/toko/{tokoSlug}/lacak-pesanan'
 */
lacakd91795e98041f8d41e770950c6fded80.post = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: lacakd91795e98041f8d41e770950c6fded80.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\PesananPublikController::lacak
 * @see app/Http/Controllers/PesananPublikController.php:72
 * @route '/toko/{tokoSlug}/lacak-pesanan'
 */
    const lacakd91795e98041f8d41e770950c6fded80Form = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: lacakd91795e98041f8d41e770950c6fded80.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\PesananPublikController::lacak
 * @see app/Http/Controllers/PesananPublikController.php:72
 * @route '/toko/{tokoSlug}/lacak-pesanan'
 */
        lacakd91795e98041f8d41e770950c6fded80Form.post = (args: { tokoSlug: string | number } | [tokoSlug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: lacakd91795e98041f8d41e770950c6fded80.url(args, options),
            method: 'post',
        })
    
    lacakd91795e98041f8d41e770950c6fded80.form = lacakd91795e98041f8d41e770950c6fded80Form

/**
* Multiple routes resolve to \App\Http\Controllers\PesananPublikController::lacak, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `lacak['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const lacak = {
    '/lacak-pesanan': lacak5b97058098ca4ae0e5267d44c45540c4,
    '/toko/{tokoSlug}/lacak-pesanan': lacakd91795e98041f8d41e770950c6fded80,
}

const PesananPublikController = { store, lacak }

export default PesananPublikController