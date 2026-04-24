import React, { useState, useEffect, useMemo } from 'react';
import { Link } from 'react-router-dom';
import api from '../../api';
import { Plus, Edit, Trash2, Eye, AppWindow, Globe, Search } from 'lucide-react';
import Modal from '../../components/Modal';
import Table from '../../components/Table';

const AplikasiIndex = () => {
    const [aplikasi, setAplikasi] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [editingAplikasi, setEditingAplikasi] = useState(null);
    const [searchTerm, setSearchTerm] = useState('');
    const [formData, setFormData] = useState({
        nama: '',
        deskripsi: '',
        tipe: 'monolith',
        ip_local: '',
        url_service: '',
        url_repository: '',
        url_api_docs: '',
    });

    useEffect(() => {
        fetchAplikasi();
    }, []);

    const fetchAplikasi = async () => {
        try {
            setLoading(true);
            const res = await api.get('/aplikasi');
            setAplikasi(res.data.data);
        } catch (error) {

        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            if (editingAplikasi) {
                await api.put(`/aplikasi/${editingAplikasi.id_aplikasi}`, formData);
            } else {
                await api.post('/aplikasi', formData);
            }
            setShowModal(false);
            setEditingAplikasi(null);
            resetForm();
            fetchAplikasi();
        } catch (error) {

        }
    };

    const resetForm = () => {
        setFormData({
            nama: '',
            deskripsi: '',
            tipe: 'monolith',
            ip_local: '',
            url_service: '',
            url_repository: '',
            url_api_docs: '',
        });
    };

    const handleEdit = (app) => {
        setEditingAplikasi(app);
        setFormData({
            nama: app.nama,
            deskripsi: app.deskripsi || '',
            tipe: app.tipe || 'monolith',
            ip_local: app.ip_local || '',
            url_service: app.url_service || '',
            url_repository: app.url_repository || '',
            url_api_docs: app.url_api_docs || '',
        });
        setShowModal(true);
    };

    const handleDelete = async (id) => {
        if (window.confirm('Are you sure you want to delete this aplikasi?')) {
            try {
                await api.delete(`/aplikasi/${id}`);
                fetchAplikasi();
            } catch (error) {

            }
        }
    };

    const filteredData = useMemo(() => aplikasi.filter(app => 
        (app.nama || '').toLowerCase().includes(searchTerm.toLowerCase()) ||
        (app.tipe || '').toLowerCase().includes(searchTerm.toLowerCase())
    ), [aplikasi, searchTerm]);

    const columns = [
        { 
            header: 'Application', 
            render: (row) => (
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <AppWindow className="w-5 h-5" />
                    </div>
                    <div>
                        <div className="font-bold text-gray-800">{row.nama}</div>
                        <div className="text-xs text-gray-400 truncate max-w-[150px]">{row.deskripsi}</div>
                    </div>
                </div>
            )
        },
        { 
            header: 'Type', 
            render: (row) => (
                <span className={`px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider ${
                    row.tipe === 'microservice' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'
                }`}>
                    {row.tipe}
                </span>
            )
        },
        { 
            header: 'Endpoint', 
            render: (row) => (
                <div className="flex items-center gap-2 text-sm text-blue-600 font-medium">
                    <Globe className="w-3.5 h-3.5" />
                    <span className="truncate max-w-[150px]">{row.url_service || '-'}</span>
                </div>
            )
        },
        { 
            header: 'IP Local', 
            key: 'ip_local',
            className: 'font-mono text-xs text-gray-500'
        },
        { 
            header: 'Actions', 
            className: 'text-right',
            render: (row) => (
                <div className="flex justify-end gap-2">
                    <Link to={`/aplikasi/${row.id_aplikasi}`} className="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all">
                        <Eye className="w-5 h-5" />
                    </Link>
                    <button onClick={() => handleEdit(row)} className="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all">
                        <Edit className="w-5 h-5" />
                    </button>
                    <button onClick={() => handleDelete(row.id_aplikasi)} className="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                        <Trash2 className="w-5 h-5" />
                    </button>
                </div>
            )
        }
    ];

    return (
        <div className="space-y-6">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div>
                    <h2 className="text-2xl font-extrabold text-gray-800 tracking-tight">Application Inventory</h2>
                    <p className="text-gray-500 text-sm">Manage monolith and microservice applications</p>
                </div>
                <div className="flex items-center gap-3">
                    <div className="relative">
                        <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                        <input 
                            type="text" 
                            placeholder="Search applications..." 
                            className="pl-10 pr-4 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none w-64"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                    </div>
                    <button
                        onClick={() => {
                            setEditingAplikasi(null);
                            resetForm();
                            setShowModal(true);
                        }}
                        className="bg-blue-600 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 font-bold text-sm"
                    >
                        <Plus className="w-5 h-5" />
                        New App
                    </button>
                </div>
            </div>

            <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <Table 
                    columns={columns}
                    data={filteredData}
                    loading={loading}
                    emptyMessage="No applications found. Add your first application to get started."
                />
            </div>

            <Modal 
                isOpen={showModal} 
                onClose={() => setShowModal(false)}
                title={editingAplikasi ? 'Edit Application' : 'Create New Application'}
                footer={
                    <>
                        <button onClick={() => setShowModal(false)} className="px-6 py-2 text-gray-500 hover:text-gray-700 font-medium">Cancel</button>
                        <button onClick={handleSubmit} className="px-8 py-2.5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">
                            {editingAplikasi ? 'Update Application' : 'Create Application'}
                        </button>
                    </>
                }
            >
                <form className="grid grid-cols-2 gap-6">
                    <div className="space-y-2 col-span-2">
                        <label className="text-xs font-bold text-gray-400 uppercase tracking-wider">Application Name</label>
                        <input
                            required
                            className="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-gray-700"
                            placeholder="e.g. Corporate Dashboard"
                            value={formData.nama}
                            onChange={(e) => setFormData({...formData, nama: e.target.value})}
                        />
                    </div>
                    <div className="space-y-2">
                        <label className="text-xs font-bold text-gray-400 uppercase tracking-wider">Type</label>
                        <select
                            className="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-gray-700 appearance-none"
                            value={formData.tipe}
                            onChange={(e) => setFormData({...formData, tipe: e.target.value})}
                        >
                            <option value="monolith">Monolith</option>
                            <option value="microservice">Microservice</option>
                        </select>
                    </div>
                    <div className="space-y-2">
                        <label className="text-xs font-bold text-gray-400 uppercase tracking-wider">IP Local</label>
                        <input
                            className="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-gray-700"
                            placeholder="192.168.1.1"
                            value={formData.ip_local}
                            onChange={(e) => setFormData({...formData, ip_local: e.target.value})}
                        />
                    </div>
                    <div className="space-y-2 col-span-2">
                        <label className="text-xs font-bold text-gray-400 uppercase tracking-wider">Description</label>
                        <textarea
                            className="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-gray-700"
                            rows="2"
                            placeholder="Briefly describe what this application does..."
                            value={formData.deskripsi}
                            onChange={(e) => setFormData({...formData, deskripsi: e.target.value})}
                        />
                    </div>
                    <div className="space-y-2 col-span-2">
                        <label className="text-xs font-bold text-gray-400 uppercase tracking-wider">Service URL (Health Check Endpoint)</label>
                        <input
                            className="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-gray-700"
                            placeholder="https://api.example.com/health"
                            value={formData.url_service}
                            onChange={(e) => setFormData({...formData, url_service: e.target.value})}
                        />
                    </div>
                    <div className="space-y-2">
                        <label className="text-xs font-bold text-gray-400 uppercase tracking-wider">Repository URL</label>
                        <input
                            className="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-gray-700 font-mono text-sm"
                            placeholder="https://github.com/..."
                            value={formData.url_repository}
                            onChange={(e) => setFormData({...formData, url_repository: e.target.value})}
                        />
                    </div>
                    <div className="space-y-2">
                        <label className="text-xs font-bold text-gray-400 uppercase tracking-wider">API Docs URL</label>
                        <input
                            className="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-gray-700 font-mono text-sm"
                            placeholder="https://docs.example.com"
                            value={formData.url_api_docs}
                            onChange={(e) => setFormData({...formData, url_api_docs: e.target.value})}
                        />
                    </div>
                </form>
            </Modal>
        </div>
    );
};

export default AplikasiIndex;
