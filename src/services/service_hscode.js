
import {of_getDiscountedPrice} from "../pages/utils";
import {of_getData, of_get} from './service_base'
import * as utils from '../pages/utils';



export function getHsCode(hs_search, token) {
         return of_getData("/PHP/hscode_finder_AI.php?description=" + hs_search, `Bearer ${token}`)
}


 
