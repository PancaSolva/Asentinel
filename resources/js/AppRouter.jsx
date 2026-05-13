import React, { Suspense, lazy } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Layout from './layouts/Layout';
import UserLayout from './layouts/UserLayout';
import ProtectedRoute from './components/ProtectedRoute';
import { getStoredUser } from './utils/auth';

// Lazy-load pages for code splitting
const Dashboard = lazy(() => import('./pages/Dashboard'));
const AplikasiIndex = lazy(() => import('./pages/Aplikasi/Index'));
const AplikasiDetail = lazy(() => import('./pages/Aplikasi/Detail'));
const ServiceIndex = lazy(() => import('./pages/Services/Index'));
const UserIndex = lazy(() => import('./pages/Users/Index'));
const Login = lazy(() => import('./pages/Login2'));

// User pages
const UserDashboard = lazy(() => import('./pages/UserDashboard'));
const UserAplikasiIndex = lazy(() => import('./pages/Aplikasi/UserIndex'));
const UserAplikasiDetail = lazy(() => import('./pages/Aplikasi/UserDetail'));
const UserMonitoringLogs = lazy(() => import('./pages/MonitoringLogs/UserIndex'));

const PageLoader = () => (
    <div className="flex items-center justify-center min-h-[400px]">
        <div className="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
);

/**
 * RoleRoute - Guards routes based on user role.
 * If user doesn't have the required role, redirects to appropriate dashboard.
 */
const RoleRoute = ({ children, allowedRoles }) => {
    const user = getStoredUser();
    const userRole = user.role || 'user';
    
    if (!allowedRoles.includes(userRole)) {
        // Redirect to correct dashboard based on actual role
        if (userRole === 'admin') {
            return <Navigate to="/" replace />;
        }
        return <Navigate to="/user-dashboard" replace />;
    }
    return children;
};

/**
 * RootRedirect - Handles the root "/" path.
 * Redirects authenticated users to their role-specific dashboard.
 */
const RootRedirect = () => {
    const token = localStorage.getItem('token');
    if (!token) {
        return <Navigate to="/login" replace />;
    }
    
    const user = getStoredUser();
    const userRole = user.role || 'user';
    
    if (userRole === 'admin') {
        // Admin stays at "/" which renders inside admin layout
        return <Dashboard />;
    }
    // Non-admin users always go to user-dashboard
    return <Navigate to="/user-dashboard" replace />;
};

const AppRouter = () => {
    return (
        <Router>
            <Suspense fallback={<PageLoader />}>
                <Routes>
                    {/* Public Route */}
                    <Route path="/login" element={<Login />} />
                    
                    {/* Protected Routes */}
                    <Route element={<ProtectedRoute />}>
                        
                        {/* Admin Routes - only accessible by admin role */}
                        <Route element={<RoleRoute allowedRoles={['admin']}><Layout /></RoleRoute>}>
                            <Route index element={<RootRedirect />} />
                            <Route path="aplikasi" element={<AplikasiIndex />} />
                            <Route path="aplikasi/:id" element={<AplikasiDetail />} />
                            <Route path="services" element={<ServiceIndex />} />
                            <Route path="users" element={<UserIndex />} />
                        </Route>

                        {/* User Routes - only accessible by user role */}
                        <Route element={<RoleRoute allowedRoles={['user']}><UserLayout /></RoleRoute>}>
                            <Route path="user-dashboard" element={<UserDashboard />} />
                            <Route path="user-aplikasi" element={<UserAplikasiIndex />} />
                            <Route path="user-aplikasi/:id" element={<UserAplikasiDetail />} />
                            <Route path="user-monitoring-logs" element={<UserMonitoringLogs />} />
                        </Route>

                    </Route>
                    
                    {/* Final Fallback - redirect to login */}
                    <Route path="*" element={<Navigate to="/login" replace />} />
                </Routes>
            </Suspense>
        </Router>
    );
};

export default AppRouter;
