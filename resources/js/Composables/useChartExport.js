/**
 * Composable pour exporter les graphiques Chart.js
 */
export function useChartExport() {
    /**
     * Export a chart as PNG image
     * @param {HTMLCanvasElement} canvas - The canvas element from Chart.js
     * @param {string} filename - The filename for the download
     */
    const exportChartAsPNG = (canvas, filename = 'chart.png') => {
        if (!canvas) {
            console.error('Canvas element not found');
            return;
        }

        try {
            const url = canvas.toDataURL('image/png');
            const link = document.createElement('a');
            link.download = filename;
            link.href = url;
            link.click();

            window.$toast?.success('Graphique exporté avec succès', 'Export réussi');
        } catch (error) {
            console.error('Error exporting chart:', error);
            window.$toast?.error('Erreur lors de l\'export du graphique', 'Erreur');
        }
    };

    /**
     * Export a chart as JPEG image
     * @param {HTMLCanvasElement} canvas - The canvas element from Chart.js
     * @param {string} filename - The filename for the download
     */
    const exportChartAsJPEG = (canvas, filename = 'chart.jpg') => {
        if (!canvas) {
            console.error('Canvas element not found');
            return;
        }

        try {
            const url = canvas.toDataURL('image/jpeg', 0.95);
            const link = document.createElement('a');
            link.download = filename;
            link.href = url;
            link.click();

            window.$toast?.success('Graphique exporté avec succès', 'Export réussi');
        } catch (error) {
            console.error('Error exporting chart:', error);
            window.$toast?.error('Erreur lors de l\'export du graphique', 'Erreur');
        }
    };

    /**
     * Print chart
     * @param {HTMLCanvasElement} canvas - The canvas element from Chart.js
     */
    const printChart = (canvas) => {
        if (!canvas) {
            console.error('Canvas element not found');
            return;
        }

        try {
            const dataUrl = canvas.toDataURL();
            const windowContent = '<!DOCTYPE html>';
            const printWin = window.open('', '', 'width=800,height=600');

            printWin.document.write(windowContent);
            printWin.document.write('<html><head><title>Impression du graphique</title>');
            printWin.document.write('<style>body{margin:0;padding:20px;}img{max-width:100%;height:auto;}</style>');
            printWin.document.write('</head><body>');
            printWin.document.write('<img src="' + dataUrl + '">');
            printWin.document.write('</body></html>');

            printWin.document.close();
            printWin.focus();

            setTimeout(() => {
                printWin.print();
                printWin.close();
            }, 250);

        } catch (error) {
            console.error('Error printing chart:', error);
            window.$toast?.error('Erreur lors de l\'impression', 'Erreur');
        }
    };

    /**
     * Copy chart to clipboard
     * @param {HTMLCanvasElement} canvas - The canvas element from Chart.js
     */
    const copyChartToClipboard = async (canvas) => {
        if (!canvas) {
            console.error('Canvas element not found');
            return;
        }

        try {
            canvas.toBlob(async (blob) => {
                if (blob) {
                    try {
                        await navigator.clipboard.write([
                            new ClipboardItem({ 'image/png': blob })
                        ]);
                        window.$toast?.success('Graphique copié dans le presse-papier', 'Succès');
                    } catch (err) {
                        console.error('Failed to copy:', err);
                        window.$toast?.error('Erreur lors de la copie', 'Erreur');
                    }
                }
            });
        } catch (error) {
            console.error('Error copying chart:', error);
            window.$toast?.error('Erreur lors de la copie', 'Erreur');
        }
    };

    /**
     * Download PDF report
     * @param {string} reportType - Type of report (profit-loss, vat-report, balance-sheet)
     */
    const downloadPDFReport = (reportType) => {
        const routes = {
            'profit-loss': '/reports/profit-loss/pdf',
            'vat-report': '/reports/vat/pdf',
            'balance-sheet': '/reports/balance-sheet/pdf'
        };

        const url = routes[reportType];

        if (!url) {
            console.error('Invalid report type:', reportType);
            window.$toast?.error('Type de rapport invalide', 'Erreur');
            return;
        }

        try {
            window.open(url, '_blank');
            window.$toast?.success('Génération du PDF en cours...', 'Export PDF');
        } catch (error) {
            console.error('Error downloading PDF:', error);
            window.$toast?.error('Erreur lors du téléchargement du PDF', 'Erreur');
        }
    };

    return {
        exportChartAsPNG,
        exportChartAsJPEG,
        printChart,
        copyChartToClipboard,
        downloadPDFReport
    };
}
