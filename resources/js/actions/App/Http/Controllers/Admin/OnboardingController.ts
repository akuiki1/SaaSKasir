import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\OnboardingController::index
 * @see app/Http/Controllers/Admin/OnboardingController.php:23
 * @route '/admin/onboarding'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/onboarding',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\OnboardingController::index
 * @see app/Http/Controllers/Admin/OnboardingController.php:23
 * @route '/admin/onboarding'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OnboardingController::index
 * @see app/Http/Controllers/Admin/OnboardingController.php:23
 * @route '/admin/onboarding'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\OnboardingController::index
 * @see app/Http/Controllers/Admin/OnboardingController.php:23
 * @route '/admin/onboarding'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\OnboardingController::index
 * @see app/Http/Controllers/Admin/OnboardingController.php:23
 * @route '/admin/onboarding'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\OnboardingController::index
 * @see app/Http/Controllers/Admin/OnboardingController.php:23
 * @route '/admin/onboarding'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\OnboardingController::index
 * @see app/Http/Controllers/Admin/OnboardingController.php:23
 * @route '/admin/onboarding'
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
* @see \App\Http\Controllers\Admin\OnboardingController::template
 * @see app/Http/Controllers/Admin/OnboardingController.php:33
 * @route '/admin/onboarding/template'
 */
export const template = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: template.url(options),
    method: 'get',
})

template.definition = {
    methods: ["get","head"],
    url: '/admin/onboarding/template',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\OnboardingController::template
 * @see app/Http/Controllers/Admin/OnboardingController.php:33
 * @route '/admin/onboarding/template'
 */
template.url = (options?: RouteQueryOptions) => {
    return template.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OnboardingController::template
 * @see app/Http/Controllers/Admin/OnboardingController.php:33
 * @route '/admin/onboarding/template'
 */
template.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: template.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\OnboardingController::template
 * @see app/Http/Controllers/Admin/OnboardingController.php:33
 * @route '/admin/onboarding/template'
 */
template.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: template.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\OnboardingController::template
 * @see app/Http/Controllers/Admin/OnboardingController.php:33
 * @route '/admin/onboarding/template'
 */
    const templateForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: template.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\OnboardingController::template
 * @see app/Http/Controllers/Admin/OnboardingController.php:33
 * @route '/admin/onboarding/template'
 */
        templateForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: template.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\OnboardingController::template
 * @see app/Http/Controllers/Admin/OnboardingController.php:33
 * @route '/admin/onboarding/template'
 */
        templateForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: template.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    template.form = templateForm
/**
* @see \App\Http\Controllers\Admin\OnboardingController::importMethod
 * @see app/Http/Controllers/Admin/OnboardingController.php:49
 * @route '/admin/onboarding/import'
 */
export const importMethod = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod.url(options),
    method: 'post',
})

importMethod.definition = {
    methods: ["post"],
    url: '/admin/onboarding/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\OnboardingController::importMethod
 * @see app/Http/Controllers/Admin/OnboardingController.php:49
 * @route '/admin/onboarding/import'
 */
importMethod.url = (options?: RouteQueryOptions) => {
    return importMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OnboardingController::importMethod
 * @see app/Http/Controllers/Admin/OnboardingController.php:49
 * @route '/admin/onboarding/import'
 */
importMethod.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\OnboardingController::importMethod
 * @see app/Http/Controllers/Admin/OnboardingController.php:49
 * @route '/admin/onboarding/import'
 */
    const importMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: importMethod.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\OnboardingController::importMethod
 * @see app/Http/Controllers/Admin/OnboardingController.php:49
 * @route '/admin/onboarding/import'
 */
        importMethodForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: importMethod.url(options),
            method: 'post',
        })
    
    importMethod.form = importMethodForm
const OnboardingController = { index, template, importMethod, import: importMethod }

export default OnboardingController