import React from 'react';
import { Navigate } from "react-router-dom";

const AdminRoute = ({ children, isAdmin }) => {
    return isAdmin ? children : <Navigate to="/DefaultPage" />;
};

export { AdminRoute };
