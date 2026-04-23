import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import axios from 'axios';
import { 
    ArrowLeft, 
    AppWindow, 
    Server, 
    Globe, 
    GitBranch, 
    FileText, 
    Activity,
    Clock
} from 'lucide-react';
import StatusBadge from '../../components/StatusBadge';
import Table from '../../components/Table';

const AplikasiDetail = () => {
    const { id } = useParams();
    const [aplikasi, setAplikasi] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchDetail();
    }, [id]);

    const fetchDetail = async () => {
        try {
            setLoading(true);
            const res = await axios.get(`/api/admin/aplikasi/${id}`);
            setAplikasi(res.data.data);
        } catch (error) {

        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center min-h-[400px]">
                <div className="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            </div>
        );
    }

    if (!aplikasi) {
        return (
            <div className="text-center py-20">
                <h2 className="text-2xl font-bold text-gray-800">Aplikasi not found</h2>
                <Link to="/aplikasi" className="text-blue-600 hover:underline mt-4 inline-block">Back to list</Link>
            </div>
        );
    }

    return (
        <div className="space-y-8">
            {/* Header */}
            <div className="flex items-center gap-4">
                <Link to="/aplikasi" className="p-2 hover:bg-white rounded-lg transition-colors shadow-sm border border-gray-100">
                    <ArrowLeft className="w-5 h-5 text-gray-600" />
                </Link>
                <div>
                    <h2 className="text-2xl font-bold text-gray-800">{aplikasi.nama}</h2>
                    <p className="text-gray-500 text-sm">Application Detail & Monitoring</p>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {/* Left Column: Info Cards */}
                <div className="lg:col-span-2 space-y-8">
                    {/* General Info */}
                    <div className="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                        <h3 className="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                            <AppWindow className="w-5 h-5 text-blue-600" />
                            General Information
                        </h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label className="text-xs font-bold text-gray-400 uppercase tracking-wider">Description</label>
                                <p className="mt-1 text-gray-700">{aplikasi.deskripsi || 'No description provided.'}</p>
                            </div>
                            <div>
                                <label className="text-xs font-bold text-gray-400 uppercase tracking-wider">Type</label>
                                <p className="mt-1 capitalize text-gray-700 font-medium">{aplikasi.tipe}</p>
                            </div>
                            <div>
                                <label className="text-xs font-bold text-gray-400 uppercase tracking-wider">IP Local</label>
                                <p className="mt-1 font-mono text-gray-700">{aplikasi.ip_local || '-'}</p>
                            </div>
                        </div>

                        <div className="mt-8 pt-8 border-t border-gray-50 grid grid-cols-1 md:grid-cols-3 gap-6">
                            <a href={aplikasi.url_service} target="_blank" rel="noreferrer" className="flex items-center gap-3 p-3 rounded-xl bg-gray-50 hover:bg-blue-50 transition-colors group">
                                <Globe className="w-5 h-5 text-gray-400 group-hover:text-blue-600" />
                                <span className="text-sm font-medium text-gray-600 group-hover:text-blue-700">Service URL</span>
                            </a>
                            <a href={aplikasi.url_repository} target="_blank" rel="noreferrer" className="flex items-center gap-3 p-3 rounded-xl bg-gray-50 hover:bg-gray-200 transition-colors group">
                                <GitBranch className="w-5 h-5 text-gray-400 group-hover:text-gray-800" />
                                <span className="text-sm font-medium text-gray-600 group-hover:text-gray-900">Repository</span>
                            </a>
                            <a href={aplikasi.url_api_docs} target="_blank" rel="noreferrer" className="flex items-center gap-3 p-3 rounded-xl bg-gray-50 hover:bg-orange-50 transition-colors group">
                                <FileText className="w-5 h-5 text-gray-400 group-hover:text-orange-600" />
                                <span className="text-sm font-medium text-gray-600 group-hover:text-orange-700">API Docs</span>
                            </a>
                        </div>
                    </div>

                    {/* Services (if microservice) */}
                    {aplikasi.tipe === 'microservice' && (
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="p-6 border-b border-gray-100">
                                <h3 className="text-lg font-bold text-gray-800 flex items-center gap-2">
                                    <Server className="w-5 h-5 text-purple-600" />
                                    Sub-Services
                                </h3>
                            </div>
                            <Table 
                                columns={[
                                    { header: 'Service Name', key: 'nama', className: 'font-semibold' },
                                    { header: 'Type', render: (row) => <span className="capitalize">{row.tipe_service}</span> },
                                    { header: 'URL', render: (row) => <code className="text-xs text-blue-600">{row.url_service}</code> }
                                ]}
                                data={aplikasi.services || []}
                                loading={false}
                                emptyMessage="This microservice has no registered sub-services."
                            />
                        </div>
                    )}
                </div>

                {/* Right Column: Monitoring History */}
                <div className="space-y-8">
                    <div className="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h3 className="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                            <Activity className="w-5 h-5 text-green-600" />
                            Recent Checks
                        </h3>
                        <div className="space-y-4">
                            {(aplikasi.log_monitors || []).map((log) => (
                                <div key={log.id_log_monitor} className="flex items-center justify-between p-4 rounded-xl border border-gray-50 bg-gray-50/30">
                                    <div className="flex items-center gap-3">
                                        <StatusBadge status={log.status} />
                                        <div className="text-xs text-gray-400 font-mono">
                                            {log.http_status_code}
                                        </div>
                                    </div>
                                    <div className="text-right">
                                        <div className="text-sm font-bold text-gray-700 flex items-center gap-1 justify-end">
                                            <Clock className="w-3 h-3" />
                                            {log.response_time_ms}ms
                                        </div>
                                        <div className="text-[10px] text-gray-400">
                                            {new Date(log.checked_at).toLocaleTimeString()}
                                        </div>
                                    </div>
                                </div>
                            ))}
                            {(aplikasi.log_monitors || []).length === 0 && (
                                <p className="text-center text-gray-400 py-4 italic">No monitoring logs yet.</p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default AplikasiDetail;
