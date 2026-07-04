import DashboardController from './DashboardController'
import UserController from './UserController'
import OnboardingController from './OnboardingController'
import LaporanController from './LaporanController'
const Admin = {
    DashboardController: Object.assign(DashboardController, DashboardController),
UserController: Object.assign(UserController, UserController),
OnboardingController: Object.assign(OnboardingController, OnboardingController),
LaporanController: Object.assign(LaporanController, LaporanController),
}

export default Admin