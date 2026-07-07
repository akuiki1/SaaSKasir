import DashboardController from './DashboardController'
import TokoController from './TokoController'
import AdminController from './AdminController'
const SuperAdmin = {
    DashboardController: Object.assign(DashboardController, DashboardController),
TokoController: Object.assign(TokoController, TokoController),
AdminController: Object.assign(AdminController, AdminController),
}

export default SuperAdmin