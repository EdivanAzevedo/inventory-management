import Alpine from 'alpinejs'

import { authLogin }    from './components/auth-login.js'
import { appHeader }    from './components/app-header.js'
import { productList }  from './components/product-list.js'
import { productDetail } from './components/product-detail.js'
import { stockDashboard } from './components/stock-dashboard.js'

Alpine.data('authLogin',    authLogin)
Alpine.data('appHeader',    appHeader)
Alpine.data('productList',  productList)
Alpine.data('productDetail', productDetail)
Alpine.data('stockDashboard', stockDashboard)

Alpine.start()
