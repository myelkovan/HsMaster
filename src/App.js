import './assets/scss/themes.scss';
import Route from './Routes';
import React, { useEffect } from 'react';
import './App.css';
import { DepositProvider } from './pages/DepositContext';




function App() {
    return (
        <React.Fragment>
            <DepositProvider>
            <Route />
            </DepositProvider>
        </React.Fragment>
    );
}

export default App;


