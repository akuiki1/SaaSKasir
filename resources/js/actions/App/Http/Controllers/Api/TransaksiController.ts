import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\TransaksiController::store
 * @see app/Http/Controllers/Api/TransaksiController.php:23
 * @route '/api/transaksi'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/transaksi',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\TransaksiController::store
 * @see app/Http/Controllers/Api/TransaksiController.php:23
 * @route '/api/transaksi'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\TransaksiController::store
 * @see app/Http/Controllers/Api/TransaksiController.php:23
 * @route '/api/transaksi'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\TransaksiController::store
 * @see app/Http/Controllers/Api/TransaksiController.php:23
 * @route '/api/transaksi'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\TransaksiController::store
 * @see app/Http/Controllers/Api/TransaksiController.php:23
 * @route '/api/transaksi'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
const TransaksiController = { store }

export default TransaksiController