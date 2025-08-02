
import {of_getData, of_get} from './service_base'


export const getRestrictedProductSearch = async (description,originCountry,destinationCountry,token) => {
    try {
        const encodedDescription = encodeURIComponent(description); // Güvenli hale getir
        const encodedoriginCountry = encodeURIComponent(originCountry); // Güvenli hale getir
        const encodeddestinationCountry = encodeURIComponent(destinationCountry); // Güvenli hale getir

        return  of_getData(`/PHP/restrictedProduct_finder.php?product_description=${encodedDescription}&from=${encodedoriginCountry}&to=${encodeddestinationCountry}`, token)

    } catch (error) {
        console.error("API Hatası:", error);
        return { error: "Veri alınamadı", details: error.message };
    }
};
