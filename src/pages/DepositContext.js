import React, { createContext, useState, useContext } from 'react';
import {getDeposit} from "../services/service_deposit";
import {NotificationContainer, NotificationManager} from 'react-notifications';
import 'react-notifications/lib/notifications.css';


// 1. Create a Context
const DepositContext = createContext();

// 2. Create a custom hook to access context values
export const useDeposit = () => {
    return useContext(DepositContext);  // This will be used in components to access the deposit state
};


// 3. Create a provider component
export const DepositProvider = ({ children }) => {
    const [depositAmount, setDepositAmount] = useState(0);  // Default deposit amount is 0

    //bu function disarida cagrilacak, deposit okunup degiskene azilacak
    const refreshDeposit = () => {
        const authuser = sessionStorage.getItem("authUser")
        const user = JSON.parse(authuser);
        
        if (user){
            getDeposit(user.id, user.token).then((response) => {
                setDepositAmount(response[0].deposit || 0)
            }).catch(error => NotificationManager.error(error, "", 4000));
        }
    };

    // Provide the state and the updater function to the context
    return (
        <DepositContext.Provider value={{ depositAmount, refreshDeposit }}>
            {children}
            <NotificationContainer/>
        </DepositContext.Provider>
           
    );
};
