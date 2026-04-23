import React, { useState, useEffect, useMemo } from 'react';
import api from '../../api';
import { Plus, Edit, Trash2, Server, AppWindow, Search, Globe, Link as LinkIcon } from 'lucide-react';
import Modal from '../../components/Modal';
import Table from '../../components/Table';

const ServiceIndex = () => {
    const [services, setServices] = useState([]);
    const [aplikasis, setAplikasis] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [editingService, setEditingService] = useState(null);
    const [searchTerm, setSearchTerm] = useState('');
    const [formData, setFormData] = useState({
        id_aplikasi: '',
        nama: '',
        type_service: 'backend',
        ip_local: '',
        url_service: '',
        url_repository: '',
        url_api_docs: '',
    });

    useEffect(() => {
        const fetchData = async () => {
            try {
                setLoading(true);
                const [servicesRes, aplikasiRes] = await Promise.all([
                    api.get('/services'),
                    api.get('/aplikasi')
                ]);
                setServices(servicesRes.data.data);
                setAplikasis(aplikasiRes.data.data.filter(app => app.tipe === 'microservice'));
            } catch (error) {
                console.error('Error fetching data:', error);
            } finally {
                setLoading(false);
            }
        };
        fetchData();
    }, []);

    const fetchServices = async () => {
        try {
            setLoading(true);
            const res = await api.get('/services');
            setServices(res.data.data);
        } catch (error) {

        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            if (editingService) {
                await api.put(`/services/${editingService.id_service}`, formData);
            } else {
                await api.post('/services', formData);
            }
            setShowModal(false);
            setEditingService(null);
            resetForm();
            fetchServices();
        } catch (error) {

        }
    };

    const resetForm = () => {
        setFormData({
            id_aplikasi: '',
            nama: '',
            type_service: 'backend',
            ip_local: '',
            url_service: '',
            url_repository: '',
            url_api_docs: '',
        });
    };

    const handleEdit = (service) => {
        setEditingService(service);
        setFormData({
            id_aplikasi: service.id_aplikasi,
            nama: service.nama,
            type_service: service.type_service || 'backend',
            ip_local: service.ip_local || '',
            url_service: service.url_service || '',
            url_repository: service.url_repository || '',
            url_api_docs: service.url_api_docs || '',
        });
        setShowModal(true);
    };

    const handleDelete = async (id) => {
        if (window.confirm('Are you sure you want to delete this service?')) {
            try {
                await api.delete(`/services/${id}`);
                fetchServices();
            } catch (error) {

            }
        }
    };

    const filteredData = useMemo(() => services.filter(service => 
        (service.nama || '').toLowerCase().includes(searchTerm.toLowerCase()) ||
        (service.aplikasi?.nama || '').toLowerCase().includes(searchTerm.toLowerCase())
    ), [services, searchTerm]);

    const columns = [
        { 
            header: 'Service', 
            render: (row) => (
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                        <Server className="w-5 h-5" />
                    </div>
                    <div>
                        <div className="font-bold text-gray-800">{row.nama}</div>
                        <div className="flex items-center gap-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-tight">
                            <AppWindow className="w-3 h-3" />
                            {row.aplikasi?.nama}
                        </div>
                    </div>
                </div>
            )
        },
        { 
            header: 'Type', 
            render: (row) => (
                <span className={`px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider ${
                    row.type_service === 'frontend' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700'
                }`}>
                    {row.type_service}
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
            header: 'Actions', 
            className: 'text-right',
            render: (row) => (
                <div className="flex justify-end gap-2">
                    <button onClick={() => handleEdit(row)} className="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all">
                        <Edit className="w-5 h-5" />
                    </button>
                    <button onClick={() => handleDelete(row.id_service)} className="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
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
                    <h2 className="text-2xl font-extrabold text-gray-800 tracking-tight">Service Management</h2>
                    <p className="text-gray-500 text-sm">Microservices and internal components</p>
                </div>
                <div className="flex items-center gap-3">
                    <div className="relative">
                        <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                        <input 
                            type="text" 
                            placeholder="Search services..." 
                            className="pl-10 pr-4 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-purple-500 outline-none w-64"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                    </div>
                    <button
                        onClick={() => {
                            setEditingService(null);
                            resetForm();
                            setShowModal(true);
                        }}
                        disabled={aplikasis.length === 0}
                        className="bg-purple-600 text-white px-5 py-2.5 rounded-xl flex items-center gap-2 hover:bg-purple-700 transition-all shadow-lg shadow-purple-200 font-bold text-sm disabled:bg-gray-300 disabled:shadow-none"
                    >
                        <Plus className="w-5 h-5" />
                        New Service
                    </button>
                </div>
            </div>

            <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <Table 
                    columns={columns}
                    data={filteredData}
                    loading={loading}
                    emptyMessage={aplikasis.length === 0 ? "No microservices available to add services to." : "No services found."}
                />
            </div>

            <Modal 
                isOpen={showModal} 
                onClose={() => setShowModal(false)}
                title={editingService ? 'Edit Service' : 'Create New Service'}
                footer={
                    <>
                        <button onClick={() => setShowModal(false)} className="px-6 py-2 text-gray-500 hover:text-gray-700 font-medium">Cancel</button>
                        <button onClick={handleSubmit} className="px-8 py-2.5 bg-purple-600 text-white rounded-xl font-bold hover:bg-purple-700 transition-all shadow-lg shadow-purple-100">
                            {editingService ? 'Update Service' : 'Create Service'}
                        </button>
                    </>
                }
            >
                <form className="grid grid-cols-2 gap-6">
                    <div className="space-y-2 col-span-2">
                        <label className="text-xs font-bold text-gray-400 uppercase tracking-wider">Parent Microservice</label>
                        <select
                            required
                            className="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 outline-none text-gray-700 appearance-none"
                            value={formData.id_aplikasi}
                            onChange={(e) => setFormData({...formData, id_aplikasi: e.target.value})}
                        >
                            <option value="">Select a Microservice</option>
                            {aplikasis.map(app => (
                                <option key={app.id_aplikasi} value={app.id_aplikasi}>{app.nama}</option>
                            ))}
                        </select>
                    </div>
                    <div className="space-y-2 col-span-2">
                        <label className="text-xs font-bold text-gray-400 uppercase tracking-wider">Service Name</label>
                        <input
                            required
                            className="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 outline-none text-gray-700"
                            placeholder="e.g. Authentication API"
                            value={formData.nama}
                            onChange={(e) => setFormData({...formData, nama: e.target.value})}
                        />
                    </div>
                    <div className="space-y-2">
                        <label className="text-xs font-bold text-gray-400 uppercase tracking-wider">Service Type</label>
                        <select
                            className="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 outline-none text-gray-700 appearance-none"
                            value={formData.type_service}
                            onChange={(e) => setFormData({...formData, type_service: e.target.value})}
                        >
                            <option value="frontend">Frontend</option>
                            <option value="backend">Backend</option>
                        </select>
                    </div>
                    <div className="space-y-2">
                        <label className="text-xs font-bold text-gray-400 uppercase tracking-wider">IP Local</label>
                        <input
                            className="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 outline-none text-gray-700"
                            placeholder="192.168.1.5"
                            value={formData.ip_local}
                            onChange={(e) => setFormData({...formData, ip_local: e.target.value})}
                        />
                    </div>
                    <div className="space-y-2 col-span-2">
                        <label className="text-xs font-bold text-gray-400 uppercase tracking-wider">Health Check URL</label>
                        <input
                            className="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 outline-none text-gray-700"
                            placeholder="https://auth.api.example.com/health"
                            value={formData.url_service}
                            onChange={(e) => setFormData({...formData, url_service: e.target.value})}
                        />
                    </div>
                    <div className="space-y-2">
                        <label className="text-xs font-bold text-gray-400 uppercase tracking-wider">Repository URL</label>
                        <input
                            className="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 outline-none text-gray-700 font-mono text-sm"
                            placeholder="https://github.com/..."
                            value={formData.url_repository}
                            onChange={(e) => setFormData({...formData, url_repository: e.target.value})}
                        />
                    </div>
                    <div className="space-y-2">
                        <label className="text-xs font-bold text-gray-400 uppercase tracking-wider">API Docs URL</label>
                        <input
                            className="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-purple-500 outline-none text-gray-700 font-mono text-sm"
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

export default ServiceIndex;
