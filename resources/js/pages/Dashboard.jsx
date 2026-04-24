import React, { useState, useEffect } from 'react';
import api from '../api';
import { 
    Activity, AppWindow, Server, AlertCircle, CheckCircle, 
    Clock, TrendingUp, RefreshCcw, LayoutDashboard, Eye
} from 'lucide-react';
import StatusBadge from '../components/StatusBadge';
import Table from '../components/Table';
import LogDetailModal from '../components/LogDetailModal';

const Dashboard = () => {
    const [stats, setStats] = useState({
        totalAplikasi: 0, totalServices: 0, totalUp: 0, totalDown: 0,
    });
    const [monitoringData, setMonitoringData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [checking, setChecking] = useState(false);
    const [selectedLog, setSelectedLog] = useState(null);
    const [showDetailModal, setShowDetailModal] = useState(false);

    useEffect(() => {
        fetchDashboardData();
        const intervalId = setInterval(() => fetchDashboardData(false), 20000);
        return () => clearInterval(intervalId);
    }, []);

    const fetchDashboardData = async (showLoading = true) => {
        try {
            if (showLoading) setLoading(true);
            const [statsRes, monitorRes] = await Promise.all([
                api.get('/dashboard-stats'),
                api.get('/monitoring-logs')
            ]);
            setStats(statsRes.data.data);
            setMonitoringData(monitorRes.data.data);
        } catch (error) {
        } finally {
            if (showLoading) setLoading(false);
        }
    };

    const runMonitoringCheck = async () => {
        try {
            setChecking(true);
            await api.post('/run-monitoring');
            await fetchDashboardData(false);
        } catch (error) {
        } finally {
            setChecking(false);
        }
    };

    const handleLogClick = (log) => {
        setSelectedLog(log);
        setShowDetailModal(true);
    };

    const statsCards = [
        { label: 'Total Apps', value: stats.totalAplikasi, icon: <AppWindow className="w-6 h-6 text-blue-600" />, bg: 'bg-blue-50', border: 'border-blue-100' },
        { label: 'Total Services', value: stats.totalServices, icon: <Server className="w-6 h-6 text-purple-600" />, bg: 'bg-purple-50', border: 'border-purple-100' },
        { label: 'Status UP', value: stats.totalUp, icon: <CheckCircle className="w-6 h-6 text-green-600" />, bg: 'bg-green-50', border: 'border-green-100' },
        { label: 'Status DOWN', value: stats.totalDown, icon: <AlertCircle className="w-6 h-6 text-red-600" />, bg: 'bg-red-50', border: 'border-red-100' },
    ];

    const columns = [
        { 
            header: 'Target', 
            render: (row) => (
                <div className="flex items-center gap-3">
                    <div className={`w-9 h-9 rounded-lg flex items-center justify-center ${row.service ? 'bg-purple-50 text-purple-600' : 'bg-blue-50 text-blue-600'}`}>
                        {row.service ? <Server className="w-5 h-5" /> : <AppWindow className="w-5 h-5" />}
                    </div>
                    <div>
                        <div className="font-bold text-gray-800">{row.service ? row.service.nama : row.aplikasi?.nama}</div>
                        {row.service && <div className="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Part of {row.aplikasi?.nama}</div>}
                    </div>
                </div>
            )
        },
        { 
            header: 'Endpoint', 
            render: (row) => (
                <code className="text-[11px] bg-gray-50 px-2 py-1 rounded-md text-gray-500 font-mono border border-gray-100 block truncate max-w-[200px]">
                    {row.url}
                </code>
            )
        },
        { header: 'Status', render: (row) => <StatusBadge status={row.status} /> },
        { 
            header: 'Code', 
            render: (row) => (
                <span className={`font-mono text-sm font-bold ${row.http_status_code >= 200 && row.http_status_code < 300 ? 'text-green-600' : 'text-red-600'}`}>
                    {row.http_status_code || '---'}
                </span>
            )
        },
        { 
            header: 'Response', 
            render: (row) => (
                <div className="flex items-center gap-1.5 text-sm font-medium text-gray-600">
                    <Clock className="w-3.5 h-3.5 text-gray-400" />
                    {row.response_time_ms}ms
                </div>
            )
        },
        { 
            header: 'Last Checked', 
            render: (row) => (
                <div className="text-xs text-gray-500">
                    <div className="font-medium text-gray-700">{new Date(row.checked_at).toLocaleTimeString()}</div>
                    <div className="text-[10px]">{new Date(row.checked_at).toLocaleDateString()}</div>
                </div>
            )
        },
        {
            header: '',
            className: 'text-right',
            render: (row) => (
                <button 
                    onClick={() => handleLogClick(row)}
                    className="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                >
                    <Eye className="w-5 h-5" />
                </button>
            )
        }
    ];

    return (
        <div className="space-y-8">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 className="text-3xl font-extrabold text-gray-800 tracking-tight flex items-center gap-3">
                        <LayoutDashboard className="w-8 h-8 text-blue-600" />
                        System Overview
                    </h2>
                    <p className="text-gray-500 font-medium">Real-time health status of your infrastructure</p>
                </div>
                <div className="flex items-center gap-3">
                    <button 
                        onClick={runMonitoringCheck}
                        disabled={checking}
                        className={`px-6 py-2.5 rounded-xl font-bold text-sm transition-all flex items-center gap-2 shadow-lg ${
                            checking 
                            ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                            : 'bg-blue-600 text-white hover:bg-blue-700 shadow-blue-100'
                        }`}
                    >
                        <RefreshCcw className={`w-4 h-4 ${checking ? 'animate-spin' : ''}`} />
                        {checking ? 'Checking...' : 'Trigger Check'}
                    </button>
                </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {statsCards.map((stat, idx) => (
                    <div key={idx} className={`bg-white p-6 rounded-2xl shadow-sm border ${stat.border} flex items-center gap-5 transition-transform hover:scale-[1.02]`}>
                        <div className={`w-14 h-14 rounded-2xl ${stat.bg} flex items-center justify-center shadow-inner`}>
                            {stat.icon}
                        </div>
                        <div>
                            <p className="text-xs font-bold text-gray-400 uppercase tracking-widest">{stat.label}</p>
                            <h3 className="text-3xl font-black text-gray-800 tracking-tight">{stat.value}</h3>
                        </div>
                    </div>
                ))}
            </div>

            <div className="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                <div className="p-8 border-b border-gray-50 flex items-center justify-between bg-gradient-to-r from-white to-gray-50/50">
                    <div className="flex items-center gap-3">
                        <div className="p-2 bg-blue-600 rounded-lg shadow-lg shadow-blue-200">
                            <Activity className="w-5 h-5 text-white" />
                        </div>
                        <h2 className="text-xl font-black text-gray-800 tracking-tight">Monitoring Feed</h2>
                    </div>
                    <div className="flex items-center gap-2 px-4 py-1.5 bg-green-50 text-green-700 rounded-full text-xs font-bold">
                        <div className="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        Auto Refresh
                    </div>
                </div>
                <Table 
                    columns={columns}
                    data={monitoringData}
                    loading={loading}
                    emptyMessage="Infrastructure looks quiet. No monitoring data available."
                    rowClassName=""
                />
            </div>

            <LogDetailModal 
                isOpen={showDetailModal}
                onClose={() => setShowDetailModal(false)}
                log={selectedLog}
            />
        </div>
    );
};

export default Dashboard;
