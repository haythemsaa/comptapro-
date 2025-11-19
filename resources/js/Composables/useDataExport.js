import { ref } from 'vue';

/**
 * Composable pour l'export de données en Excel/CSV
 * Supporte: CSV, Excel, JSON, copie vers presse-papier
 */
export function useDataExport() {
    const isExporting = ref(false);

    /**
     * Convertir des données en CSV
     */
    const convertToCSV = (data, columns = null) => {
        if (!data || data.length === 0) {
            return '';
        }

        // Déterminer les colonnes
        const cols = columns || Object.keys(data[0]);

        // Créer l'en-tête
        const header = cols.join(',');

        // Créer les lignes
        const rows = data.map(row => {
            return cols.map(col => {
                let value = row[col];

                // Gérer les valeurs nulles/undefined
                if (value === null || value === undefined) {
                    value = '';
                }

                // Convertir en string
                value = String(value);

                // Échapper les guillemets et virgules
                if (value.includes(',') || value.includes('"') || value.includes('\n')) {
                    value = `"${value.replace(/"/g, '""')}"`;
                }

                return value;
            }).join(',');
        });

        return [header, ...rows].join('\n');
    };

    /**
     * Télécharger un fichier CSV
     */
    const downloadCSV = (data, filename = 'export.csv', columns = null) => {
        isExporting.value = true;

        try {
            const csv = convertToCSV(data, columns);
            const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);

            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            window.$toast?.success(`Fichier ${filename} téléchargé`, 'Export réussi');
            return true;
        } catch (error) {
            console.error('Error exporting CSV:', error);
            window.$toast?.error('Erreur lors de l\'export CSV', 'Erreur');
            return false;
        } finally {
            isExporting.value = false;
        }
    };

    /**
     * Convertir des données en Excel (format basique)
     */
    const convertToExcel = (data, columns = null) => {
        if (!data || data.length === 0) {
            return '';
        }

        const cols = columns || Object.keys(data[0]);

        let html = '<table>';

        // En-tête
        html += '<thead><tr>';
        cols.forEach(col => {
            html += `<th>${col}</th>`;
        });
        html += '</tr></thead>';

        // Corps
        html += '<tbody>';
        data.forEach(row => {
            html += '<tr>';
            cols.forEach(col => {
                const value = row[col] !== null && row[col] !== undefined ? row[col] : '';
                html += `<td>${value}</td>`;
            });
            html += '</tr>';
        });
        html += '</tbody>';

        html += '</table>';

        return html;
    };

    /**
     * Télécharger un fichier Excel (format HTML compatible)
     */
    const downloadExcel = (data, filename = 'export.xls', columns = null) => {
        isExporting.value = true;

        try {
            const html = convertToExcel(data, columns);
            const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);

            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            window.$toast?.success(`Fichier ${filename} téléchargé`, 'Export réussi');
            return true;
        } catch (error) {
            console.error('Error exporting Excel:', error);
            window.$toast?.error('Erreur lors de l\'export Excel', 'Erreur');
            return false;
        } finally {
            isExporting.value = false;
        }
    };

    /**
     * Télécharger en JSON
     */
    const downloadJSON = (data, filename = 'export.json', pretty = true) => {
        isExporting.value = true;

        try {
            const json = pretty ? JSON.stringify(data, null, 2) : JSON.stringify(data);
            const blob = new Blob([json], { type: 'application/json' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);

            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            window.$toast?.success(`Fichier ${filename} téléchargé`, 'Export réussi');
            return true;
        } catch (error) {
            console.error('Error exporting JSON:', error);
            window.$toast?.error('Erreur lors de l\'export JSON', 'Erreur');
            return false;
        } finally {
            isExporting.value = false;
        }
    };

    /**
     * Copier en CSV vers le presse-papier
     */
    const copyToClipboard = async (data, columns = null) => {
        try {
            const csv = convertToCSV(data, columns);

            if (navigator.clipboard && navigator.clipboard.writeText) {
                await navigator.clipboard.writeText(csv);
            } else {
                // Fallback pour navigateurs anciens
                const textarea = document.createElement('textarea');
                textarea.value = csv;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
            }

            window.$toast?.success('Données copiées dans le presse-papier', 'Copié');
            return true;
        } catch (error) {
            console.error('Error copying to clipboard:', error);
            window.$toast?.error('Erreur lors de la copie', 'Erreur');
            return false;
        }
    };

    /**
     * Exporter avec options multiples
     */
    const exportData = (data, options = {}) => {
        const {
            format = 'csv',
            filename = `export-${Date.now()}`,
            columns = null,
            pretty = true
        } = options;

        switch (format.toLowerCase()) {
            case 'csv':
                return downloadCSV(data, `${filename}.csv`, columns);
            case 'excel':
            case 'xls':
                return downloadExcel(data, `${filename}.xls`, columns);
            case 'json':
                return downloadJSON(data, `${filename}.json`, pretty);
            case 'clipboard':
                return copyToClipboard(data, columns);
            default:
                window.$toast?.error(`Format ${format} non supporté`, 'Erreur');
                return false;
        }
    };

    /**
     * Exporter une table HTML existante
     */
    const exportTableToCSV = (tableElement, filename = 'table-export.csv') => {
        isExporting.value = true;

        try {
            const rows = tableElement.querySelectorAll('tr');
            const csvData = [];

            rows.forEach(row => {
                const cols = row.querySelectorAll('td, th');
                const rowData = [];

                cols.forEach(col => {
                    let text = col.textContent.trim();

                    // Échapper les guillemets et virgules
                    if (text.includes(',') || text.includes('"') || text.includes('\n')) {
                        text = `"${text.replace(/"/g, '""')}"`;
                    }

                    rowData.push(text);
                });

                csvData.push(rowData.join(','));
            });

            const csv = csvData.join('\n');
            const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);

            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            window.$toast?.success(`Table exportée: ${filename}`, 'Export réussi');
            return true;
        } catch (error) {
            console.error('Error exporting table:', error);
            window.$toast?.error('Erreur lors de l\'export de la table', 'Erreur');
            return false;
        } finally {
            isExporting.value = false;
        }
    };

    /**
     * Imprimer des données
     */
    const printData = (data, title = 'Données', columns = null) => {
        const html = convertToExcel(data, columns);

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>${title}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    table { border-collapse: collapse; width: 100%; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; font-weight: bold; }
                    tr:nth-child(even) { background-color: #f9f9f9; }
                    h1 { color: #333; }
                    @media print {
                        body { padding: 0; }
                    }
                </style>
            </head>
            <body>
                <h1>${title}</h1>
                ${html}
            </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.focus();

        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);

        window.$toast?.success('Document envoyé à l\'imprimante', 'Impression');
    };

    return {
        isExporting,
        downloadCSV,
        downloadExcel,
        downloadJSON,
        copyToClipboard,
        exportData,
        exportTableToCSV,
        printData
    };
}
