/**
 * revisor-index.js
 * Shows/hides conditional fields based on the selected analysis type.
 */
document.addEventListener('DOMContentLoaded', () => {
    const typeSelect = document.getElementById('type');
    if (!typeSelect) return;

    const fieldDoc = document.getElementById('fieldDocumento');
    const fieldMin = document.getElementById('fieldMinuta');
    const fieldQ   = document.getElementById('fieldPergunta');

    function updateFields() {
        const val = typeSelect.value;
        if (fieldDoc) fieldDoc.style.display = (val === 'analise_documento' || val === 'resumo_caso') ? '' : 'none';
        if (fieldMin) fieldMin.style.display = (val === 'revisao_minuta') ? '' : 'none';
        if (fieldQ)   fieldQ.style.display   = (val === 'pesquisa_juridica') ? '' : 'none';
    }

    typeSelect.addEventListener('change', updateFields);
    updateFields();
});
