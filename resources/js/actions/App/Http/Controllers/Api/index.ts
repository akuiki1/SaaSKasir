import AuthController from './AuthController'
import ProdukController from './ProdukController'
import TransaksiController from './TransaksiController'
const Api = {
    AuthController: Object.assign(AuthController, AuthController),
ProdukController: Object.assign(ProdukController, ProdukController),
TransaksiController: Object.assign(TransaksiController, TransaksiController),
}

export default Api