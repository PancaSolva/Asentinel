import React, { useState } from 'react';
import { Outlet, Link, useLocation, useNavigate } from 'react-router-dom';
import { LayoutDashboard, AppWindow, Server, Activity, LogOut, User, ChevronDown, AlertTriangle, Users } from 'lucide-react';
import api from '../api';
import Modal from '../components/Modal';

const Layout = () => {
    const location = useLocation();
    const navigate = useNavigate();
    const [showProfileDropdown, setShowProfileDropdown] = useState(false);
    const [showLogoutConfirm, setShowLogoutConfirmation] = useState(false);
    
    const user = JSON.parse(localStorage.getItem('user') || '{}');

    const handleLogout = async () => {
        try {
            await api.post('/logout');
        } catch (error) {
            // Silently handle - we're logging out anyway
        } finally {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            navigate('/login');
        }
    };

    const navItems = [
        { name: 'Dashboard', path: '/', icon: <LayoutDashboard className="w-5 h-5" /> },
        { name: 'Aplikasi', path: '/aplikasi', icon: <AppWindow className="w-5 h-5" /> },
        { name: 'Services', path: '/services', icon: <Server className="w-5 h-5" /> },
        ...(user.role === 'admin' ? [{ name: 'User Management', path: '/users', icon: <Users className="w-5 h-5" /> }] : []),
    ];

    const getPageTitle = () => {
        const item = navItems.find(item => item.path === location.pathname);
        if (item) return item.name;
        if (location.pathname.startsWith('/aplikasi/')) return 'Aplikasi Detail';
        return 'Page';
    };

    return (
        <div className="flex min-h-screen bg-gray-50">

            <aside className="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col">
                <div className="p-6 border-b border-gray-100 flex items-center gap-2">
                    <Activity className="w-8 h-8 text-blue-600" />
                    <span className="text-xl font-bold text-gray-800">Asentinel</span>
                </div>
                <nav className="flex-1 p-4 space-y-2">
                    {navItems.map((item) => (
                        <Link
                            key={item.path}
                            to={item.path}
                            className={`flex items-center gap-3 px-4 py-3 rounded-lg transition-colors ${
                                location.pathname === item.path || (item.path === '/aplikasi' && location.pathname.startsWith('/aplikasi/'))
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'text-gray-600 hover:bg-gray-50'
                            }`}
                        >
                            {item.icon}
                            <span className="font-medium">{item.name}</span>
                        </Link>
                    ))}
                </nav>
            </aside>


            <main className="flex-1 flex flex-col min-w-0 overflow-hidden">
                <header className="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8">
                    <h1 className="text-lg font-semibold text-gray-800">
                        {getPageTitle()}
                    </h1>
                    <div className="flex items-center gap-4 relative">
                        <div 
                            className="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded-xl transition-all"
                            onClick={() => setShowProfileDropdown(!showProfileDropdown)}
                        >
                            <div className="text-right hidden sm:block">
                                <p className="text-sm font-bold text-gray-800 leading-none">{user.name || 'Admin'}</p>
                                <p className="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Administrator</p>
                            </div>
                            <div className="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold shadow-lg shadow-blue-100">
                                {user.name ? user.name.charAt(0) : 'A'}
                            </div>
                            <ChevronDown className={`w-4 h-4 text-gray-400 transition-transform ${showProfileDropdown ? 'rotate-180' : ''}`} />
                        </div>


                        {showProfileDropdown && (
                            <>
                                <div className="fixed inset-0 z-10" onClick={() => setShowProfileDropdown(false)}></div>
                                <div className="absolute right-0 top-14 w-64 bg-white rounded-2xl shadow-2xl border border-gray-100 py-2 z-20 animate-in fade-in slide-in-from-top-2 duration-200">
                                    <div className="px-4 py-3 border-b border-gray-50">
                                        <p className="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Administrator Profile</p>
                                        <p className="text-sm font-bold text-gray-800 truncate">{user.name || 'Admin User'}</p>
                                        <p className="text-[11px] text-gray-500 truncate">{user.email || 'admin@asentinel.com'}</p>
                                    </div>
                                    <div className="p-2">
                                        <div className="px-3 py-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Account Actions</div>
                                        <button 
                                            onClick={() => {
                                                setShowProfileDropdown(false);
                                                setShowLogoutConfirmation(true);
                                            }}
                                            className="w-full flex items-center gap-3 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors mt-1 font-bold"
                                        >
                                            <LogOut className="w-4 h-4" />
                                            Logout System
                                        </button>
                                    </div>
                                </div>
                            </>
                        )}
                    </div>
                </header>
                <div className="flex-1 overflow-auto p-8">
                    <Outlet />
                </div>
            </main>


            <Modal
                isOpen={showLogoutConfirm}
                onClose={() => setShowLogoutConfirmation(false)}
                title="Confirm Logout"
                footer={
                    <>
                        <button 
                            onClick={() => setShowLogoutConfirmation(false)}
                            className="px-6 py-2 text-gray-500 hover:text-gray-700 font-medium"
                        >
                            Cancel
                        </button>
                        <button 
                            onClick={handleLogout}
                            className="px-8 py-2.5 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 transition-all shadow-lg shadow-red-100"
                        >
                            Yes, Log Out
                        </button>
                    </>
                }
            >
                <div className="flex flex-col items-center text-center space-y-4 py-4">
                    <div className="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center">
                        <AlertTriangle className="w-8 h-8" />
                    </div>
                    <div>
                        <h4 className="text-xl font-bold text-gray-800">Yakin mau log out?</h4>
                        <p className="text-gray-500 mt-2">Sesi Anda akan diakhiri dan Anda perlu login kembali untuk mengakses dashboard.</p>
                    </div>
                </div>
            </Modal>
        </div>
    );
};

export default Layout;
