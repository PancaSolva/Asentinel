import React from 'react';
import { CheckCircle, AlertCircle } from 'lucide-react';

const StatusBadge = ({ status }) => {
    const isUp = status?.toUpperCase() === 'UP';
    
    return (
        <span className={`px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1.5 ${
            isUp ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
        }`}>
            {isUp ? <CheckCircle className="w-3.5 h-3.5" /> : <AlertCircle className="w-3.5 h-3.5" />}
            {status || 'UNKNOWN'}
        </span>
    );
};

export default StatusBadge;
