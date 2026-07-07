import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\SuperAdmin\AdminController::index
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:27
 * @route '/superadmin/admins'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/superadmin/admins',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SuperAdmin\AdminController::index
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:27
 * @route '/superadmin/admins'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SuperAdmin\AdminController::index
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:27
 * @route '/superadmin/admins'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SuperAdmin\AdminController::index
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:27
 * @route '/superadmin/admins'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\SuperAdmin\AdminController::index
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:27
 * @route '/superadmin/admins'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\SuperAdmin\AdminController::index
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:27
 * @route '/superadmin/admins'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\SuperAdmin\AdminController::index
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:27
 * @route '/superadmin/admins'
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
* @see \App\Http\Controllers\SuperAdmin\AdminController::resetPassword
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:68
 * @route '/superadmin/admins/{user}/password'
 */
export const resetPassword = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: resetPassword.url(args, options),
    method: 'put',
})

resetPassword.definition = {
    methods: ["put"],
    url: '/superadmin/admins/{user}/password',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\SuperAdmin\AdminController::resetPassword
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:68
 * @route '/superadmin/admins/{user}/password'
 */
resetPassword.url = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { user: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    user: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        user: typeof args.user === 'object'
                ? args.user.id
                : args.user,
                }

    return resetPassword.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SuperAdmin\AdminController::resetPassword
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:68
 * @route '/superadmin/admins/{user}/password'
 */
resetPassword.put = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: resetPassword.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\SuperAdmin\AdminController::resetPassword
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:68
 * @route '/superadmin/admins/{user}/password'
 */
    const resetPasswordForm = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: resetPassword.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\SuperAdmin\AdminController::resetPassword
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:68
 * @route '/superadmin/admins/{user}/password'
 */
        resetPasswordForm.put = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: resetPassword.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    resetPassword.form = resetPasswordForm
const AdminController = { index, resetPassword }

export default AdminController