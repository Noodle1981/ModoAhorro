export function useFormatters() {
    const formatDate = (dateString) => {
        if (!dateString) return '-';
        try {
            const clean = String(dateString).split('T')[0];
            const parts = clean.split('-');
            if (parts.length === 3) {
                const [year, month, day] = parts;
                return `${day}/${month}/${year.length === 4 ? year.slice(-2) : year}`;
            }
            return clean;
        } catch {
            return '-';
        }
    };

    const formatDateFull = (dateString) => {
        if (!dateString) return '-';
        try {
            const clean = String(dateString).split('T')[0];
            const parts = clean.split('-');
            if (parts.length === 3) {
                const [year, month, day] = parts;
                return `${day}/${month}/${year}`;
            }
            return clean;
        } catch {
            return '-';
        }
    };

    const formatCurrency = (val) => {
        const num = Number(val) || 0;
        return new Intl.NumberFormat('es-AR', {
            style: 'currency',
            currency: 'ARS',
            maximumFractionDigits: 0
        }).format(num);
    };

    const formatMoney = formatCurrency;

    const formatKwh = (val, decimals = 1) => {
        return Number(val || 0).toFixed(decimals);
    };

    const formatNumber = (val, decimals = 0) => {
        return new Intl.NumberFormat('es-AR', {
            maximumFractionDigits: decimals
        }).format(Number(val) || 0);
    };

    const formatPercent = (val, decimals = 1) => {
        return `${Number(val || 0).toFixed(decimals)}%`;
    };

    return {
        formatDate,
        formatDateFull,
        formatCurrency,
        formatMoney,
        formatKwh,
        formatNumber,
        formatPercent
    };
}
