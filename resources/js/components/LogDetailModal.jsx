import React from 'react';
import Modal from './Modal';
import StatusBadge from './StatusBadge';
import { 
    Clock, 
    Globe, 
    Activity, 
    Server, 
    AppWindow, 
    Calendar,
    Hash,
    ExternalLink
} from 'lucide-react';

const LogDetailModal = ({ isOpen, onClose, log }) => {
    if (!log) return null;

    const isSuccess = log.http_status_code >= 200 && log.http_status_code < 300;

    return (
        <Modal 
            isOpen={isOpen} 
            onClose={onClose} 
            title="Monitoring Log Details"
            footer={
                <button 
                    onClick={onClose}
                    className="px-6 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition-all"
                >
                    Close
                </button>
            }
        >
            <div className="space-y-6">
                {/* Status Header */}
                <div className={`p-6 rounded-2xl flex items-center justify-between ${isSuccess ? 'bg-green-50' : 'bg-red-50'}`}>
                    <div className="flex items-center gap-4">
                        <div className={`p-3 rounded-xl ${isSuccess ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'}`}>
                            <Activity className="w-6 h-6" />
                        </div>
                        <div>
                            <p className="text-xs font-bold text-gray-400 uppercase tracking-wider">Health Status</p>
                            <StatusBadge status={log.status} />
                        </div>
                    </div>
                    <div className="text-right">
                        <p className="text-xs font-bold text-gray-400 uppercase tracking-wider">HTTP Code</p>
                        <p className={`text-2xl font-black ${isSuccess ? 'text-green-600' : 'text-red-600'}`}>
                            {log.http_status_code || '---'}
                        </p>
                    </div>
                </div>

                {/* Target Info */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                        <div className="flex items-center gap-2 mb-2">
                            <AppWindow className="w-4 h-4 text-blue-600" />
                            <span className="text-xs font-bold text-gray-400 uppercase">Application</span>
                        </div>
                        <p className="font-bold text-gray-800">{log.aplikasi?.nama || 'Unknown'}</p>
                    </div>
                    {log.service && (
                        <div className="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                            <div className="flex items-center gap-2 mb-2">
                                <Server className="w-4 h-4 text-purple-600" />
                                <span className="text-xs font-bold text-gray-400 uppercase">Service</span>
                            </div>
                            <p className="font-bold text-gray-800">{log.service.nama}</p>
                        </div>
                    )}
                </div>

                {/* Performance & URL */}
                <div className="space-y-4">
                    <div className="p-4 bg-white rounded-2xl border border-gray-100 shadow-sm">
                        <div className="flex items-center gap-2 mb-3">
                            <Globe className="w-4 h-4 text-gray-400" />
                            <span className="text-xs font-bold text-gray-400 uppercase">Target Endpoint</span>
                        </div>
                        <div className="flex items-center justify-between gap-4">
                            <code className="text-sm text-blue-600 font-mono break-all bg-blue-50/50 px-2 py-1 rounded">
                                {log.url}
                            </code>
                            <a 
                                href={log.url} 
                                target="_blank" 
                                rel="noreferrer"
                                className="p-2 hover:bg-gray-100 rounded-lg transition-colors text-gray-400 hover:text-blue-600"
                            >
                                <ExternalLink className="w-4 h-4" />
                            </a>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="p-4 bg-white rounded-2xl border border-gray-100 shadow-sm">
                            <div className="flex items-center gap-2 mb-2">
                                <Clock className="w-4 h-4 text-gray-400" />
                                <span className="text-xs font-bold text-gray-400 uppercase">Response Time</span>
                            </div>
                            <p className="text-lg font-bold text-gray-800">{log.response_time_ms}ms</p>
                        </div>
                        <div className="p-4 bg-white rounded-2xl border border-gray-100 shadow-sm">
                            <div className="flex items-center gap-2 mb-2">
                                <Calendar className="w-4 h-4 text-gray-400" />
                                <span className="text-xs font-bold text-gray-400 uppercase">Checked At</span>
                            </div>
                            <p className="text-sm font-bold text-gray-800">
                                {new Date(log.checked_at).toLocaleString()}
                            </p>
                        </div>
                    </div>
                </div>

                {/* Raw ID Info */}
                <div className="flex items-center gap-2 text-[10px] text-gray-300 font-mono justify-center">
                    <Hash className="w-3 h-3" />
                    LOG_ID: {log.id_log_monitor}
                </div>
            </div>
        </Modal>
    );
};

export default LogDetailModal;
