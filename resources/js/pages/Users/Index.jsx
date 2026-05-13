import React, { useState, useEffect } from 'react';
import api from '../../api';
import { Plus, Edit, Trash2, User, Mail, Shield, Search, Lock, CheckCircle, XCircle } from 'lucide-react';
import Modal from '../../components/Modal';
import Table from '../../components/Table';

const UserIndex = () => {
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [editingUser, setEditingUser] = useState(null);
    const [searchTerm, setSearchTerm] = useState('');
    const [notification, setNotification] = useState(null);
    const [saving, setSaving] = useState(false);
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        password: '',
        role: 'user',
    });

    useEffect(() => {
        fetchUsers();
    }, []);

    // Auto-dismiss notification after 4 seconds
    useEffect(() => {
        if (notification) {
            const timer = setTimeout(() => setNotification(null), 4000);
            return () => clearTimeout(timer);
        }
    }, [notification]);

    const fetchUsers = async () => {
        try {
            setLoading(true);
            const res = await api.get('/users');
            setUsers(res.data.data);
        } catch (error) {
            setNotification({ type: 'error', message: 'Failed to load users.' });
        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async (e) => {
        e?.preventDefault();
        
        // Client-side validation
        if (!formData.name.trim()) {
            setNotification({ type: 'error', message: 'Username is required.' });
            return;
        }
        if (!editingUser && !formData.password) {
            setNotification({ type: 'error', message: 'Password is required.' });
            return;
        }
        if (formData.password && formData.password.length < 8) {
            setNotification({ type: 'error', message: 'Password must be at least 8 characters.' });
            return;
        }

        try {
            setSaving(true);
            const payload = { ...formData };
            
            // Don't send empty password on edit
            if (editingUser && !payload.password) {
                delete payload.password;
            }
            // Don't send empty email
            if (!payload.email || !payload.email.trim()) {
                delete payload.email;
            }

            if (editingUser) {
                await api.put(`/users/${editingUser.id}`, payload);
                setNotification({ type: 'success', message: 'User updated successfully!' });
            } else {
                await api.post('/users', payload);
                setNotification({ type: 'success', message: 'User created successfully!' });
            }
            setShowModal(false);
            setEditingUser(null);
            resetForm();
            fetchUsers();
        } catch (error) {
            // Show the first error message from the backend
            const msg = error.response?.data?.message 
                || error.response?.data?.errors?.name?.[0]
                || error.response?.data?.errors?.email?.[0]
                || error.response?.data?.errors?.password?.[0]
                || 'Error saving user. Please check your input.';
            setNotification({ type: 'error', message: msg });
        } finally {
            setSaving(false);
        }
    };

    const resetForm = () => {
        setFormData({
            name: '',
            email: '',
            password: '',
            role: 'user',
        });
    };

    const handleEdit = (user) => {
        setEditingUser(user);
        setFormData({
            name: user.name,
            email: user.email || '',
            password: '',
            role: user.role || 'user',
        });
        setShowModal(true);
    };

    const handleDelete = async (id) => {
        if (window.confirm('Are you sure you want to delete this user?')) {
            try {
                await api.delete(`/users/${id}`);
                setNotification({ type: 'success', message: 'User deleted successfully!' });
                fetchUsers();
            } catch (error) {
                setNotification({ type: 'error', message: 'Failed to delete user.' });
            }
        }
    };


    const filteredData = users.filter(user => 
        user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        (user.email && user.email.toLowerCase().includes(searchTerm.toLowerCase()))
    );

    const columns = [
        { 
            header: 'User Profile', 
            render: (row) => (
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <User className="w-5 h-5" />
                    </div>
                    <div>
                        <div className="font-bold text-gray-800">{row.name}</div>
                        {row.email && (
                            <div className="text-xs text-gray-400 flex items-center gap-1">
                                <Mail className="w-3 h-3" />
                                {row.email}
                            </div>
                        )}
                    </div>
                </div>
            )
        },
        { 
            header: 'Role', 
            render: (row) => (
                <div className="flex items-center gap-2">
                    <Shield className={`w-3.5 h-3.5 ${row.role === 'admin' ? 'text-amber-500' : 'text-blue-500'}`} />
                    <span className={`px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider ${
                        row.role === 'admin' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700'
                    }`}>
                        {row.role}
                    </span>
                </div>
            )
        },
        { 
            header: 'Created At', 
            render: (row) => (
                <span className="text-xs text-gray-500">
                    {new Date(row.created_at).toLocaleDateString()}
                </span>
            )
        },
        { 
            header: 'Actions', 
            className: 'text-right',
            render: (row) => (
                <div className="flex justify-end gap-2">
                    <button onClick={() => handleEdit(row)} className="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all">
                        <Edit className="w-5 h-5" />
                    </button>
                    <button onClick={() => handleDelete(row.id)} className="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                        <Trash2 className="w-5 h-5" />
                    </button>
                </div>
            )
        }
    ];

    return (
        <div className="space-y-6">
            {/* Notification Toast */}
            {notification && (
                <div className={`fixed top-6 right-6 z-50 flex items-center gap-3 px-5 py-3 rounded-xl shadow-2xl text-sm font-bold transition-all duration-300 ${
                    notification.type === 'success' 
                        ? 'bg-green-600 text-white' 
                        : 'bg-red-600 text-white'
                }`}
                    style={{ animation: 'slideIn 0.3s ease-out' }}
                >
                    {notification.type === 'success' 
                        ? <CheckCircle className="w-5 h-5" /> 
                        : <XCircle className="w-5 h-5" />}
                    {notification.message}
                </div>
            )}
            
            <style>{`
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
            `}</style>

            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div>
                    <h2 className="text-2xl font-extrabold text-gray-800 tracking-tight">User Management</h2>
                    <p className="text-gray-500 text-sm">Create and manage system users</p>
                </div>
                <div className="flex items-center gap-3">
                    <div className="relative">
                        <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                        <input 
                            type="text" 
                            placeholder="Search users..." 
                            className="pl-10 pr-4 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none w-64"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                    </div>
                    <button
                        onClick={() => {
                            setEditingUser(null);
                            resetForm();
                            setShowModal(true);
                        }}
                        className="bg-blue-600 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 font-bold text-sm"
                    >
                        <Plus className="w-5 h-5" />
                        New User
                    </button>
                </div>
            </div>

            <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <Table 
                    columns={columns}
                    data={filteredData}
                    loading={loading}
                    emptyMessage="No users found."
                />
            </div>

            <Modal 
                isOpen={showModal} 
                onClose={() => setShowModal(false)}
                title={editingUser ? 'Edit User' : 'Add New User'}
                footer={
                    <div className="flex justify-end gap-3">
                        <button 
                            onClick={() => setShowModal(false)}
                            className="px-6 py-2.5 text-gray-500 hover:text-gray-700 font-medium text-sm transition-colors"
                        >
                            Cancel
                        </button>
                        <button 
                            onClick={handleSubmit}
                            disabled={saving}
                            className={`px-8 py-2.5 rounded-xl font-bold text-sm transition-all shadow-lg shadow-blue-200 ${
                                saving 
                                    ? 'bg-gray-400 text-white cursor-not-allowed' 
                                    : 'bg-blue-600 text-white hover:bg-blue-700'
                            }`}
                        >
                            {saving ? 'Saving...' : (editingUser ? 'Update User' : 'Create User')}
                        </button>
                    </div>
                }
            >
                <form onSubmit={handleSubmit} className="space-y-4 py-2">
                    <div className="space-y-1.5">
                        <label className="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1">
                            Username <span className="text-red-500">*</span>
                        </label>
                        <div className="relative group">
                            <div className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                                <User className="w-4 h-4" />
                            </div>
                            <input 
                                type="text" 
                                required
                                className="w-full bg-gray-50 border-none rounded-xl py-3 pl-11 pr-4 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all font-medium"
                                placeholder="Enter username (used for login)"
                                value={formData.name}
                                onChange={(e) => setFormData({...formData, name: e.target.value})}
                            />
                        </div>
                        <p className="text-[10px] text-gray-400 ml-1">This username will be used for login</p>
                    </div>

                    <div className="space-y-1.5">
                        <label className="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1">
                            Email Address <span className="text-gray-300">(Optional)</span>
                        </label>
                        <div className="relative group">
                            <div className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                                <Mail className="w-4 h-4" />
                            </div>
                            <input 
                                type="email" 
                                className="w-full bg-gray-50 border-none rounded-xl py-3 pl-11 pr-4 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all font-medium"
                                placeholder="user@example.com (optional)"
                                value={formData.email}
                                onChange={(e) => setFormData({...formData, email: e.target.value})}
                            />
                        </div>
                        <p className="text-[10px] text-gray-400 ml-1">Email is optional, for contact purposes only</p>
                    </div>

                    <div className="space-y-1.5">
                        <label className="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1">
                            {editingUser ? 'New Password (Leave blank to keep current)' : <>Password <span className="text-red-500">*</span></>}
                        </label>
                        <div className="relative group">
                            <div className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                                <Lock className="w-4 h-4" />
                            </div>
                            <input 
                                type="text" 
                                required={!editingUser}
                                className="w-full bg-gray-50 border-none rounded-xl py-3 pl-11 pr-4 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all font-medium"
                                placeholder="Min. 8 characters"
                                value={formData.password}
                                onChange={(e) => setFormData({...formData, password: e.target.value})}
                            />
                        </div>
                    </div>

                    <div className="space-y-1.5">
                        <label className="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1">
                            System Role <span className="text-red-500">*</span>
                        </label>
                        <div className="relative group">
                            <div className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                                <Shield className="w-4 h-4" />
                            </div>
                            <select 
                                className="w-full bg-gray-50 border-none rounded-xl py-3 pl-11 pr-4 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all font-medium appearance-none"
                                value={formData.role}
                                onChange={(e) => setFormData({...formData, role: e.target.value})}
                            >
                                <option value="user">User</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                    </div>
                </form>
            </Modal>
        </div>
    );
};

export default UserIndex;
