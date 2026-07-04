import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\KasirController::store
 * @see app/Http/Controllers/KasirController.php:347
 * @route '/kasir/transaksi'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/kasir/transaksi',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\KasirController::store
 * @see app/Http/Controllers/KasirController.php:347
 * @route '/kasir/transaksi'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\KasirController::store
 * @see app/Http/Controllers/KasirController.php:347
 * @route '/kasir/transaksi'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\KasirController::store
 * @see app/Http/Controllers/KasirController.php:347
 * @route '/kasir/transaksi'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\KasirController::store
 * @see app/Http/Controllers/KasirController.php:347
 * @route '/kasir/transaksi'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\KasirController::sync
 * @see app/Http/Controllers/KasirController.php:388
 * @route '/kasir/transaksi/sync'
 */
export const sync = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sync.url(options),
    method: 'post',
})

sync.definition = {
    methods: ["post"],
    url: '/kasir/transaksi/sync',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\KasirController::sync
 * @see app/Http/Controllers/KasirController.php:388
 * @route '/kasir/transaksi/sync'
 */
sync.url = (options?: RouteQueryOptions) => {
    return sync.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\KasirController::sync
 * @see app/Http/Controllers/KasirController.php:388
 * @route '/kasir/transaksi/sync'
 */
sync.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sync.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\KasirController::sync
 * @see app/Http/Controllers/KasirController.php:388
 * @route '/kasir/transaksi/sync'
 */
    const syncForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: sync.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\KasirController::sync
 * @see app/Http/Controllers/KasirController.php:388
 * @route '/kasir/transaksi/sync'
 */
        syncForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: sync.url(options),
            method: 'post',
        })
    
    sync.form = syncForm
const transaksi = {
    store: Object.assign(store, store),
sync: Object.assign(sync, sync),
}

export default transaksi