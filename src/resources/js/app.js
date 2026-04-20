import Alpine from 'alpinejs'

import { productList }    from './components/product-list.js'
import { productDetail }  from './components/product-detail.js'
import { stockDashboard } from './components/stock-dashboard.js'

Alpine.data('productList',    productList)
Alpine.data('productDetail',  productDetail)
Alpine.data('stockDashboard', stockDashboard)

Alpine.start()
