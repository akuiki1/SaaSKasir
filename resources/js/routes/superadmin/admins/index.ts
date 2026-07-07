import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\SuperAdmin\AdminController::password
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:68
 * @route '/superadmin/admins/{user}/password'
 */
export const password = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: password.url(args, options),
    method: 'put',
})

password.definition = {
    methods: ["put"],
    url: '/superadmin/admins/{user}/password',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\SuperAdmin\AdminController::password
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:68
 * @route '/superadmin/admins/{user}/password'
 */
password.url = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return password.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SuperAdmin\AdminController::password
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:68
 * @route '/superadmin/admins/{user}/password'
 */
password.put = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: password.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\SuperAdmin\AdminController::password
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:68
 * @route '/superadmin/admins/{user}/password'
 */
    const passwordForm = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: password.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\SuperAdmin\AdminController::password
 * @see app/Http/Controllers/SuperAdmin/AdminController.php:68
 * @route '/superadmin/admins/{user}/password'
 */
        passwordForm.put = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: password.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    password.form = passwordForm
const admins = {
    password: Object.assign(password, password),
}

export default admins