import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\LaporanWaController::preview
 * @see app/Http/Controllers/Admin/LaporanWaController.php:21
 * @route '/admin/laporan-wa/preview'
 */
export const preview = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preview.url(options),
    method: 'get',
})

preview.definition = {
    methods: ["get","head"],
    url: '/admin/laporan-wa/preview',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\LaporanWaController::preview
 * @see app/Http/Controllers/Admin/LaporanWaController.php:21
 * @route '/admin/laporan-wa/preview'
 */
preview.url = (options?: RouteQueryOptions) => {
    return preview.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\LaporanWaController::preview
 * @see app/Http/Controllers/Admin/LaporanWaController.php:21
 * @route '/admin/laporan-wa/preview'
 */
preview.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preview.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\LaporanWaController::preview
 * @see app/Http/Controllers/Admin/LaporanWaController.php:21
 * @route '/admin/laporan-wa/preview'
 */
preview.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: preview.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\LaporanWaController::preview
 * @see app/Http/Controllers/Admin/LaporanWaController.php:21
 * @route '/admin/laporan-wa/preview'
 */
    const previewForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: preview.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\LaporanWaController::preview
 * @see app/Http/Controllers/Admin/LaporanWaController.php:21
 * @route '/admin/laporan-wa/preview'
 */
        previewForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: preview.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\LaporanWaController::preview
 * @see app/Http/Controllers/Admin/LaporanWaController.php:21
 * @route '/admin/laporan-wa/preview'
 */
        previewForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: preview.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    preview.form = previewForm
const laporanWa = {
    preview: Object.assign(preview, preview),
}

export default laporanWa