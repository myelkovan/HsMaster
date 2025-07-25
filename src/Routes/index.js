import React from 'react';
import { Routes, Route } from "react-router-dom";

//Layouts
import NonAuthLayout from "../Layouts/NonAuthLayout";
import VerticalLayout from "../Layouts/index";

//routes
import { authProtectedRoutes, publicRoutes,adminRoutes } from "./allRoutes";
import { AuthProtected } from './AuthProtected';
import { AdminRoute } from './AdminRoute';




const Index = () => {
     const authUser = sessionStorage.getItem("authUser");
   const user = authUser ? JSON.parse(authUser) : null;
   let isAdmin = false
   if (user && Number(user.permission_level) > 1) {isAdmin = true}

    return (
        <React.Fragment>
            <Routes>
                <Route>
                    {publicRoutes.map((route, idx) => (
                        <Route
                            path={route.path}
                            element={
                                <NonAuthLayout>
                                    {route.component}
                                </NonAuthLayout>
                            }
                            key={idx}
                            exact={true}
                        />
                    ))}
                </Route>

                <Route>
                    {authProtectedRoutes.map((route, idx) => (
                        <Route
                            path={route.path}
                            element={
                                <AuthProtected>
                                    <VerticalLayout>{route.component}</VerticalLayout>
                                </AuthProtected>}
                            key={idx}
                            exact={true}
                        />
                    ))}
                </Route>

                     {/* Admin Routes */}
                <Route>
                    {adminRoutes.map((route, idx) => (
                        <Route
                            path={route.path}
                            element={
                                <AdminRoute isAdmin={isAdmin}>
                                    <VerticalLayout>{route.component}</VerticalLayout>
                                </AdminRoute>
                            }
                            key={idx}
                            exact={true}
                        />
                    ))}
                </Route>


            </Routes>
        </React.Fragment>
    );
};

export default Index;