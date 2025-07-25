import {of_getData, of_get, of_update} from './service_base'


export function getCountry() {
    return of_getData("/PHP/country_select.php", "", true)
}

export function getFlag(countryName) {
    return getCountry().then(countries => {
            const country = countries.find(c => c.name === countryName);
                if (country) {
                    return country.flag;
                } else {
                    throw new Error("Country not found");
                }
            }).catch(error => {console.error(error); return null;
        });
}


export function getCarrier() {
    return of_getData("/PHP/carrier_select.php", "", true)
}


export function getStatus() {
    return of_getData("/PHP/status_select.php", "", true)
}


export function getCancelReason(props) {
    return [
        { key: 1, value: props.t("Damaged or broken") },
        { key: 2, value: props.t("Wrong item") },
        { key: 3, value: props.t("Customer changed mind") },
        { key: 4, value: props.t("Arrived late") },
        { key: 5, value: props.t("Customer requested") },
        { key: 6, value: props.t("Prohibited item") },
        { key: 7, value: props.t("Item not for sale") },
        { key: 15, value: props.t("Other") }
    ];
}
